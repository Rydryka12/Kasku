<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\ExportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TransactionExportController extends Controller
{
    public function excel(Request $request)
    {
        $user = Auth::user();

        $filters = [
            'type' => $request->query('type'),
            'account_source_id' => $request->query('account_source_id'),
            'category_id' => $request->query('category_id'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'search' => $request->query('search'),
        ];

        $filename = 'Transaksi-'.now()->format('Ymd-His').'.xlsx';
        $storagePath = "exports/{$user->id}/{$filename}";

        $export = new TransactionsExport($user, $filters);
        Excel::store($export, $storagePath, 'local');

        ExportHistory::create([
            'user_id' => $user->id,
            'type' => 'transactions_excel',
            'filters' => $filters,
            'file_path' => $storagePath,
            'file_name' => $filename,
        ]);

        return Excel::download(
            $export,
            $filename
        );
    }
}
