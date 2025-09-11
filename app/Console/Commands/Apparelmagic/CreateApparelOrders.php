<?php

namespace App\Console\Commands\Apparelmagic;

use App\Jobs\Apparelmagic\CreateApparelmagicOrders;
use App\Models\AmOrder;
use App\Traits\Apparelmagic\ApparelmagicHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreateApparelOrders extends Command
{
    use ApparelmagicHelper;

    protected $signature = 'create:apparel-orders {--orderId=}';
    protected $description = 'Create or update ApparelMagic orders from Shopify and save into database';

    public function handle()
    {
        $orderId = $this->option('orderId');

        if ($orderId) {
            // fetch single order
            $shopifyOrder = AmOrder::with('order_items')
                ->where('shopify_order_id', $orderId)
                ->first();

            if ($shopifyOrder) {
                $response = $this->getOrdersByOrderId($shopifyOrder->shopify_order_id);

                if (empty($response['response'])) {
                    CreateApparelmagicOrders::dispatch($shopifyOrder);
                } else {
                    info("update");
                    $item = $response['response'][0];

                    $orderDetail = AmOrder::updateOrCreate(
                        ['shopify_order_id' => $shopifyOrder->shopify_order_id],
                        [
                            'order_id'       => $item['order_id'] ?? null,
                            'customer_id'    => $item['customer_id'] ?? null,
                            'division_id'    => $item['division_id'] ?? null,
                            'warehouse_id'   => $item['warehouse_id'] ?? null,
                            'currency_id'    => $item['currency_id'] ?? null,
                            'ar_acct'        => $item['ar_acct'] ?? null,
                            'date'           => !empty($item['date']) ? Carbon::parse($item['date'])->format('Y-m-d') : null,
                            'date_start'     => !empty($item['date_start']) ? Carbon::parse($item['date_start'])->format('Y-m-d') : null,
                            'source'         => $item['source'] ?? null,
                            'notes'          => $item['notes'] ?? null,
                            'name'           => $item['name'] ?? null,
                            'customer_po'    => $item['customer_po'] ?? null,
                            'address_1'      => $item['address_1'] ?? null,
                            'address_2'      => $item['address_2'] ?? null,
                            'fulfillment_status' => $item['fulfillment_status'] ?? null,
                            'city'           => $item['city'] ?? null,
                            'postal_code'    => $item['postal_code'] ?? null,
                            'country'        => $item['country'] ?? null,
                            'state'          => $item['state'] ?? null,
                            'phone'          => $item['phone'] ?? null,
                            'email'          => $item['email'] ?? null,
                            'created_at'     => $item['creation_time'] ?? now(),
                        ]
                    );

                    if (!empty($item['order_items']) && is_array($item['order_items'])) {
                        foreach ($item['order_items'] as $orderItem) {
                            $orderDetail->order_items()->updateOrCreate(
                                [
                                    'shopify_order_id' => $shopifyOrder->shopify_order_id,
                                    'shopify_sku'      => $orderItem['sku_alt'] ?? null,
                                ],
                                [
                                    'order_id'     => $orderItem['order_id'] ?? null,
                                    'sku_id'       => $orderItem['sku_id'] ?? null,
                                    'row_id'       => $orderItem['row_id'] ?? null,
                                    'date_due'     => $orderItem['date_due'] ?? null,
                                    'product_id'   => $orderItem['product_id'] ?? null,
                                    'sku_alt'      => $orderItem['sku_alt'] ?? null,
                                    'upc'          => $orderItem['upc'] ?? null,
                                    'style_number' => $orderItem['style_number'] ?? null,
                                    'description'  => $orderItem['description'] ?? null,
                                    'size'         => $orderItem['size'] ?? null,
                                    'qty'          => $orderItem['qty'] ?? 0,
                                    'qty_picked'   => $orderItem['qty_picked'] ?? 0,
                                    'qty_cancelled'=> $orderItem['qty_cxl'] ?? 0,
                                    'qty_shipped'  => $orderItem['qty_shipped'] ?? 0,
                                    'unit_price'   => $orderItem['unit_price'] ?? 0,
                                    'amount'       => $orderItem['amount'] ?? 0,
                                    'is_taxable'   => $orderItem['is_taxable'] ?? '0',
                                    'warehouse_id' => $orderItem['warehouse_id'] ?? $item['warehouse_id'] ?? null,
                                ]
                            );
                        }
                    }
                }
            }
        } else {
            $shopifyOrders = AmOrder::with('order_items')->whereNotNull('shopify_order_id')->get();

            foreach ($shopifyOrders as $shopifyOrder) {
                $response = $this->getOrdersByOrderId($shopifyOrder->shopify_order_id);

                if (empty($response['response'])) {
                    CreateApparelmagicOrders::dispatch($shopifyOrder);
                } else {
                    $item = $response['response'][0];

                    $orderDetail = AmOrder::updateOrCreate(
                        ['shopify_order_id' => $shopifyOrder->shopify_order_id],
                        [
                            'order_id'       => $item['order_id'] ?? null,
                            'customer_id'    => $item['customer_id'] ?? null,
                            'division_id'    => $item['division_id'] ?? null,
                            'warehouse_id'   => $item['warehouse_id'] ?? null,
                            'currency_id'    => $item['currency_id'] ?? null,
                            'ar_acct'        => $item['ar_acct'] ?? null,
                            'date'           => !empty($item['date']) ? Carbon::parse($item['date'])->format('Y-m-d') : null,
                            'date_start'     => !empty($item['date_start']) ? Carbon::parse($item['date_start'])->format('Y-m-d') : null,
                            'source'         => $item['source'] ?? null,
                            'notes'          => $item['notes'] ?? null,
                            'name'           => $item['name'] ?? null,
                            'customer_po'    => $item['customer_po'] ?? null,
                            'address_1'      => $item['address_1'] ?? null,
                            'address_2'      => $item['address_2'] ?? null,
                            'fulfillment_status' => $item['fulfillment_status'] ?? null,
                            'city'           => $item['city'] ?? null,
                            'postal_code'    => $item['postal_code'] ?? null,
                            'country'        => $item['country'] ?? null,
                            'state'          => $item['state'] ?? null,
                            'phone'          => $item['phone'] ?? null,
                            'email'          => $item['email'] ?? null,
                            'created_at'     => $item['creation_time'] ?? now(),
                        ]
                    );

                    if (!empty($item['order_items']) && is_array($item['order_items'])) {
                        foreach ($item['order_items'] as $orderItem) {
                            $orderDetail->order_items()->updateOrCreate(
                                [
                                    'shopify_order_id' => $shopifyOrder->shopify_order_id,
                                    'shopify_sku'      => $orderItem['sku_alt'] ?? null,
                                ],
                                [
                                    'order_id'     => $orderItem['order_id'] ?? null,
                                    'sku_id'       => $orderItem['sku_id'] ?? null,
                                    'row_id'       => $orderItem['row_id'] ?? null,
                                    'date_due'     => $orderItem['date_due'] ?? null,
                                    'product_id'   => $orderItem['product_id'] ?? null,
                                    'sku_alt'      => $orderItem['sku_alt'] ?? null,
                                    'upc'          => $orderItem['upc'] ?? null,
                                    'style_number' => $orderItem['style_number'] ?? null,
                                    'description'  => $orderItem['description'] ?? null,
                                    'size'         => $orderItem['size'] ?? null,
                                    'qty'          => $orderItem['qty'] ?? 0,
                                    'qty_picked'   => $orderItem['qty_picked'] ?? 0,
                                    'qty_cancelled'=> $orderItem['qty_cxl'] ?? 0,
                                    'qty_shipped'  => $orderItem['qty_shipped'] ?? 0,
                                    'unit_price'   => $orderItem['unit_price'] ?? 0,
                                    'amount'       => $orderItem['amount'] ?? 0,
                                    'is_taxable'   => $orderItem['is_taxable'] ?? '0',
                                    'warehouse_id' => $orderItem['warehouse_id'] ?? $item['warehouse_id'] ?? null,
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
