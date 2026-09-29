<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(
        public readonly string $sourceName,
        public readonly int $availableBalance,
        public readonly int $requiredAmount,
        string $message = ''
    ) {
        $msg = $message ?: "Saldo {$this->sourceName} tidak cukup. Tersedia ".rupiah($this->availableBalance).', dibutuhkan '.rupiah($this->requiredAmount).'.';
        parent::__construct($msg);
    }
}
