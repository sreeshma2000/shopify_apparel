<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    { 
        $settings = [     
        [
            'type'=>'apparelmagic',
            'field_type' => 'text',
            'dataset'=> null,
            'data_source' => '',
            'code' => 'apparelmagic_api_endpoint',
            'title' => 'API Endpoint',
            'placeholder' => '',
            'value' => 'https://sandbox2.app.apparelmagic.com/api/json',
            'status' => 1,
        ],
        [
            'type'=>'apparelmagic',
            'field_type' => 'password',
            'dataset'=> null,
            'data_source' => '',
            'code' => 'apparelmagic_token',
            'title' => 'Token',
            'placeholder' => '',
            'value' => '818aa1a4bf83a8f8114f59e971a55edd',
            'status' => 1,
        ],
        [
            'type'=>'apparelmagic',
            'field_type' => 'select',
            'dataset'=> null,
            'data_source' => '',
            'code' => 'apparelmagic_location',
            'title' => 'Location',
            'placeholder' => '',
            'value' =>'',
            'status' => 0,
        ],
        [
            'type'=>'apparelmagic',
            'field_type' => 'text',
            'dataset'=> null,
            'data_source' => '',
            'code' => 'apparelmagic_page_size',
            'title' => 'PageSize',
            'placeholder' => '',
            'value' =>100,
            'status' => 1,
        ],
        [
            'type'=>'apparelmagic',
            'field_type' => 'select',
            'dataset'=> null,
            'data_source' => 'am_customers-status,1-name,am_customer_id',
            'code' => 'apparelmagic_customer',
            'title' => 'Customer',
            'placeholder' => '',
            'value' =>'',
            'status' => 1,
        ],
        [
            'type'=>'apparelmagic',
            'field_type' => 'select',
            'dataset'=> null,
            'data_source' => 'am_warehouses-status,1-name,warehouse_id',
            'code' => 'apparelmagic_location',
            'title' => 'Location',
            'placeholder' => '',
            'value' =>'',
            'status' => 1,
        ],
        [
            'type'=>'apparelmagic',
            'field_type' => 'select',
            'dataset'=> null,
            'data_source' => 'am_divisions-status,1-name,division_id',
            'code' => 'apparelmagic_division',
            'title' => 'Division',
            'placeholder' => '',
            'value' =>'',
            'status' => 1,
        ],
        [
            'type'=>'apparelmagic',
            'field_type' => 'select',
            'dataset'=> null,
            'data_source' => 'am_accounts-status,1-name,account_id',
            'code' => 'apparelmagic_account',
            'title' => 'Account',
            'placeholder' => '',
            'value' =>'',
            'status' => 1,
        ],
        [
            'type' => 'shopify',
            'field_type' => 'text',
            'dataset' => NULL,
            'data_source' => '',
            'code' => 'shopify_store',
            'title' => 'Store Name',
            'placeholder' => 'store-name.myshopify.com',
            'value' => 'alvarez-marsal.myshopify.com',
            'status' => 1,
        ],
        [
            'type' => 'shopify',
            'field_type' => 'password',
            'dataset' => NULL,
            'data_source' => '',
            'code' => 'shopify_token',
            'title' => 'Token',
            'placeholder' => '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',
            'value' => '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',
            'status' => 1,
        ],
        [
            'type' => 'shopify',
            'field_type' => 'select',
            'dataset' => '[{"id":1,"name":"gid://shopify/Location/61271867569"}]',
            'data_source' => '',
            'code' => 'shopify_location',
            'title' => 'Location',
            'placeholder' => 'Test',
            'value' => '',
            'status' => 1,
        ],
        [
            'type' => 'shopify',
            'field_type' => 'text',
            'dataset' => NULL,
            'data_source' => '',
            'code' => 'shopify_page_size',
            'title' => 'Page Size',
            'placeholder' => '5',
            'value' => '5',
            'status' => 1,
        ],
        [
            'type' => 'shopify',
            'field_type' => 'text',
            'dataset' => NULL,
            'data_source' =>'',
            'code' => 'shopify_buffer_quantity',
            'title' => 'Buffer Quantity',
            'placeholder' => '10',
            'value' => '10',
            'status' => 1,
        ]
       
        ];
        foreach($settings as $setting){
            Setting::updateOrCreate([
                'code' => $setting['code'],
                 ],$setting);
        }
    }
}
