<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recurring_id',
        'run_date',
        'status',
        'message',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'run_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class, 'recurring_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
