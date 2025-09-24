<?php

namespace App\Console\Commands\Shopify;

use App\Models\ProductVariant;
use App\Models\Setting;
use App\Traits\Apparelmagic\ApparelmagicHelper;
use App\Traits\Shopify\ShopifyHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateShopifyInventory extends Command
{
    use ApparelmagicHelper,ShopifyHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:shopify-inventory {--shopifySku=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update shopify inventory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Get Inventories  ...........');
        $shopify_sku = $this->option('shopifySku');
        if ($shopify_sku) {
            $shopifyProduct = ProductVariant::where('shopify_sku',$shopify_sku)->first();
            $warehouseId = Setting::where('code','apparelmagic_location')->value('value');

            $settings = Setting::where('type','shopify')->where('status',1)->get();
            $locationSetting = $settings->where('code', 'shopify_location')->first();
            $location = null;

            if ($locationSetting) {
                $dataset = json_decode($locationSetting->dataset, true);
                foreach ($dataset as $item) {
                    if ($item['id'] == $locationSetting->value) {
                        $location = $item['name'];
                        break;
                    }
                }
            }

            if($shopifyProduct)
            {
                $skuId = $shopifyProduct->sku_id;
                $warehouseStock = $this->getAmStockByWarehouse($skuId, $warehouseId);
                $amStock = (int)$warehouseStock['qty_avail_sell'];
                $inventory = $this->getInventoryById($shopifyProduct->inventory_level_gid);
                $inventoryId = $inventory['id'];
                $shopifyStock = $inventory['quantities'][0]['quantity'] ?? 0;

                $actualStock =  $amStock;
                $currentStock = $shopifyStock;

                $availableDelta = $actualStock - $currentStock;
                $newStock = $availableDelta;
                // dd($availableDelta);
                $desiredStock = $currentStock + $newStock;
                // dd($desiredStock);
                if ($newStock >= $currentStock && $currentStock < 0 && $actualStock <= 0) {
                    $newStock = $currentStock * -1;
                    // dd($newStock);
                }

                if ($actualStock < 0) {
                    $newStock = $currentStock * -1;
                    // dd($newStock);
                }


                if ($desiredStock != $currentStock && $newStock != 0) {
                    $updatedInventory = $this->updateInventoryLevel($inventoryId,$location,$newStock);
                    if (!empty($updatedInventory)) {

                        $this->info($shopifyProduct->inventory_item_sku . ' - inventory updated qty ' . $newStock);
                        Log::info($shopifyProduct->inventory_item_sku  . ' - inventory updated qty ' . $newStock);
                    } else {
                        $this->info('Failed to update inventory in shopify');
                    }
                }
                else{
                     $this->info('Nothing to adjust  for this item');
                }
                $this->info('Completed updating Inventory');
            }
            
        }
        else{
            $this->info("something went wrong");
        }
    }
}
