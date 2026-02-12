<?php

use App\Models\Merchant;

if (! function_exists('amount_to_words')) {
    function amount_to_words(float $amount): string
    {
        $dollars = floor($amount);
        $cents = round(($amount - $dollars) * 100);

        return ucfirst(number_to_words($dollars))
            . ' and '
            . str_pad((string)$cents, 2, '0', STR_PAD_LEFT)
            . '/100';
    }
}

if (! function_exists('number_to_words')) {
    function number_to_words(int $number): string
    {
        $ones = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three',
            4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven',
            8 => 'eight', 9 => 'nine', 10 => 'ten',
            11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
            14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen',
            17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen'
        ];

        $tens = [
            20 => 'twenty', 30 => 'thirty', 40 => 'forty',
            50 => 'fifty', 60 => 'sixty', 70 => 'seventy',
            80 => 'eighty', 90 => 'ninety'
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            return $tens[intdiv($number, 10) * 10]
                . ($number % 10 ? ' ' . $ones[$number % 10] : '');
        }

        if ($number < 1000) {
            return $ones[intdiv($number, 100)] . ' hundred'
                . ($number % 100 ? ' ' . number_to_words($number % 100) : '');
        }

        if ($number < 1000000) {
            return number_to_words(intdiv($number, 1000)) . ' thousand'
                . ($number % 1000 ? ' ' . number_to_words($number % 1000) : '');
        }

        return (string)$number;
    }
}

if (! function_exists('mask_account')) {
    /**
     * Mask bank account numbers
     * Example: 123456789 => ****6789
     */
    function mask_account(string $number, int $visible = 4): string
    {
        return str_repeat('*', max(0, strlen($number) - $visible))
            . substr($number, -$visible);
    }
}

if (! function_exists('mask_routing')) {
    /**
     * Mask routing number
     * Example: 011000015 => *****0015
     */
    function mask_routing(string $routing): string
    {
        return '*****' . substr($routing, -4);
    }
}

if (! function_exists('generate_check_number')) {
    /**
     * Generate a realistic check number
     */
    function generate_check_number(): string
    {
        return (string) random_int(1000, 9999);
    }
}

if (! function_exists('format_money')) {
    /**
     * Format amount consistently
     */
    function format_money(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}

if (! function_exists('echeck_reference')) {
    /**
     * Generate Paycron-style reference
     * Example: ECHK-9F2A1C
     */
    function echeck_reference(): string
    {
        return 'ECHK-' . strtoupper(bin2hex(random_bytes(3)));
    }
}

if (! function_exists('merchant_name_by_id')) {
    /**
     * Get merchant name by merchant ID
     *
     * @param int|null $merchantId
     * @return string
     */
    function merchant_name_by_id(?int $merchantId): string
    {
        if (! $merchantId) {
            return 'Merchant Name';
        }

        static $cache = [];

        if (isset($cache[$merchantId])) {
            return $cache[$merchantId];
        }

        $merchant = Merchant::select('name')
            ->where('id', $merchantId)
            ->first();

        return $cache[$merchantId] = $merchant?->name ?? 'Merchant Name';
    }
}
