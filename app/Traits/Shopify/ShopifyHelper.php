<?php

namespace App\Traits\Shopify;

use App\Jobs\Shopify\GetShopifyOrders;
use App\Jobs\Shopify\GetShopifyProduct;
use App\Models\AmOrder;
use App\Models\AmOrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Traits\ApiHelper;
use App\Traits\Apparelmagic\ApparelmagicHelper;
use Exception;
use Illuminate\Support\Facades\Log;

trait ShopifyHelper
{
    use ApiHelper,ApparelmagicHelper;
    public function fetchInventory($limit, $reverse, $variantCount, $nextPageCursor, $settings)
    {
        $locationSetting = $settings->where('code', 'shopify_location')->first();
        $location = null;

        if ($locationSetting) {
            $dataset = json_decode($locationSetting->dataset, true);
            foreach ($dataset as $item) {
                if ($item['id'] == $locationSetting->value) {
                    $location = $item['name']; 
                    break;
                }
            }
        }

        $queryString = '
            query getProducts($limit: Int, $reverse: Boolean, $nextPageCursor: String,$location: ID!) {
            products(first: $limit, reverse: $reverse, after: $nextPageCursor) {
                edges {
                node {
                    id
                    title
                    description
                    handle
                    totalVariants
                    priceRange {
                        minVariantPrice {
                        amount
                        currencyCode
                        }
                        maxVariantPrice {
                        amount
                        currencyCode
                        }
                    }
                    featuredImage{
                        transformedSrc(maxWidth:300, maxHeight:300)
                    }
                        
                    variants(first: 6) {
                    edges {
                        node {
                        id
                        title
                        sku
                        price
                        barcode
                        selectedOptions {
                            name
                            value
                        }
                        inventoryItem {
                                id
                                sku
                                inventoryLevel(locationId:$location) {
                                    id
                                    quantities(names: ["available", "incoming"]) {
                                    name
                                    quantity
                                    }
                                                        
                                }
                            }
                        }
                    }
                    }
                }
                }
                pageInfo 
                {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
            }
            }';

        $variables = ['limit' => $limit, 'reverse' => $reverse, 'nextPageCursor' => $nextPageCursor, 'location' => $location];
        $shopifyresponse = $this->getHttp($queryString, $variables);
        Log::info("Shopify response" . json_encode($shopifyresponse));
        if (!empty($shopifyresponse)) {
            $shopifyProducts = $shopifyresponse['data']['products']['edges'];
            foreach ($shopifyProducts as $product) {
                if($product['node']['id']=='gid://shopify/Product/7240295776433'){
                 Log::info("node id" . json_encode($product));   
                }
                $products = Product::updateOrCreate(
                    [
                        'shopify_product_id' => str_replace('gid://shopify/Product/', '', $product['node']['id'])
                    ],
                    [
                        'total_variants'=> $product['node']['totalVariants'] ?? null,
                        'title'=> $product['node']['title'] ?? null,
                        'description' => $product['node']['description'] ?? null,
                        'shopify_handle' => $product['node']['handle'] ?? null,
                        'style_number' => $product['node']['handle'] ?? null,
                        'price' => $product['node']['priceRange']['minVariantPrice']['amount']  ?? null,
                        'image' => $product['node']['featuredImage']['transformedSrc'] ?? null,
                    ]
                );

                if ($product['node']['totalVariants']) {
                    $variantsCollection = [];
                    foreach ($product['node']['variants']['edges'] as $productVariant) {
                        $size = '';
                        $color = '';
                        foreach ($productVariant['node']['selectedOptions'] as $option) {
                            if ($option['name'] === 'Size') {
                                $size = $option['value'];
                            }
                            if ($option['name'] === 'Color') {
                                $color = $option['value'];
                            }
                        }
                        $productVariants = ProductVariant::updateOrCreate(
                            [
                                'shopify_product_id' => str_replace('gid://shopify/Product/', '', $product['node']['id']),
                                'shopify_variant_id' => str_replace('gid://shopify/ProductVariant/', '', $productVariant['node']['id']),

                            ],
                        [
                                'style_number'=>$products->style_number??null,
                                'shopify_sku' => $productVariant['node']['sku'] ?? null,
                                'shopify_barcode' => $productVariant['node']['barcode'] ?? null,
                                'color' => $color ?: 'MALTESE',
                                'size' => $size ?? null,
                                'price' => $productVariant['node']['price'] ?? null
                            ]

                        );
                        $variantsCollection[] = $productVariants;
                    }
                }
            //     $response = $this->getProductByStyleNumber($product['node']['handle']);
            //     if (empty($response['response'])) {
            //         $this->createAmProducts($products, $variantsCollection);
            //     } else {
            //         if (!empty($response['response'])) {
            //             $item = $response['response'][0];
            //             //  info('updated');

            //             $product = Product::where('style_number', $item['style_number'])->first();

            //             if (!empty($product)) {
            //                 $updated = Product::where('style_number', $item['style_number'])
            //                     ->update([
            //                         'product_id' => $item['product_id'] ?? null,
            //                         'size_range_id' => $item['size_range_id'] ?? null,
            //                         'is_product' => $item['is_product'] ?? null,
            //                         'is_component' => $item['is_component'] ?? null,
            //                         'price' => $item['price'] ?? null,
            //                         'description' => $item['description'] ?? null,
            //                     ]);
            //                 // dd($updated);

            //                 $this->getApparelVariants($item);
            //             }
            //         }
            //         Log::info('exist');
            //     }
            }
            Log::info("response",$shopifyresponse);
            $pageInfo =  $shopifyresponse['data']['products']['pageInfo'];
            $nextPageCursor = $pageInfo['endCursor'];
            if ($pageInfo['hasNextPage'] == true) {
                Log::info("has next page");
                GetShopifyProduct::dispatch((int) $limit, $reverse, $variantCount, $nextPageCursor, $settings);
            } else {
                Log::info("shopify product fetch completed");
            }
        }
    }

    public function fetchShopifyOrders($limit, $reverse, $nextPageCursor, $settings)
    {
        try {
            $queryString = 'query orders($limit: Int, $reverse:Boolean, $nextPageCursor: String) {
            orders(first: $limit, reverse:$reverse, after:$nextPageCursor) {
                edges {
                    node {
                        id
                        email
                        name
                        displayFulfillmentStatus
                        createdAt
                        updatedAt
                        closedAt
                        note
                        totalPriceSet {
                            shopMoney {
                                amount
                            }
                        }
                        fulfillmentOrders(first:5) {
                            edges {
                                cursor
                                node {
                                    id
                                    status
                                    lineItems (first:10) {
                                        edges {
                                            node {
                                                id
                                                lineItem {
                                                    id
                                                    sku
                                                    title
                                                    variant {
                                                        id
                                                        title
                                                    }
                                                    originalTotalSet {
                                                        shopMoney {
                                                            amount
                                                            currencyCode
                                                        }
                                                    }
                                                }
                                                totalQuantity
                                                remainingQuantity
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        totalShippingPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalTaxSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalDiscountsSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        subtotalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        totalPriceSet {
                            shopMoney {
                                amount
                                currencyCode
                            }
                        }
                        customer {
                            id
                            firstName
                            lastName
                        }
                        billingAddress {
                            id
                            name
                            phone
                            address1
                            address2
                            company
                            zip
                            city
                            country
                        }
                        shippingAddress {
                            id
                            name
                            phone
                            address1
                            address2
                            company
                            zip
                            city
                            country
                            provinceCode
                        }
                        shippingLine {
                            id
                            carrierIdentifier
                            code
                            source
                            title
                        }
                    }
                }
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
            }
        }';

            $variables = ['limit' => $limit, 'reverse' => $reverse, 'nextPageCursor' => $nextPageCursor];
            $response = $this->getHttp($queryString, $variables);
            info("response".json_encode($response));

            if (empty($response)) {
                Log::info(json_encode($response['body']['container']['data']['orders']['pageInfo']));
            }
            Log::info('Shopify response' . json_encode($response));
            if (!empty($response['data']['orders']['edges']) && !isset($response['status'])) {
                foreach ($response['data']['orders']['edges'] as $edge) {
                    $orderNode = $edge['node'];

                    $shopifyOrder = AmOrder::updateOrCreate(
                        ['shopify_order_id' => str_replace('gid://shopify/Order/', '', $orderNode['id'])],
                        [
                            'shopify_order_name' => $orderNode['name'] ?? null,
                            'shopify_email' => $orderNode['email'] ?? null,
                            'shopify_shipping_total' => $orderNode['totalPriceSet']['shopMoney']['amount'] ?? 0,
                            'shopify_fulfillment_status' => $orderNode['displayFulfillmentStatus'] ?? null,
                            'shopify_notes' => $orderNode['note'] ?? null,
                            'shopify_customer_id' => $orderNode['customer']['id'] ?? null,
                            'shopify_customer_firstname' => $orderNode['customer']['firstName'] ?? null,
                            'shopify_customer_lastname' => $orderNode['customer']['lastName'] ?? null,
                            'shopify_shipping_address1' => $orderNode['shippingAddress']['address1'] ?? null,
                            'shopify_shipping_address2' => $orderNode['shippingAddress']['address2'] ?? null,
                            'shopify_shipping_city' => $orderNode['shippingAddress']['city'] ?? null,
                            'shopify_shipping_zip' => $orderNode['shippingAddress']['zip'] ?? null,
                            'shopify_shipping_country' => $orderNode['shippingAddress']['country'] ?? null,
                            'shopify_shipping_provincecode' => $orderNode['shippingAddress']['provinceCode'] ?? null,
                            'shopify_shipping_phone' => $orderNode['shippingAddress']['phone'] ?? null,
                            'shopify_created_at' => isset($orderNode['createdAt']) ? date('Y-m-d', strtotime($orderNode['createdAt'])) : null,
                        ]
                    );

                    if (!empty($orderNode['fulfillmentOrders']['edges'])) {
                        foreach ($orderNode['fulfillmentOrders']['edges'] as $fulfillmentEdge) {
                            $fulfillmentNode = $fulfillmentEdge['node'];

                            if (!empty($fulfillmentNode['lineItems']['edges'])) {
                                foreach ($fulfillmentNode['lineItems']['edges'] as $lineItemEdge) {
                                    $lineItemNode = $lineItemEdge['node']['lineItem'];

                                    $shopifyOrderitems = AmOrderItem::updateOrCreate(
                                    [
                                        'shopify_order_id' => str_replace('gid://shopify/Order/', '', $orderNode['id']),
                                        'shopify_line_item_id' => str_replace('gid://shopify/LineItem/', '', $lineItemNode['id']),
                                    ],
                                    [
                                        'shopify_order_gid' => str_replace('gid://shopify/Order/', '', $orderNode['id']),
                                        'shopify_order_name' => $shopifyOrder->shopify_order_name,
                                        'shopify_sku' => $lineItemNode['sku'] ?? null,
                                        'shopify_variant_title' => $lineItemNode['variant']['title'] ?? null,
                                        'shopify_quantity' => $lineItemEdge['node']['totalQuantity'] ?? 0,
                                        'shopify_current_quantity' => $lineItemEdge['remainingQuantity'] ?? 0,
                                        'shopify_variant_id' => $lineItemNode['variant']['id'] ?? null,
                                        'shopify_fulfillment_order_id' => $fulfillmentNode['id'] ?? null,
                                        'shopify_amount' => $lineItemNode['originalTotalSet']['shopMoney']['amount']
                                    ]
                                );

                                }
                            }
                        }
                    }
                }
            }

            if (!empty($response['data']['orders']['pageInfo'])) {
                $pageInfo = $response['data']['orders']['pageInfo'];
                $nextPageCursor = $pageInfo['endCursor'];

                if ($pageInfo['hasNextPage'] === true) {
                    Log::info("has next page");
                    GetShopifyOrders::dispatch($limit, $reverse, $nextPageCursor, $settings);
                } else {
                    Log::info("completed");
                }
            } else {
                Log::warning("Shopify API returned no data: " . json_encode($response));
            }
        } catch (Exception $e) {
            // dd($e);
            Log::info($e->getMessage());
        }
        
    }
    public function fulfillShopifyOrder($shopifyOrder, $pickticket, $shipment, $site = 1)
    {
        $boxItems = [];
        $error = 0;
        $pickticketWarehouse = $pickticket->warehouse_id;

        if ($shopifyOrder->fulfillment_status == 'fulfilled') {
            $error = 1;
            return ['message' => 'Order already fulfilled', 'error' => $error];
        }

        foreach ($shipment->boxes as $box) {
            foreach ($box->box_items as $boxItem) {
                $boxItems[$boxItem->sku_id] = ($boxItems[$boxItem->sku_id] ?? 0) + (int)$boxItem->qty;
            }
        }

        $fulfillmentResponse = [];
        $trackingNumber = $shipment->tracking_number ?? '1234567890123';

        $shopifyFulfilResponse = $this->getHttp(
            "orders/{$shopifyOrder->id}/fulfillment_orders.json",
            []
        );

        if (empty($shopifyFulfilResponse['fulfillment_orders'])) {
            return ['message' => 'No fulfillment orders found for this Shopify order', 'error' => 1];
        }

        foreach ($shopifyFulfilResponse['fulfillment_orders'] as $fulfillmentOrder) {
            $location = Setting::where('type','shopify')->where('code','shopify_location')->value('value');

            if (!$location) {
                continue;
            }

            $warehouseId = $location->am_warehouse_id;
            if ($pickticketWarehouse != $warehouseId) {
                continue;
            }

            $lineItemsByFulfillmentOrder = [];

            foreach ($fulfillmentOrder['line_items'] as $fulfillLineItem) {
                if ($fulfillLineItem['fulfillable_quantity'] == 0) {
                    continue;
                }

                $productVariant = ProductVariant::where('shopify_inventory_item_id', $fulfillLineItem['inventory_item_id'])->first();
                if ($productVariant) {
                    $quantity = $boxItems[$productVariant->sku_id] ?? 0;
                    if ($quantity > 0) {
                        $lineItemsByFulfillmentOrder[] = [
                            "id"       => "gid://shopify/FulfillmentOrderLineItem/" . $fulfillLineItem['id'],
                            "quantity" => $quantity,
                        ];
                    }
                }
            }

            if (empty($lineItemsByFulfillmentOrder)) {
                continue;
            }

            $variables = [
                "fulfillment" => [
                    "notifyCustomer" => true,
                    "lineItemsByFulfillmentOrder" => [
                        "fulfillmentOrderId" => "gid://shopify/FulfillmentOrder/" . $fulfillmentOrder['id'],
                        "fulfillmentOrderLineItems" => $lineItemsByFulfillmentOrder,
                    ],
                    "trackingInfo" => [
                        "number"  => $trackingNumber,
                    ],
                ],
                "message" => "Fulfilled By MagicForce",
            ];

            $request = [
                'query' => 'mutation fulfillmentCreateV2($fulfillment: FulfillmentV2Input!) {
                    fulfillmentCreateV2(fulfillment: $fulfillment) {
                        fulfillment {
                            id
                            status
                        }
                        userErrors {
                            field
                            message
                        }
                    }
                }',
                'variables' => $variables,
            ];

            $response = $this->shopifyGraphQL($request, 'create fulfillment', $shopifyOrder->id, [], $site);

            $errorMessages = [];
            if (!empty($response->data->fulfillmentCreateV2->userErrors)) {
                foreach ($response->data->fulfillmentCreateV2->userErrors as $err) {
                    $errorMessages[] = "Field: " . implode(', ', $err->field) . " - " . $err->message;
                }
                $fulfillmentResponse[$fulfillmentOrder['id']] = implode("; ", $errorMessages);
                $error = 1;
            } elseif (!empty($response->errors)) {
                $fulfillmentResponse[$fulfillmentOrder['id']] = $response->errors[0]->message;
                $error = 1;
            } else {
                $fulfillmentResponse[$fulfillmentOrder['id']] = "Order fulfilled successfully";
            }
        }

        $responseString = '';
        foreach ($fulfillmentResponse as $id => $message) {
            $responseString .= "Fulfillment Order ID {$id}: {$message}\n";
        }

        return ['message' => trim($responseString), 'error' => $error];
    }


}
