<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;

trait ApiHelper
{
    public function apparelMagicApiRequest($url, $params)
    {
        try {
            // Log::info('Fetching Apparel page', ['url' => $baseUrl, 'params' => $params]);
            $request = Http::accept('application/json')
                ->get($url, $params)
                ->throw();
            return $request->json();
        } catch (HttpClientException $e) {
            return (['status' => 'failure', 'statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
        } catch (ConnectionException $e) {
            return (['status' => 'failure', 'statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
        } catch (RequestException $e) {
            return (['status' => 'failure', 'statusCode' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
     public function getHttp($queryString, $variables){
        $domain = Setting::where('type','shopify')->where('code','shopify_store')->where('status',1)->value('value');
        $domain='alvarez-marsal.myshopify.com';
        $url = 'https://' . $domain . '/admin/api/2025-07/graphql.json';
        try 
        {
            $response = Http::accept('application/json')->withHeaders([
                'Content-Type' => 'application/json' ,
                'X-Shopify-Access-Token' =>'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',
                ])
                ->post($url, ['query' => $queryString,'variables'=> $variables])
                ->throw();
    
            return $response->json();
        } 
        catch (\Exception $e) 
        {
            return ['status' => 'failure', 'message' => $e->getMessage()];
        }
    
    }

    public function apparelMagicApiPostRequest($url, $params, $headers = [])
    {
        try {
            $request = Http::withHeaders($headers)
                ->accept('application/json')
                ->post($url, $params)
                ->throw();

            return $request->json();
        } catch (HttpClientException $e) {
            return ['status' => 'failure', 'statusCode' => $e->getCode(), 'message' => $e->getMessage()];
        } catch (ConnectionException $e) {
            return ['status' => 'failure', 'statusCode' => $e->getCode(), 'message' => $e->getMessage()];
        } catch (RequestException $e) {
            return ['status' => 'failure', 'statusCode' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    public function apparelMagicApiPutRequest($url, $params)
    {
        try {
            $response = Http::acceptJson()
                ->put($url, $params)
                ->throw();

            return $response->json();
        } catch (HttpClientException $e) {
            return ['status' => 'failure','statusCode' => $e->getCode(),'message' => $e->getMessage()];
        } catch (ConnectionException $e) {
            return ['status' => 'failure','statusCode' => $e->getCode(),'message' => $e->getMessage()];
        } catch (RequestException $e) {
            return ['status' => 'failure','statusCode' => $e->getCode(),'message' => $e->getMessage()];
        }
    }

}
