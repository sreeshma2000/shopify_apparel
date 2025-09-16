<?php

namespace App\Console\Commands\Shopify;

use App\Jobs\Shopify\FetchShopifyOrderByName;
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
    protected $signature = 'fetch:shopify-orders {--orderId=}';

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
        if ($this->option('orderId')) {
            $orderIds=explode(',',$this->option('orderId'));
            foreach($orderIds as $orderId){
                 FetchShopifyOrderByName::dispatch($orderId);
            }
            return $this->info(' order id ' . $this->option('orderId') . ' processed');
        }
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
