<?php

namespace App\Jobs\Shopify;

use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchShopifyOrderByName implements ShouldQueue
{
    use Queueable,ShopifyHelper;
protected $orderId;
    /**
     * Create a new job instance.
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->getShopifyOrderById($this->orderId);
    }
}
