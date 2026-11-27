<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\{Table, Fillable, Hidden, Casts};
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['customer_name', 'delivery_date', 'signature_text', 'user_id', 'signature_file', 'po_number', 'challan_number', 'company_id'])]
class DeliveryChallan extends Model
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
        return $this->hasMany(DeliveryChallanItem::class);
    }

}
