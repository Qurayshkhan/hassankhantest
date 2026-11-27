<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Fillable(['user_id', 'company_id', 'document_number', 'date', 'po_number', 'rgp', 'igp', 'document_number', 'signature_file', 'signature_text'])]
class Ses extends Model
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
        return $this->hasMany(SesItem::class, 'ses_id', 'id');
    }
}
