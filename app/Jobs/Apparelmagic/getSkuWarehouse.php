<?php

namespace App\Jobs\Apparelmagic;

use App\Traits\Apparelmagic\ApparelmagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class getSkuWarehouse implements ShouldQueue
{
    use Queueable;
    use ApparelmagicHelper;

    protected $page_size;
    protected $startAfter;
    protected $settings;
    /**
     * Create a new job instance.
     */
    public function __construct($page_size,$startAfter,$settings)
    {
        $this->page_size = $page_size;
        $this->startAfter=$startAfter;
        $this->settings=$settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->getSkuWarehouse($this->page_size,$this->startAfter,$this->settings);
    }
}
