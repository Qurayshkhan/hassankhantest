<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Repositories\QuotationRepository;
use App\Traits\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuotationController extends Controller
{
    use Utility;

    public $quotationRepository;

    public function __construct(QuotationRepository $quotationRepository)
    {
        $this->quotationRepository = $quotationRepository;
    }

    public function index(Request $request)
    {
        $quotations = $this->quotationRepository->getQuotation($request);
        return Inertia::render('Quotations/Index', compact('quotations'));
    }

    public function create()
    {
        $companies = $this->getCompanies();
        return Inertia::render('Quotations/Create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required',
            'validity' => 'required|numeric|min:1',
            'items' => 'required|array|min:1',
            'items.*.pr' => 'nullable|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate_per_unit' => 'required|numeric|min:0.01',
            'items.*.line_total' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $quotation = $this->quotationRepository->create([
                'quotation_number' => $this->generateQuotationNumber(),
                'date' => Carbon::createFromFormat('d/m/Y', $validatedData['date'])->format('Y-m-d'),
                'validity' => $validatedData['validity'] ?? 7,
                'company_id' => $validatedData['company_id'],
                'user_id' => Auth::id(),
            ]);

            if (!empty($validatedData['items'])) {
                $this->quotationRepository->syncItems($quotation, $validatedData['items']);
            }

            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Quotation saved successfully.')]);
            return to_route('quotations');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['company', 'items']);
        $companies = $this->getCompanies();

        return Inertia::render('Quotations/Edit', compact('quotation', 'companies'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required',
            'validity' => 'required|numeric|min:1',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:quotation_items,id',
            'items.*.pr' => 'nullable|string|max:255',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate_per_unit' => 'required|numeric|min:0.01',
            'items.*.line_total' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $this->quotationRepository->update($quotation, [
                'company_id' => $validatedData['company_id'],
                'date' => Carbon::createFromFormat('d/m/Y', $validatedData['date'])->format('Y-m-d'),
                'validity' => $validatedData['validity'],
            ]);

            $this->quotationRepository->syncItems($quotation, $validatedData['items']);

            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Quotation updated successfully.')]);
            return to_route('quotations');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function destroy(Quotation $quotation)
    {
        try {

            $this->quotationRepository->delete($quotation);


            Inertia::flash('toast', ['type' => 'success', 'message' => __('Quotation deleted successfully.')]);
            return to_route('quotations');
        } catch (\Exception $e) {

            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back();
        }
    }

    public function generateQuotationPDF(Request $request, $id)
    {
        $quotation = $this->quotationRepository->find($id);

        if (!$quotation) {

            Inertia::flash('toast', ['type' => 'success', 'message' => __('Quotation not found.')]);
            return to_route('quotations');
        }

        $quotation->load('items', 'user', 'company');

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
        ])->setPaper('a4', 'portrait');

        $fileName = 'quotation-' . ($quotation->quotation_number ?? $quotation->id) . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }


}
