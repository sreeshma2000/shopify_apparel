<?php

namespace App\Console\Commands\Apparelmagic;

use App\Jobs\Apparelmagic\getApparelMagicAccount;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchAmAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:apparelmagic-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch ApparelMagic Account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings=Setting::where('type','apparelmagic')->where('status',1)->get();
        getApparelMagicAccount::dispatch($settings);
    }
}
