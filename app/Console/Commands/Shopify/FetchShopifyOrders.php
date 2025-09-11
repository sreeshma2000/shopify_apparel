<?php

namespace App\Console\Commands\Shopify;

use App\Jobs\Shopify\GetShopifyOrders;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchShopifyOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:shopify-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch orders from shopify and create an order in apparelmagic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Get orders  ...........');
        $settings = Setting::where('type', 'shopify')->where('status', 1)->get();
        if(empty($settings)){
            return $this->info('No Settings Found.');
        }
        $limit = 50;
        $reverse = false;
        $nextPageCursor = null;
        GetShopifyOrders::dispatch((int)$limit,$reverse,$nextPageCursor,$settings);

    }
}
