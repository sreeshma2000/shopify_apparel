<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\AmOrder;
use App\Models\AmOrderItem;
use App\Models\Setting;
use App\Traits\Apparelmagic\ApparelmagicHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use function GuzzleHttp\json_decode;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use ApparelmagicHelper;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = AmOrder::select(
                'id',
                'am_order_id',
                'shopify_order_id',
                'name',
                'customer_po',
                'email',
                'amount',
                'shopify_fulfillment_status',
                'fulfillment_status'
            )->orderBy('id', 'asc');

            return DataTables::of($query)
                ->addColumn('action', function ($order) {
                    return '
                        <div class="d-flex">
                         <button class="btn btn-sm btn-success fulfil-order-btn" 
                                data-id="' . $order->id . '">
                                Fulfil
                            </button>
                            <a href="' . route('order.show', $order->id) . '" 
                                class="btn btn-sm btn-clean btn-icon text-end" 
                                title="Show">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('orders.list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = AmOrder::with('order_items')->findOrFail($id);

        return view('orders.detail', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function fetchOrders()
    {
        try {
            $settings = Setting::where('type', 'shopify')
                ->where('status', 1)
                ->get();

            $limit = 50;
            $reverse = false;
            $nextPageCursor = null;

            GetShopifyOrders::dispatch((int) $limit, $reverse, $nextPageCursor, $settings);

            return response()->json([
                'status' => true,
                'message' => 'Order fetch has been started. You will see updates shortly.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to start product fetch.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function createAmOrders(Request $request)
    {
        $orderId = $request->order_id;
        $sync_all  = $request->sync_all;

        if ($orderId) {
            Artisan::call('create:apparel-orders', [
                '--orderId' => $orderId
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => "Order {$orderId} processed for AM"
            ]);
        }

        if ($sync_all == 1) {
            Artisan::call('create:apparel-orders');

            return response()->json([
                'status'  => 'success',
                'message' => 'All orders processed for AM'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'No orders ID or sync_all flag provided'
        ], 400);
    }

    public function fulfilfulOrder(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer',
                'tracking_number' => 'required|string'
            ]);

            $order = AmOrder::findOrFail($request->order_id);

            if($order){
                $result = $this->shopifyFulfilment($order);

            }

            return response()->json([
                'status' => true,
                'message' => "Fulfilment triggered for Order ID {$order->am_order_id} with tracking number {$request->tracking_number}",
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function createShipmentFromOrder(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|string'
        ]);

        $orderIds = explode(',', $request->order_ids); 
        $messages = [];

        foreach ($orderIds as $amOrderId) {
            $amOrderId = trim($amOrderId);
            $order = AmOrder::where('am_order_id', $amOrderId)->first();

            if (!$order) {
                $messages[] = "AM Order ID {$amOrderId} not found";
                continue;
            }

            $pickticketId = $order->pick_ticket_id;
            if (!$pickticketId) {
                $messages[] = "Pick Ticket not found for AM Order ID {$amOrderId}";
                continue;
            }

            $result = $this->amShipments($pickticketId);
                Log::info("result in controller".json_encode($result));
            }

        return response()->json([
            'status' => true,
            'message' => implode("\n", $messages)
        ]);
    }

}
