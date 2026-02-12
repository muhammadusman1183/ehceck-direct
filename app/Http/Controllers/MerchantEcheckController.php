<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CustomerPayment;
use App\Models\Echeck;
use App\Models\Merchant;
use App\Services\PlaidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;



class MerchantEcheckController extends Controller
{
    private function merchantId(Request $request): int
    {
        return (int) $request->session()->get('merchant_id');
    }

    public function index(Request $request)
    {
        $merchantId = $this->merchantId($request);

        $status = $request->query('status'); // pending|cleared|rejected|null

        $q = CustomerPayment::where('merchant_id', $merchantId)->latest();

        if (in_array($status, ['pending','cleared','rejected'], true)) {
            $q->where('status', $status);
        }

        $payments = $q->paginate(25);

        return view('merchant.echecks.index', compact('payments','status'));
    }

    private function authedMerchant(Request $request): Merchant
    {
        return Merchant::findOrFail($request->session()->get('merchant_id'));
    }
    public function create(Request $request)
    {
        $merchant = $this->authedMerchant($request);

        // sidebar counts (optional)
        $statusCounts = [
            'all' => Echeck::where('merchant_id', $merchant->id)->count(),
            'pending' => Echeck::where('merchant_id', $merchant->id)->where('status','pending')->count(),
            'cleared' => Echeck::where('merchant_id', $merchant->id)->where('status','cleared')->count(),
            'rejected' => Echeck::where('merchant_id', $merchant->id)->where('status','rejected')->count(),
        ];

        $recent = Echeck::where('merchant_id', $merchant->id)->latest()->limit(20)->get();

        return view('merchant.echeck.create', compact('merchant','statusCounts','recent'));
    }

    public function linkToken(Request $request, PlaidService $plaid)
    {
        $merchant = $this->authedMerchant($request);

        try {
            $linkToken = $plaid->createLinkTokenForMerchant($merchant);
            return response()->json(['link_token' => $linkToken]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // public function store(Request $request, PlaidService $plaid)
    // {
    //     $merchant = $this->authedMerchant($request);

    //     $data = Validator::make($request->all(), [
    //         'customer_name' => ['required','string','max:255'],
    //         'customer_email' => ['nullable','email','max:255'],
    //         'customer_phone' => ['nullable','string','max:30'],
    //         'amount' => ['required','numeric','min:0.01'],

    //         // Plaid outputs
    //         'public_token' => ['required','string'],
    //         'account_id' => ['required','string'],
    //     ])->validate();

    //     // Exchange token
    //     $exchange = $plaid->exchangePublicToken($data['public_token']);
    //     $accessToken = $exchange['access_token'] ?? null;
    //     $itemId = $exchange['item_id'] ?? null;

    //     // Pull auth + identity (optional)
    //     $auth = $plaid->authGet($accessToken);
    //     $identity = $plaid->identityGet($accessToken);

    //     $selected = $plaid->findAuthAccount($auth, $data['account_id']);
    //     if (!$selected) {
    //         return back()->with('error', 'Account not found in Plaid auth response.');
    //     }

    //     // Best-effort verification rules (your existing helper)
    //     $verification = $plaid->verifyMerchantAutoApprove($merchant, $selected, $identity);

    //     $routing = $selected['routing'] ?? null;
    //     $acct = $selected['account'] ?? null;
    //     $last4 = $acct ? substr($acct, -4) : null;

    //     $echeck = Echeck::create([
    //         'merchant_id' => $merchant->id,

    //         'customer_name' => $data['customer_name'],
    //         'customer_email' => $data['customer_email'] ?? null,
    //         'customer_phone' => $data['customer_phone'] ?? null,

    //         'amount' => $data['amount'],

    //         'plaid_item_id' => $itemId,
    //         'plaid_access_token' => $accessToken,
    //         'plaid_account_id' => $data['account_id'],

    //         'bank_name' => $selected['name'] ?? null,
    //         'bank_mask' => $selected['mask'] ?? null,
    //         'account_subtype' => $selected['subtype'] ?? null,

    //         'routing_number' => $routing,
    //         'account_last4' => $last4,

    //         // status now is pending; you can flip to processing/cleared later with ACH provider
    //         'status' => $verification['status'] === 'verified' ? 'pending' : 'rejected',
    //         'verification_json' => $verification,
    //     ]);

    //     return redirect()
    //         ->route('merchant.echeck.create')
    //         ->with('success', 'eCheck created successfully. Status: '.$echeck->status);
    // }

    public function store(Request $request, PlaidService $plaid)
    {
        $merchant = $this->authedMerchant($request);
        
        $data = Validator::make($request->all(), [
            'customer_name'  => ['required','string','max:255'],
            'customer_email' => ['nullable','email','max:255'],
            'customer_phone' => ['nullable','string','max:30'],
            'amount'         => ['required','numeric','min:0.01'],

            // Plaid outputs
            'public_token'   => ['required','string'],
            'account_id'     => ['required','string'],
        ])->validate();

        // Exchange token
        $exchange    = $plaid->exchangePublicToken($data['public_token']);
        $accessToken = $exchange['access_token'] ?? null;
        $itemId      = $exchange['item_id'] ?? null;

        if (!$accessToken) {
            return back()->with('error', 'Plaid exchange failed: missing access token.');
        }
        
        $institutionName = $plaid->getInstitutionName($accessToken);

        // Pull auth + identity (optional)
        $auth     = $plaid->authGet($accessToken);
        $identity = $plaid->identityGet($accessToken);

        $identity['accounts'][0]['owners'][0]['names'];      // array of names
        $identity['accounts'][0]['owners'][0]['addresses'];

        $selected = $plaid->findAuthAccount($auth, $data['account_id']);
        // dd($selected);
        if (!$selected) {
            return back()->with('error', 'Account not found in Plaid auth response.');
        }

        $owner = $plaid->getOwnerFromIdentity($identity, $data['account_id']);

        $ownerNames = $owner['name'] ?? [];

        if (is_string($ownerNames)) {
            $ownerNames = [$ownerNames];
        } elseif (!is_array($ownerNames)) {
            $ownerNames = [];
        }
        
        $ownerNames = array_values(array_filter($ownerNames, fn($n) => is_string($n) && trim($n) !== ''));
        
        if (!empty($ownerNames)) {
            $ok = $plaid->customerNameMatchesIdentity($data['customer_name'], $ownerNames);
            
            if (! $ok) {
                return back()
                    ->withInput()
                    ->with('error', 'Customer name does not match the bank account holder name. Please enter the exact account holder name.');
            }
        } else {
            /**
             * Identity names not available.
             *
             * Option A (recommended): Allow, but warn (soft-fail)
             * - Continue verification using Auth (routing/account) only.
             * - Store a note in your DB so you can review later.
             */
            session()->flash('warning', 'Bank owner name could not be retrieved from Plaid Identity. Verification proceeded without name match.');
        
            // Option B (strict): Block if Identity names are missing
            // return back()
            //     ->withInput()
            //     ->with('error', 'Unable to verify account holder name (Identity unavailable). Try another bank.');
        }
        
        $holderName = $owner['name'] ?? null;
        $holderAddr1 = $owner['address1'] ?? null;
        $holderAddr2 = $owner['address2'] ?? null;

        // verification rules (your existing helper)
        $verification = $plaid->verifyMerchantAutoApprove($merchant, $selected, $identity);

        $routing = $selected['routing'] ?? null;
        $acct    = $selected['account'] ?? null;
        $last4   = $acct ? substr($acct, -4) : null;
       


        // Create the eCheck record first (we need ID for file paths)
        $echeck = Echeck::create([
            'merchant_id' => $merchant->id,

            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,

            'account_holder_name' => $holderName,
            'account_holder_address1' => $holderAddr1,
            'account_holder_address2' => $holderAddr2,

            'amount' => $data['amount'],

            'plaid_item_id'     => $itemId,
            'plaid_access_token'=> $accessToken, // for demo only (encrypt later)
            'plaid_account_id'  => $data['account_id'],

            'bank_name' => $institutionName ?? ($selected['name'] ?? null),
            'bank_mask'       => $selected['mask'] ?? null,
            'account_subtype' => $selected['subtype'] ?? null,

            'routing_number' => $routing,
            'account_number' =>  $selected['account'] ?? null,
            'account_last4'  => $last4,

            'status' => $verification['status'] === 'verified' ? 'pending' : 'rejected',
            'verification_json' => $verification,
        ]);
        
        // Generate files (PDF + optional PNG)
        try {
            [$pdfPath, $imagePath] = $this->generateEcheckFiles($echeck, $merchant);

            $echeck->update([
                'pdf_path'   => $pdfPath,
                'image_path' => $imagePath,
            ]);
        } catch (\Throwable $e) {
            // Don’t block creation if file generation fails
            // You can log it if you want: logger()->error($e);
            return redirect()
                ->route('merchant.echeck.create')
                ->with('success', 'eCheck created, but PDF generation failed: '.$e->getMessage());
        }
        
        return redirect()
            ->route('merchant.echeck.show', $echeck) // recommended route
            ->with('success', 'eCheck created successfully. Status: '.$echeck->status);
    }

    public function show(Echeck $echeck)
    {
        // Optional: ensure merchant owns this eCheck
        // $merchantId = session('merchant_id');

        // if ($echeck->merchant_id !== $merchantId) {
        //     abort(403, 'Unauthorized access to eCheck.');
        // }

        // return view('merchant.echeck.show', [
        //     'echeck' => $echeck,
        // ]);

        $merchantId = session('merchant_id');

        if ((int) $echeck->merchant_id !== $merchantId) {
            abort(403, 'Unauthorized access to eCheck.');
        }
    
        return view('merchant.echeck.show', [
            'echeck' => $echeck,
        ]);
    }

    /**
     * Generates eCheck PDF and tries to generate a PNG image too.
     * Returns [pdfPath, imagePath|null] paths relative to "public" disk.
     */
    private function generateEcheckFiles(Echeck $echeck, $merchant): array
    {
        $dir = "echecks/{$echeck->id}";
        Storage::disk('public')->makeDirectory($dir);

        // PDF
        $pdfFile = "{$dir}/echeck-{$echeck->id}.pdf";

        $pdf = Pdf::loadView('merchant.echeck.pdf', [
            'echeck'   => $echeck,
            'merchant' => $merchant,
            'amountWords' => function_exists('amount_to_words')
                ? amount_to_words((float)$echeck->amount)
                : null,
        ])->setPaper('letter', 'portrait')->setOption('dpi', 150);

        Storage::disk('public')->put($pdfFile, $pdf->output());

        // Optional image (requires Imagick)
        $imageFile = null;

        if (extension_loaded('imagick')) {
            $absolutePdf = Storage::disk('public')->path($pdfFile);
            $img = new \Imagick();
            $img->setResolution(200, 200);
            $img->readImage($absolutePdf.'[0]'); // first page only
            $img->setImageFormat('png');
            $img->setImageCompressionQuality(90);

            $imageFile = "{$dir}/echeck-{$echeck->id}.png";
            Storage::disk('public')->put($imageFile, $img->getImageBlob());

            $img->clear();
            $img->destroy();
        }

        return [$pdfFile, $imageFile];
    }   
    public function pdf(Request $request, Echeck $echeck)
    {
        // (Optional) protect: make sure echeck belongs to logged-in merchant
        $merchantId = $request->session()->get('merchant_id');
        abort_unless((int)$echeck->merchant_id === (int)$merchantId, 403);

        abort_unless(!empty($echeck->pdf_path) && Storage::disk('public')->exists($echeck->pdf_path), 404);

        return response()->file(Storage::disk('public')->path($echeck->pdf_path));
    }
}
