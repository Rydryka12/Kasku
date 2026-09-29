<?php

namespace App\Models;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'person',
        'amount',
        'paid_amount',
        'due_date',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'type' => DebtType::class,
            'status' => DebtStatus::class,
            'amount' => 'integer',
            'paid_amount' => 'integer',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class)->latest('date');
    }

    public function getRemainingAmountAttribute(): int
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->amount <= 0) {
            return 100;
        }

        return (int) min(100, round(($this->paid_amount / $this->amount) * 100));
    }

    public function scopeUnpaidOrPartial(Builder $query): Builder
    {
        return $query->whereIn('status', [DebtStatus::Unpaid, DebtStatus::Partial]);
    }

    public function scopeDebts(Builder $query): Builder
    {
        return $query->where('type', DebtType::Debt);
    }

    public function scopeReceivables(Builder $query): Builder
    {
        return $query->where('type', DebtType::Receivable);
    }
}
