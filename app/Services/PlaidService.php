<?php

namespace App\Services;

use App\Models\Merchant;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class PlaidService
{
    private Client $http;
    private string $env;
    private string $clientId;
    private string $secret;
    private ?string $redirectUri;

    public function __construct()
    {
        $this->env = config('services.plaid.env', env('PLAID_ENV', 'sandbox'));
        $this->clientId = config('services.plaid.client_id', env('PLAID_CLIENT_ID', ''));
        $this->secret = config('services.plaid.secret', env('PLAID_SECRET', ''));
        $this->redirectUri = config('services.plaid.redirect_uri', env('PLAID_REDIRECT_URI'));

        $base = match ($this->env) {
            'production' => 'https://production.plaid.com',
            'development' => 'https://development.plaid.com',
            default => 'https://sandbox.plaid.com',
        };

        $this->http = new Client([
            'base_uri' => $base,
            'timeout' => 25,
        ]);
    }

    private function post(string $path, array $body): array
    {
        $payload = array_merge($body, [
            'client_id' => $this->clientId,
            'secret' => $this->secret,
        ]);

        $res = $this->http->post($path, [
            'json' => $payload,
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        return json_decode((string) $res->getBody(), true);
    }

    public function createLinkTokenForMerchant(Merchant $merchant): string
    {
        $body = [
            'client_name' => config('app.name', 'ECHECK DIRECT'),
            'language' => 'en',
            'country_codes' => [config('services.plaid.allowed_country', 'US')],
            'user' => [
                'client_user_id' => 'merchant_' . $merchant->id,
            ],
            'products' => ['auth', 'identity'],
        ];
        
        // IMPORTANT: Only include redirect_uri if it exists
        if (!empty($this->redirectUri)) {
            $body['redirect_uri'] = $this->redirectUri;
        }
        
        $resp = $this->post('/link/token/create', $body);
        
        return $resp['link_token'];
    }

    public function createLinkTokenForCustomer(Merchant $merchant): string
    {
        $body = [
            'client_name' => $merchant->name . ' Payments',
            'language' => 'en',
            'country_codes' => [config('services.plaid.allowed_country', 'US')],
            'user' => [
                'client_user_id' => 'customer_checkout_' . Str::uuid(),
            ],
            'products' => ['auth'],
        ];
        // dd($body);
        // ONLY include redirect_uri if set
        if (!empty($this->redirectUri)) {
            $body['redirect_uri'] = $this->redirectUri;
        }
        
        $resp = $this->post('/link/token/create', $body);
        
        return $resp['link_token'];
        
    }

    public function exchangePublicToken(string $publicToken): array
    {
        return $this->post('/item/public_token/exchange', [
            'public_token' => $publicToken,
        ]);
    }

    public function authGet(string $accessToken): array
    {
        return $this->post('/auth/get', [
            'access_token' => $accessToken,
        ]);
    }

    public function identityGet(string $accessToken): array
    {
        // Requires Identity product enabled on your Plaid account.
        return $this->post('/identity/get', [
            'access_token' => $accessToken,
        ]);
    }

    /**
     * Returns selected account auth data including routing/account.
     * Plaid auth/get response includes numbers->ach[] which correspond to accounts.
     */
    public function findAuthAccount(array $auth, string $accountId): ?array
    {
        $accountsById = [];
        foreach (($auth['accounts'] ?? []) as $acct) {
            $accountsById[$acct['account_id']] = $acct;
        }

        $base = $accountsById[$accountId] ?? null;
        if (!$base) return null;

        $numbers = $auth['numbers']['ach'] ?? [];
        $match = null;
        foreach ($numbers as $n) {
            if (($n['account_id'] ?? null) === $accountId) {
                $match = $n;
                break;
            }
        }
        if (!$match) return null;

        return [
            'account_id' => $accountId,
            'name' => $base['name'] ?? null,
            'mask' => $base['mask'] ?? null,
            'type' => $base['type'] ?? null,
            'subtype' => $base['subtype'] ?? null,
            'routing' => $match['routing'] ?? null,
            'account' => $match['account'] ?? null,
        ];
    }

    /**
     * Auto-approval verification rules (practical, simple).
     * - Must be checking
     * - Must have routing + account number
     * - Identity owner name should roughly match merchant name (best-effort)
     *
     * This is NOT a replacement for KYC/KYB if you are moving money.
     */
    public function verifyMerchantAutoApprove(Merchant $merchant, array $selectedAuth, array $identity): array
    {
        $reasons = [];

        $subtype = strtolower((string)($selectedAuth['subtype'] ?? ''));
        if ($subtype !== 'checking') {
            $reasons[] = 'Selected account is not checking.';
        }

        if (empty($selectedAuth['routing']) || empty($selectedAuth['account'])) {
            $reasons[] = 'Missing routing/account numbers from Plaid Auth.';
        }

        // Identity signals (best-effort)
        $ownerNames = [];
        foreach (($identity['accounts'] ?? []) as $acct) {
            if (($acct['account_id'] ?? null) === ($selectedAuth['account_id'] ?? null)) {
                foreach (($acct['owners'] ?? []) as $owner) {
                    if (!empty($owner['names'])) {
                        $ownerNames = array_merge($ownerNames, $owner['names']);
                    }
                }
            }
        }

        $merchantName = $this->normalizeName($merchant->name);
        $nameMatch = false;
        foreach ($ownerNames as $n) {
            if ($this->roughNameMatch($merchantName, $this->normalizeName($n))) {
                $nameMatch = true;
                break;
            }
        }

        if (!$nameMatch) {
            // Some institutions return limited identity; don't hard-fail if we at least have Auth.
            $reasons[] = 'Owner name did not match merchant name (or identity not available).';
        }

        // Decision: approve if hard requirements pass (checking + routing/account).
        $hardFail = in_array('Selected account is not checking.', $reasons, true)
            || in_array('Missing routing/account numbers from Plaid Auth.', $reasons, true);

        return [
            'status' => $hardFail ? 'failed' : 'verified',
            'checked_at' => now()->toISOString(),
            'owner_names' => $ownerNames,
            'rules' => [
                'checking_required' => true,
                'routing_account_required' => true,
                'name_match_best_effort' => true,
            ],
            'reasons' => $reasons,
            'note' => 'Auto-approval uses simple rules. For real-world money movement you will still need KYC/KYB via your processor/bank sponsor.',
        ];
    }

    private function normalizeName(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    private function roughNameMatch(string $merchantName, string $ownerName): bool
    {
        if ($merchantName === '' || $ownerName === '') return false;
        // simple token overlap
        $m = array_filter(explode(' ', $merchantName));
        $o = array_filter(explode(' ', $ownerName));
        $common = array_intersect($m, $o);

        // match if at least 2 tokens overlap OR merchant token appears in owner
        if (count($common) >= 2) return true;

        foreach ($m as $t) {
            if (strlen($t) >= 4 && str_contains($ownerName, $t)) {
                return true;
            }
        }
        return false;
    }

    public function balanceGet(string $accessToken): array
    {
        return $this->post('/accounts/balance/get', [
            'access_token' => $accessToken,
        ]);
    }

    public function findBalanceForAccount(array $balanceResp, string $accountId): ?array
    {
        foreach (($balanceResp['accounts'] ?? []) as $acct) {
            if (($acct['account_id'] ?? null) === $accountId) {
                return [
                    'available' => $acct['balances']['available'] ?? null,
                    'current' => $acct['balances']['current'] ?? null,
                    'iso_currency_code' => $acct['balances']['iso_currency_code'] ?? null,
                ];
            }
        }
        return null;
    }
    
    public function createLinkTokenGeneric(array $body): string
    {
        $resp = $this->post('/link/token/create', $body);
        return $resp['link_token'];
    }

    public function extractOwnerAndAddress(array $identity, string $accountId): array
    {
        $names = [];
        $addressLine1 = null;
        $addressLine2 = null;

        foreach (($identity['accounts'] ?? []) as $acct) {
            if (($acct['account_id'] ?? null) !== $accountId) continue;

            foreach (($acct['owners'] ?? []) as $owner) {
                // Names
                foreach (($owner['names'] ?? []) as $n) {
                    if ($n) $names[] = $n;
                }

                // Addresses
                foreach (($owner['addresses'] ?? []) as $addr) {
                    $data = $addr['data'] ?? null;
                    if (!$data) continue;

                    $street = trim((string)($data['street'] ?? ''));
                    $city   = trim((string)($data['city'] ?? ''));
                    $region = trim((string)($data['region'] ?? ''));
                    $postal = trim((string)($data['postal_code'] ?? ''));
                    $country= trim((string)($data['country'] ?? ''));

                    if ($street !== '') {
                        $addressLine1 = $street;
                        $addressLine2 = trim($city.' '.$region.' '.$postal.' '.$country);
                        $addressLine2 = preg_replace('/\s+/', ' ', $addressLine2);
                        break 2; // take first good address
                    }
                }
            }

            break; // found account
        }

        $names = array_values(array_unique(array_filter($names)));

        return [
            'owner_name'    => $names[0] ?? null,
            'owner_names'   => $names,
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
        ];
    }

    public function getOwnerFromIdentity(array $identity, string $accountId): array
    {
        $ownerName = null;
        $address1 = null;
        $address2 = null;

        foreach (($identity['accounts'] ?? []) as $acct) {
            if (($acct['account_id'] ?? null) !== $accountId) continue;

            foreach (($acct['owners'] ?? []) as $owner) {
                // name
                if (!empty($owner['names'][0])) {
                    $ownerName = $owner['names'][0];
                }

                // address (best effort)
                $addr = $owner['addresses'][0]['data'] ?? null;
                if ($addr) {
                    $street = trim((string)($addr['street'] ?? ''));
                    $city   = trim((string)($addr['city'] ?? ''));
                    $region = trim((string)($addr['region'] ?? ''));
                    $postal = trim((string)($addr['postal_code'] ?? ''));
                    $country= trim((string)($addr['country'] ?? ''));

                    $address1 = $street ?: null;

                    $line2 = trim("$city $region $postal $country");
                    $line2 = preg_replace('/\s+/', ' ', $line2);
                    $address2 = $line2 ?: null;
                }

                break 2; // first owner is enough
            }
        }

        return [
            'name' => $ownerName,
            'address1' => $address1,
            'address2' => $address2,
        ];
    }
    
    public function itemGet(string $accessToken): array
    {
        return $this->post('/item/get', [
            'access_token' => $accessToken,
        ]);
    }

    public function institutionGetById(string $institutionId, array $countryCodes = ['US']): array
    {
        return $this->post('/institutions/get_by_id', [
            'institution_id' => $institutionId,
            'country_codes'  => $countryCodes,
            'options' => [
                'include_optional_metadata' => true,
            ],
        ]);
    }

    public function getInstitutionName(string $accessToken): ?string
    {
        $item = $this->itemGet($accessToken);
        $institutionId = $item['item']['institution_id'] ?? null;
        if (!$institutionId) return null;

        $inst = $this->institutionGetById($institutionId, [env('PLAID_ALLOWED_COUNTRY', 'US')]);
        return $inst['institution']['name'] ?? null;
    }

    public function customerNameMatchesIdentity(string $enteredName, array|string|null $identityOwnerNames): bool
    {
        // Normalize to array
        if (is_string($identityOwnerNames)) {
            $identityOwnerNames = [$identityOwnerNames];
        } elseif (!is_array($identityOwnerNames)) {
            $identityOwnerNames = [];
        }

        $identityOwnerNames = array_values(array_filter($identityOwnerNames, fn($n) => is_string($n) && trim($n) !== ''));

        if (trim($enteredName) === '' || empty($identityOwnerNames)) {
            return false;
        }

        $entered = $this->normalizeName($enteredName);

        foreach ($identityOwnerNames as $n) {
            if ($this->roughNameMatch($entered, $this->normalizeName($n))) {
                return true;
            }
        }

        return false;
    }

}
