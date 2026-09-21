<?php

namespace App\Http\Controllers;

use App\Models\Material;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::withTrashed(false)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('materials.index', compact('materials'));
    }

    public function show(Material $material)
    {
        $transactions = $material->transactions()
            ->with('site')
            ->where('status', 'approved')
            ->orderByDesc('transaction_date')
            ->limit(20)
            ->get();

        return view('materials.show', compact('material', 'transactions'));
    }
}
