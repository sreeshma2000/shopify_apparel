<?php

namespace App\Console\Commands\Apparelmagic;

use App\Jobs\Apparelmagic\getApparelMagicCurrency;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchAmCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch-am-currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch ApparelMagic Currency';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings=Setting::where('type','apparelmagic')->where('status',1)->get();
        getApparelMagicCurrency::dispatch($settings);
    }
}
