<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_source_id',
        'to_source_id',
        'amount',
        'admin_fee',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'admin_fee' => 'integer',
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromSource(): BelongsTo
    {
        return $this->belongsTo(AccountSource::class, 'from_source_id');
    }

    public function toSource(): BelongsTo
    {
        return $this->belongsTo(AccountSource::class, 'to_source_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
