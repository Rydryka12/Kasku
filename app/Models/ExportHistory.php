<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'filters',
        'file_path',
        'file_name',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'report_pdf' => 'Laporan (PDF)',
            'report_excel' => 'Laporan (Excel)',
            'transactions_excel' => 'Daftar Transaksi (Excel)',
            default => strtoupper($this->type),
        };
    }
}
