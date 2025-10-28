<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'warehouse_id',
        'product_id',
        'sku_id',
        'date_start',
        'date_due',
        'date_ex_factory',
        'qty',
        'qty_open',
        'qty_cxl',
        'qty_in_transit',
        'qty_received',
        'style_number',
        'description',
        'size',
        'upc',
        'upc_display',
        'sku_alt',
        'unit_cost',
        'amount',
        'is_taxable',
        'notes'
    ];
}
