<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Fillable(['description', 'unit', 'quantity', 'amount', 'rate'])]
class SesItem extends Model
{
    use SoftDeletes;
}
