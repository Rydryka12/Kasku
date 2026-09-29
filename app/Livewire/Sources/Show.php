<?php

namespace App\Livewire\Sources;

use App\Models\AccountSource;
use App\Models\Transaction;
use App\Models\Transfer;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithPagination;

    public AccountSource $source;

    public function mount(AccountSource $source): void
    {
        $this->authorize('view', $source);
        $this->source = $source;
    }

    public function render()
    {
        // Ambil transaksi langsung
        $directTransactions = Transaction::where('account_source_id', $this->source->id)
            ->whereNull('transfer_id')
            ->with(['category'])
            ->get()
            ->map(function ($tx) {
                return (object) [
                    'id' => 'tx-'.$tx->id,
                    'date' => $tx->date,
                    'title' => $tx->category->name ?? 'Transaksi',
                    'subtitle' => $tx->description,
                    'type' => $tx->type->value, // income / expense
                    'amount' => $tx->amount,
                    'badge' => $tx->type->value === 'income' ? 'Pemasukan' : 'Pengeluaran',
                ];
            });

        // Ambil transfer keluar
        $outgoingTransfers = Transfer::where('from_source_id', $this->source->id)
            ->with(['toSource'])
            ->get()
            ->map(function ($tr) {
                return (object) [
                    'id' => 'tr-out-'.$tr->id,
                    'date' => $tr->date,
                    'title' => 'Transfer ke '.($tr->toSource->name ?? 'Akun'),
                    'subtitle' => $tr->notes.($tr->admin_fee > 0 ? ' (Biaya: '.rupiah($tr->admin_fee).')' : ''),
                    'type' => 'expense',
                    'amount' => $tr->amount + $tr->admin_fee,
                    'badge' => 'Transfer Keluar',
                ];
            });

        // Ambil transfer masuk
        $incomingTransfers = Transfer::where('to_source_id', $this->source->id)
            ->with(['fromSource'])
            ->get()
            ->map(function ($tr) {
                return (object) [
                    'id' => 'tr-in-'.$tr->id,
                    'date' => $tr->date,
                    'title' => 'Transfer dari '.($tr->fromSource->name ?? 'Akun'),
                    'subtitle' => $tr->notes,
                    'type' => 'income',
                    'amount' => $tr->amount,
                    'badge' => 'Transfer Masuk',
                ];
            });

        // Gabung dan urutkan tanggal terbaru
        $allMutations = $directTransactions->concat($outgoingTransfers)->concat($incomingTransfers)
            ->sortByDesc('date');

        // Pagination manual
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $items = $allMutations->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator($items, $allMutations->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return view('livewire.sources.show', [
            'mutations' => $paginator,
        ]);
    }
}
