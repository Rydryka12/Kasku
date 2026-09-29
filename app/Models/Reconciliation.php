<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_source_id',
        'app_balance',
        'actual_balance',
        'difference',
        'adjusted',
        'adjustment_transaction_id',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'app_balance' => 'integer',
            'actual_balance' => 'integer',
            'difference' => 'integer',
            'adjusted' => 'boolean',
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accountSource(): BelongsTo
    {
        return $this->belongsTo(AccountSource::class);
    }

    public function adjustmentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'adjustment_transaction_id');
    }
}
