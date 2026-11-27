<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['quotation_id', 'pr', 'description', 'unit', 'quantity', 'rate_per_unit', 'line_total'])]
class QuotationItem extends Model
{
    use SoftDeletes;
}
