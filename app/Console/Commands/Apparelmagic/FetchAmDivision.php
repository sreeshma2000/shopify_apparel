<?php

namespace App\Console\Commands\ApparelMagic;

use App\Jobs\ApparelMagic\getApparelMagicDivision;
use App\Models\Setting;
use Illuminate\Console\Command;

class FetchAmDivision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:apparelmagic-division';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Apparelmagic Division';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $settings=Setting::where('type','apparelmagic')->where('status',1)->get();
         getApparelMagicDivision::dispatch($settings);
    }
}
