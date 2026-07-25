<?php

namespace App\Helpers;

class RupiahHelper
{
    /**
     * Format an integer amount to Indonesian Rupiah notation.
     *
     * Examples:
     *   0       → "Rp 0"
     *   1000    → "Rp 1.000"
     *   50000   → "Rp 50.000"
     *   1500000 → "Rp 1.500.000"
     *
     * @param int $amount Non-negative integer amount in Rupiah
     * @return string Formatted Rupiah string
     */
    public static function formatRupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
