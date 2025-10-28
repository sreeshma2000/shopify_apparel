<?php

namespace App\Console\Commands\Apparelmagic;

use App\Jobs\Apparelmagic\CreateAmPurchaseOrder;
use App\Models\AmOrder;
use Illuminate\Console\Command;

class CreateApparelPurchaseOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:apparel-purchase-order {--orderId=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->option('orderId');
        if ($orderId) {
            $amOrder = AmOrder::with('order_items')->where('am_order_id', $orderId)->first();
            // dd($amOrder);
            if ($amOrder) {
                $response = CreateAmPurchaseOrder::dispatch($amOrder);
            }
        }
    }
}
