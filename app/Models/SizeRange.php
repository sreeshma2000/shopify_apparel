<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SizeRange extends Model
{
    protected $fillable=[
        'size_range_id',
        'name',
        'is_product',
        'is_component',
        'sizes'
    ];
}
