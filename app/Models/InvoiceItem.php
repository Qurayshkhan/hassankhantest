<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['invoice_id', 'description', 'unit', 'quantity', 'price_per_unit', 'amount_excluding_sale_tax', 'plus_gs_t18', 'line_total'])]
class InvoiceItem extends Model
{
    use SoftDeletes;

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
