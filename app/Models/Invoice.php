<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['invoice_number', 'date', 'po_number', 'pay_method', 'company_id', 'user_id', 'signature_text', 'signature_file'])]
class Invoice extends Model
{
    use SoftDeletes;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'id');
    }
}
