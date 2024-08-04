<?php

namespace App\Jobs\Shopify\Sync;

use App\Models\WarehouseProduct as ModelsProduct;
use App\Models\ProductVariant as ModelsProductVariant;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WarehouseProduct implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use FunctionTrait, RequestTrait;
    public $user, $store,$warehouse;
    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct($user, $store,$warehouse) {
        $this->user = $user;
        $this->store = $store;
        $this->warehouse = $warehouse;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle() {
        try {
            $since_id = 0;
            $headers = getShopifyHeadersForStore($this->store);
            $products = [];
                $endpoint = getShopifyURLForStore('/inventory_levels.json?location_ids='.$this->user->warehouse_id.'&limit=250', $this->store);
                //$response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
                //$products = $response['statusCode'] == 200 ? $response['body']['inventory_levels'] ?? null : null;
                $hasMorePages = true;
                $products = [];
                while ($hasMorePages) {
                    $response = Http::withHeaders($headers)->get($endpoint);
                    if ($response->successful()) {
                        // Access the inventory levels within the response body
                        $body = $response->json();
                        $products = array_merge($products, $body['inventory_levels']);

                        // Handle pagination
                        $linkHeader = $response->header('Link');
                        if ($linkHeader) {
                            $nextPageLink = $this->parseNextPageLink($linkHeader);
                            if ($nextPageLink) {
                                $endpoint = $nextPageLink;
                            } else {
                                $hasMorePages = false;
                            }
                        } else {
                            $hasMorePages = false;
                        }
                        // Sleep to handle rate limiting (2 calls per second)
                        //sleep(500000); // 500 milliseconds
                    } else {
                        // if ($response->status() == 429) { // Rate limit error
                        //     sleep(1); // Sleep for 1 second if rate limit is hit
                        // }
                        dd($response->body());
                    }

                }

                foreach($products as $product) {
                    $prod = ModelsProductVariant::where('inventory_item_id', $product['inventory_item_id'])->first();
                    if($prod)
                    {
                        $this->updateOrCreateThisVariantInDB($product,$prod,$product['available']);
                    }
                }
        } catch(Exception $e) {
            Log::info($e->getMessage());
        }
    }

    private function parseNextPageLink($linkHeader)
    {
        $links = explode(',', $linkHeader);
        foreach ($links as $link) {
            [$urlPart, $relPart] = explode(';', $link);
            $url = trim($urlPart, '<> ');
            $rel = trim(explode('=', $relPart)[1], '" ');

            if ($rel === 'next') {
                return $url;
            }
        }

        return null;
    }

    public function updateOrCreateThisVariantInDB($product,$variant,$qty)
    {
        try {
            $payload = [
                'store_id' => $this->store->table_id,
                'warehouse_id' => $this->user->warehouse_id,
                'id' => $variant['id'],
                'product_id' => $variant['product_id'],
                'title' => $variant['title'],
                'price' => $variant['price'],
                'sku' => $variant['sku'],
                'option1' => $variant['option1'],
                'option2' => $variant['option2'],
                'option3' => $variant['option3'],
                'created_at' => $variant['created_at'],
                'updated_at' => $variant['updated_at'],
                'barcode' => $variant['barcode'],
                'admin_graphql_api_id' => $variant['admin_graphql_api_id'],
                'inventory_item_id' => $variant['inventory_item_id'],
                'inventory_quantity' => $qty??0,
                'image_id' => $variant['image_id']
            ];
            $update_arr = [
                'store_id' => $this->store->table_id,
                'id' => $variant['id'],
                'warehouse_id' => $this->user->warehouse_id,
            ];
            \App\Models\WarehouseProduct::updateOrCreate($update_arr, $payload);
            return true;
        } catch (Exception $e) {
            dd($e);
            Log::info($e->getMessage());
        }
    }
}
