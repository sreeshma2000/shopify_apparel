<?php

namespace App\Jobs\Apparelmagic;

use App\Traits\Apparelmagic\ApparelmagicHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateAmPurchaseOrder implements ShouldQueue
{
    use Queueable,ApparelmagicHelper;
    protected $amOrder;
    /**
     * Create a new job instance.
     */
    public function __construct($amOrder)
    {
      $this->amOrder = $amOrder;  
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createPurchaseOrder($this->amOrder);

    }
}
