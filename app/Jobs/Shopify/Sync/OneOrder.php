<?php

namespace App\Jobs\Shopify\Sync;

use Exception;
use App\Models\Refund;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use App\Models\Order as order2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OneOrder implements ShouldQueue {
    private $user, $store, $order_id, $indexes_to_insert;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RequestTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $store, $order_id) {
        $this->user = $user;
        $this->store = $store;
        $this->order_id = $order_id;
        $this->indexes_to_insert = config('custom.table_indexes.orders_table_indexes');
    }

    private function formatFulfillmentsForOrder($fulfillments) {
        try {
            return $fulfillments;
        } catch(Exception $e) {
            return null;
        }
    }

    private function formatBillingAndShippingAddress($shippingAddress) {
        try {
            return [
                'first_name' => $shippingAddress['firstName'],
                'address1' => $shippingAddress['address1'],
                'phone' => $shippingAddress['phone'],
                'city' => $shippingAddress['city'],
                'zip' => $shippingAddress['zip'],
                'province' => $shippingAddress['province'],
                'country' => $shippingAddress['country'],
                'last_name' => $shippingAddress['lastName'],
                'address2' => $shippingAddress['address2'],
                'name' => $shippingAddress['firstName'].' '.$shippingAddress['lastName'],
            ];
        } catch(Exception $e) {
            return null;
        }
    }

    private function formatLineItems($lineItems) {
        try {
            $arr = [];
            $edges = $lineItems['edges'];
            foreach($edges as $nodes) {
                $item = $nodes['node'];
                $arr[] = [
                    'id' => (int) str_replace('gid://shopify/LineItem/', '', $item['id']),
                    'admin_graphql_api_id' => $item['id'],
                    'fulfillable_quantity' => $item['unfulfilledQuantity'],
                    'name' => $item['title'],
                    'variant_title' => $item['variantTitle'],
                    'vendor' => $item['vendor'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'price' => $item['variant']['price'],
                    'price_set' => $item['originalTotalSet'],
                    'product_id' => (int) str_replace('gid://shopify/Product/', '', $item['product']['id']),
                    'variant_id' => (int) str_replace('gid://shopify/ProductVariant/', '', $item['variant']['id']),
                    'variant_title' => $item['variant']['title']
                ];
            }
            return $arr;
        } catch(Exception $e) {
            return null;
        }
    }

    public function handle()
    {
        try{
            $payload = [];
            do{
                $orders_payload = [];
                $sales_payload = [];
                $endpoint = getShopifyURLForStore('orders/'.$this->order_id.'.json', $this->store);
                $headers = getShopifyHeadersForStore($this->store, 'GET');
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
                //dd($response);
                if(isset($response) && isset($response['statusCode']) && $response['statusCode'] === 200 && is_array($response) && is_array($response['body']['order']) && count($response['body']['order']) > 0) {
                    $payload = $response['body']['order'];
                    $temp_payload = [];
                    foreach($payload as $key => $v)
                    {
                        if (is_array($v) && $key == "shipping_address") {
                            
                            
                            foreach($v as $k=>$val)
                            {
                                $v[$k] = is_array($val) ? $val : str_replace('"', '\'', $val);
                            }
                        }
                        $temp_payload[$key] = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
                    }
                    $table = "orders";
                    $temp_payload = $this->store->getOrdersPayload($temp_payload);
                    $temp_payload['store_id'] = (int) $this->store->table_id;
                    $province_and_country = $this->getShippingAddressProvinceAndCountry($payload);
                    $temp_payload = array_merge($province_and_country, $temp_payload);
                    $temp_payload['fulfillment_status'] = $temp_payload['fulfillment_status'] == "" ? "processing" : $temp_payload['fulfillment_status'];
                    $temp_payload['note'] =  strip_tags(htmlspecialchars($temp_payload['note'] , ENT_QUOTES, 'UTF-8', false));
                    $db_order = order2::where('id', $temp_payload['id'])->first();
                    
                    if($db_order){
                        $db_order->total_price = $temp_payload['total_price'];
                        
                        $total_shipping = 0;
                        foreach ($db_order['shipping_lines'] as $ship) {
                            $total_shipping += $ship['price'];
                        }
                        $db_order->subtotal_price = $temp_payload['total_price'] - $total_shipping;
                        $db_order->save();
                    }
                    if(json_decode($temp_payload['refunds']) != null && json_decode($temp_payload['refunds']) != [] && json_decode($temp_payload['refunds']) != "[]") {
                        foreach(json_decode($temp_payload['refunds']) as $key=>$ref)
                        {
                            foreach($ref->refund_line_items as $key=>$line) {
                                if($line && isset($line->line_item_id))
                                {
                                    $refund = Refund::where('refund_id', json_decode($temp_payload['refunds'])[0]->id)->where('line_item_id',$line->line_item_id)->first();
                                    if(!$refund)
                                        $refund = new Refund();
                                    $refund->refund_id = json_decode($temp_payload['refunds'])[0]->id;
                                    $refund->order_id = json_decode($temp_payload['refunds'])[0]->order_id;
                                    $refund->order_name = $temp_payload['name'];
                                    $refund->data = json_encode($ref);
                                    $refund->amount = $line->subtotal;
                                    $refund->line_item_id = $line->line_item_id;
                                    $refund->created_at = now();
                                    $refund->updated_at = now();
                                    $refund->save();
                                    
                                    if($db_order) {
                                        $db_order->total_price -= $refund->amount;
                                        $db_order->save();
                                        Log::info([$db_order->total_price, $refund->amount]);
                                    } else {
                                        $temp_payload['total_price'] -= $refund->amount;
                                        $temp_payload['subtotal_price'] -= $refund->amount;
                                        Log::info([$db_order, $refund, json_decode($temp_payload['refunds'])[0]->id]);
                                    }
                                }
                                
                            }
                        }
                        
                        
                    }
                    $visa_unpaid = $temp_payload['financial_status']=="pending" && $temp_payload['payment_gateway_names']!='["Cash on Delivery (COD)"]' && $temp_payload['payment_gateway_names']!='[]' && $temp_payload['payment_gateway_names']!='[""]' ;
                    if ($temp_payload['note'] == "pos" || $temp_payload['note'] == "Point of Sale")
                    {
                        $temp_payload['table_id'] = $temp_payload['id'];
                       
                        $sales_payload[] = $temp_payload;
                    }
                        
                    elseif(!$db_order && !$visa_unpaid)
                        $orders_payload[] = $temp_payload;;


                    $ordersTableString = $this->getOrdersTableString($orders_payload);
                    $salesTableString = $this->getOrdersTableString($sales_payload);
                    
                    if($ordersTableString !== null)
                        $this->insertOrders($ordersTableString,$table);
                    if($salesTableString !== null)
                        $this->insertOrders($salesTableString,"sales");
                    
                } else { $payload = null; }
            } while(false);
        } catch (Exception $e) {
            Log::critical(['code' => $e->getCode(), 'message' => $e->getMessage(), 'trace' => json_encode($e->getTrace())]);
            throw $e;
        }
    }

    private function getOrdersTableString($orders_payload) {
        $ordersTableString = [];
        if($orders_payload !== null && is_array($orders_payload) && count($orders_payload) > 0) {
            foreach($orders_payload as $payload) {
                $tempString = '(';
                foreach($this->indexes_to_insert as $index => $dataType) {
                    settype($payload[$index], $dataType);
                    $tempString .= (gettype($payload[$index]) === 'string' ? "'".str_replace("'", '', $payload[$index])."'" : $payload[$index] ?? null).',';
                }
                $tempString = rtrim($tempString, ',');
                $tempString .= ')';
                $ordersTableString[] = $tempString;
            }
        }
        return count($ordersTableString) > 0 ? implode(',', $ordersTableString) : null;
    }

    private function getShippingAddressProvinceAndCountry($shopifyOrderJsonArray) {
        try {
            return [
                'ship_country' => $shopifyOrderJsonArray['shipping_address']['country'],
                'ship_province' => $shopifyOrderJsonArray['shipping_address']['province']
            ];
        } catch(Exception $e) {
            Log::info($e->getMessage());
            return ['ship_country' => null, 'ship_province' => null];
        }
    }

    private function getUpdateString() {
        $returnString = [];
        foreach($this->indexes_to_insert as $index => $dataType)
            $returnString[] = $index.' = VALUES(`'.$index.'`)';
        return implode(', ', $returnString);
    }

    private function getIndexString() {
        $returnString = [];
        foreach($this->indexes_to_insert as $index => $dataType)
            $returnString[] = '`'.$index.'`';
        return implode(', ', $returnString);
    }

    //The function that carries out the DB query to insert orders into the table
    private function insertOrders($ordersTableString,$table="orders"){
		try {
            $updateString = $this->getUpdateString();
            $insertString = $this->getIndexString();
            $query = "INSERT INTO `".$table."` (".$insertString.") VALUES ".$ordersTableString." ON DUPLICATE KEY UPDATE ".$updateString;
            DB::insert($query);
            return true;
        } catch(\Exception $e) {
            dd($e->getMessage().' '.$e->getLine() );
            return false;
        }
	}
}
