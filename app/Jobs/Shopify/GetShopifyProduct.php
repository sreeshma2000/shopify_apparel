<?php

namespace App\Jobs\Shopify;

use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetShopifyProduct implements ShouldQueue
{
    use Queueable;
    use ShopifyHelper;


    /**
     * Create a new job instance.
     */
     protected $limit;
    protected $reverse;
    protected $nextPageCursor;
    protected $settings;
    protected $variantCount;

    public function __construct($limit,$reverse,$variantCount,$nextPageCursor,$settings)
    {
        $this->limit = $limit;
         $this->reverse = $reverse;
         $this->variantCount=$variantCount;
         $this->nextPageCursor=$nextPageCursor;
         $this->settings=$settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->fetchInventory($this->limit, $this->reverse, $this->variantCount, $this->nextPageCursor, $this->settings);

    }
}
