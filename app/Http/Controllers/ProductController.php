<?php

namespace App\Http\Controllers;

use App\Jobs\ApparelMagic\CreateApparelMagicProducts;
use App\Jobs\ApparelMagic\CreateApparelProduct;
use App\Jobs\Apparelmagic\CreateApparelProducts;
use App\Jobs\Shopify\GetShopifyProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\Facades\DataTables;


class ProductController extends Controller
{
    use ShopifyHelper,ApparelMagicHelper;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,Datatables $datatables)
    {
        if ($request->ajax()) {
            $query = Product::select('product_id','style_number','price','description','shopify_handle','image','shopify_product_id','title','total_variants');

            return  datatables()->eloquent($query)
                ->addColumn('image', function (Product $product) {
                    $image = $product->image
                    ? $product->image  
                    : asset('assets/images/no-image.png');
                    return '<img src="' . $image . '" class="rounded" width="40" height="50" alt="Product">';
                })

                ->editColumn('status', function (Product $product) {
                    return $product->status
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->rawColumns(['image'])
                ->make(true);
        }

        return view('products.list');
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

    public function fetchProducts()
    {
        try {
            $settings = Setting::where('type', 'shopify')
                ->where('status', 1)
                ->get();

            $limit = 200;
            $reverse = false;
            $nextPageCursor = null;
            $variantCount = 10;

            GetShopifyProduct::dispatch((int) $limit, $reverse, $variantCount, $nextPageCursor, $settings);

            return response()->json([
                'status' => true,
                'message' => 'Product fetch has been started. You will see updates shortly.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to start product fetch.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function createAmProducts(Request $request)
    {
        $productId = $request->product_id;
        $sync_all  = $request->sync_all;

    if ($productId) {
        Artisan::call('create:am-products', [
            '--productId' => $productId
        ]);

        $product = Product::where('shopify_product_id', $productId)->first();

        if (!$product) {
            return response()->json([
                'status'  => false,
                'message' => "Shopify Product ID {$productId} not found. Please enter a valid Shopify Product ID."
            ]);
        }
        return response()->json([
            'status'  => 'success',
            'message' => "Product {$productId} processed for AM"
        ]);
    }

    if ($sync_all == 1) {
        Artisan::call('create:am-products');

        return response()->json([
            'status'  => 'success',
            'message' => 'All products processed for AM'
        ]);
    }

    return response()->json([
        'status'  => 'error',
        'message' => 'No product ID or sync_all flag provided'
    ], 400);
}

    // return response()->json(['status' => 'success', 'message' => 'All products processed for AM']);

}

