<?php

namespace App\Console\Commands\Apparelmagic;

use App\Jobs\Apparelmagic\getSkuWarehouse;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchSkuWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:sku-warehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Sku Warehouse from apparel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings=Setting::where('type','apparelmagic')->where('status',1)->get();
        $page_size=100;
        getSkuWarehouse::dispatch($page_size,$startAfter=null,$settings);
    }
}
