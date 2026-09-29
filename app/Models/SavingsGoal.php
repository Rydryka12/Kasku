<?php

namespace App\Models;

use App\Enums\GoalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'deadline',
        'icon',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'integer',
            'current_amount' => 'integer',
            'deadline' => 'date',
            'status' => GoalStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SavingsEntry::class, 'goal_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', GoalStatus::Active);
    }

    public function scopeActiveOrAchieved(Builder $query): Builder
    {
        return $query->whereIn('status', [GoalStatus::Active, GoalStatus::Achieved]);
    }

    /**
     * Hitung persentase ketercapaian (0 - 100+).
     */
    public function progressPercentage(): int
    {
        if ($this->target_amount <= 0) {
            return 100;
        }

        $percentage = (int) round(($this->current_amount / $this->target_amount) * 100);

        return min($percentage, 100);
    }

    /**
     * Sisa nominal yang harus dikumpulkan.
     */
    public function remainingAmount(): int
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Perkiraan bulan tercapai berdasarkan rata-rata setoran bersih 3 bulan terakhir (FR-SAV-04).
     * Format: 'M Y' (contoh: 'Mar 2027') atau null jika belum cukup data.
     */
    public function estimateAchievedMonth(): ?string
    {
        if ($this->current_amount >= $this->target_amount) {
            return 'Sudah tercapai';
        }

        $threeMonthsAgo = now()->subMonths(3)->startOfDay();

        $recentDeposits = (int) $this->entries()
            ->where('type', 'deposit')
            ->where('date', '>=', $threeMonthsAgo)
            ->sum('amount');

        $recentWithdrawals = (int) $this->entries()
            ->where('type', 'withdraw')
            ->where('date', '>=', $threeMonthsAgo)
            ->sum('amount');

        $netThreeMonths = $recentDeposits - $recentWithdrawals;

        if ($netThreeMonths <= 0) {
            return null; // Belum bisa diperkirakan
        }

        $monthlyAverage = $netThreeMonths / 3;
        $remaining = $this->remainingAmount();

        if ($monthlyAverage <= 0) {
            return null;
        }

        $monthsNeeded = (int) ceil($remaining / $monthlyAverage);
        $estimatedDate = now()->addMonths($monthsNeeded);

        return $estimatedDate->translatedFormat('M Y');
    }
}
