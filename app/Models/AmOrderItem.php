<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmOrderItem extends Model
{
    protected $fillable = [
        'am_order_id',
        'am_order_item_id',
        'product_id',
        'row_id',
        'sku_id',
        'sku_alt',
        'upc',
        'style_number',
        'description',
        'size',
        'qty',
        'is_taxable',
        'amount',
        'unit_price',
        'warehouse_id',
        'shopify_order_id',
        'shopify_order_name',
        'shopify_sku',
        'qty_picked',
        'qty_cancelled',
        'qty_shipped',
        'date_due',
        'shopify_order_gid',
        'shopify_line_item_id',
        'shopify_title',
        'shopify_quantity',
        'shopify_current_quantity',
        'shopify_variant_id',
        'shopify_variant_title',
        'shopify_fulfillment_order_id',
        'shopify_amount',
        'qty_open'

    ];
    public function order(){
        return $this->belongsTo(AmOrder::class,'shopify_order_id','shopify_order_id');
    }
    public function returns()
    {
        return $this->hasMany(AmReturn::class, 'am_order_id', 'order_id'); 
    }

}
