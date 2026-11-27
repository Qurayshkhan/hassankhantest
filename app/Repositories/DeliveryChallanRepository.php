<?php

namespace App\Repositories;

use App\Models\DeliveryChallan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryChallanRepository
{

    public $deliveryChallan;

    public function __construct(DeliveryChallan $deliveryChallan)
    {
        $this->deliveryChallan = $deliveryChallan;
    }

    public function get($request)
    {
        $search = trim((string) $request->query('search', ''));
        $deliveryDate = trim((string) $request->query('delivery_date', ''));

        return $this->deliveryChallan
            ->with(['user:id,name', 'company:id,name,branch'])
            ->when($search !== '', function ($query) use ($search) {
                $like = "%{$search}%";

                $query->where(function ($subQuery) use ($like) {
                    $subQuery
                        ->whereLike('challan_number', $like)
                        ->orWhereLike('po_number', $like)
                        ->orWhereHas('company', function ($companyQuery) use ($like) {
                            $companyQuery->where(function ($q) use ($like) {
                                $q->whereLike('name', $like)
                                    ->orWhereLike('branch', $like);
                            });
                        })
                        ->orWhereHas('items', function ($itemQuery) use ($like) {
                            $itemQuery->whereLike('description', $like);
                        });
                });
            })
            ->when($deliveryDate !== '', function ($query) use ($deliveryDate) {
                $formattedDate = Carbon::parse($deliveryDate)->format('Y-m-d');
                $query->whereDate('delivery_date', $formattedDate);
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();
    }

    public function create(array $data)
    {
        return $this->deliveryChallan->create($data);
    }

    public function find($id)
    {
        return $this->deliveryChallan->where('id', $id)->first();
    }


    public function update(DeliveryChallan $deliveryChallan, array $data): DeliveryChallan
    {
        $deliveryChallan->update($data);

        return $deliveryChallan->fresh();
    }

    public function delete(DeliveryChallan $deliveryChallan){
        DB::transaction(function () use ($deliveryChallan) {
            if (method_exists($deliveryChallan, 'items')) {
                $deliveryChallan->items()->delete();
            }
            $deliveryChallan->delete();
        });
    }
}
