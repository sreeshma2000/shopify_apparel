<?php

namespace App\Jobs\ApparelMagic;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\ApparelMagic\ApparelMagicHelper;

class getApparelMagicWarehouse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,ApparelMagicHelper;
    protected $settings;
    /**
     * Create a new job instance.
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->getAmWarehouses($this->settings);
    }
}
