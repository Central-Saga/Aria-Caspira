<?php

namespace App\Http\Controllers;

use App\Models\KategoriBaju;
use Illuminate\Http\Request;

class KategoriBajuController extends Controller
{
    public function index()
    {
        $kategoris = KategoriBaju::latest()->paginate(10);
        return view('livewire.pages.kategori-baju.index', compact('kategoris'));
    }

    public function create()
    {
        return view('livewire.pages.kategori-baju.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100',
        ]);
        KategoriBaju::create($request->only('nama_kategori'));
        return redirect()->route('kategori-baju.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(KategoriBaju $kategori_baju)
    {
        return view('livewire.pages.kategori-baju.edit', compact('kategori_baju'));
    }

    public function update(Request $request, KategoriBaju $kategori_baju)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100',
        ]);
        $kategori_baju->update($request->only('nama_kategori'));
        return redirect()->route('kategori-baju.index')->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(KategoriBaju $kategori_baju)
    {
        $kategori_baju->delete();
        return redirect()->route('kategori-baju.index')->with('success', 'Kategori berhasil dihapus.');
    }
}