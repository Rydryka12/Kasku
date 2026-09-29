<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CategoriesIndex extends Component
{
    public string $activeTab = 'expense';

    public bool $showModal = false;

    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $type = 'expense';

    public string $color = '#2B3A8C';

    public string $icon = 'tag';

    public array $palette = [
        '#2B3A8C', '#0B7A55', '#B7791F', '#B93A2B', '#6B3FA0',
        '#0E7C86', '#C2547A', '#4F6B2D', '#7A5230', '#44515C',
    ];

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
        $category = Category::defaultTemplates()->findOrFail($id);

        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type->value;
        $this->color = $category->color;
        $this->icon = $category->icon ?? 'tag';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:50',
            'type' => ['required', Rule::in(['income', 'expense'])],
            'color' => 'required|string|max:7',
        ]);

        if ($this->editingCategoryId) {
            $cat = Category::defaultTemplates()->findOrFail($this->editingCategoryId);
            $cat->update([
                'name' => $this->name,
                'color' => $this->color,
            ]);
            $this->dispatch('toast', ['message' => 'Template kategori berhasil diperbarui', 'type' => 'success']);
        } else {
            Category::create([
                'user_id' => null,
                'name' => $this->name,
                'type' => $this->type,
                'color' => $this->color,
                'icon' => $this->icon,
            ]);
            $this->dispatch('toast', ['message' => 'Template kategori berhasil ditambahkan', 'type' => 'success']);
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $cat = Category::defaultTemplates()->findOrFail($id);
        $cat->delete();
        $this->dispatch('toast', ['message' => 'Template kategori sistem dihapus', 'type' => 'success']);
    }

    public function render()
    {
        $categories = Category::defaultTemplates()
            ->where('type', $this->activeTab)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.categories-index', [
            'categories' => $categories,
        ]);
    }
}
