<?php

namespace App\Jobs\Shopify;

use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class getShopifyInventory implements ShouldQueue
{
    use Queueable;
    use ShopifyHelper;
    protected $limit;
    protected $nextPageCursor;
    protected $settings;
    /**
     * Create a new job instance.
     */
    public function __construct($settings,$limit,$nextPageCursor)
    {
        $this->limit = $limit;
        $this->nextPageCursor=$nextPageCursor;
        $this->settings=$settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->fetchShopifyInventoryItems($this->settings, $this->limit,   $this->nextPageCursor);

    }
}
