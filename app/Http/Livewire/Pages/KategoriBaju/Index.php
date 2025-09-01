<?php

namespace App\Http\Livewire\Pages\KategoriBaju;

use App\Models\KategoriBaju;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $kategoris = KategoriBaju::latest()->paginate(10);
        return view('livewire.pages.kategori-baju.index', compact('kategoris'));
    }
}
