<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\AmOrder;
use App\Models\AmOrderItem;
use App\Models\AmReturn;
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
                'pick_ticket_id',  
                'ship_id',
                'amount',
                'shopify_fulfillment_status',
                'is_cancelled',
                'fulfillment_status',
                'payment_id',
                'allocated'
            )->orderBy('id', 'asc');
            return DataTables::of($query)
             ->addColumn('status_message', function ($order) {
                    if($order->is_cancelled ==1){
                        return '<span class="badge bg-danger">Order Cancelled</span>';
                    }elseif (!empty($order->shopify_fulfillment_status == 'FULFILLED')) {
                        return '<span class="badge bg-warning text-dark">Shopify order fulfilled</span>';
                    } elseif (!empty($order->payment_id)) {
                        return '<span class="badge bg-info">Payment Generated</span>';
                    } elseif (!empty($order->pick_ticket_id)) {
                        return '<span class="badge bg-success">Pickticket generated</span>';
                    } elseif (!empty($order->allocated == 1)) {
                        return '<span class="badge bg-warning text-dark">Apparel Order allocated</span>';
                    } elseif (!empty($order->am_order_id)) {
                        return '<span class="badge bg-info">AM order created</span>';
                    } elseif (!empty($order->shopify_order_id)) {
                        return '<span class="badge bg-warning text-dark">Shopify order fetched</span>';
                    }  
                    return '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function ($order) {
                    // dd($order->is_cancelled);

                    $buttons = '<div class="d-flex">';
                    $buttons .= '<a href="' . route('order.show', $order->id) . '" class="btn btn-sm btn-clean btn-icon text-end ms-2" title="Show">
                        <i class="fa fa-eye"></i>
                    </a>';
                    if (!empty($order->ship_id && $order->shopify_fulfillment_status !== 'FULFILLED')) {
                        $buttons .= '<button class="btn btn-sm btn-success ms-2 fulfil-order-btn" data-id="' . $order->id . '">Fulfil</button>';
                    }

                    if (empty($order->ship_id) && $order->is_cancelled == 0) {
                        $buttons .= '<button class="btn btn-sm btn-danger ms-2 cancel-order-btn" data-id="' . $order->id . '">Cancel</button>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })

                ->rawColumns(['action','status_message'])
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
        $order = AmOrder::with('order_items', 'returns')->findOrFail($id);
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

            $status = true; 
            $message = "Order {$orderId} processed for AM";

            $order = AmOrder::where('shopify_order_id', $orderId)->first();

            if ($order) {
                if ($order->allocated == 0) {
                    $status = false;
                    $message = "Failed to allocate AM order: {$orderId}";
                } elseif (empty($order->pick_ticket_id)) {
                    $status = false;
                    $message = "Pick Ticket not created for AM Order ID {$orderId}";
                }
            }

            return response()->json([
                'status'  => $status, 
                'message' => $message
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
            $result = null;
            $status = true;
            $message = "Fulfilment triggered for Order ID {$order->am_order_id} with tracking number {$request->tracking_number}";

            if ($order) {
                $result = $this->shopifyFulfilment($order);
                Log::info("Result" . json_encode($result));
                $userErrors = $result['data']['fulfillmentCreateV2']['userErrors'] ?? [];

                $errors     = $result['errors'] ?? [];
                if (!empty($errors) || !empty($userErrors)) {
                    $status = false;
                    $messages = [];

                    if (!empty($errors)) {
                        $messages = array_merge($messages, $errors);
                    }

                    if (!empty($userErrors)) {
                        $messages = array_merge($messages, array_column($userErrors, 'message'));
                    }

                    $message = implode(', ', $messages);
                }
            }

            return response()->json([
                'status'  => $status,
                'message' => $message,
                'result'  => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
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
        $status = true; 

        foreach ($orderIds as $amOrderId) {
            $amOrderId = trim($amOrderId);
            $order = AmOrder::where('am_order_id', $amOrderId)->first();

            if (!$order) {
                $messages[] = "AM Order ID {$amOrderId} not found";
                $status = false;
                continue;
            }

            $pickticketId = $order->pick_ticket_id;
            if (!$pickticketId) {
                $messages[] = "Pick Ticket not found for AM Order ID {$amOrderId}";
                $status = false;
                continue;
            }

            $result = $this->amShipments($pickticketId);
            Log::info("result in controller".json_encode($result));

            $errors = $result['meta']['errors'] ?? $result['response']['meta']['errors'] ?? [];
            if (!empty($errors)) {
                $messages[] = "AM Order {$amOrderId}: " . implode(", ", $errors);
                $status = false;
            } else {
                $messages[] = "AM Order {$amOrderId} shipment created successfully";
            }
        }

        return response()->json([
            'status' => $status, 
            'message' => implode("\n", $messages)
        ]);
    }


    public function cancelOrder(Request $request)
    {
        try {
            $orderId = $request->order_id;
            $order   = AmOrder::find($orderId);

            if (!$order || !$order->am_order_id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid order selected',
                ]);
            }

            if ($order->is_cancelled == 1) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Order is already cancelled',
                ]);
            }
            $amorderId = $order->am_order_id;

            $amOrderResponse = $this->getOrdersByOrderId($amorderId);

            if (empty($amOrderResponse) || empty($amOrderResponse['response'])) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Order not found in ApparelMagic, cannot cancel',
                ]);
            }

            $response = $this->cancelApparelOrder($amorderId);

            if (!empty($response)) {
                Log::info("Updating order {$order->id} as cancelled");
                $order->update(['is_cancelled' => 1]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Order cancelled successfully',
                    'data'    => $response
                ]);
            }


            return response()->json([
                'status'  => false,
                'message' => 'Failed to cancel the order in ApparelMagic',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function createReturn(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',  
            'reason'   => 'required|string',
        ]);

        try {
            $order = AmOrder::with('order_items')->findOrFail($request->order_id);
            // dd($order);
            $reason = $request->reason;
            $response = $this->createAmReturn($order, $reason);

            return response()->json([
                'status'  => true,
                'message' => 'Return created successfully',
                'data'    => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createCreditMemo(Request $request)
    {
        $request->validate([
                'order_id' => 'required|integer',  
            ]);

            try {
                $order = AmOrder::with('order_items')->findOrFail($request->order_id);
                // dd($order);
                $response = $this->createAmCreditMemo($order);

                return response()->json([
                    'status'  => true,
                    'message' => 'Credit memo created successfully',
                    'data'    => $response
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => $e->getMessage()
                ], 500);
            }
    }

    public function createRefund(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',  
        ]);

        try {
            $order = AmOrder::with('order_items')->findOrFail($request->order_id);
            $response = $this->createAmRefund($order);

            if (!empty($response['refund_id'])) {
                $order->update(['refund_status' => $response['refund_id']]);
                return response()->json([
                    'status'  => true,
                    'message' => 'Refund created successfully',
                    'data'    => $response
                ]);
            }

            if (!empty($response['meta']['errors'])) {
                return response()->json([
                    'status'  => false,
                    'message' => implode("\n", $response['meta']['errors']), 
                ]);
            }

            return response()->json([
                'status'  => false,
                'message' => $response['message'] ?? 'Refund failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
