<?php

namespace App\Http\Controllers;

use App\Models\MaterialTransaction;
use App\Models\Site;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = MaterialTransaction::with(['site', 'material', 'fromSite', 'toSite', 'creator'])
            ->where('status', 'approved')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25);

        $sites = Site::orderByDesc('is_central_store')->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'sites'));
    }
}
