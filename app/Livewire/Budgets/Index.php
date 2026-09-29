<?php

namespace App\Livewire\Budgets;

use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    #[Url]
    public string $month = '';

    // Modal Add / Edit
    public bool $showModal = false;

    public ?int $editingBudgetId = null;

    public ?int $category_id = null;

    public ?int $limit_amount = null;

    protected function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|integer|min:1000',
        ];
    }

    public function mount(): void
    {
        $this->month = $this->month ?: now()->format('Y-m');
    }

    public function prevMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)->addMonth()->format('Y-m');
    }

    public function getMonthLabelProperty(): string
    {
        return Carbon::createFromFormat('Y-m', $this->month)->translatedFormat('F Y');
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingBudgetId = null;
        $this->category_id = null;
        $this->limit_amount = null;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $budget);

        $this->editingBudgetId = $budget->id;
        $this->category_id = $budget->category_id;
        $this->limit_amount = $budget->limit_amount;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|integer|min:1000',
        ]);

        if ($this->editingBudgetId) {
            $budget = Budget::where('user_id', Auth::id())->findOrFail($this->editingBudgetId);
            $this->authorize('update', $budget);

            $budget->update([
                'category_id' => $this->category_id,
                'limit_amount' => $this->limit_amount,
            ]);

            $this->dispatch('toast', ['message' => 'Budget berhasil diperbarui', 'type' => 'success']);
        } else {
            // Cek apakah sudah ada budget untuk kategori ini pada bulan ini
            $exists = Budget::where('user_id', Auth::id())
                ->where('category_id', $this->category_id)
                ->where('month', $this->month)
                ->exists();

            if ($exists) {
                $this->addError('category_id', 'Budget untuk kategori ini di bulan ini sudah dibuat.');

                return;
            }

            Budget::create([
                'user_id' => Auth::id(),
                'category_id' => $this->category_id,
                'month' => $this->month,
                'limit_amount' => $this->limit_amount,
            ]);

            $this->dispatch('toast', ['message' => 'Budget berhasil ditambahkan', 'type' => 'success']);
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('delete', $budget);

        $budget->delete();
        $this->dispatch('toast', ['message' => 'Budget berhasil dihapus', 'type' => 'info']);
    }

    public function copyPrevious(BudgetService $budgetService): void
    {
        $copied = $budgetService->copyFromPreviousMonth(Auth::user(), $this->month);

        if ($copied > 0) {
            $this->dispatch('toast', ['message' => "Berhasil menyalin {$copied} budget dari bulan lalu", 'type' => 'success']);
        } else {
            $this->dispatch('toast', ['message' => 'Tidak ada budget baru yang bisa disalin dari bulan lalu', 'type' => 'info']);
        }
    }

    public function render(BudgetService $budgetService)
    {
        $user = Auth::user();

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $this->month)
            ->with(['category'])
            ->get()
            ->map(function ($b) use ($budgetService) {
                $usage = $budgetService->usage($b);
                $b->used = $usage['used'];
                $b->remaining = $usage['remaining'];
                $b->percent = $usage['percent'];
                $b->status = $usage['status'];

                return $b;
            })
            ->sortByDesc('percent')
            ->values();

        // Kategori pengeluaran aktif milik user
        $availableCategories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->active()
            ->orderBy('name')
            ->get();

        $totalLimit = $budgets->sum('limit_amount');
        $totalUsed = $budgets->sum('used');

        return view('livewire.budgets.index', [
            'budgets' => $budgets,
            'availableCategories' => $availableCategories,
            'totalLimit' => $totalLimit,
            'totalUsed' => $totalUsed,
        ]);
    }
}
