<?php

namespace App\Jobs\ApparelMagic;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Traits\ApparelMagic\ApparelMagicHelper;

class CreateApparelMagicProducts implements ShouldQueue
{
    use ApparelMagicHelper;
    use Queueable;
    protected $product;
    protected $productVariants;

    /**
     * Create a new job instance.
     */
    public function __construct($product,$productVariants)
    {
        $this->product=$product;
        $this->productVariants=$productVariants;

    }

    /**c
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createApparelmagicProducts(  $this->product,$this->productVariants);

    }
}
