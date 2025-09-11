<?php

namespace App\Jobs\Shopify;

use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GetShopifyOrders implements ShouldQueue
{
    use Queueable,ShopifyHelper;
    protected $limit,$reverse,$nextPageCursor,$settings; 

    /**
     * Create a new job instance.
     */
    public function __construct($limit, $reverse, $nextPageCursor, $settings)
    {
        $this->limit = $limit;
        $this->reverse = $reverse;
        $this->nextPageCursor = $nextPageCursor;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->fetchShopifyOrders($this->limit, $this->reverse, $this->nextPageCursor, $this->settings);
    }
}
