<?php

namespace App\Http\Controllers;

class PengadaanController extends Controller
{
    public function create()
    {
        return view('pengadaan.create');
    }

    public function store()
    {
        // Handled by Livewire PengadaanForm component
    }
}
