<?php

namespace App\Console\Commands\Shopify;

use App\Jobs\Shopify\getShopifyInventory;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchShopifyInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:shopify-ineventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Shopify Inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = Setting::where('type','shopify')->where('status',1)->get();
        $limit=50;
        $nextPageCursor = null;
        getShopifyInventory::dispatch($settings,$limit,$nextPageCursor);
    }
}
