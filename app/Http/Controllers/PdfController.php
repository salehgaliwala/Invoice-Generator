<?php

namespace App\Http\Controllers;

use App\Models\Edf;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function downloadInvoice(Invoice $invoice)
    {
        $invoice->load(['company', 'customer', 'items.product']);

        $pdf = Pdf::loadView('pdf.tax-invoice', [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'customer' => $invoice->customer,
            'items' => $invoice->items,
        ]);

        $filename = str_replace('/', '-', $invoice->invoice_number) . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadEdf(Edf $edf)
    {
        $edf->load(['company', 'invoice.customer']);

        $pdf = Pdf::loadView('pdf.edf-form', [
            'edf' => $edf,
            'company' => $edf->company,
            'invoice' => $edf->invoice,
        ]);

        $filename = 'EDF-' . str_replace('/', '-', $edf->invoice->invoice_number) . '.pdf';

        return $pdf->download($filename);
    }
}
