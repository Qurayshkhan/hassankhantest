<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['delivery_challan_id', 'description', 'quantity', 'unit'])]
class DeliveryChallanItem extends Model
{
     use  SoftDeletes;
}
