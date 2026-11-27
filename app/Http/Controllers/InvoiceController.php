<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Traits\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    use Utility;

    public $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'date']);
        $invoices = $this->invoiceRepository->getInvoices($request);

        return Inertia::render('Invoice/Index', compact('invoices', 'filters'));
    }

    public function create()
    {
        $companies = $this->getCompanies();

        return Inertia::render('Invoice/Create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required',
            'po_number' => 'nullable|numeric',
            'pay_method' => 'nullable|string|max:255',
            'signature_text' => 'nullable|string|max:255',
            'signature_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price_per_unit' => 'required|numeric|min:0',
            'items.*.amount_excluding_sale_tax' => 'nullable|numeric|min:0',
            'items.*.plus_gs_t18' => 'nullable|numeric|min:0',
            'items.*.line_total' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $invoice = $this->invoiceRepository->create([
                'user_id' => Auth::id(),
                'company_id' => $validatedData['company_id'],
                'invoice_number' => $this->generateInvoiceNumber(),
                'date' => Carbon::createFromFormat('d/m/Y', $validatedData['date'])->format('Y-m-d'),
                'po_number' => $validatedData['po_number'] ?? null,
                'pay_method' => $validatedData['pay_method'] ?? null,
                'signature_text' => $validatedData['signature_text'] ?? null,
            ]);

            $this->invoiceRepository->syncItems($invoice, $validatedData['items']);

            if ($request->hasFile('signature_file')) {
                $filePath = $request->file('signature_file')->store('assets/images/signatures', 'public');
                $this->invoiceRepository->update($invoice, ['signature_file' => $filePath]);
            }

            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice saved successfully.')]);
            return to_route('invoices');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['company', 'user', 'items']);
        return redirect()->route('invoices.edit', $invoice->id);
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['company', 'items']);
        $companies = $this->getCompanies();

        return Inertia::render('Invoice/Edit', compact('invoice', 'companies'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required',
            'po_number' => 'nullable|numeric',
            'pay_method' => 'nullable|string|max:255',
            'signature_text' => 'nullable|string|max:255',
            'signature_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price_per_unit' => 'required|numeric|min:0',
            'items.*.amount_excluding_sale_tax' => 'nullable|numeric|min:0',
            'items.*.plus_gs_t18' => 'nullable|numeric|min:0',
            'items.*.line_total' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'company_id' => $validatedData['company_id'],
                'date' => Carbon::createFromFormat('d/m/Y', $validatedData['date'])->format('Y-m-d'),
                'po_number' => $validatedData['po_number'] ?? null,
                'pay_method' => $validatedData['pay_method'] ?? null,
                'signature_text' => $validatedData['signature_text'] ?? null,
            ];

            if ($request->hasFile('signature_file')) {
                if (!empty($invoice->signature_file) && Storage::disk('public')->exists($invoice->signature_file)) {
                    Storage::disk('public')->delete($invoice->signature_file);
                }
                $updateData['signature_file'] = $request->file('signature_file')->store('assets/images/signatures', 'public');
            }

            $this->invoiceRepository->update($invoice, $updateData);
            $this->invoiceRepository->syncItems($invoice, $validatedData['items']);

            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice updated successfully.')]);
            return to_route('invoices');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            $this->invoiceRepository->delete($invoice);
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Invoice deleted successfully.')]);
            return to_route('invoices');
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back();
        }
    }

    public function generateInvoicePdf($id)
    {
        $invoice = $this->invoiceRepository->find($id);

        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

        $invoice->load(['items', 'user', 'company']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4', 'portrait');

        $fileName = 'invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf';

        return $pdf->download($fileName);
    }
}
