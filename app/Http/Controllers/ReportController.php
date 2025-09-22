<?php

namespace App\Http\Controllers;

use App\Models\InventoryReport;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Artisan;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = InventoryReport::select(
                'am_sku_id',
                'shopify_sku_id',
                'product_name',
                'am_quantity_available',
                'shopify_quantity_available',
                'shopify_barcode',
                'am_upc_display'
            );

            return DataTables::of($query)->make(true); 
        }

        return view('reports.inventory_list');
    }
    public function sync(Request $request)
    {
        try {
            $exitCode = Artisan::call('fetch:shopify-ineventory');
            $output = Artisan::output();

            $message = $exitCode === 0
                ? 'Inventory synced successfully!'
                : 'Inventory sync failed!';

            return response()->json([
                'success' => $exitCode === 0,
                'message' => $message,
                'output' => $output
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'output' => $e->getTraceAsString()
            ], 500);
        }
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
}
