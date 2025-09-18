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
            $shopifyOrder = AmOrder::with('order_items')
                ->where('shopify_order_id', $orderId)
                ->first();

            if ($shopifyOrder) {
                $response = $this->getAmOrdersByCustomerPo($shopifyOrder->shopify_order_id);
              //  CreateApparelmagicOrders::dispatch($shopifyOrder);
                if (empty($response['response'])) {
                     $this->info("create");
                    CreateApparelmagicOrders::dispatch($shopifyOrder);
                } else {
                    $this->info("update");
                    $item = $response['response'][0];
                    $orderDetail = $this->storeAmOrder($shopifyOrder, $item);
                    if (!empty($orderDetail) && ($order['credit_status'] ?? '') != 'Pending') {
                        if ($orderDetail->allocated == 0) {
                            $response=$this->getAmOrdersByCustomerPo($orderDetail->shopify_order_id);
                            Log::info("Command response".json_encode($response));
                            $amOrder = $response['response'][0];
                            // dd($amOrder['order_items']);
                            $amItems=collect($amOrder['order_items']);
                            $items = $amItems->where('qty_open', '>', 0);
                            $itemIds = $items->pluck('id')->toArray();
                            // Log::info("command ItemIds".json_encode($itemIds));
                            if ($this->allocateAmOrder($orderDetail,$itemIds)) {
                                $orderDetail->allocated = 1;
                                $orderDetail->save();
                                Log::info("AM order allocated: " . $orderDetail->shopify_order_id);
                            } else {
                                $response = $this->getApparelPickTicketsByOrderId($orderDetail->am_order_id);
                                if ($response && isset($response['pick_ticket_id'])) {
                                    $orderDetail->allocated = 1;
                                    $orderDetail->pick_ticket_id=$response['pick_ticket_id'];
                                    $orderDetail->save();

                                }

                                Log::error("Failed to allocate AM order: " . $orderDetail->shopify_order_id);
                            }

                        } else {
                            Log::info("AM order already allocated: " . $orderDetail->shopify_order_id);
                        }

                        if ($orderDetail->allocated == 1) {
                            if (empty($orderDetail->pick_ticket_id)) {
                                $pickticket = $this->createAmPickTicket($orderDetail->am_order_id);
                                if (!empty($pickticket) && isset($pickticket['pick_ticket_id'])) {
                                    $orderDetail->pick_ticket_id = $pickticket['pick_ticket_id'];
                                    $orderDetail->save();
                                    Log::info("AM pick ticket created: " . $pickticket['pick_ticket_id']);
                                } else {
                                    Log::warning("Pick ticket creation failed for order: " . $orderDetail->shopify_order_id);
                                }

                            } else {
                                $pickticket = $this->getAmPickTicket($orderDetail->pick_ticket_id);

                                $pickTicketId = is_object($pickticket) 
                                    ? $pickticket->pick_ticket_id 
                                    : ($pickticket['pick_ticket_id'] ?? null);

                                if ($pickTicketId) {
                                    Log::info("AM pick ticket already exists: " . $pickTicketId);
                                } else {
                                    Log::warning("Failed to fetch existing pick ticket for order: " . $orderDetail->shopify_order_id);
                                    Log::debug("Pick ticket raw response: " . json_encode($pickticket));
                                }
                            }

                        }
                    }
                }
            }
        } else {
            $shopifyOrders = AmOrder::with('order_items')->whereNotNull('shopify_order_id')->get();

            foreach ($shopifyOrders as $shopifyOrder) {
                $response = $this->getAmOrdersByCustomerPo($shopifyOrder->shopify_order_id);

                if (empty($response['response'])) {
                    CreateApparelmagicOrders::dispatch($shopifyOrder);
                } else {
                    $item = $response['response'][0];
                    $orderDetail = $this->storeAmOrder($shopifyOrder, $item);

                }
            }
        }
    }
}
