<?php

namespace App\Jobs\Apparelmagic;

use App\Traits\Apparelmagic\ApparelmagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateApparelmagicOrders implements ShouldQueue
{
    use Queueable,ApparelmagicHelper;
    protected $shopifyOrder;

    /**
     * Create a new job instance.
     */
    public function __construct($shopifyOrder)
    {
        $this->shopifyOrder = $shopifyOrder;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createAmOrders($this->shopifyOrder);
    }
}
