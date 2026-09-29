<?php

if (! function_exists('rupiah')) {
    /**
     * Format integer into rupiah representation without decimals.
     * Example: 1000000 -> "Rp 1.000.000"
     */
    function rupiah(int|float|null $amount, bool $signed = false): string
    {
        $val = (int) ($amount ?? 0);
        $formatted = 'Rp '.number_format(abs($val), 0, ',', '.');

        if (! $signed) {
            return $val < 0 ? '-'.$formatted : $formatted;
        }

        if ($val > 0) {
            return '+'.$formatted;
        }

        if ($val < 0) {
            return '−'.$formatted;
        }

        return $formatted;
    }
}
