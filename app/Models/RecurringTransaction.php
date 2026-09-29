<?php

namespace App\Models;

use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_source_id',
        'category_id',
        'type',
        'amount',
        'tax_amount',
        'tax_note',
        'description',
        'frequency',
        'start_date',
        'next_run',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'frequency' => RecurringFrequency::class,
            'status' => RecurringStatus::class,
            'amount' => 'integer',
            'tax_amount' => 'integer',
            'start_date' => 'date',
            'next_run' => 'date',
            'end_date' => 'date',
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

    public function logs(): HasMany
    {
        return $this->hasMany(RecurringLog::class, 'recurring_id')->latest('run_date');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'recurring_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', RecurringStatus::Active);
    }
}
