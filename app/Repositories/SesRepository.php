<?php

namespace App\Repositories;

use App\Models\Ses;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SesRepository
{
    public $ses;

    public function __construct(Ses $ses)
    {
        $this->ses = $ses;
    }

    public function getSes(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $date = trim((string) $request->query('date', ''));

        return $this->ses
            ->with(['user:id,name', 'company:id,name,branch'])
            ->when($search !== '', function ($query) use ($search) {
                $like = "%{$search}%";

                $query->where(function ($subQuery) use ($like) {
                    $subQuery
                        ->whereLike('document_number', $like)
                        ->orWhereLike('po_number', $like)
                        ->orWhereLike('rgp', $like)
                        ->orWhereLike('igp', $like)
                        ->orWhereHas('company', function ($companyQuery) use ($like) {
                            $companyQuery->where(function ($q) use ($like) {
                                $q->whereLike('name', $like)
                                    ->orWhereLike('branch', $like);
                            });
                        })->orWhereHas('items', function ($itemQuery) use ($like) {
                            $itemQuery->where(function ($q) use ($like) {
                                $q->whereLike('description', $like)
                                ;
                            });
                        })
                        ->orWhereHas('user', function ($userQuery) use ($like) {
                            $userQuery->whereLike('name', $like);
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

    public function create(array $data): Ses
    {
        return $this->ses->create($data);
    }

    public function update(Ses $ses, array $data): Ses
    {
        $ses->update($data);

        return $ses;
    }

    public function syncItems(Ses $ses, array $items): void
    {
        if (!method_exists($ses, 'items')) {
            return;
        }

        $keptIds = [];

        foreach ($items as $item) {
            $payload = [
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'Each',
                'quantity' => (float) $item['quantity'],
                'rate' => (float) $item['rate'],
                'amount' => (float) $item['amount'],
            ];

            if (!empty($item['id'])) {
                $existing = $ses->items()->where('id', $item['id'])->first();
                if ($existing) {
                    $existing->update($payload);
                    $keptIds[] = $existing->id;
                    continue;
                }
            }

            $created = $ses->items()->create($payload);
            $keptIds[] = $created->id;
        }

        $ses->items()->whereNotIn('id', $keptIds)->delete();
    }

    public function delete(Ses $ses): void
    {
        DB::transaction(function () use ($ses) {
            if (method_exists($ses, 'items')) {
                $ses->items()->delete();
            }
            $ses->delete();
        });
    }

    public function find($id): ?Ses
    {
        return $this->ses->find($id);
    }
}
