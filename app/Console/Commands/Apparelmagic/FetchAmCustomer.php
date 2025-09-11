<?php

namespace App\Console\Commands\Apparelmagic;

use App\Jobs\Apparelmagic\getApparelMagicCustomer;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchAmCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch-am-customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch ApparelMagic Customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings=Setting::where('type','apparelmagic')->where('status',1)->get();
        $page_size=100;

        getApparelMagicCustomer::dispatch($page_size,$settings,$startAfter= null);
    }
}
