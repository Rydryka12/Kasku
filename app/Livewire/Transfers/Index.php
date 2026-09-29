<?php

namespace App\Livewire\Transfers;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $from_source_id = null;

    public ?int $to_source_id = null;

    public ?int $amount = null;

    public ?int $admin_fee = 0;

    public string $date = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'from_source_id' => [
                'required',
                'different:to_source_id',
                Rule::exists('account_sources', 'id')->where('user_id', Auth::id()),
            ],
            'to_source_id' => [
                'required',
                'different:from_source_id',
                Rule::exists('account_sources', 'id')->where('user_id', Auth::id()),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'admin_fee' => ['nullable', 'integer', 'min:0'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'from_source_id.required' => 'Pilih sumber asal transfer.',
            'from_source_id.different' => 'Sumber asal dan tujuan tidak boleh sama.',
            'to_source_id.required' => 'Pilih sumber tujuan transfer.',
            'to_source_id.different' => 'Sumber tujuan dan asal tidak boleh sama.',
            'amount.required' => 'Nominal transfer wajib diisi.',
            'amount.min' => 'Nominal transfer minimal Rp 1.',
            'date.required' => 'Tanggal transfer wajib diisi.',
        ];
    }

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->amount = null;
        $this->admin_fee = 0;
        $this->notes = '';
        $this->date = now()->format('Y-m-d');

        $sources = AccountSource::where('user_id', Auth::id())->active()->get();
        $this->from_source_id = $sources->first()?->id;
        $this->to_source_id = $sources->skip(1)->first()?->id;

        $this->showModal = true;
    }

    public function save(TransferService $transferService): void
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'from_source_id' => $this->from_source_id,
            'to_source_id' => $this->to_source_id,
            'amount' => (int) $this->amount,
            'admin_fee' => (int) ($this->admin_fee ?? 0),
            'date' => $this->date,
            'notes' => $this->notes ?: null,
        ];

        try {
            $transferService->transfer($data);
            $this->dispatch('toast', ['message' => 'Transfer berhasil dilakukan', 'type' => 'success']);
            $this->showModal = false;
        } catch (InsufficientBalanceException $e) {
            $this->addError('amount', $e->getMessage());
        } catch (\Exception $e) {
            $this->addError('from_source_id', $e->getMessage());
        }
    }

    public function delete(int $id, TransferService $transferService): void
    {
        $transfer = Transfer::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('delete', $transfer);

        try {
            $transferService->delete($transfer);
            $this->dispatch('toast', ['message' => 'Transfer berhasil dibatalkan dan saldo dikembalikan', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function render()
    {
        $transfers = Transfer::where('user_id', Auth::id())
            ->with(['fromSource', 'toSource'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $sources = AccountSource::where('user_id', Auth::id())->active()->get();

        return view('livewire.transfers.index', [
            'transfers' => $transfers,
            'sources' => $sources,
        ]);
    }
}
