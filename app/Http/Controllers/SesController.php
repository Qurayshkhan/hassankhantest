<?php

namespace App\Http\Controllers;

use App\Models\Ses;
use App\Repositories\SesRepository;
use App\Traits\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SesController extends Controller
{
    use Utility;

    public $sesRepository;

    public function __construct(SesRepository $sesRepository)
    {
        $this->sesRepository = $sesRepository;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'date']);
        $sesRecords = $this->sesRepository->getSes($request);

        return Inertia::render('Ses/Index', compact('sesRecords', 'filters'));
    }

    public function create()
    {
        $companies = $this->getCompanies();

        return Inertia::render('Ses/Create', compact('companies'));
    }

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required',
            'po_number' => 'nullable|numeric',
            'rgp' => 'nullable|string|max:255',
            'igp' => 'nullable|string|max:255',
            'signature_text' => 'nullable|string|max:255',
            'signature_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'items' => 'nullable|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0.01',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $ses = $this->sesRepository->create([
                'user_id' => Auth::id(),
                'company_id' => $validatedData['company_id'],
                'document_number' => $this->generateDocumentNumber(),
                'date' => Carbon::createFromFormat('d/m/Y', $validatedData['date'])->format('Y-m-d'),
                'po_number' => $validatedData['po_number'] ?? null,
                'rgp' => $validatedData['rgp'] ?? null,
                'igp' => $validatedData['igp'] ?? null,
                'signature_text' => $validatedData['signature_text'] ?? null,
            ]);

            if (!empty($validatedData['items'])) {
                $this->sesRepository->syncItems($ses, $validatedData['items']);
            }

            if ($request->hasFile('signature_file')) {
                $filePath = $request->file('signature_file')->store('assets/images/signatures', 'public');
                $this->sesRepository->update($ses, ['signature_file' => $filePath]);
            }

            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('SES saved successfully.')]);
            return to_route('ses');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }




    public function edit(Ses $ses)
    {
        $ses->load(['company', 'items']);
        $companies = $this->getCompanies();

        return Inertia::render('Ses/Edit', compact('ses', 'companies'));
    }

    public function update(Request $request, Ses $ses)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required',
            'po_number' => 'nullable|numeric',
            'rgp' => 'nullable|string|max:255',
            'igp' => 'nullable|string|max:255',
            'signature_text' => 'nullable|string|max:255',
            'signature_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'items' => 'nullable|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0.01',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $updatePayload = [
                'company_id' => $validatedData['company_id'],
                 'date' => Carbon::createFromFormat('d/m/Y', $validatedData['date'])->format('Y-m-d'),
                'po_number' => $validatedData['po_number'] ?? null,
                'rgp' => $validatedData['rgp'] ?? null,
                'igp' => $validatedData['igp'] ?? null,
                'signature_text' => $validatedData['signature_text'] ?? null,
            ];

            if ($request->hasFile('signature_file')) {
                if (!empty($ses->signature_file) && Storage::disk('public')->exists($ses->signature_file)) {
                    Storage::disk('public')->delete($ses->signature_file);
                }
                $updatePayload['signature_file'] = $request->file('signature_file')->store('assets/images/signatures', 'public');
            }

            $this->sesRepository->update($ses, $updatePayload);

            if (!empty($validatedData['items'])) {
                $this->sesRepository->syncItems($ses, $validatedData['items']);
            }

            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('SES updated successfully.')]);
            return to_route('ses');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function destroy(Ses $ses)
    {
        try {
            $this->sesRepository->delete($ses);
            Inertia::flash('toast', ['type' => 'success', 'message' => __('SES deleted successfully.')]);
            return to_route('ses');
        } catch (\Exception $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back();
        }
    }


    public function generateSesPDF($id)
    {
        $ses = $this->sesRepository->find($id);

        if (!$ses) {
            return redirect()->back()->with('error', 'SES not found.');
        }

        $ses->load(['items', 'user', 'company']);

        $pdf = Pdf::loadView('pdf.ses', [
            'ses' => $ses,
        ])->setPaper('a4', 'portrait');

        $fileName = 'ses-' . ($ses->document_number ?? $ses->id) . '.pdf';

        return $pdf->download($fileName);
    }


}
