<?php

namespace App\Http\Controllers;

use App\Models\ExportHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDownloadController extends Controller
{
    public function download(ExportHistory $exportHistory): StreamedResponse
    {
        if ($exportHistory->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan');
        }

        if (! Storage::disk('local')->exists($exportHistory->file_path)) {
            abort(404, 'File export tidak ditemukan');
        }

        return Storage::disk('local')->download($exportHistory->file_path, $exportHistory->file_name);
    }
}
