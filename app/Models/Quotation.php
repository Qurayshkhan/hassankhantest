<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Fillable(['user_id', 'company_id','quotation_number', 'date', 'validity'])]
class Quotation extends Model
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
        return $this->hasMany(QuotationItem::class, 'quotation_id', 'id');
    }
}
