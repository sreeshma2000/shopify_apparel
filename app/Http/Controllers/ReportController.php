<?php

namespace App\Http\Controllers;

use App\Models\InventoryReport;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    public function inventoryExport()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = ['AM SKU ID','Shopify SKU','Product Name','AM Qty','Shopify Qty','Shopify Barcode','AM UPC'];
        $sheet->fromArray($columns, null, 'A1');

        $reports = InventoryReport::all();
        $row = 2;

        foreach ($reports as $item) {
            $sheet->setCellValueExplicit("A{$row}", $item->am_sku_id, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$row}", $item->shopify_sku_id, DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $item->product_name ?? '--');
            $sheet->setCellValue("D{$row}", $item->am_quantity_available ?? 0);
            $sheet->setCellValue("E{$row}", $item->shopify_quantity_available ?? 0);
            $sheet->setCellValueExplicit("F{$row}", $item->shopify_barcode ?? '--', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$row}", $item->am_upc_display ?? '--', DataType::TYPE_STRING);
            $row++;
        }

        foreach (range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'inventory_report_' . now()->format('Ymd_His') . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);

        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
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
