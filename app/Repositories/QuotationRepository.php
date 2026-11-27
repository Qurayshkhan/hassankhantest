<?php

namespace App\Repositories;

use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuotationRepository
{
    public $quotation;

    public function __construct(Quotation $quotation)
    {
        $this->quotation = $quotation;
    }

    public function getQuotation($request)
    {
        $search = trim((string) $request->query('search', ''));
        $date = trim((string) $request->query('date', ''));

        return $this->quotation
            ->with(['user:id,name', 'company:id,name,branch'])
            ->when($search !== '', function ($query) use ($search) {
                $like = "%{$search}%";

                $query->where(function ($subQuery) use ($like) {
                    $subQuery
                        ->whereLike('quotation_number', $like)
                        ->orWhereHas('user', function ($userQuery) use ($like) {
                            $userQuery->whereLike('name', $like);
                        })
                        ->orWhereHas('company', function ($companyQuery) use ($like) {
                            $companyQuery->where(function ($q) use ($like) {
                                $q->whereLike('name', $like)
                                    ->orWhereLike('branch', $like);
                            });
                        })
                        ->orWhereHas('items', function ($itemQuery) use ($like) {
                            $itemQuery->where(function ($q) use ($like) {
                                $q->whereLike('pr', $like)
                                    ->orWhereLike('description', $like);
                            });
                        });
                });
            })
            ->when($date !== '', function ($query) use ($date) {
                $formattedDate = Carbon::parse($date)->format('Y-m-d');
                $query->whereDate('date', $formattedDate);
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();
    }

    public function create(array $data)
    {
        return $this->quotation->create($data);
    }

    public function update(Quotation $quotation, array $data): Quotation
    {
        $quotation->update($data);

        return $quotation;
    }

    public function syncItems(Quotation $quotation, array $items): void
    {
        $keptItemIds = [];

        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $rate = (float) $item['rate_per_unit'];

            $payload = [
                'pr' => $item['pr'] ?? null,
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'Each',
                'quantity' => $quantity,
                'rate_per_unit' => $rate,
                'line_total' => round($quantity * $rate, 2),
            ];

            if (!empty($item['id'])) {
                $existingItem = $quotation->items()->where('id', $item['id'])->first();

                if ($existingItem) {
                    $existingItem->update($payload);
                    $keptItemIds[] = $existingItem->id;
                    continue;
                }
            }

            $newItem = $quotation->items()->create($payload);
            $keptItemIds[] = $newItem->id;
        }

        $quotation->items()->whereNotIn('id', $keptItemIds)->delete();
    }

    public function find($id): ?Quotation
    {
        return $this->quotation->find($id);
    }

        public function delete(Quotation $quotation){
        DB::transaction(function () use ($quotation) {
            if (method_exists($quotation, 'items')) {
                $quotation->items()->delete();
            }
            $quotation->delete();
        });
    }

}
