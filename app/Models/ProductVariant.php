<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable=[
       'product_id',
       'shopify_sku',
        'sku_id',
        'sku_concat',
        'sku_alt',
        'color',
        'size',
        'style_number',
        'shopify_product_id',
        'shopify_variant_id',
        'shopify_inventory_item_id',
        'inventory_item_gid',
        'inventory_level_gid',
        'shopify_barcode',
        'upc_display',
        'price'
    ];
}

 
