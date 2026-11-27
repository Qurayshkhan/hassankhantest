<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DeliveryChallan;
use App\Repositories\DeliveryChallanRepository;
use App\Traits\Utility;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DeliveryChallanController extends Controller
{
    use Utility;
    public $deliveryChallanRepository;
    public function __construct(DeliveryChallanRepository $deliveryChallanRepository)
    {
        $this->deliveryChallanRepository = $deliveryChallanRepository;
    }
    public function index(Request $request)
    {
        $deliveryChallans = $this->deliveryChallanRepository->get($request);
        return Inertia::render('DeliveryChallan/Index', ['deliveryChallans' => $deliveryChallans, 'filters' => [
            'search' => $request->query('search', ''),
            'delivery_date' => $request->query('delivery_date', ''),
        ]]);
    }

    public function create()
    {
        $companies = Company::all();
        return Inertia::render('DeliveryChallan/Create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'po_number' => 'required',
            'company_id' => 'required|exists:companies,id',
            'delivery_date' => 'required',
            'signature_text' => 'nullable|string|max:255',
            'signature_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'items' => 'nullable|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);
        try {

            DB::beginTransaction();
            $deliveryChallan = $this->deliveryChallanRepository->create([
                'user_id' => Auth::id(),
                'challan_number' => $this->generateChallanNumber(),
                'company_id' => $validatedData['company_id'],
                'po_number' => $validatedData['po_number'],
                'delivery_date' => Carbon::createFromFormat('d/m/Y', $validatedData['delivery_date'])->format('Y-m-d') ?? Carbon::now(),
                'signature' => $validatedData['signature'] ?? null,
                'signature_text' => $validatedData['signature_text'] ?? null
            ]);
            if ($request->hasFile('signature_file')) {
                $filePath = $request->file('signature_file')->store('/assets/images/signatures', 'public');
                $deliveryChallan->update(['signature_file' => $filePath]);
            }
            if (!empty($validatedData['items']) && is_array($validatedData['items'])) {

                foreach ($validatedData['items'] as $item) {
                    $deliveryChallan->items()->create([
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'] ?? 0,
                        'unit' => $item['unit'] ?? 'Each',
                    ]);
                }
            }
            DB::commit();
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Delivery Challan created successfully.')]);
            return to_route('delivery-challans');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return to_route('delivery-challans');
        }
    }


    public function generateDeliveryChallanPDF($id)
    {
        $deliveryChallan = $this->deliveryChallanRepository->find($id);

        if (!$deliveryChallan) {
            return redirect()->back()->with('error', 'Delivery Challan not found.');
        }

        $deliveryChallan->load('items', 'user');

        $pdf = Pdf::loadView('pdf.delivery-challan', [
            'deliveryChallan' => $deliveryChallan,
        ])->setPaper('a4', 'portrait');

        $fileName = 'delivery-challan-' . ($deliveryChallan->po_number ?? $deliveryChallan->id) . '.pdf';

        return $pdf->download($fileName);

    }



    public function edit(DeliveryChallan $deliveryChallan)
    {
        $deliveryChallan->load('items');
        $deliveryChallan->setAttribute(
            'delivery_date',
            $deliveryChallan->delivery_date ? Carbon::parse($deliveryChallan->delivery_date)->format('Y-m-d') : null
        );

        $companies = Company::all();

        return Inertia::render('DeliveryChallan/Edit', [
            'companies' => $companies,
            'deliveryChallan' => $deliveryChallan,
        ]);
    }

    public function update(Request $request, DeliveryChallan $deliveryChallan)
    {
        $validatedData = $request->validate([
            'po_number' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'delivery_date' => 'required',
            'signature_text' => 'nullable|string|max:255',
            'items' => 'nullable|array|min:1',
            'items.*.id' => 'nullable|integer|exists:delivery_challan_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {

            DB::beginTransaction();
            $this->deliveryChallanRepository->update($deliveryChallan, [
                'company_id' => $validatedData['company_id'],
                'po_number' => $validatedData['po_number'],
                'delivery_date' => Carbon::createFromFormat('d/m/Y', $validatedData['delivery_date'])->format('Y-m-d') ?? $deliveryChallan->delivery_date ?? Carbon::now(),
                'signature_text' => $validatedData['signature_text'] ?? null,
            ]);


            $submittedItems = $validatedData['items'] ?? [];
            $keptIds = [];

            foreach ($submittedItems as $itemData) {
                if (!empty($itemData['id'])) {
                    $existingItem = $deliveryChallan->items()->where('id', $itemData['id'])->first();
                    if ($existingItem) {
                        $existingItem->update([
                            'description' => $itemData['description'],
                            'unit' => $itemData['unit'] ?? 'Each',
                            'quantity' => $itemData['quantity'],
                        ]);
                        $keptIds[] = $existingItem->id;
                    }
                } else {
                    $createdItem = $deliveryChallan->items()->create([
                        'description' => $itemData['description'],
                        'unit' => $itemData['unit'] ?? 'Each',
                        'quantity' => $itemData['quantity'],
                    ]);
                    $keptIds[] = $createdItem->id;
                }
            }

            $deliveryChallan->items()->whereNotIn('id', $keptIds)->delete();

            DB::commit();

            Inertia::flash('toast', ['type' => 'success', 'message' => __('Delivery Challan updated successfully.')]);

            return to_route('delivery-challans');
        } catch (\Exception $e) {
            DB::rollBack();
            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }
    }

    public function destroy(DeliveryChallan $deliveryChallan)
    {
        try {


           $this->deliveryChallanRepository->delete($deliveryChallan);


            Inertia::flash('toast', ['type' => 'success', 'message' => __('Delivery Challan deleted successfully.')]);
            return to_route('delivery-challans');

        } catch (\Exception $e) {

            Inertia::flash('toast', ['type' => 'error', 'message' => __($e->getMessage())]);
            return back()->withInput();
        }

    }
}
