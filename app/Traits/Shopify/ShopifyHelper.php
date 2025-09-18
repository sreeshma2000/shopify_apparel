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
use Exception;
use Illuminate\Support\Facades\Log;

trait ShopifyHelper
{
    use ApiHelper;
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
                                'shopify_inventory_item_id' => str_replace('gid://shopify/InventoryItem/', '', $productVariant['node']['inventoryItem']['id'] ?? null),
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
                                                        inventoryItem {
                                                        id
                                                        }
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

    public function getShopifyOrderById($orderId)
    {
        try {
            $settings = Setting::where('type', 'shopify')->where('status', 1)->get();
            $filter = 'id:' . $orderId;
            // dd( $filter);
            // $orderGid = 'gid://shopify/Order/' . $orderId;
            $queryString ='query order($filter: String) {
                orders(first: 1, query: $filter) {
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
                                                        inventoryItem {
                                                            id
                                                        }
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
            $variables = ['filter' => $filter];
            $response = $this->getHttp($queryString, $variables);
            Log::info("response".json_encode($response));
            if (!empty($response['data']['orders']['edges'])) {
                return $response;
            }

            $orderNode = $response['data']['orders']['edges'][0]['node'];
            $shopifyOrderId = str_replace('gid://shopify/Order/', '', $orderNode['id']);

            $amOrder = AmOrder::updateOrCreate(
                ['shopify_order_id' => $shopifyOrderId],
                [
                    'shopify_order_name' => $orderNode['name'] ?? null,
                    'shopify_email' => $orderNode['email'] ?? null,
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
                    foreach ($fulfillmentNode['lineItems']['edges'] as $lineItemEdge) {
                        $lineItem = $lineItemEdge['node']['lineItem'];
                        AmOrderItem::updateOrCreate(
                            [
                                'shopify_order_id' => $shopifyOrderId,
                                'shopify_line_item_id' => str_replace('gid://shopify/LineItem/', '', $lineItemEdge['node']['id']),
                            ],
                            [
                                'shopify_order_gid' => $shopifyOrderId,
                                'shopify_order_name' => $amOrder->shopify_order_name,
                                'shopify_sku' => $lineItem['sku'] ?? null,
                                'shopify_variant_title' => $lineItem['variant']['title'] ?? null,
                                'shopify_quantity' => $lineItemEdge['node']['totalQuantity'] ?? 0,
                                'shopify_current_quantity' => $lineItemEdge['node']['remainingQuantity'] ?? 0,
                                'shopify_variant_id' => $lineItem['variant']['id'] ?? null,
                                'shopify_fulfillment_order_id' => $fulfillmentNode['id'] ?? null,
                            ]
                        );
                    }
                }
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
   
    public function shopifyFulfilment(AmOrder $order)
    {
        if (!$order) {
            return ['message' => 'Order not found', 'error' => 1];
        }
        $shipments = $this->getApparelShipments($order->pick_ticket_id);
        Log::info("shipments in shopify helper".json_encode($shipments));
        if (empty($shipments)) {
            return ['message' => 'No shipments found for pickticket', 'error' => 1];
        }

        $pickticket = $this->getAmPickTicket($order->pick_ticket_id);
        Log::info("pickticket");
        if (empty($pickticket)) {
            return ['message' => 'Pickticket not found', 'error' => 1];
        }

        $shopifyOrderResponse = $this->getShopifyOrderById($order->shopify_order_id);
        // dd($order->shopify_order_id);
        Log::info("shopifyOrderResponse".json_encode($shopifyOrderResponse));
        if (empty($shopifyOrderResponse) && empty($shopifyOrderResponse['data']['orders']['edges'][0]['node'])) {
            return ['message' => 'Failed to get Shopify order'];
        }

        $shopifyOrder = $shopifyOrderResponse['data']['orders']['edges'][0]['node'] ?? null;
        $fulfillmentResults = [];

        // foreach ($shipments as $shipment) {
        //     Log::info("hai");

            if (isset($shopifyOrder['displayFulfillmentStatus']) && $shopifyOrder['displayFulfillmentStatus'] === 'FULFILLED') {
                Log::info("Order already fulfilled");
                $fulfillmentResults[] = 'Order already fulfilled';
            }

            // $shipmentData = [
            //     'boxes' => [
            //         [
            //             'box_items' => $pickticket['pick_ticket_items'] ?? []
            //         ]
            //     ],
            //     'tracking_number' => $shipment['tracking_number'] ?? '1234567890123',
            //     'ship_via'        => $pickticket['ship_via'] ?? null,
            // ];

            // Log::info("shipment" . json_encode($shipmentData));

            $result = $this->fulfillShopifyOrder($shopifyOrder, $pickticket);
            $fulfillmentResults[] = $result['message'] ?? 'Fulfillment attempted';
        // }

        return [
            'message' => implode("\n", $fulfillmentResults),
            'error'   => 0
        ];

    }

    public function fulfillShopifyOrder($shopifyOrder, $pickticket)
    {
        Log::info("fulfillment started for order:" . json_encode($shopifyOrder));
        $error = 0;

        if ($shopifyOrder['displayFulfillmentStatus'] === 'FULFILLED') {
            return ['message' => 'Order already fulfilled'];
        }

        $fulfillmentResponse = [];
        $trackingNumber = '1234567890123';

        $shopifyFulfilResponse = $shopifyOrder['fulfillmentOrders']['edges'][0]['node'];
        Log::info(json_encode($shopifyFulfilResponse ));
        Log::info('fulfil');
        if (empty($shopifyFulfilResponse)) {
            return ['message' => 'No fulfillment orders found for this Shopify order', 'error' => 1];
        }
            $lineItemsByFulfillmentOrder = [];

            foreach ($shopifyFulfilResponse['lineItems']['edges'] as $fulfillLineItem) {
                $lineItemNode = $fulfillLineItem['node'];

                $inventoryItemId = $lineItemNode['lineItem']['variant']['inventoryItem']['id'] ?? null;
                if ($inventoryItemId) {
                    $inventoryItemId = basename($inventoryItemId); 
                }
                Log::info("Inventory Item ID: $inventoryItemId");
                $productVariant = ProductVariant::where('shopify_inventory_item_id', $inventoryItemId)->first();
                Log::info("Product Variant:".json_encode($productVariant));

                if ($productVariant) {
                    $quantity = $lineItemNode['remainingQuantity'] ?? 0;
                    if ($quantity > 0) {
                        $lineItemsByFulfillmentOrder[] = [
                            "id"       => $lineItemNode['id'],   
                            "quantity" => $quantity,
                        ];
                    }
                }
            }

            $variables = [
                "fulfillment" => [
                    "notifyCustomer" => true,
                    "lineItemsByFulfillmentOrder" => [
                        "fulfillmentOrderId" =>  $shopifyFulfilResponse['id'],
                        "fulfillmentOrderLineItems" => $lineItemsByFulfillmentOrder,
                    ],
                    "trackingInfo" => [
                        "number" => $trackingNumber,
                    ],
                ],
                "message" => "Fulfilled By MagicForce",
            ];

            $response = $this->getHttp(
                'mutation fulfillmentCreateV2($fulfillment: FulfillmentV2Input!) {
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
                $variables
            );


            // $response = $this->getHttp($response);
        Log::info(json_encode($response));
    
        if (empty($response['errors']) && empty($response['data']['fulfillmentCreateV2']['userErrors'])) {
            AmOrder::where('shopify_order_id', $shopifyOrder['id'])
                ->update(['shopify_fulfillment_status' => 'FULFILLED']);
        }
        $responseString = '';
        foreach ($fulfillmentResponse as $id => $message) {
            $responseString .= "Fulfillment Order ID {$id}: {$message}\n";
        }

        return ['message' => trim($responseString), 'error' => $error];
    }
}
