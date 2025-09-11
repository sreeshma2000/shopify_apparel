<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
     protected $fillable = [
        'product_id',
        'size_range_id',
        'is_product',
        'is_component',
        'style_number',
        'title',
        'total_variants',
        'price',
        'description',
        'image',
        'shopify_product_id',
        'shopify_handle'
      ];
}
