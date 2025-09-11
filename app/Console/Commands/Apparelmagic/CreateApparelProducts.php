<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\CreateApparelMagicProducts;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Traits\ApparelMagic\ApparelMagicHelper;
use Illuminate\Console\Command;
use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Support\Facades\Log;

class CreateApparelProducts extends Command
{
    use ShopifyHelper,ApparelMagicHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:am-products {--productId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create am products from shopify saves into database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->option('productId');

        $products = $productId ? Product::where('shopify_product_id', $productId)->get() : Product::whereNotNull('shopify_product_id')->get();

        foreach ($products as $product) {
            if (!$product) {
                $this->error("Product not found for Shopify ID: {$productId}");
                continue;
            }

            $productVariants = ProductVariant::where('shopify_product_id', $product->shopify_product_id)->select('color', 'size')->get()->toArray();
// dd($productVariants);
            $styleNumber = $product->shopify_handle;
    
            $response = $this->getProductByStyleNumber($styleNumber);

            if (empty($response['response'])) {
                $this->info("Creating ApparelMagic product: {$product->shopify_product_id}");
                CreateApparelMagicProducts::dispatch($product, $productVariants);
            } else {
                $this->info("Updating ApparelMagic product: {$product->shopify_product_id}");
                $item = $response['response'][0];

                Product::where('style_number', $item['style_number'])
                    ->update([
                        'product_id' => $item['product_id'] ?? null,
                        'size_range_id' => $item['size_range_id'] ?? null,
                        'is_product' => $item['is_product'] ?? null,
                        'is_component' => $item['is_component'] ?? null,
                        'price' => $item['price'] ?? null,
                        'description' => $item['description'] ?? null,
                    ]);

                $this->getApparelVariants($item);
            }
        }

        $createdCount = Product::whereNotNull('product_id')->count();
        $this->info("Total ApparelMagic products created: {$createdCount}");
    }
}