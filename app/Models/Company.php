<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Fillable(['name', 'branch', 'address', 'strn', 'ntn'])]
class Company extends Model
{
    use SoftDeletes;
}
