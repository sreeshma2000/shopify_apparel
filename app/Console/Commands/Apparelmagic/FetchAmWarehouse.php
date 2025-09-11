<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\getApparelMagicWarehouse;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchAmWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:apparelmagic-warehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch apparelmagic warehouses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings=Setting::where('type','apparelmagic')->where('status',1)->get();
        getApparelMagicWarehouse::dispatch($settings);

    }
}
