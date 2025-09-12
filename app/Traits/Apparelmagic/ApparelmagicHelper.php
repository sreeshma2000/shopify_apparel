<?php

namespace App\Traits\Apparelmagic;

use App\Models\AmAccount;
use App\Models\AmCurrency;
use App\Models\AmCustomer;
use App\Models\AmDivision;
use App\Models\AmOrder;
use App\Models\AmOrderItem;
use App\Models\AmWarehouse;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\SizeRange;
use App\Traits\ApiHelper;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

trait ApparelmagicHelper
{
     use ApiHelper;

    public function createApparelmagicProducts($product, $productVariants)
    {
        // info("productVariant-sizes",$productVariants);
       
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $url = $this->apparelUrl . '/products';
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $sizeRangeName = $this->getSizeRangeByVariant($productVariants);
        if (!is_array($sizeRangeName) || empty($sizeRangeName[1])) {
            return; 
        }
        $imgurl=$this->encodeImageBase64($product);
      
        if(!empty($sizeRangeName)){
        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
        ];

        $header = [];
        $header['style_number'] =$product->shopify_handle;
        $header['description'] =$product->description;   
        $header['is_product'] = 1;
        $header['is_component'] = 0;
        $header['price'] =$product->price;  
        $header['size_range_name'] = $sizeRangeName[1];
        $params['header'] = $header;
        $skus = [];
       
        foreach ($productVariants as $variant) {
            $color = !empty($variant['color']) ? $variant['color'] : 'MALTESE';
            $size=$variant['size'];

            $skus[] = [
                'attr_2' => $color,
                'size' => $size,
                'cost_offset' => '10',
                'active' => '1',
            ];
        }
        $params['sku'] = $skus;
        $params['images'] = [
            [
                'base64' => $imgurl
            ]
        ];
        $response = $this->apparelMagicApiPostRequest($url, $params);
        info("amProducts--1".json_encode($response));


        if(!empty($response['response']) && !isset($response['status'])){
        $amProducts = $response['response'][0];
        info("amProducts".json_encode($amProducts));

        if (!empty($amProducts)) {
            foreach ($amProducts as $item) {
                $product = Product::where('style_number', $item['style_number'] ?? '')->first();
                Log::info("product table Creats starts".json_encode($product));
                if (!empty($product)) {
                Log::info("product table Creats starts2".json_encode($product));

                    $updated = Product::where('style_number', $item['style_number'] ?? '')
                        ->update([
                            'product_id' => $item['product_id'] ?? null,
                            'size_range_id' => $item['size_range_id'] ?? null,
                            'is_product' => $item['is_product'] ?? null,
                            'is_component' => $item['is_component'] ?? null,
                            'price' => $item['price'] ?? null,
                            'description' => $item['description'] ?? null,
                        ]);
                    Log::info("product table ends starts2".json_encode($updated));


                    $this->getApparelVariants($item);
                }
            }
        }
        }
      }
    }

    //update the product image with existing productId
    
    public function  updateProductWithImages()
    {
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $productId=13952;
        $product = Product::where('product_id', $productId)->first();
        info("product-id".json_encode($product));
        $url = $this->apparelUrl . "/products/{$productId}";
        $imgurl=$this->encodeImageBase64($product);
        info("imgurl:".json_encode($imgurl));
          $params = [
                'time' => (string) $time,
                'token' => (string) $token,
                'product_id' => (string) $productId,
                'images' => [
                    [
                        'base64' => $imgurl
                    ]
                ]
            ];
        $response=$this->apparelMagicApiPutRequest($url,$params);
        Log::info("image_response",["image_response"=>$response]);
        if(!empty($response)){
            Log::info("image updated successfully",$response);
        }
        else{

            Log::info("image",$response);
        }
    }

    public function encodeImageBase64($product)
    {
        $imgUrl = $product->image;

        if (empty($imgUrl)) {
            return null;
        }

        try {
            $imageContent = file_get_contents($imgUrl);

            if ($imageContent === false) {
                return null;
            }

            $extension = strtolower(pathinfo(parse_url($imgUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            $mimeType = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };

            $base64String = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
            return $base64String;

        } catch (Exception $e) {
            Log::info("Failed to encode image to Base64: " . $e->getMessage());
            return null;
        }
    }
    public function getProductByStyleNumber($styleNumber)
    {
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $url = $apparelUrl . '/products';
           $params = [
            'time' => (string) $time,
            'token' => (string) $token,
              'parameters' => [
                    [
                        'field' => 'style_number',
                        'value' =>$styleNumber,
                        'operator' => '=',
                        'include_type' => 'AND'
                    ],
                ]
                ];
            $response=$this->apparelMagicApiRequest($url,$params);
            Log::info("response".json_encode($response));
            return $response;

    }
  
    public function getApparelVariants($item)
    {
        // dd($item);
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $inventoryUrl = $this->apparelUrl . '/inventory/';
        $inventoryParams = [
            'time' => $time,
            'token' => $token,
            'parameters' => [
                [
                    'field' => 'product_id',
                    'value' => $item['product_id'],
                    'operator' => '=',
                    'include_type' => 'AND'
                ],
            ]
        ];
        $inventory = $this->apparelMagicApiRequest($inventoryUrl, $inventoryParams);
        Log::info("response".json_encode($inventory));
        if (!empty($inventory['response']) && !isset($inventory['status'])) { 
        $inventoryItems = $inventory['response'];
        Log::info("inventory response".json_encode($inventoryItems));
        foreach ($inventoryItems as $variantData) {
           ProductVariant::updateOrCreate(
                [
                    'style_number'=>$variantData['style_number'] ?? null,
                    'color' => !empty($variantData['attr_2']) ? $variantData['attr_2'] : 'MALTESE',
                    'size'  => $variantData['size'] ?? null,
                ],
                [
                    'product_id' => $variantData['product_id'] ?? null,
                    'sku_id'     => $variantData['sku_id'] ?? null,
                    'sku_concat' => $variantData['sku_concat'] ?? null,
                    'sku_alt' => $variantData['sku_alt'] ?? null,
                    'upc_display' => $variantData['upc_display'] ?? null,
                ]
                );
            // info(json_encode($productVariant));
            $sku_id = $variantData['sku_id'] ?? null;
            $sku_alt     = $variantData['sku_alt'] ?? null;
            $upc_display = $variantData['upc_display'] ?? null;
            if ($sku_id && (empty($sku_alt) || empty($upc_display))) {
               $this->fetchApparelmagicInventory($settings, $sku_id);
            }
        }
        }
      
    }

   public function fetchApparelmagicInventory($settings, $sku_id)
    {
        $settings = Setting::where('type', 'apparelmagic')->where('status', 1)->get();
        // $productId = Product::where('am_product_id')->first();
        // $productId = '2103';
        // $sku_id = ProductVariant::where('sku_id', $inventoryId)->first();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $baseUrl = $this->apparelUrl . '/inventory';
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $variant=ProductVariant::where('sku_id',$sku_id)->first();
        // dd($variant);
        $shopify_sku= $variant->shopify_sku;
        $shopifybarcode=$variant->shopify_barcode;

        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
            'sku_id' => (string) $sku_id,
        ];

        $inventories = $this->apparelMagicApiRequest($baseUrl, $params);

        if (!empty($inventories['response'])&&!isset($inventory['status'])) {
            foreach ($inventories['response'] as $inventory) {
                $inventoryId = $inventory['sku_id'];
                // $header=[];
                // $header['sku_alt'] = 'MCLSCK74001S';
                // $header['upc_display'] = '195386100014';
                // $header['time'] = $time;
                // $header['token']=$token;
                $params = [
                    'time' => (string) $time,
                    'token' => (string) $token,
                    'sku_id' => $sku_id,
                    'sku_alt' =>  $shopify_sku,
                    'upc_display' => $shopifybarcode,
                ];
                $response = $this->apparelMagicApiPutRequest($baseUrl . '/' . $inventoryId, $params);
                if(!empty($response['response'])&&!isset($response['status'])){
                info("inventory put response:".json_encode($response));
                    $inventoryItems=$response['response'][0];
                    if(!empty($inventoryItems)){
                    ProductVariant::updateOrCreate(
                        [
                            'sku_id' => $sku_id 
                        ],
                        [
                            'sku_alt'     => $inventoryItems['sku_alt'] ?? null,
                            'upc_display' => $inventoryItems['upc_display'] ?? null
                        ]
                    );
                }
            }


            }
        }
    }
    public function apparelSizeRanges()
    {
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $url = $this->apparelUrl . '/size_ranges';
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $params = [
            'time' => (string) $time,
            'token' => (string) $token,
        ];

        $response = $this->apparelMagicApiRequest($url, $params);

        if (!empty($response)) {
            $sizeRanges = $response['response'];

            foreach ($sizeRanges as $range) {
                $sizes = [];

                for ($i = 1; $i <= 30; $i++) {
                    $key = 'size_' . str_pad($i, 2, '0', STR_PAD_LEFT);
                    if (!empty($range[$key])) {
                        $sizes[] = $range[$key];
                    }
                }

                if (!empty($sizes)) {
                    SizeRange::updateOrCreate(
                     ['size_range_id' => $range['id']],  
                        [
                            'name'         => $range['name'],
                            'sizes'        => json_encode($sizes),
                            'is_product'   => (bool) $range['is_product'],
                            'is_component' => (bool) $range['is_component'],
                        ]
                    );
                }

            }

            return $sizeRanges;
        }

        return [];
    }

    //get  size range name corresponsding sizes of variant
    public function getSizeRangeByVariant($productVariants)
    {
        Log::info('productVariants ' . json_encode($productVariants));
        $shopify_sizes = [];
        foreach ($productVariants as $variant) {
            $shopify_sizes[] = $variant['size'];
            Log::info("shopify_sizes".json_encode($shopify_sizes));
        }
        $sizeRanges = SizeRange::all()->toArray();

        if (empty($sizeRanges)) {
            $this->apparelSizeRanges(); 
            $sizeRanges = SizeRange::all()->toArray();
            // dd($sizeRanges);
        }
        Log::info("sizeRanges".json_encode($sizeRanges));

        $matchingIds = collect($sizeRanges)
            ->filter(function ($item) use ($shopify_sizes) {
                $sizes = json_decode($item['sizes'], true);
                return collect($shopify_sizes)->every(fn($size) => in_array($size, $sizes));
            })
            ->pluck('name')
            ->toArray();
            Log::info("Matching IDs: " . json_encode($matchingIds));
        return $matchingIds;
    }

    public function getAmCustomer($page_size = 100, $startAfter = null, $settings)
    {
        try {
            $settings = Setting::where('type', 'apparelmagic')->where('status', 1)->get();
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/customers';
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $time = time();

            $params = [
                'time' => (string) $time,
                'token' => (string) $token,
                'pagination' => [
                    'page_size' => $page_size
                ],
            ];

            if ($startAfter) {
                $params['pagination']['last_id'] = $startAfter;
            }

            $customers = $this->apparelMagicApiRequest($baseUrl, $params);

            if (!empty($customers['response']) && !isset($customers['status'])) {
                $fetchedCustomers = $customers['response'];

                foreach ($fetchedCustomers as $cust) {
                    AmCustomer::updateOrCreate(
                        ['am_customer_id' => $cust['customer_id']],
                        ['name' => $cust['customer_name'],
                        'status' => 1]
                    );
                }
            }

        } catch (Exception $e) {
            Log::error('Exception while fetching customers', ['error' => $e->getMessage()]);
        }
    }

    public function getAmWarehouses($settings)
    {
        try {
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/warehouses';
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $time = time();
            
            $params = [
                'time'  => (string) $time,
                'token' => (string) $token,
            ];

            $warehouses = $this->apparelMagicApiRequest($baseUrl, $params);

            if (!empty($warehouses['response']) && !isset($warehouses['status'])) {
                $fetchedWarehouses = $warehouses['response'];
                Log::info("warehouses: " . json_encode($fetchedWarehouses));

                foreach ($fetchedWarehouses as $warehouse) {
                    AmWarehouse::updateOrCreate(
                        ['warehouse_id' => $warehouse['id']],
                        ['name' => $warehouse['name'],
                                'status' => 1
                        ]
                    );
                }
            }

            Log::info("warehouses: " . json_encode($warehouses));
        } catch (Exception $e) {
            Log::error('Exception while fetching warehouses', ['error' => $e->getMessage()]);
        }
    }

    public function getAmDivision($settings)
    {
        try {
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/divisions';
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $time = time();
            $params = [
                'time'  => (string) $time,
                'token' => (string) $token,
            ];

            $divisions = $this->apparelMagicApiRequest($baseUrl, $params);

            if (!empty($divisions['response']) && !isset($divisions['status'])) {
                $fetchedDivisions = $divisions['response'];
                Log::info("divisions: " . json_encode($fetchedDivisions));

                foreach ($fetchedDivisions as $division) {
                    AmDivision::updateOrCreate(
                        ['division_id' => $division['id']],
                        ['name' => $division['name'],
                        'status' => 1
                        ]
                    );
                }
            }

            Log::info("divisions: " . json_encode($divisions));
        } catch (Exception $e) {
            Log::error('Exception while fetching divisions', ['error' => $e->getMessage()]);
        }
    }
    public function getAmCurrency($settings)
     { 
        try { 
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value; 
            $currencyUrl = $this->apparelUrl . '/currencies';
             $time = time(); 
             $token = $settings->firstWhere('code', 'apparelmagic_token')->value; 
             $params = [ 'time' => (string) $time, 'token' => (string) $token, ]; 
             $response = $this->apparelMagicApiRequest($currencyUrl, $params); 
             if (!empty($response['response']) && !isset($response['status'])) 
                { 
                    $fetchedCurrency = $response['response'];
                     Log::info("currencies".json_encode($fetchedCurrency)); 
                     $dataset = []; foreach ($fetchedCurrency as $currency) 
                     { 
                        AmCurrency::updateOrCreate( 
                            ['currency_id' => $currency['id']], ['name' => $currency['name']] ); 
                        $dataset[] = [ 'id' => $currency['id'], 'name' => $currency['name'] ]; 
                    }  
                } else { 
                    $this->logApi('Apparelmagic', 'APPAREL_FETCH_CURRENCY', '', 'GET', $currencyUrl, $params, json_encode($response), 500, 'Empty response from API'); 
                } 
                Log::info("currencies".json_encode($response)); 
            } catch (Exception $e) { 
                Log::error('Exception while fetching currencies', ['error' => $e->getMessage()]); 
                return []; 
            } 
    }


    public function getAmAccount($settings)
    {
        try {
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/chart_of_accounts';
            $time = time();
            $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $params = [
                'time'  => (string) $time,
                'token' => (string) $token,
            ];

            $response = $this->apparelMagicApiRequest($baseUrl, $params);

            if (!empty($response['response']) && !isset($response['status'])) {
                $fetchedAccount = $response['response'];
                Log::info("accounts: " . json_encode($fetchedAccount));

                foreach ($fetchedAccount as $account) {
                    AmAccount::updateOrCreate(
                    ['account_id' => $account['account_id']],
                    [
                        'name' => $account['description'],
                        'status' => 1 
                    ]
                );
                }
            } else {
                $this->logApi('Apparelmagic', 'APPAREL_FETCH_ACCOUNT', '', 'GET', $baseUrl, $params, json_encode($response), 500, 'Empty response from API');
            }

            Log::info("accounts: " . json_encode($response));
        } catch (Exception $e) {
            Log::error('Exception while fetching accounts', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function createAmOrders($shopifyOrder)
    {
        try {
            Log::info("orderitems in apparel " . json_encode($shopifyOrder));

            $settings = Setting::where('type', 'apparelmagic')->where('status', 1)->get();
            $this->apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
            $baseUrl = $this->apparelUrl . '/orders';
            $token   = $settings->firstWhere('code', 'apparelmagic_token')->value;
            $time    = time();

            $division_id  = $settings->firstWhere('code', 'apparelmagic_division')->value;
            $warehouse_id = $settings->firstWhere('code', 'apparelmagic_location')->value;
            $customer_id  = $settings->firstWhere('code', 'apparelmagic_customer')->value;
            $account_id   = $settings->firstWhere('code', 'apparelmagic_account')->value;
            // dd($account_id);

            $header = [];
            $header['customer_id'] = $customer_id;
            $header['division_id'] = $division_id;
            $header['ar_acct']     = $account_id;
            $header['warehouse_id'] = $warehouse_id;
            $header['currency_id']  = '1000';

            $datecreated  = isset($shopifyOrder['shopify_created_at']) ? $shopifyOrder['shopify_created_at'] : date('Y-m-d');
            $formateddate = DateTime::createFromFormat('Y-m-d', $datecreated);
            $datecreated  = $formateddate ? $formateddate->format('m/d/Y') : date('m/d/Y');

            $header['date']       = $datecreated;
            $header['customer_po'] = $shopifyOrder['shopify_order_name']??'';
            $header['date_start'] = $datecreated;
            $header['source']     = 'Shopify Wholesale';
            $header['notes']      = $shopifyOrder['shopify_notes'] ?? '';
            $header['amount']     = (float) $shopifyOrder['shopify_shipping_total'];
            $header['name']       = trim(($shopifyOrder['shopify_customer_firstname'] ?? '') . ' ' . ($shopifyOrder['shopify_customer_lastname'] ?? ''));

            $header['address_1']  = $shopifyOrder['shopify_shipping_address1'] ?? '';
            $header['address_2']  = $shopifyOrder['shopify_shipping_address2'] ?? '';
            $header['city']       = $shopifyOrder['shopify_shipping_city'] ?? '';
            $header['postal_code']= $shopifyOrder['shopify_shipping_zip'] ?? '';
            $header['country']    = $shopifyOrder['shopify_shipping_country'] ?? '';
            $header['state']      = $shopifyOrder['shopify_shipping_provincecode'] ?? '';
            $header['phone']      = $shopifyOrder['shopify_shipping_phone'] ?? '';
            $header['email']      = $shopifyOrder['shopify_email'] ?? '';

            $items = [];
            $orderItems = $shopifyOrder->order_items ?? [];
log::info('orderItems: '.json_encode($orderItems));
            foreach ($orderItems as $item) {
                $variant = ProductVariant::where('shopify_sku', $item['shopify_sku'])->whereNotNull('product_id')->first();
               // Log::info("variant".json_encode($variant));
                if (!$variant) {
                    continue; 
                }
                $quantity = $item->shopify_quantity; 
                $lineTotal = (float) $item->shopify_amount;
                $unitPrice = $quantity > 0 ? ($lineTotal / $quantity) : 0;

                $items[] = [
                    'sku'        => $variant->sku_id,   
                    'qty' => (string) $quantity,
                    'unit_price' => (string) $unitPrice,
                    'amount'     => (string) $lineTotal,
                ];
            }
           // Log::info("items in apparel " . json_encode($items));
            $params = [
                'time'   => (string) $time,
                'token'  => (string) $token,
                'header' => $header,
                'items'  => $items,
            ];
            Log::info("params".json_encode($params));
            
            $response = $this->apparelMagicApiPostRequest($baseUrl, $params);
            Log::info("am-order" . json_encode($response));
            if (!empty($response) && !isset($response['status'])) {
                $amOrders = $response['response'];
                info("am order response".json_encode($amOrders));
                foreach ($amOrders as $order) {
                    $orderDetail=AmOrder::updateOrCreate(
            ['shopify_order_id' => $shopifyOrder['shopify_order_id']],
                    
                        [
                            'order_id'      =>$order['order_id']??null,
                            'customer_id'   => $order['customer_id'] ?? null,
                            'division_id'   => $order['division_id'] ?? null,
                            'warehouse_id'  => $order['warehouse_id'] ?? null,
                            'currency_id'   => $order['currency_id'] ?? null,
                            'ar_acct'       => $order['ar_acct'] ?? null,
                            'date'          => isset($order['date']) ? Carbon::parse($order['date'])->format('Y-m-d') : null,
                            'date_start'    => isset($order['date_start']) ?Carbon::parse($order['date_start'])->format('Y-m-d') : null,
                            'source'        => $order['source'] ?? null,
                            'notes'         => $order['notes'] ?? null,
                            'name'          => $order['name'] ?? null,
                            'amount_open' => $order['amount_open'] ?? 0,
                            'customer_po'=> $order['customer_po']??null,
                            'credit_status' =>$order['credit_status']??null,
                            'address_1'     => $order['address_1'] ?? null,
                            'address_2'     => $order['address_2'] ?? null,
                            'fulfillment_status' =>$order['fulfillment_status'] ?? null,
                            'city'          => $order['city'] ?? null,
                            'postal_code'   => $order['postal_code'] ?? null,
                            'country'       => $order['country'] ?? null,
                            'state'         => $order['state'] ?? null,
                            'phone'         => $order['phone'] ?? null,
                            'email'         => $order['email'] ?? null,
                            'created_at'    => $order['creation_time'] ?? '',
                        ]
                    );
                    if (!empty($orderDetail) && ($order['credit_status'] ?? '') != 'Pending') {
                        if ($orderDetail->allocated == 0) {
                            if ($this->allocateAmOrder($orderDetail)) {
                                $orderDetail->allocated = 1;
                                $orderDetail->save();
                                Log::info("AM order allocated: " . $orderDetail->shopify_order_id);
                            } else {
                                Log::error("Failed to allocate AM order: " . $orderDetail->shopify_order_id);
                            }

                        } else {
                            Log::info("AM order already allocated: " . $orderDetail->shopify_order_id);
                        }
                    }
                    if (!empty($order['order_items']) && is_array($order['order_items'])) {
                        Log::info("Order items");
                        foreach ($order['order_items'] as $item) {
                                $orderDetail->order_items()->updateOrCreate(
                                    [
                                    'shopify_order_id'     => $shopifyOrder['shopify_order_id'],
                                    'shopify_sku'=>$item['sku_alt'],
                                ],
                                    [
                                        'order_id'     => $item['order_id'] ?? null,
                                        'sku_id'       => $item['sku_id']??null,
                                        'row_id'       => $item['row_id']??null,
                                        'date_due'     =>$item['date_due']??null,
                                        'product_id'   => $item['product_id'] ?? null,
                                        'sku_alt'      => $item['sku_alt'] ?? null,
                                        'upc'          => $item['upc'] ?? null,
                                        'style_number' => $item['style_number'] ?? null,
                                        'description'  => $item['description'] ?? null,
                                        'size'         => $item['size'] ?? null,
                                        'qty'          => $item['qty'] ?? 0,
                                        'qty_open'     => $item['qty_open'] ?? 0,
                                        'qty_picked'   =>$item['qty_picked']??0,
                                        'qty_cancelled'=>$item['qty_cxl']??0, 
                                        'qty_shipped'  =>$item['qty_shipped']??0,
                                        'unit_price'   => $item['unit_price'] ?? 0,
                                        'amount'       => $item['amount'] ?? 0,
                                        'is_taxable'   => $item['is_taxable'] ?? '0',
                                        'warehouse_id' => $item['warehouse_id'] ?? $order['warehouse_id'] ?? null,
                                    ]
                                );
                            }
                    }
                }
        }
        } catch (Exception $e) {
            Log::error('Exception while creating Apparelmagic order', ['error' => $e->getMessage()]);
        }
    }
    public function getOrdersByOrderId($orderId)
    {
        $settings = Setting::where(['type' => 'apparelmagic', 'status' => 1])->get();
        $apparelUrl = $settings->firstWhere('code', 'apparelmagic_api_endpoint')->value;
        $token = $settings->firstWhere('code', 'apparelmagic_token')->value;
        $time = time();
        $url = $apparelUrl . '/orders';
           $params = [
            'time' => (string) $time,
            'token' => (string) $token,
              'parameters' => [
                    [
                        'field' => 'customer_po',
                        'value' =>$orderId,
                        'operator' => '=',
                        'include_type' => 'AND'
                    ],
                ]
                ];
            $response=$this->apparelMagicApiRequest($url,$params);
            Log::info("response customer_po".$orderId.json_encode($response));
            return $response;

    }

    public function storeApparelOrders(){
        
    }
    public function allocateOrder($orderDetail)
    {
        $items = $orderDetail->order_items()->where('qty_open', '>', 0)->get();
        if ($items->isEmpty()) {
            Log::info("No open items to allocate for order: " . $orderDetail->shopify_order_id);
            return false;
        }

        $itemIds = $items->pluck('id')->toArray();
        $request = ['item_ids' => $itemIds];

        $allocate = $this->amPut('order_items/force_allocate', $request, 'Allocate order');

        if (!empty($allocate->response)) {
            $orderDetail->allocated = 1;
            $orderDetail->save();
            Log::info("AM order allocated: " . $orderDetail->shopify_order_id);
            return true;
        } else {
            Log::error("Failed to allocate AM order: " . $orderDetail->shopify_order_id, ['response' => $allocate]);
            return false;
        }
    }
}
