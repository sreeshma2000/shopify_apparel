<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmReturn extends Model
{
    protected $fillable = [
        'shopify_order_id',
        'am_order_id',
        'return_authorisation_id',
        'credit_memo_id',
    ];
}
