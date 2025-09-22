<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryReport extends Model
{
    protected $fillable = ['am_sku_id', 'shopify_sku_id','product_name','am_quantity_available','shopify_quantity_available','shopify_barcode','am_upc_display']; 

}
