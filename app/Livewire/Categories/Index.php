<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $activeTab = 'expense'; // 'income' or 'expense'

    public bool $showModal = false;

    public ?int $editingCategoryId = null;

    #[Validate]
    public string $name = '';

    public string $type = 'expense';

    public string $color = '#2B3A8C';

    public string $icon = 'tag';

    public array $palette = [
        '#2B3A8C', '#0B7A55', '#B7791F', '#B93A2B', '#6B3FA0',
        '#0E7C86', '#C2547A', '#4F6B2D', '#7A5230', '#44515C',
    ];

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories')
                    ->where('user_id', Auth::id())
                    ->where('type', $this->type)
                    ->ignore($this->editingCategoryId),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'color' => ['required', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan untuk tipe ini.',
        ];
    }

    public function mount(): void
    {
        $this->type = $this->activeTab;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->type = $tab;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingCategoryId = null;
        $this->name = '';
        $this->type = $this->activeTab;
        $this->color = $this->palette[0];
        $this->icon = 'tag';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $category = Category::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $category);

        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type->value;
        $this->color = $category->color;
        $this->icon = $category->icon ?? 'tag';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingCategoryId) {
            $category = Category::where('user_id', Auth::id())->findOrFail($this->editingCategoryId);
            $this->authorize('update', $category);

            $category->update([
                'name' => $this->name,
                'color' => $this->color,
                'icon' => $this->icon,
            ]);

            $this->dispatch('toast', ['message' => 'Kategori berhasil diperbarui', 'type' => 'success']);
        } else {
            Category::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
                'type' => $this->type,
                'color' => $this->color,
                'icon' => $this->icon,
            ]);

            $this->dispatch('toast', ['message' => 'Kategori berhasil ditambahkan', 'type' => 'success']);
        }

        $this->showModal = false;
    }

    public function toggleArchive(int $id): void
    {
        $category = Category::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $category);

        if ($category->isArchived()) {
            $category->update(['archived_at' => null]);
            $this->dispatch('toast', ['message' => 'Kategori berhasil diaktifkan kembali', 'type' => 'success']);
        } else {
            // Arsip kategori (K-06)
            $category->update(['archived_at' => now()]);
            $this->dispatch('toast', ['message' => 'Kategori berhasil diarsipkan', 'type' => 'info']);
        }
    }

    public function delete(int $id): void
    {
        $category = Category::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('delete', $category);

        // Jika sudah pernah dipakai transaksi, arsipkan bukan hapus (K-06)
        if ($category->transactions()->exists()) {
            $category->update(['archived_at' => now()]);
            $this->dispatch('toast', ['message' => 'Kategori sudah memiliki transaksi, dialihkan ke arsip', 'type' => 'warning']);

            return;
        }

        $category->delete();
        $this->dispatch('toast', ['message' => 'Kategori berhasil dihapus', 'type' => 'success']);
    }

    public function render()
    {
        $categories = Category::where('user_id', Auth::id())
            ->where('type', $this->activeTab)
            ->active()
            ->orderBy('name')
            ->get();

        $archivedCategories = Category::where('user_id', Auth::id())
            ->where('type', $this->activeTab)
            ->archived()
            ->orderBy('name')
            ->get();

        return view('livewire.categories.index', [
            'categories' => $categories,
            'archivedCategories' => $archivedCategories,
        ]);
    }
}
