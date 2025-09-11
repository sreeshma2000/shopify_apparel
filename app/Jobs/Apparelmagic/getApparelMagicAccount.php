<?php

namespace App\Jobs\Apparelmagic;

use App\Traits\Apparelmagic\ApparelmagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class getApparelMagicAccount implements ShouldQueue
{
    use Queueable,ApparelmagicHelper;
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
        $this->getAmAccount($this->settings);
    }
}
