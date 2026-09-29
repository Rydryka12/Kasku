<?php

namespace App\Models;

use App\Enums\EntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'source_id',
        'type',
        'amount',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => EntryType::class,
            'amount' => 'integer',
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class, 'goal_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(AccountSource::class, 'source_id');
    }
}
