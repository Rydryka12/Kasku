<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_source_id',
        'category_id',
        'type',
        'is_adjustment',
        'amount',
        'tax_amount',
        'tax_note',
        'date',
        'description',
        'receipt_path',
        'transfer_id',
        'recurring_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'is_adjustment' => 'boolean',
            'amount' => 'integer',
            'tax_amount' => 'integer',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class, 'recurring_id');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Income);
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Expense);
    }

    public function scopeNonAdjustment(Builder $query): Builder
    {
        return $query->where('is_adjustment', false);
    }
}
