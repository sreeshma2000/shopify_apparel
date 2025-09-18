<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmOrder extends Model
{
    protected $fillable = [
        'am_order_id',
        'warehouse_id',
        'customer_id',
        'division_id',
        'ar_acct',
        'shopify_fulfillment_status',
        'currency_id',
        'shopify_order_id',
        'shopify_order_name',
        'date',
        'date_start',
        'notes',
        'amount',
        'name',
        'address_1',
        'address_2',
        'city',
        'postal_code',
        'country',
        'state',
        'phone',
        'email',
        'customer_po',
        'credit_status',
        'qty',
        'qty_cancelled',
        'qty_shipped',
        'ship_via',
        'amount_paid',
        'fulfillment_status',
        'balance',
        'pick_ticket_id',
        'ship_id',
        'am_invoice_id',
        'is_cancelled',
        'allocated',
        'shopify_email',
        'shopify_customer_id',
        'shopify_customer_firstname',
        'shopify_customer_lastname',
        'shopify_shipping_address1',
        'shopify_shipping_address2',
        'shopify_shipping_city',
        'shopify_shipping_zip',
        'shopify_shipping_country',
        'shopify_shipping_provincecode',
        'shopify_shipping_phone',
        'shopify_notes',
        'shopify_shipping_total',
        'shopify_created_at',
        'amount_open',
        'payment_id',
        'refund_status',
    ];

    public function order_items(){
       return $this->hasMany(AmOrderItem::class,'shopify_order_id','shopify_order_id');
    }
    public function returns()
    {
        return $this->hasMany(AmReturn::class, 'am_order_id', 'am_order_id');
    }

}
