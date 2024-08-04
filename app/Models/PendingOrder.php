<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingOrder extends Model {
    
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'billing_address' => 'array',
        'fulfillments' => 'array',
        'shipping_lines' => 'array',
        'discount_applications' => 'array',
        'total_shipping_price_set' => 'array',
        'total_price_set' => 'array',
        'total_tax_set' => 'array',
        'refunds' => 'array',
        'payment_gateway_names' => 'array',
        'total_discounts_set' => 'array',
        'subtotal_price_set' => 'array',
        'tax_lines' => 'array',
        'discount_codes' => 'array',
        'shipping_lines' => 'array'
    ];

    public function getOrderFulfillmentsInfo() {
        return $this->hasMany(OrderFulfillment::class, 'order_id', 'table_id');
    }

    public function getFulfillmentOrderDataInfo() {
        return $this->hasMany(FulfillmentOrderData::class, 'order_table_id', 'table_id');
    }

    public function getLineItemsAttribute()
    {
        $line_items = is_array($this->attributes['line_items']) ? $this->attributes['line_items'] : json_decode($this->attributes['line_items'],true);
            if (!$line_items)
                    $line_items = json_decode(str_replace('\\','/',$this->attributes['line_items']), true);
        return $line_items;
    }
    public function getShippingAddressAttribute()
    {
         //sanitize
        $string = str_replace('\\', '/', $this->attributes['shipping_address']);
        // $string = str_replace('"/', '\\"', $string);
        // $string = str_replace('/"', '"\\', $string);
        
        $shipping_address = is_array($this->attributes['shipping_address']) ? $this->attributes['shipping_address'] : json_decode($string,true);
        if($shipping_address == null)
        {
            $string = filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS);
            $shipping_address = is_array($this->attributes['shipping_address']) ? $this->attributes['shipping_address'] : json_decode($string,true);

        }
        if ($shipping_address && !is_array($shipping_address))
            $shipping_address = $shipping_address->toArray();
        // if ($this->attributes['order_number'] == 6020) {
        //     dd($this->attributes['shipping_address'],is_array($this->attributes['shipping_address']),str_replace('/"','\\',str_replace('\\','/',$this->attributes['shipping_address'])),str_replace('\\','/',$this->attributes['shipping_address']),json_decode(str_replace('\\','/',$this->attributes['shipping_address']),true),json_decode($this->attributes['shipping_address']),$shipping_address);
       
        //                                                 }
        return $shipping_address;
    }

    public function getShippingLinesAttribute()
    {
         //sanitize
        $string = str_replace('\\', '/', $this->attributes['shipping_lines']);
        // $string = str_replace('"/', '\\"', $string);
        // $string = str_replace('/"', '"\\', $string);
        
        $shipping_lines = is_array($this->attributes['shipping_lines']) ? $this->attributes['shipping_lines'] : json_decode($string,true);
        if($shipping_lines == null)
        {
            $string = filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS);
            $shipping_lines = is_array($this->attributes['shipping_lines']) ? $this->attributes['shipping_lines'] : json_decode($string,true);

        }
        if ($shipping_lines && !is_array($shipping_lines))
            $shipping_lines = $shipping_lines->toArray();
        // if ($this->attributes['order_number'] == 6020) {
        //     dd($this->attributes['shipping_lines'],is_array($this->attributes['shipping_lines']),str_replace('/"','\\',str_replace('\\','/',$this->attributes['shipping_lines'])),str_replace('\\','/',$this->attributes['shipping_lines']),json_decode(str_replace('\\','/',$this->attributes['shipping_lines']),true),json_decode($this->attributes['shipping_lines']),$shipping_lines);
       
        //                                                 }
        return $shipping_lines;
    }


    public function prepare()
    {
        $this->hasOne(Prepare::class);
    }

    public function refund()
    {
        $this->hasMany(Refund::class,'order_name');
    }

    public function getProductIdsForLineItems() {
        $line_items = $this->getLineItemsAttribute();
        $return_val = [];
        if(is_array($line_items) && count($line_items) > 0) 
            foreach($line_items as $item)
                $return_val[] = $item['product_id'];
        return $return_val;
    }

    public function getPaymentStatus() {
        switch($this->financial_status) {
            case 'paid': return 'Paid';
            case 'pending': return 'COD';
            case 'partially_refunded': return 'Partially Refunded';
            default: return $this->financial_status;
        }
    }

    public function getFulfillmentStatus() {
        return strlen($this->fulfillment_status) > 0 ? ucwords($this->fulfillment_status) : 'Unfulfilled';
    }

    public function getDiscountBreakDown() {
        $returnArr = [];
        $discounts = is_array($this->discount_applications) ? $this->discount_applications : json_decode($this->discount_applications, true);
        if($discounts && $discounts !== null && count($discounts) > 0) {
            foreach($discounts as $discount) {
                if(isset($discount['title']) && isset($discount['value_type']))
                    $returnArr[$discount['title'] ?? $discount['code']] = $discount['value_type'] == 'percentage' ? $this->total_line_items_price * $discount['value'] / 100 : $discount['value'];
            }
        }
        return $returnArr;
    }

}
