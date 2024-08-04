<?php

namespace App\Imports;

use App\Traits\RequestTrait;
use App\Models\InventoryDetail;
use App\Models\InventoryTransfer;
use App\Models\Product;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Auth;

//class ProductsImport implements ToModel, WithHeadingRow, WithValidation
class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    use RequestTrait;
    private $rows = 0;
    public $transfer_id = null;
    public $message = null;
    protected $request;

    public function __construct($variable)
    {
        $this->request = $variable;
    }

    public function collection(Collection $rows)
    {
        $canImport = true;
        $user = Auth::user();

        if ($canImport) {

            $transfer = new InventoryTransfer();
            $transfer->user_id = Auth::user()->id;
            $transfer->qty = $rows->sum('available');
            $transfer->items = $rows->count();
            $transfer->ref = "tr-" . rand(10000000, 99999999);
            $transfer->created_at = now();
            $transfer->updated_at = now();
            $transfer->save();
            $this->transfer_id = $transfer->id;

            $errors = [];


            foreach ($rows as $row) {
                
                if(isset($row['title']) && isset($row['available']) && isset($row['sku']))
                {

                    $sku = $row['sku'];
                    $inventory_item_id = $sku;
                    $qty_before = 0;
                    $qty_after = 0;

                    $product = Product::where('title', $row['title'])->first();
                    if($product)
                    {
                        $variants = is_array($product->variants) ? collect($product->variants) : json_decode(str_replace('\\' , '/', $product->variants), true);
                        if (is_array($variants))
                            $variants = collect($variants);
                        $inventory = $variants->where('sku', (string)$sku)->first();
                        if ($inventory)
                            $inventory_item_id = $inventory['inventory_item_id'];
                    }
                    else{

                        $this->message = "Product Not Found";
                        return [$this->transfer_id, $this->message];
                    }
                    //api
                    $user = Auth::user();
                    $store = $user->getShopifyStore;
                    $endpoint = getShopifyURLForStore('inventory_levels/adjust.json', $store);
                    $headers = getShopifyHeadersForStore($store);
                    
                    $payload = ["location_id" => $this->request->location_id ?? 95353602340, "inventory_item_id" => (int)$inventory_item_id, "available_adjustment" =>(int)$row['available']];
                    $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
                    
                    if($response['statusCode'] == 200 && $response['body']['inventory_level'] != null) {
                        $qty_before = $response['body']['inventory_level']['available'] - (int)$row['available'];
                        $qty_after = $response['body']['inventory_level']['available'];

                        $option1 = isset($row['option1_value']) ? $row['option1_value'] : "";
                        $option2 = isset($row['option2_value']) ? $row['option2_value'] : "";

                        $detail = new InventoryDetail();
                        $detail->transfer_id = $transfer->id;
                        $detail->line_item_id = $sku;
                        $detail->qty_before = $qty_before;
                        $detail->qty_after = $qty_after;
                        $detail->variation = $option1."-". $option2;
                        $detail->product_name = $row['title'];
                        $detail->created_at = now();
                        $detail->updated_at = now();
                        $detail->save();
                    }
                    else
                    {
                        $errors[$response['statusCode']][] = [$response, $payload,$row];
                        
                    }
                }
                else{
                    $this->message = "One or More Columns are Missing";
                    return [$this->transfer_id, $this->message];
                }

            }
            
        }
        return $this->transfer_id;
    }

    public function model(array $row)
    {
        ++$this->rows;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function rules(): array
    {
        return [
            // Can also use callback validation rules
            'unit_price' => function ($attribute, $value, $onFailure) {
                if (!is_numeric($value)) {
                    $onFailure('Unit price is not numeric');
                }
            }
        ];
    }

    public function downloadThumbnail($url)
    {
        try {
            $upload = new Upload;
            $upload->external_link = $url;
            $upload->type = 'image';
            $upload->save();

            return $upload->id;
        } catch (\Exception $e) {
        }
        return null;
    }

    public function downloadGalleryImages($urls)
    {
        $data = array();
        foreach (explode(',', str_replace(' ', '', $urls)) as $url) {
            $data[] = $this->downloadThumbnail($url);
        }
        return implode(',', $data);
    }
}
