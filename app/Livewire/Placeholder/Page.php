<?php

namespace App\Livewire\Placeholder;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Page extends Component
{
    public string $title = 'Halaman';

    public string $feature = 'Fitur';

    public function mount(string $title = 'Halaman', string $feature = 'Fitur'): void
    {
        $this->title = $title;
        $this->feature = $feature;
    }

    public function render()
    {
        return view('livewire.placeholder.page');
    }
}
