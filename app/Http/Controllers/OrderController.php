<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\AmOrder;
use App\Models\AmOrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         if ($request->ajax()) {
            $query = AmOrder::select('order_id','shopify_order_id','name','customer_po','email','phone','amount','fulfillment_status')
            ->orderBy('id', 'asc');

            return DataTables::of($query) 
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
        //
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

    public function fetchOrders(){
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
}
