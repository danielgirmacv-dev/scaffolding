<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\View\View;

class InvoicePrintController extends Controller
{
    public function show(Invoice $invoice): View
    {
        $invoice->load(['rental.site', 'rental.items.material', 'creator']);

        return view('invoices.printable', [
            'invoice' => $invoice,
            'rental' => $invoice->rental,
            'site' => $invoice->rental?->site,
            'items' => $invoice->rental?->items ?? collect(),
        ]);
    }
}
