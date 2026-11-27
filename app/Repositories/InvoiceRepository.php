<?php

namespace App\Repositories;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceRepository
{
    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getInvoices(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $date = trim((string) $request->query('date', ''));

        return $this->invoice
            ->with(['user:id,name', 'company:id,name,branch'])
            ->when($search !== '', function ($query) use ($search) {
                $like = "%{$search}%";

                $query->where(function ($subQuery) use ($like) {
                    $subQuery
                        ->whereLike('invoice_number', $like)
                        ->orWhereLike('po_number', $like)
                        ->orWhereLike('pay_method', $like)
                        ->orWhereHas('company', function ($companyQuery) use ($like) {
                            $companyQuery->where(function ($q) use ($like) {
                                $q->whereLike('name', $like)
                                    ->orWhereLike('branch', $like);
                            });
                        })
                        ->orWhereHas('user', function ($userQuery) use ($like) {
                            $userQuery->whereLike('name', $like);
                        })
                        ->orWhereHas('items', function ($itemQuery) use ($like) {
                            $itemQuery->whereLike('description', $like);
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

    public function find($id): ?Invoice
    {
        return $this->invoice->find($id);
    }

    public function create(array $data): Invoice
    {
        return $this->invoice->create($data);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        $invoice->update($data);

        return $invoice;
    }

    public function syncItems(Invoice $invoice, array $items): void
    {
        $keptIds = [];

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['price_per_unit'] ?? 0);
            $exclTax = round($qty * $price, 2);
            $gst18 = round($exclTax * 0.18, 2);
            $lineTotal = round($exclTax + $gst18, 2);

            $payload = [
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'Each',
                'quantity' => $qty,
                'price_per_unit' => $price,
                'amount_excluding_sale_tax' => $exclTax,
                'plus_gs_t18' => $gst18,
                'line_total' => $lineTotal,
            ];

            if (!empty($item['id'])) {
                $existing = $invoice->items()->where('id', $item['id'])->first();
                if ($existing) {
                    $existing->update($payload);
                    $keptIds[] = $existing->id;
                    continue;
                }
            }

            $created = $invoice->items()->create($payload);
            $keptIds[] = $created->id;
        }

        $invoice->items()->whereNotIn('id', $keptIds)->delete();
    }

    public function delete(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });
    }
}
