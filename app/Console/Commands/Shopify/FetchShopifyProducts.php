<?php

namespace App\Console\Commands\Shopify;

use App\Jobs\Shopify\GetShopifyProduct;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchShopifyProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:shopify-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch shopify inventory and map into apparel magic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = Setting::where('type','shopify')->where('status',1)->get();
        $limit=200;
        $reverse=false;
        $nextPageCursor = null;
        $variantCount=10;
        GetShopifyProduct::dispatch((int) $limit,$reverse,$variantCount,$nextPageCursor,$settings);
    }
}
