<?php

namespace App\Http\Controllers;

use Excel;
use Exception;
use App\Models\Sale;
use App\Models\User;
use App\Models\Pickup;
use App\Models\Store;
use App\Models\Refund;
use App\Models\Prepare;
use App\Models\Employee;
use App\Models\Warehouse;
use App\Models\CashRegister;
use App\Models\OrderHistory;
use App\Models\PendingOrder;
use App\Models\ReturnDetail;
use App\Models\ReturnPickup;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use App\Models\ResyncedOrder;
use App\Models\ReturnedOrder;
use App\Models\CancelledOrder;
use App\Imports\ProductsImport;
use App\Models\InventoryDetail;
use App\Models\Order as order2;
use App\Jobs\Shopify\Sync\Order;
use App\Models\InventoryTransfer;
use App\Jobs\Shopify\Sync\Product;
use App\Models\PrepareProductList;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\FulfillOrder;
use App\Jobs\Shopify\Sync\Customer;
use App\Jobs\Shopify\Sync\OneOrder;
use App\Models\Product as Product2;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\Shopify\Sync\Locations;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer as Customer2;
use App\Models\CashRegisterTransaction;
use Illuminate\Support\Facades\Validator;
use App\Jobs\Shopify\Sync\WarehouseProduct;
use App\Jobs\Shopify\Sync\OrderFulfillments;
use Illuminate\Support\Facades\View;
use App\Models\WarehouseProduct as WarehouseProduct2;

class ShopifyController extends Controller {

    use RequestTrait;

    public function __construct() {
        $this->middleware('auth');
        $user = Auth::user();
        $store = $user?$user->getShopifyStore:Store::first();
        $this->store = $store;
        View::share('store',$store);
        $locations = Warehouse::all();
        View::share('locations',$locations);
    }

    public function orders(Request $request) {
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';
        $prepare_users_list = [];
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $orders = $store->getOrders()->where('fulfillment_status', 'processing');
        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('name', 'like', '%' . $sort_search . '%')
                ->orWhere('id', 'like', '%' . $sort_search . '%')
                ->orWhere('fulfillment_status', 'like', '%' . $sort_search . '%');
        }
        if($date !=null)
        {
            $orders = $orders->whereDate('created_at_date', '=',$date);
        }

        if($request->delivery_status)
        {
            $delivery_status = $request->delivery_status;
            $orders = $orders->where('fulfillment_status', $delivery_status);
        }

        $orders = $orders->orderBy('table_id', 'asc')
                        ->simplePaginate(15)->appends($request->query());


        $prepare_users = User::where('role_id', '5')->get();
        if(count($prepare_users)) {
            foreach ($prepare_users as $key => $prepare) {

                $prepare_users_list['id'][$key] = $prepare->id;
                $prepare_users_list['name'][$key] = $prepare->name;
            }
        }
        return view('orders.index', compact('orders','prepare_users_list','date','sort_search','delivery_status','payment_status'));
    }

    public function warehouse_products(Request $request)
    {
        $user = Auth::user();
        $warehouse = $user->warehouse_id;
        $products = WarehouseProduct2::where('warehouse_id', $user->warehouse_id)->orderBy("created_at", 'desc')->simplePaginate(20)->appends($request->query());
        return view('products.warehouse', compact('products'));

    }

    public function syncWarehouseProducts()
    {
        try {
            $user = Auth::user();
            $warehouse = $user->warehouse_id;
            $store = $user->getShopifyStore;
            WarehouseProduct::dispatch($user, $store, $warehouse);
            return back()->with('success', 'Product sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function limsProductSearch(Request $request)
    {
        $todayDate = date('Y-m-d');
        $product_code = explode(" ",$request['data']);
        $product_variant_id = null;
        $lims_product_data = WarehouseProduct2::where('sku', $product_code[0])->where('warehouse_id',auth()->user()->warehouse_id)->where('inventory_quantity','>',0)->first();
        if (!$lims_product_data)
            return null;
        $product[] = $lims_product_data->title;
        $product[] = $lims_product_data->sku;
        $product[] = $lims_product_data->price;
        $product[] = 0;
        $product[] = 'No Tax';
        $product[] = "N\A";

        $product[] = 'n/a'. ',';
        $product[] = 'n/a'. ',';
        $product[] = 'n/a'. ',';
        $product[] = $lims_product_data->id;
        $product[] = $product_variant_id;
        $product[] = $todayDate;

        return $product;

    }

    public function store_order(Request $request)
    {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $headers = getShopifyHeadersForStore($store, 'POST');
        $endpoint = getShopifyURLForStore('orders.json', $store);
        $line_items = [];
        $customer = Customer2::where('phone_number', $request->get_customer_sale)->first();
        $data = [];
        
        foreach($request->product_code as $key=>$item)
        {
            $product = WarehouseProduct2::where('sku',$item)->where('warehouse_id',$user->warehouse_id)->first();
            $line_items[] = [
                'variant_id'=>$product->id,
                'quantity'=>$request->qty[$key],
            ] ;
        }
        $payload['order'] = [
            'line_items' => $line_items,
            'total_tax' =>0.0,
            'currency' => "EGP",
            'source_name' => "Point of Sale",
            'note' => "Point of Sale",
            'customer' => [
                'id' => $customer->shopify_id,
            ],
            'billing_address' => [
                "first_name" => $customer->name,
                "last_name" => "",
                "name" => $customer->name,
                "address1" => "Maadi",
                "phone" => "+2".$customer->phone_number,
                "city" => "Cairo",
                "province" => "Cairo",
                "country" => "Egypt",
                'zip' => "123"
            ],
            'shipping_address' => [
                "first_name" => $customer->name,
                "last_name" => "",
                "name" => $customer->name,
                "address1" => "Maadi",
                "phone" => "+2".$customer->phone_number,
                "city" => "Cairo",
                "province" => "Cairo",
                "country" => "Egypt",
                "zip" => "123"
            ],
            'financial_status' => "paid",
            'fulfillment_status' => "unfulfilled",
            'phone' => "+2".$customer->phone_number,
        ];
        $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers,$payload);
        if ($response['statusCode'] === 201 || $response['statusCode'] === 200) {
            if(isset($response['body']['order']))
            {
                $order_id = $response['body']['order']['id'];
                OneOrder::dispatchNow($user,$store,$order_id);
                            $user_id = auth()->user()->id;
                $register =  CashRegister::where('user_id', $user_id)
                    ->where('status', 'open')
                    ->first();

                $payment = new CashRegisterTransaction([
                    'amount' => $response['body']['order']['total_price'],
                    'pay_method' => $request->paid_by_id_select,
                    'type' => 'credit',
                    'transaction_type' => 'sell',
                    'sale_id' => $order_id
                ]);
                if (!empty($payment)) {
                    $register->cash_register_transactions()->save($payment);
                }
                $order = Sale::find($order_id);
                if ($order) {
                    $order->location_id = auth()->user()->warehouse_id;
                    $order->cashier_id = auth()->user()->id;
                    $order->fulfillment_status = "shipped";
                    $order->employee_id = $request->sale_by_employee_select;
                    $order->save();

                    //prepare
                    $add_History_sale = new OrderHistory();
                    $add_History_sale->order_id = $order->id;
                    $add_History_sale->user_id = Auth::user()->id;
                    $add_History_sale->action = "POS";
                    $add_History_sale->created_at = now();
                    $add_History_sale->updated_at = now();
                    $add_History_sale->note = " Order Has Been Created From POS By : <strong>" . auth()->user()->name . "</strong>";

                    $add_History_sale->save();

                    $add_to_prepare = new Prepare();
                    $add_to_prepare->order_id = $order->id;
                    $add_to_prepare->store_id = $order->store_id;
                    $add_to_prepare->table_id = $order->table_id;
                    $add_to_prepare->assign_by = Auth::user()->id;
                    $add_to_prepare->assign_to = Auth::user()->id;
                    $add_to_prepare->status = "3";
                    $add_to_prepare->type = "pos";
                    $add_to_prepare->delivery_status = "fulfilled";
                    $add_to_prepare->sale_created_at = $order->created_at_date;
                    $add_to_prepare->created_at = now();
                    $add_to_prepare->updated_at = now();
                    $add_to_prepare->save();
                    $prepare_product = PrepareProductList::where('order_id', $order->id)->delete();
                    $product_images = $store->getProductImagesForOrder($order);

                    if($order->getFulfillmentOrderDataInfo()->doesntExist()){
                        OrderFulfillments::dispatch($user, $store, $order);
                    }

                    foreach ($order->line_items as $item) {
                        $product_img = "";
                        $prepare_product = new PrepareProductList();
                        if (isset($item['product_id']) && $item['product_id'] != null) {
                            if (isset($product_images[$item['product_id']])) {
                                $product_imgs = is_array($product_images[$item['product_id']]) ? $product_images[$item['product_id']] : json_decode(str_replace('\\', '/', $product_images[$item['product_id']]), true);
                                if ($product_imgs && !is_array($product_imgs))
                                    $product_imgs = $product_imgs->toArray();

                                $product_img = is_array($product_imgs) && isset($product_imgs[0]) && isset($product_imgs[0]['src']) ? $product_imgs[0]['src'] : null;
                            }

                            $product = Product2::find($item['product_id']);
                        } else {
                            $product = Product2::where('variants', 'like', '%' . $item['sku'] . '%')->first();
                        }
                        if ($product) {

                            $variants = collect(json_decode($product->variants));
                            $variant = $variants->where('id', $item['variant_id'])->first();
                            $images = collect(json_decode($product->images));
                            if (!$variant) {
                                $variant = $variants->where('sku', $item['sku'])->first();
                            }

                            if ($variant) {
                                $product_img2 = $images->where('id', $variant->image_id)->first();
                                if ($product_img2 && $product_img2->src != null && $product_img2->src != '')
                                    $product_img = $product_img2->src;
                            }
                        }

                        $prepare_product->order_id = $order->id;
                        $prepare_product->table_id = $order->table_id;
                        $prepare_product->store_id = $order->store_id;
                        $prepare_product->prepare_id = $add_to_prepare->id;
                        $prepare_product->user_id = Auth::user()->id;
                        $prepare_product->product_id = $item['id'];
                        $prepare_product->product_sku = $item['sku'];
                        $prepare_product->variation_id = $item['variant_title'];
                        $prepare_product->variant_image = $product_img;
                        $prepare_product->order_qty = $item['quantity'];
                        $prepare_product->product_status = "prepared";
                        $prepare_product->prepared_qty = 0;
                        $prepare_product->needed_qty = $item['quantity'];
                        $prepare_product->product_name = $item['title'];
                        $prepare_product->price = $item['price'];
                        $prepare_product->created_at = now();
                        $prepare_product->updated_at = now();
                        $prepare_product->save(); 
                        
                        
                        
                        $posted_data['lineItemId'] = $item['id'];
                        $posted_data['number']= "spg-" . $order->order_number ;
                        $posted_data['shipping_company']= "Best Express";
                        $posted_data['no_of_packages']= "1";
                        $posted_data['message']= "Ship To Customer";
                        $posted_data['tracking_url']= "https://track.bestexpresseg.com/" . "spg-" .$order->order_number;
                        $posted_data['notify_customer']= "off";
                        $posted_data['order_id']=$order->order_number;
                        $posted_data2[] = $posted_data;

                        $fulfillment_order = $this->getFulfillmentLineItem($posted_data, $order);
                        $payload = null;
                        if($fulfillment_order !== null) {
                            $check = $this->checkIfCanBeFulfilledDirectly($fulfillment_order);
                            if(!$check) {

                                $payload = $this->getFulfillmentV2PayloadForFulfillment2($fulfillment_order->line_items, $posted_data,$user->warehouse_id);
                                $api_endpoint = 'graphql.json';
                            } else {
                                if($store->hasRegisteredForFulfillmentService())
                                $payload = $this->getPayloadForFulfillment2($fulfillment_order->line_items, $request,$user->warehouse_id);
                                $api_endpoint = 'fulfillments.json';
                            }

                            $endpoint = getShopifyURLForStore($api_endpoint, $store);
                            $headers = getShopifyHeadersForStore($store);
                            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
                            
                        
                        }

                    }
                    //end prepare
                }
                return $this->generate_invoice($order_id);
            }
        }
        
            return redirect()->back()->with('error', 'Something Went Wrong');
                
    }    
    public function locations(Request $request)
    {
        $warehouses = Warehouse::orderBy("created_at",'DESC');
        $search = null;
        if($request->search)
        {
            $warehouses = $warehouses->where('name','LIKE','%'.$request->search.'%')->orWhere('address1','LIKE','%'.$request->search.'%');
        }
        $warehouses = $warehouses->simplePaginate(15);
        return view('products.locations',compact('warehouses','search'));
    }

    public function hold_products(Request $request)
    {
        $date = $request->date;
        $hold_date = null;
        $delivery_status = null;

        $products = PrepareProductList::whereHas('prepare', function ($q) {
            return $q->where('delivery_status', 'hold');
        })->where('product_status', '!=', 'prepared');

        if($date !=null)
        {
            $products = $products->whereDate('created_at', '=',$date);
        
        }

        if($hold_date !=null)
        {
            $products = $products->whereDate('updated_at', '=',$date);
        }
        if($request->delivery_status)
        {
            $delivery_status = $request->delivery_status;
            $products = $products->where('product_status', $delivery_status);
        }


        $products_count = $products->count();
        
        if(isset($request->button) && $request->button == "export")
        {
            return $this->export_hold_products($products->get());
        }
        $products = $products->orderBy('created_at','desc')->simplePaginate(15);
        $orders = Prepare::all();
        return view('reports.hold_products', compact('products','products_count','orders','date','hold_date','delivery_status'));
    }

    public function export_hold_products($productss)
    {
        $csvData=array('Product Name, Item SKU ,Variation ID, Product Status ,Qty ,Variant,Product Image');
        
        if ($productss) {
            foreach ($productss as $key => $product) {
                $product = PrepareProductList::where('id',$product->id)->first();
                if($product)
                {
                    $csvData[]=   
                    $product->product_name . ','
                    .$product->product_sku . ','
                    . $product->product_id  . ','
                    . $product->product_status  . ','
                    . $product->order_qty  . ','
                    . $product->variation_id  . ','
                    . $product->variant_image  . ','
                    ;
                }
            }
            $filename= 'hold-products-' . date('Ymd').'-'.date('his'). ".xlsx";
            $file_path= public_path().'/download/'.$filename;

            $file = fopen($file_path, "w+");
            foreach ($csvData as $cellData){
                fputcsv($file, explode(',', $cellData));
            }
            fclose($file);

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');

            $objPHPExcel = $reader->load($file_path);
            $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Csv');
            $filenamexlsx= 'hold-products' . '-' .date('Ymd').'-'.date('his'). ".Csv";
            $file_pathxlsx= public_path().'/download/'. $filenamexlsx;

            $objWriter->save($file_pathxlsx);
            return response()->download($file_pathxlsx, $filenamexlsx);

        }
        return redirect()->back();
    }

    public function export_staff_report($prepare_users_list)
    {
        $csvData=array('Name,Total Orders,New Orders,Prepared Orders,Hold Orders,Fulfilled Orders,Shipped Orders');
        foreach($prepare_users_list['name'] as $key => $user)
        {
            $csvData[]=   $prepare_users_list['name'][$key] . ','
                . $prepare_users_list['all'][$key]  . ','
                . $prepare_users_list['new'][$key]  . ','
                . $prepare_users_list['prepared'][$key]  . ','
                . $prepare_users_list['hold'][$key]  . ','
                . $prepare_users_list['fulfilled'][$key]  . ','
                . $prepare_users_list['shipped'][$key]  . ','
                ;
        }
        $filename= 'staff-report-' . date('Ymd').'-'.date('his'). ".xlsx";


        $file_path= public_path().'/download/'.$filename;

        $file = fopen($file_path, "w+");
        foreach ($csvData as $cellData){
            fputcsv($file, explode(',', $cellData));
        }
        fclose($file);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');

        $objPHPExcel = $reader->load($file_path);
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Csv');
        $filenamexlsx= 'staff-report-' . date('Ymd').'-'.date('his'). ".csv";
        $file_pathxlsx= public_path().'/download/'. $filenamexlsx;

        $objWriter->save($file_pathxlsx);
        //dd($file_pathxlsx);
        return response()->download($file_pathxlsx, $filenamexlsx);
        
    }

    public function all_orders(Request $request) {

        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';
        $prepare_users_list = [];

        $user = Auth::user();
        $store = $user->getShopifyStore;
        $orders = $store->getPrepares();

        $all_orders = $store->getOrders()->get();
        
        $all_prepares = $orders->get();

        $orders = $orders->where('type','!=', 'pos');

        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('delivery_status', 'like', '%' . $sort_search . '%')
                ->orWhereHas('order', function ($q)use($sort_search) {
                    return $q->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhere('phone', 'like', '%' . $sort_search . '%')
                    ->orWhere('order_number', 'like', '%' . $sort_search . '%')
                    ->orWhere('shipping_address', 'like', '%' . $sort_search . '%')
                    ->orWhere('email', 'like', '%' . $sort_search . '%');
                });
        }

        if($request->delivery_status) {
            $delivery_status = $request->delivery_status;
            $orders = $orders->where('delivery_status', $delivery_status);
            
        }

        if($date !=null)
        {
            $orders = $orders->whereDate('created_at', '=',$date);
        
        }

        $orders = $orders->orderBy('order_id', 'desc')
                        ->simplePaginate(15)->appends($request->query());

        $prepare_users = User::where('role_id', '5')->get();
        if(count($prepare_users)) {
            foreach ($prepare_users as $key => $prepare) {

                $prepare_users_list['id'][$key] = $prepare->id;
                $prepare_users_list['name'][$key] = $prepare->name;
            }
        }
        
        return view('preparation.all', compact('orders','all_prepares','all_orders','prepare_users_list','date','sort_search','delivery_status','payment_status'));
    }

    public function all_sales(Request $request) {

        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';
        $prepare_users_list = [];

        $user = Auth::user();
        $store = $user->getShopifyStore;
        $orders = $store->getPrepares();

        $all_orders = $store->getOrders()->get();
        
        $all_prepares = $orders->get();

        $orders = $orders->where('type', 'pos');

        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('delivery_status', 'like', '%' . $sort_search . '%')
                ->orWhereHas('order', function ($q)use($sort_search) {
                    return $q->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhere('phone', 'like', '%' . $sort_search . '%')
                    ->orWhere('order_number', 'like', '%' . $sort_search . '%')
                    ->orWhere('shipping_address', 'like', '%' . $sort_search . '%')
                    ->orWhere('email', 'like', '%' . $sort_search . '%');
                });
        }

        if($request->delivery_status) {
            $delivery_status = $request->delivery_status;
            $orders = $orders->where('delivery_status', $delivery_status);
            
        }

        if($date !=null)
        {
            $orders = $orders->whereDate('created_at', '=',$date);
        
        }

        $orders = $orders->orderBy('order_id', 'desc')
                        ->simplePaginate(15)->appends($request->query());

        $prepare_users = User::where('role_id', '5')->get();
        if(count($prepare_users)) {
            foreach ($prepare_users as $key => $prepare) {

                $prepare_users_list['id'][$key] = $prepare->id;
                $prepare_users_list['name'][$key] = $prepare->name;
            }
        }
        
        return view('preparation.sales', compact('orders','all_prepares','all_orders','prepare_users_list','date','sort_search','delivery_status','payment_status'));
    }

    public function new_orders() {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        if($user->id == 1 || $user->id == 2)
        $orders = $store->getPrepares()->where('delivery_status','distributed')->orderBy('table_id', 'desc')
                ->simplePaginate(15);
        else
        $orders = $store->getPrepares()->where('delivery_status','distributed')
                ->where('assign_to',$user->id)->orderBy('table_id', 'asc')
                ->simplePaginate(15);
        return view('preparation.new', ['orders' => $orders]);
    }

    public function prepared_orders() {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        if($user->id == 1 || $user->id == 2)
        $orders = $store->getPrepares()->where('delivery_status','prepared')->orderBy('table_id', 'desc')
                ->simplePaginate(15);
        else
        $orders = $store->getPrepares()->where('delivery_status','prepared')
                ->where('assign_to',$user->id)->orderBy('table_id', 'asc')
                ->simplePaginate(15);
        return view('preparation.order', ['orders' => $orders]);
    }

    public function hold_orders() {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        if($user->id == 1 || $user->id == 2)
        $orders = $store->getPrepares()->where('delivery_status','hold')->orderBy('table_id', 'desc')
                ->simplePaginate(15);
        else
        $orders = $store->getPrepares()->where('delivery_status','hold')
                ->where('assign_to',$user->id)->orderBy('table_id', 'desc')
                ->simplePaginate(15);
        return view('preparation.hold', ['orders' => $orders]);
    }

    public function staff_report(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $prepare_users_list = [];
        $daterange = null;

        $user = Auth::user();
        $store = $user->getShopifyStore;
        $orders = $store->getPrepares();

        if($request->delivery_status) {
            $delivery_status = $request->delivery_status;
            $orders = $orders->where('delivery_status', $delivery_status);
            
        }
        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('delivery_status', 'like', '%' . $sort_search . '%')
                ->orWhereHas('user', function ($q)use($sort_search) {
                    return $q->where('name', 'like', '%' . $sort_search . '%');
                });
        }
        if($request->daterange)
        {
            $daterange = $request->daterange;
            $date = explode(' - ', $daterange);
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[0])->format('Y-m-d');
            $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[1])->format('Y-m-d');
            $orders = $orders->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
        }
        
        $all_prepares = $orders->get();

        $prepare_users = User::where('role_id', '5')->get();
        if(count($prepare_users)) {
            foreach ($prepare_users as $key => $prepare) {

                $prepare_users_list['id'][$key] = $prepare->id;
                $prepare_users_list['name'][$key] = $prepare->name;
                $prepare_users_list['all'][$key] = $all_prepares->where('assign_to',$prepare->id)->count();
                $prepare_users_list['hold'][$key] = $all_prepares->where('assign_to',$prepare->id)->where('delivery_status','hold')->count();
                $prepare_users_list['prepared'][$key] = $all_prepares->where('assign_to',$prepare->id)->where('delivery_status','prepared')->count();
                $prepare_users_list['new'][$key] = $all_prepares->where('assign_to',$prepare->id)->where('delivery_status','distributed')->count();
                $prepare_users_list['shipped'][$key] = $all_prepares->where('assign_to',$prepare->id)->where('delivery_status','shipped')->count();
                $prepare_users_list['fulfilled'][$key] = $all_prepares->where('assign_to',$prepare->id)->where('delivery_status','fulfilled')->count();
                $prepare_users_list['cancelled'][$key] = $all_prepares->where('assign_to',$prepare->id)->where('delivery_status','cancelled')->count();
            }
        }
        if(isset($request->action) && $request->action == "export")
        {
            return $this->export_staff_report($prepare_users_list);
        }
        return view('reports.staff', compact('prepare_users_list','daterange', 'delivery_status', 'all_prepares', 'sort_search'));
    }

    public function cash_register_report(Request $request) {

        ini_set('max_execution_time', 180);
        $data = $request->all();
        $start_date = $data['start_date'] ?? Carbon::now()->startOfDay();
        $end_date = $data['end_date'] ?? Carbon::now()->endOfDay();
        $warehouse_id = $data['warehouse_id'];

        if($warehouse_id == 0) {
            $warehouse_auth = Warehouse::find(Auth::user()->warehouse_id);

            if ($warehouse_auth == null) {
                $warehouse_id = 0;
                $warehouse_val = '>=';
            }else {
                $warehouse_id = $warehouse_auth->id;
                $warehouse_val = '=';
            }

        }else {
            $warehouse_id = $data['warehouse_id'];
            $warehouse_val = '=';

        }
        $registers = cashRegister::select('cash_registers.*')
            -> where('cash_registers.warehouse_id',$warehouse_val , $warehouse_id)->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)->get();
        $registers_sum = DB::table('cash_registers')
            ->select(DB::raw('SUM(total_sales_amount) AS totalSales') ,
                     DB::raw('SUM(register_close_amount) AS TotalClose'),
                     DB::raw('SUM(total_cash) AS TotalCash'),
                     DB::raw('SUM(total_card_slips) AS TotalCredit'))
            -> where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)->get();


        $cash_all_amount = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.pay_method','cash')
            ->where('cash_register_transactions.transaction_type','sell')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('amount');

        $refund_cash_all_amount = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','refund')
            ->where('cash_register_transactions.pay_method','cash')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('amount');

        $credit_all_amount = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','sell')
            ->where('cash_register_transactions.pay_method','Credit Card')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('amount');

        $refund_credit_all_amount = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','refund')
            ->where('cash_register_transactions.pay_method','Credit Card')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('amount');

        $online_all_amount = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','online_refund')
            ->where('cash_register_transactions.pay_method','Cash')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('amount');

        $refund_online_all_amount = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','online_refund')
            ->where('cash_register_transactions.pay_method','Cash')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->count('amount');

        $cash_negative_amount = DB::table('cash_registers')
            ->select('cash_registers.*')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_registers.close_status','negative')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('close_status_amount');

        $cash_positive_amount = DB::table('cash_registers')
            ->select('cash_registers.*')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_registers.close_status','positive')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('close_status_amount');

        $cash_all_register_amount = DB::table('cash_registers')
            ->select('cash_registers.*')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->sum('register_close_amount');

        $all_sales_register_count = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.sale_id','!=' ,'null')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->count('sale_id');

        $all_sales_register_item = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*','sales.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->join('sales','sales.id', '=', 'cash_register_transactions.sale_id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.sale_id','!=' ,'null')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->sum('sales.item');

        $all_sales_register_qty = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*','sales.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->join('sales','sales.id', '=', 'cash_register_transactions.sale_id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.sale_id','!=' ,'null')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->sum('sales.total_qty');


        $all_cash_register_count = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'sell')
            ->where('cash_register_transactions.pay_method','=' ,'Cash')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->count('sale_id');

        $all_cash_register_refund_count = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'refund')
            ->where('cash_register_transactions.pay_method','=' ,'Cash')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->count('sale_id');


        $all_cash_register_refund_item = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*','sales.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->join('sales','sales.id', '=', 'cash_register_transactions.sale_id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'refund')
            ->where('cash_register_transactions.pay_method','=' ,'Cash')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->sum('sales.item');


        $all_cash_register_refund_qty = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*','sales.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->join('sales','sales.id', '=', 'cash_register_transactions.sale_id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'refund')
            ->where('cash_register_transactions.pay_method','=' ,'Cash')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->sum('sales.total_qty');

        $all_credit_register_count = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'sell')
            ->where('cash_register_transactions.pay_method','=' ,'Credit Card')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->count('sale_id');

        $all_credit_register_refund_count = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'refund')
            ->where('cash_register_transactions.pay_method','=' ,'Credit Card')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->count('sale_id');

        $all_credit_register_refund_item = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*','sales.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->join('sales','sales.id', '=', 'cash_register_transactions.sale_id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'refund')
            ->where('cash_register_transactions.pay_method','=' ,'Credit Card')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->sum('sales.item');

        $all_credit_register_refund_qty = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*','sales.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->join('sales','sales.id', '=', 'cash_register_transactions.sale_id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.transaction_type','=' ,'refund')
            ->where('cash_register_transactions.pay_method','=' ,'Credit Card')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->sum('sales.total_qty');

        $all_sales_register_user__count = DB::table('cash_registers')
            ->select('cash_registers.*','cash_register_transactions.*')
            ->join('cash_register_transactions','cash_register_transactions.cash_register_id', '=', 'cash_registers.id')
            ->where('cash_registers.warehouse_id',$warehouse_val ,$warehouse_id)
            ->where('cash_register_transactions.sale_id','!=' ,'null')
            ->whereDate('cash_registers.created_at', '>=' , $start_date)->whereDate('cash_registers.created_at', '<=' , $end_date)
            ->get()->count('sale_id');


        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_user_list = User::where('is_active', true)->get();



        return view('reports.cash_registers_report',
            compact('registers','lims_warehouse_list','lims_user_list','registers_sum','start_date','end_date',
                'warehouse_id','cash_all_amount','refund_cash_all_amount','credit_all_amount','refund_credit_all_amount',
                'cash_negative_amount','cash_positive_amount','cash_all_register_amount',
                'all_sales_register_count', 'all_cash_register_count' ,'all_cash_register_refund_count' ,
                'all_credit_register_count','all_credit_register_refund_count','all_sales_register_item','all_sales_register_qty',
            'all_cash_register_refund_item',
                'online_all_amount','refund_online_all_amount','all_cash_register_refund_qty','all_credit_register_refund_item','all_credit_register_refund_qty'));
    }

    public function pos()
    {
        $user = Auth::user();
        $warehouse_id = $user->warehouse_id;
        
        if ($this->countOpenedRegister() != 0) {


            $lims_customer_list = Customer2::select('id','name','phone_number')->where('is_active', true)->limit(1)->get();
            $lims_product_list = WarehouseProduct2::select('id', 'title', 'barcode', 'image_id', 'sku')->where('warehouse_id',$warehouse_id)->limit(15)->get();
            $employees = Employee::where('warehouse_id', $warehouse_id)->get();
            $product_number = 20;
            $cashRegister = CashRegister::where('user_id', Auth::user()->id)
                            ->where('status', 'open')
                            ->first();
            return view('orders.pos', compact('lims_customer_list','employees', 'lims_product_list','warehouse_id', 'product_number', 'cashRegister'));
        }else{
            $register = CashRegister::where('user_id', Auth::user()->id)
                ->latest()->first();

            if($register) {

                $user_id = auth()->user()->id;
                $warehouse_id = auth()->user()->warehouse_id;

                $newRegister = CashRegister::create([
                            'warehouse_id' => $warehouse_id,
                            'user_id' => $user_id,
                            'status' => 'open',
                            'created_at' => Carbon::now()->format('Y-m-d H:i:00')
                ]);
                $newRegister->cash_register_transactions()->create([
                    'amount' => $register->next_day_amount??0,
                    'pay_method' => 'Cash',
                    'type' => 'credit',
                    'transaction_type' => 'initial'
                ]);
                return redirect(route('sale.pos'));
            }else {
                return redirect(route('open.register'));
            }
        }
    }


    
    public function getProduct($id)
    {
        $lims_product_warehouse_data = WarehouseProduct2::where([
            ['warehouse_id', $id],['inventory_quantity','>',0]
        ])->get();
        $product_code = [];
        $product_name = [];
        $product_qty = [];
        $product_data = [];
        $product_type = [];
        $product_id = [];
        $product_list = [];
        $qty_list = [];
        //product without variant
        foreach ($lims_product_warehouse_data as $product_warehouse)
        {
            $product_qty[] = $product_warehouse->inventory_quantity;
            $product_code[] =  $product_warehouse->sku;
            $product_name[] = $product_warehouse->title;
            $product_id[] = $product_warehouse->product_id;
        }
        $product_data = [$product_code, $product_name, $product_qty, $product_id];
        return $product_data;
    }

    public function countOpenedRegister()
    {
        $user_id = auth()->user()->id;
        $count =  CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->count();
        return $count;
    }

    public function stock_report(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $prepare_users_list = [];
        $daterange = null;

        $user = Auth::user();
        $store = $user->getShopifyStore;

        $mostSellingProducts = PrepareProductList::select('product_name', 'variant_image','product_sku', 'price', DB::raw('COUNT(*) as total'))
            ->groupBy('product_name', 'variant_image','product_sku', 'price')
            ->orderBy('total', 'desc');
            

        if ($request->search) {
            $sort_search = $request->search;
            $mostSellingProducts = $mostSellingProducts->where('product_name', 'like', '%' . $sort_search . '%');
        }
        if($request->daterange)
        {
            $daterange = $request->daterange;
            $date = explode(' - ', $daterange);
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[0])->format('Y-m-d');
            $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[1])->format('Y-m-d');
            $orders = $mostSellingProducts->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
        }
        
        $mostSellingProducts = $mostSellingProducts->simplePaginate(15);

        return view('reports.stock', compact('mostSellingProducts','daterange', 'delivery_status', 'sort_search'));
    }
    
    public function return_order(Request $request)
    {
        $return = new ReturnedOrder();
        $order = order2::where('order_number', $request->order_number)->orWhere('name', $request->order_number)->first();
        if(!$order)
        $order = Sale::where('order_number', $request->order_number)->orWhere('name', $request->order_number)->first();
        // $validator = Validator::make($request->all(), 
        //     [
        //     'qty' => 'required',
        //     'amount' => 'required',
        //     'reason' => 'required',
        //     ]
        //     , [
        //         'qty.required' => 'This field is required.',
        //         'amount.required' => 'This field is required.',
        //         'reason.required' => 'This field is required.',
        //     ]);

        // if ($validator->fails()) {
        //     return redirect()->back()->withErrors($validator)->withInput();
        // }
        
        if($order)
        {
            //     $user = Auth::user();
            // $store = $user->getShopifyStore;

            // OneOrder::dispatchNow($user, $store, $order->id);
            if($order->note="Point of Sale" || $order->note="Point of Sale")
            $return->type = "pos";
            else
            $return->type = "online";
            $return->order_id = $order->id;
            $return->order_number = $order->order_number;
            $return->note =$request->note;
            $return->shipping_on = $request->shipping_on;
            $return->status = "In Progress";
            $return->user_id = Auth::user()->id;
            $return->created_at = now();
            $return->updated_at = now();
            $return->save();
            $return->return_number = 1000+$return->id;
            $return->save();
            $qty = 0;
            $amount = 0;
            $line_items = [];

            $user = Auth::user();
            $store = $user->getShopifyStore;

            $payload = $this->getFulfillmentItemForReturn($return->order_id);

            $api_endpoint = 'graphql.json';
            

            $endpoint = getShopifyURLForStore($api_endpoint, $store);
            $headers = getShopifyHeadersForStore($store);
            
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);

            $items = [];
            if($response['statusCode'] === 201 || $response['statusCode'] === 200)
            {
                if (isset($response['body']['data']['returnableFulfillments']['edges'])) {
                    foreach($response['body']['data']['returnableFulfillments']['edges'] as $edge)
                    {
                        if(isset($edge['node']['returnableFulfillmentLineItems']['edges']))
                        {
                            foreach($edge['node']['returnableFulfillmentLineItems']['edges'] as $mini)
                            {
                                 $items[] = $mini;
                            }
                        }
                    }
                   
                }
            }
            
            foreach($request->items as $key=>$item) {
                if (!$item)
                    continue;
                $detail = new ReturnDetail();
                $detail->return_id = $return->id;
                $detail->line_item_id = $item;
                $detail->qty = $request->qty[$key];
                $detail->amount = $request->amount[$key];
                $detail->reason = $request->reason[$key];
                $detail->created_at = now();
                $detail->updated_at = now();
                $detail->save();
                $qty += $request->qty[$key];
                $amount += $request->amount[$key];

                if(isset($items[$key]))
                {
                    $line_items[] = '
                    {
                    fulfillmentLineItemId: "'.$items[$key]['node']['fulfillmentLineItem']['id'].'",
                    quantity: '.$detail->qty.',
                    returnReason: '.$detail->reason.'
                    returnReasonNote: "'.$return->note.'"
                    }
                    ';
                }
                

            }

            $return->qty = $qty;
            $return->amount = $amount;
            $return->save();

            $payload = $this->createReturnMutation($return->order_id,$line_items);
            $api_endpoint = 'graphql.json';
            

            $endpoint = getShopifyURLForStore($api_endpoint, $store);
            $headers = getShopifyHeadersForStore($store);
            
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
            if($response['statusCode'] === 201 || $response['statusCode'] === 200)
                return redirect()->route('orders.returned')->with('success','Return Created Successfully');
            dd($response);
        }
        return redirect()->route('orders.returned')->with('error','Order Not Found');
    }

    public function returned_products_report(Request $request){

        $date = $request->date;
        $sort_search = null;
        $reason = null;
        $payment_status = '';
        $prepare_users_list = [];
        $paginate_num = 0;
        $orders = ReturnedOrder::orderBy('created_at','desc');
        $order_ids = $orders->pluck('order_id')->toArray();
        $orders = $orders->pluck('id')->toArray();
        $daterange = null;
        
        $returns = ReturnDetail::whereIn('return_id', $orders)->orderBy('created_at','desc');
            
        if($request->daterange)
        {
            $daterange = $request->daterange;
            $date = explode(' - ', $daterange);
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[0])->format('Y-m-d');
            $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[1])->format('Y-m-d');
            $returns = $returns->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
        }
        

        if($request->reason)
        {
            $reason = $request->reason;
            $returns = $returns->where('reason','like', '%'.$reason.'%');
        }
        $returned_orders = $returns;
        $returns = $returns->pluck('line_item_id')->toArray();   
        if ($request->paginate) {
            $paginate_num = $request->paginate;
        }else {
            $paginate_num = 15;
        }
        $data = [];
        $products = PrepareProductList::orderBy('created_at', 'desc');
        if($request->search)
        {
            $sort_search = $request->search;
            $products = $products->where('product_name', 'like', '%' . $sort_search . '%')->orWhereHas('order', function ($q)use($sort_search) {
                $q->where('order_number', 'like', '%' . $sort_search . '%');
            });
        }
        $products = $products->get();
        foreach($returned_orders->get() as $key=>$return) 
        {
            $products2 = $products->where('product_id',$return->line_item_id)->where('order_id',$return->return->order_id);
            
            foreach($products2 as $product)
            {
                $data[] = [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_img' => $product->variant_image,
                    'old_qty' => $product->order_qty,
                    'returned_qty' => $return->qty,
                    'return_number' => $return->return->return_number,
                    'order_id' => $return->return->order_number,
                    'reason' => $return->reason,
                    'amount' => $return->amount,
                    'created_at' => $return->created_at,
                ];
            }
            
        }
        $data = collect($data);
        $returns_count = $data->count();


        return view('reports.returned_products', compact('data','returns_count','daterange','reason','sort_search'));
    
    }
    public function returned_orders_report(Request $request){
        $returns = ReturnedOrder::orderBy('created_at','desc');
        $date = $request->date;
        $sort_search = null;
        $paginate_num = 0;
        $delivery_status = null;
        $orders_count = ReturnedOrder::count();

        if ($request->paginate) {
            $paginate_num = $request->paginate;
        }else {
            $paginate_num = 15;
        }

        if($request->delivery_status)
        {
            $delivery_status = $request->delivery_status;
            $returns = $returns->where('status', $delivery_status);
        }

        if ($request->search) {
            $sort_search = $request->search;
            $returns = $returns->where('order_number', 'like', '%' . $sort_search . '%')
                ->orWhere('return_number', 'like', '%' . $sort_search . '%')
                ->orWhere('status', 'like', '%' . $sort_search . '%')
                ->orWhereHas('order', function ($q)use($sort_search) {
                    return $q->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhere('phone', 'like', '%' . $sort_search . '%')
                    ->orWhere('shipping_address', 'like', '%' . $sort_search . '%')
                    ->orWhere('email', 'like', '%' . $sort_search . '%');
                });
        }
        if($date !=null)
        {
            $returns = $returns->whereDate('created_at', '=',$date);
        
        }
        $returns_count = $returns->count();
        $returns = $returns->simplePaginate($paginate_num);
        return view('reports.returned_orders', compact('orders_count','returns_count','returns','delivery_status', 'date', 'sort_search'));
    
    }
    public function returned_orders(Request $request){

        $returns = ReturnedOrder::where('status','In Progress')->orderBy('created_at','desc');
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';
        $prepare_users_list = [];
        $paginate_num = 0;
        $orders_count = ReturnedOrder::count();

        $prepare_users = User::where('role_id', '4')->get();
        if(count($prepare_users)) {
            foreach ($prepare_users as $key => $prepare) {

                $prepare_users_list['id'][$key] = $prepare->id;
                $prepare_users_list['name'][$key] = $prepare->name;
            }
        }

        if ($request->paginate) {
            $paginate_num = $request->paginate;
        }else {
            $paginate_num = 15;
        }
        
        if($request->delivery_status)
        {
            $delivery_status = $request->delivery_status;
            $returns = $returns->where('status', $delivery_status);
        }

        if ($request->search) {
            $sort_search = $request->search;
            $returns = $returns->where('order_number', 'like', '%' . $sort_search . '%')
                ->orWhere('return_number', 'like', '%' . $sort_search . '%')
                ->orWhere('status', 'like', '%' . $sort_search . '%')
                ->orWhereHas('order', function ($q)use($sort_search) {
                    return $q->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhere('phone', 'like', '%' . $sort_search . '%')
                    ->orWhere('shipping_address', 'like', '%' . $sort_search . '%')
                    ->orWhere('email', 'like', '%' . $sort_search . '%');
                });
        }
        if($date !=null)
        {
            $returns = $returns->whereDate('created_at', '=',$date);
        
        }
        $returns = $returns->paginate($paginate_num);
        return view('preparation.returned_orders', compact('orders_count','prepare_users_list','returns', 'delivery_status', 'date', 'sort_search'));
    }

    
    public function import_inventory(Request $request)
    {
        if($request->hasFile('sheet')){
            $import = new ProductsImport($request);
            Excel::import($import, request()->file('sheet'));
            
            $file = $request->file('sheet');
            $fileName = $file->getClientOriginalName();
            $file->move(public_path('uploads'), $fileName);

            // Get the public path to the uploaded file
            $publicPath = asset('uploads/' . $fileName);
            if(isset($import->message))
                return redirect()->route('inventories.index')->with('errors', $import->message);
            if(isset($import->transfer_id)){
                $transfer = InventoryTransfer::where('id', $import->transfer_id)->first();
                if($transfer)
                {
                    $transfer->sheet = $publicPath;
                    //dd($publicPath);
                    $transfer->note = $request->note;
                    $transfer->save();
                    return redirect()->route('inventories.index')->with('success', 'Inventory Updated Successfully');
                }
            }
        }

        return back();
    }

    public function inventory_transfers(Request $request)
    {
        $date = $request->date;
        $transfers = InventoryTransfer::orderBy('created_at', 'desc');
        if($date !=null)
        {
            $transfers = $transfers->whereDate('created_at', '=',$date);
        
        }
        $transfers = $transfers->simplePaginate(15);
        return view('transfers.index', compact('transfers', 'date'));

    }

    public function show_inventory_transfers($id)
    {
        $transfer = InventoryTransfer::find($id);
        if($transfer)
        {
            $details = InventoryDetail::where('transfer_id',$transfer->id)->orderBy('created_at','desc')->get();
            return view('transfers.show', compact('details','transfer'));
        }
        return redirect()->back()->with('error', 'Transfer Not Found');

    }

    public function prepareOrder($id)
    {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $order = order2::where('name','like','%'. $id.'%')->first();
        if(!$order)
        $order = Sale::where('name','like','%'. $id.'%')->first();

        if ($user->role_id == 6 && $order->fulfillment_status != "shipped")
            return redirect()->back()->with('error',"Youre not permitted to view this page");

        $product_images = $store->getProductImagesForOrder($order);

        $prepare = Prepare::where('order_id', $order->id)->first();

        $prepare_products = PrepareProductList::where('prepare_id', $prepare->id)->first();
        $refunds = Refund::where('order_name', $order->name)->pluck('line_item_id')->toArray();
        $returns = [];
        $return = ReturnedOrder::where('order_number', $order->order_number)->pluck('id')->toArray();
        if ($return)
            $returns = ReturnDetail::whereIn('return_id', $return)->pluck('line_item_id')->toArray();
        return view('preparation.prepare', [
            'order_currency' => getCurrencySymbol($order->currency),
            'product_images' => $product_images,
            'order' => $order,
            'prepare_products' => $prepare_products,
            'prepare'=>$prepare,
            'refunds'=>$refunds,
            'returns'=>$returns,
        ]);
    }


    public function validateAssignOrders($id)
    {
        $order = order2::find($id);
        $check_order_status_error = [];
        if ($order != null) {
            if($order->fulfillment_status == "shipped" || $order->fulfillment_status == "fulfilled") {
                $check_order_status_error[] = $order->id;
            }
        }
        return $check_order_status_error;
    }

    public function assignOrders($id,$prepare_emp)
    {
        $prepare_employee = User::find($prepare_emp);
        $order = order2::find($id);
        
        $user = Auth::user();
        $store = $user->getShopifyStore;
        if ($order != null) {

                try {
                    $order = order2::where('id',$id)->first();
                    $order->status = "3";
                    $order->fulfillment_status = "distributed";
                    $order->save();

                    // Add TO Prepare
                    //firstornew
                    $add_to_prepare = Prepare::where('order_id',$order->id)->first();
                    if($add_to_prepare){
                        $add_to_prepare->delete();
                        $add_History_sale = new OrderHistory();
                        $add_History_sale->order_id = $order->id;
                        $add_History_sale->user_id = Auth::user()->id;
                        $add_History_sale->action = "ReAssign";
                        $add_History_sale->created_at = now();
                        $add_History_sale->updated_at = now();
                        $add_History_sale->note = " Order Has Been ReAssigned By : <strong>" . auth()->user()->name . "</strong> To : <strong>" . $prepare_employee->name ."</strong>";
                    }
                    else{
                        $add_History_sale = new OrderHistory();
                        $add_History_sale->order_id = $order->id;
                        $add_History_sale->user_id = Auth::user()->id;
                        $add_History_sale->action = "Assign";
                        $add_History_sale->created_at = now();
                        $add_History_sale->updated_at = now();
                        $add_History_sale->note = " Order Has Been Assigned By : <strong>" . auth()->user()->name . "</strong> To : <strong>" . $prepare_employee->name ."</strong>";


                    }
                    $add_History_sale->save();
                    $add_to_prepare = new Prepare();
                    $add_to_prepare->order_id  = $order->id;
                    $add_to_prepare->store_id  = $order->store_id;
                    $add_to_prepare->table_id  = $order->table_id;
                    $add_to_prepare->assign_by  = Auth::user()->id;
                    $add_to_prepare->assign_to  = $prepare_emp;
                    $add_to_prepare->status  = "3";
                    $add_to_prepare->delivery_status  = "distributed";
                    $add_to_prepare->sale_created_at  = $order->created_at_date;
                    $add_to_prepare->created_at  = now();
                    $add_to_prepare->updated_at  = now();
                    $add_to_prepare->save();
                    $prepare_product = PrepareProductList::where('order_id',$order->id)->delete();
                    $product_images = $store->getProductImagesForOrder($order);
                
                    foreach($order->line_items as $item)
                    {
                        $product_img = "";
                        $prepare_product = new PrepareProductList();
                        if(isset($item['product_id']) && $item['product_id'] != null)
                        {
                            if(isset($product_images[$item['product_id']]))
                            {
                                $product_imgs = is_array($product_images[$item['product_id']]) ? $product_images[$item['product_id']] : json_decode(str_replace('\\','/',$product_images[$item['product_id']]),true);
                                if ($product_imgs && !is_array($product_imgs))
                                    $product_imgs = $product_imgs->toArray();

                                $product_img = is_array($product_imgs) && isset($product_imgs[0]) && isset($product_imgs[0]['src']) ? $product_imgs[0]['src'] : null;
                            }
                        
                            $product = Product2::find($item['product_id']);
                        }
                        else
                        {
                            $product = Product2::where('variants','like','%'.$item['sku'].'%')->first();
                        }
                        if($product) {

                            $variants = collect(json_decode($product->variants));
                            $variant = $variants->where('id',$item['variant_id'])->first();
                            $images = collect(json_decode($product->images));
                            if(!$variant)
                            {
                                $variant = $variants->where('sku',$item['sku'])->first();
                            }

                            if($variant)
                            {
                                $product_img2 = $images->where('id', $variant->image_id)->first();
                                if ($product_img2 && $product_img2->src != null && $product_img2->src != '')
                                    $product_img = $product_img2->src;
                            }
                        }
                        



                        $prepare_product->order_id = $order->id;
                        $prepare_product->table_id = $order->table_id;
                        $prepare_product->store_id = $order->store_id;
                        $prepare_product->prepare_id = $add_to_prepare->id;
                        $prepare_product->user_id = Auth::user()->id;
                        $prepare_product->product_id = $item['id'];
                        $prepare_product->product_sku = $item['sku'];
                        $prepare_product->variation_id = $item['variant_title'];
                        $prepare_product->variant_image = $product_img;
                        $prepare_product->order_qty = $item['quantity'];
                        $prepare_product->product_status= $item['fulfillment_status']??"unfulfilled";
                        $prepare_product->prepared_qty = 0;
                        $prepare_product->needed_qty = $item['quantity'];
                        $prepare_product->product_name = $item['title'];
                        $prepare_product->price = $item['price'];
                        $prepare_product->created_at = now();
                        $prepare_product->updated_at = now();
                        $prepare_product->save();

                    }

                } catch (\Exception $e) {
                dd($e);
                }
            
            $order->save();
            return redirect()->back()->with('success','Orders has been Assigned successfully');
        } else {
            return redirect()->back()->with('error','Something went wrong');
        }
        return back();

    }

    public function bulk_order_assign(Request $request)
    {

        $check_order_status = [];
        $auth_user = Auth::user()->id;
        if ($request->id) {
            foreach ($request->id as $key => $order_id) {
                if($this->validateAssignOrders($order_id) != null) {
                    $check_order_status[] = $this->validateAssignOrders($order_id)[0];
                }
            }
        }

        if ($check_order_status != null) {

            foreach ($check_order_status as $error_orders) {
                return redirect()->back()->with('success',"Order Number: " . $error_orders . ' Not A Processing');
            }
            return 1;
        } else {
            if ($request->id) {
                foreach ($request->id as $order_id) {
                    // $order = order2::findOrFail($order_id);
                    $this->assignOrders($order_id,$request->prepare_emp);


                    //Add Order History
                }
            }
        }

    }

    public function showOrder($id) {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $order = $store->getOrders()->where('table_id', $id)->first();
        if($order->getFulfillmentOrderDataInfo()->doesntExist())
            OrderFulfillments::dispatch($user, $store, $order);
        $product_images = $store->getProductImagesForOrder($order);
        $refunds = Refund::where('order_name', $order->name)->pluck('line_item_id')->toArray();
        return view('orders.show', [
            'order_currency' => getCurrencySymbol($order->currency),
            'product_images' => $product_images,
            'order' => $order,
            'refunds' => $refunds,
        ]);
    }

    public function createReturnMutation($order_id,$line_items)
    {
        $fulfillmentV2Mutation = 'returnCreate (

            returnInput: {
            orderId: "gid://shopify/Order/'.$order_id.'",
            returnLineItems: ['.implode(',',$line_items).'],
            requestedAt: "2022-05-04T00:00:00Z",
            	notifyCustomer: false,
        }
        )
        {
            return {
            id
            }
            userErrors {
            field
            message
            }
        }';
        $mutation = 'mutation returnCreateMutation{ '.$fulfillmentV2Mutation.' }';
        // dd($mutation);
        return ['query' => $mutation];

    }
    public function getFulfillmentItemForReturn($order_id)
    {
        $query = '
        
        query returnableFulfillmentsQuery {
            returnableFulfillments(orderId: "gid://shopify/Order/'.$order_id.'", first: 10) {
                edges {
                node {
                    id
                    fulfillment {
                    id
                    }
                    returnableFulfillmentLineItems(first: 10) {
                    edges {
                        node {
                        fulfillmentLineItem {
                            id
                        }
                        quantity
                        }
                    }
                    }
                }
                }
            }
            }

        ';
        return ['query' => $query];
    }

    private function getFulfillmentLineItem($posted_data, $order) {
        try {
            $search = (int) $posted_data['lineItemId'];
            $fulfillment_orders = $order->getFulfillmentOrderDataInfo;

            foreach($fulfillment_orders as $fulfillment_order) {
                $line_items = $fulfillment_order->line_items;
                foreach($line_items as $item) {
                    if($item['line_item_id'] === $search){
                        return $fulfillment_order;
                    }// Found it!
                }
            }

        } catch(Exception $e) {
            return null;
        }
    }

    private function getPayloadForFulfillment($line_items, $request) {
        return [
            'fulfillment' => [
                'message' => $request['message'],
                'notify_customer' => $request['notify_customer'] === 'on',
                'tracking_info' => [
                    'number' => $request['number'],
                    'url' => $request['tracking_url'],
                    'company' => $request['shipping_company']
                ],
                'line_items_by_fulfillment_order' => $this->getFulfillmentOrderArray($line_items, $request)
            ]
        ];
    }
    private function getPayloadForFulfillment2($line_items, $request,$location_id) {
        return [
            'fulfillment' => [
                'message' => $request['message'],
                'notify_customer' => $request['notify_customer'] === 'on',
                'tracking_info' => [
                    'number' => $request['number'],
                    'url' => $request['tracking_url'],
                    'company' => $request['shipping_company']
                ],
                
                'line_items_by_fulfillment_order' => $this->getFulfillmentOrderArray($line_items, $request)
            ]
        ];
    }

    public function markAsPaidMutation($order_id)
    {
        $mutation = 'mutation orderMarkAsPaid($input: OrderMarkAsPaidInput!) {
        orderMarkAsPaid(input: $input) {
            order {
            id
            note
            email
            totalPrice
            }
            userErrors {
            field
            message
            }
        }
        }';
        $variables = [
            'input' => [
                'id' => "gid://shopify/Order/".$order_id,
            ]
        ];
        return ['query' => $mutation,'variables' => $variables];

    }

    private function getFulfillmentOrderArray($line_items, $request) {
        $temp_payload = [];
        $search = (int) $request['lineItemId'];
        foreach($line_items as $line_item)
            if($line_item['line_item_id'] === $search)
                $temp_payload[] = [
                    'fulfillment_order_id' => $line_item['fulfillment_order_id'],
                    'fulfillment_order_line_items' => [[
                        'id' => $line_item['id'],
                        'quantity' => (int) $request['no_of_packages']
                    ]]
                ];
        return $temp_payload;
    }

    private function checkIfCanBeFulfilledDirectly($fulfillment_order) {
        return in_array('request_fulfillment', $fulfillment_order->supported_actions);
    }

    private function getLineItemsByFulifllmentOrderPayload($line_items, $request) {
        $search = (int) $request['lineItemId'];
        $id = $line_items[0]['fulfillment_order_id'];
        $items = "";
        foreach($line_items as $line_item)
            if($line_item['line_item_id'] === $search){
                $items = $items . 'fulfillmentOrderLineItems: { id: "gid://shopify/FulfillmentOrderLineItem/' . $line_item['id'] . '", quantity: ' . (int) $request['no_of_packages'] . ' }';
            }
        return implode(',', [
                    'fulfillmentOrderId: "gid://shopify/FulfillmentOrder/'.$id.'"',
                    $items
                ]);
    }

    private function getLineItemsByHoldFulifllmentOrderPayload($line_items, $request) {
        $search = (int) $request['lineItemId'];
        foreach($line_items as $line_item)
            if($line_item['line_item_id'] === $search)
                return implode(',', [
                    'id: "gid://shopify/FulfillmentOrder/'.$line_item['fulfillment_order_id'].'"',
                    'quantity:'. 1 ,
                ]);
    }

    private function getGraphQLPayloadForFulfillment($line_items, $request) {
        $temp = [];
        $temp[] = 'notifyCustomer: '.($request['notify_customer'] === 'on' ? 'true':'false');
        $temp[] = 'trackingInfo: { company: "'.$request['shipping_company'].'", number: "'.$request['number'].'", url: "'.$request['tracking_url'].'"}';
        $temp[] = 'lineItemsByFulfillmentOrder: [{ '.$this->getLineItemsByFulifllmentOrderPayload($line_items, $request).' }]';
        return implode(',', $temp);
    }
    private function getGraphQLPayloadForFulfillment2($line_items, $request,$location_id) {
        $temp = [];
        $temp[] = 'notifyCustomer: '.($request['notify_customer'] === 'on' ? 'true':'false');
        $temp[] = 'trackingInfo: { company: "'.$request['shipping_company'].'", number: "'.$request['number'].'", url: "'.$request['tracking_url'].'"}';
        // $temp[] = 'location: { id: "gid://shopify/Location/77177291002" }';
        $temp[] = 'lineItemsByFulfillmentOrder: [{ '.$this->getLineItemsByFulifllmentOrderPayload($line_items, $request).' }]';
        return implode(',', $temp);
    }

    private function getGraphQLPayloadForHoldFulfillment($line_items, $request) {
        $temp = [];
        $temp[] = 'notifyMerchant: false';
        $temp[] = 'reason: INVENTORY_OUT_OF_STOCK';
        $temp[] = 'reasonNotes: "Waiting on new shipment"';
        return implode(',', $temp);
    }

    private function getFulfillmentV2PayloadForFulfillment($line_items, $request) {
        $fulfillmentV2Mutation = 'fulfillmentCreateV2 (fulfillment: {'.$this->getGraphQLPayloadForFulfillment($line_items, $request).'}) {
            fulfillment { id }
            userErrors { field message }
        }';
        $mutation = 'mutation MarkAsFulfilledSubmit{ '.$fulfillmentV2Mutation.' }';
        return ['query' => $mutation];
    }
    private function getFulfillmentV2PayloadForFulfillment2($line_items, $request,$location_id) {
        $fulfillmentV2Mutation = 'fulfillmentCreateV2 (fulfillment: {'.$this->getGraphQLPayloadForFulfillment2($line_items, $request,$location_id).'}) {
            fulfillment { id }
            userErrors { field message }
        }';
        $mutation = 'mutation MarkAsFulfilledSubmit{ '.$fulfillmentV2Mutation.' }';
        return ['query' => $mutation];
    }

    private function getFulfillmentV2PayloadForHoldFulfillment($line_items, $request) {

        $fulfillmentHoldMutation = 'fulfillmentOrderHold (fulfillmentHold: {'.$this->getGraphQLPayloadForHoldFulfillment($line_items, $request).'}
        ,id:"gid://shopify/FulfillmentOrder/'. $line_items[0]['fulfillment_order_id']. '") {
            userErrors { field message }
        },
        ';
        $mutations = 'mutation fulfillmentOrderHold{ '.$fulfillmentHoldMutation.' }';
        return ['query' => $mutations];
    }

    public function review_order($id)
    {
        $order = order2::where('id', $id)->first();
        $prepare = Prepare::where('order_id', $id)->first();
        $order_currency=getCurrencySymbol($order->currency);
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $product_images = $store->getProductImagesForOrder($order);
        $refunds = Refund::where('order_name', $order->name)->pluck('line_item_id')->toArray();

        return view('preparation.review', compact('order','prepare','order_currency','product_images','refunds'));
    }

    public function reviewed_orders(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';
        $prepare_users_list = [];
        $paginate_num = 0;
        $orders_count = order2::where('fulfillment_status', 'fulfilled')->count();
        $orders = order2::where('fulfillment_status', 'fulfilled')->orderBy('id', 'desc');

        $prepare_users = User::where('role_id', '4')->get();
        if(count($prepare_users)) {
            foreach ($prepare_users as $key => $prepare) {

                $prepare_users_list['id'][$key] = $prepare->id;
                $prepare_users_list['name'][$key] = $prepare->name;
            }
        }

        if ($request->paginate) {
            $paginate_num = $request->paginate;
        }else {
            $paginate_num = 15;
        }

         if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%' . $sort_search . '%')
                ->orWhere('id', 'like', '%' . $sort_search . '%')
                ->orWhere('fulfillment_status', 'like', '%' . $sort_search . '%');
        }
        if($date !=null)
        {
            $orders = $orders->whereDate('created_at', '=',$date);
        
        }

        $orders = $orders->simplePaginate($paginate_num)->appends($request->query());
        return view('preparation.review_orders_list', compact('orders','delivery_status','prepare_users_list', 'sort_search', 'orders_count', 'date'));
    }

    public function review_post(Request $request)
    {
        $data = $request->all();
        $auth_id = auth()->user()->id;
        $auth_user = auth()->user()->name;
        $user = Auth::user();
        $store = $user->getShopifyStore;

        $order = order2::where('order_number', $request['order_id'])->first();
        $prepare = Prepare::where('order_id', $order->id)->first();
        $refunds = Refund::where('order_name', $order->name)->pluck('line_item_id')->toArray();

        if($order->getFulfillmentOrderDataInfo()->doesntExist()){
            OrderFulfillments::dispatch($user, $store, $order);
        }

        if($order)
        {
            $order->fulfillment_status = "reviewed";
            $order->status = 8;
            $order->save();
            $data['name'] = $order->name;
        }
        if($prepare)
        {
            $prepare->delivery_status = 'reviewed';
            $prepare->save();
        }

        $data['quantity'] = 0;
        $data['order_id'] = str_replace('#','',$order->name);
        if (is_array($order->payment_gateway_names) && isset($order->payment_gateway_names[0]) && ($order->payment_gateway_names[0]  == "fawrypay (pay by card or at any fawry location)" || $order->payment_gateway_names[0]  == "Paymob"))
            $data['total'] = 0;
        else
            $data['total'] = $order->total_price;

        
        $shipping_cost = 0;
        foreach ($order['shipping_lines'] as $ship) {
            $shipping_cost += $ship['price'];
        }
        $posted_data2 = [];
        foreach ($request['line_item_id'] as $key => $item) {
            
            $prepare_product = PrepareProductList::where('product_id', $item)->where('order_id', $order->id)->first();
            if(!in_array($prepare_product->product_id , $refunds))
                $data['quantity']+= $prepare_product->order_qty;


            $posted_data['lineItemId'] = $item;
            $posted_data['number']= "spg-" . $request['order_id'] ;
            $posted_data['shipping_company']= "Best Express";
            $posted_data['no_of_packages']= "1";
            $posted_data['message']= "Ship To Customer";
            $posted_data['tracking_url']= "https://track.bestexpresseg.com/" . "spg-" . $request['order_id'];
            $posted_data['notify_customer']= "off";
            $posted_data['order_id']= $request['order_id'];
            $posted_data2[] = $posted_data;

            $fulfillment_order = $this->getFulfillmentLineItem($posted_data, $order);
            $payload = null;

            if($fulfillment_order !== null) {
                if(!in_array($item,$refunds))
                {
                    $check = $this->checkIfCanBeFulfilledDirectly($fulfillment_order);
                    if(!$check) {

                        if ($request["product_status"][$key] == 'prepared') {
                            $payload = $this->getFulfillmentV2PayloadForFulfillment($fulfillment_order->line_items, $posted_data);
                        }
                        $api_endpoint = 'graphql.json';
                    } else {
                        if($store->hasRegisteredForFulfillmentService())
                        $payload = $this->getPayloadForFulfillment($fulfillment_order->line_items, $request);
                        $api_endpoint = 'fulfillments.json';
                    }

                    $endpoint = getShopifyURLForStore($api_endpoint, $store);
                    $headers = getShopifyHeadersForStore($store);
                    $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
                }
                

            }

        }

        OneOrder::dispatch($user, $store, $order->id);
        if ($prepare->delivery_status != "fulfilled")
        {
            $prepare->delivery_status = 'fulfilled';
            $prepare->save();
        }
        
        if($order)
        {
            $order->fulfillment_status = "fulfilled";
            $order->status = 10;
            $order->save();
        }
            
        
            
        if(isset($response))
        {
            Log::info('Response for fulfillment');
            Log::info(json_encode($response));
        }



        $add_History_sale = new OrderHistory();
        $add_History_sale->order_id = $order->id;
        $add_History_sale->user_id = Auth::user()->id;
        $add_History_sale->action = "Fulfilled";
        $add_History_sale->note = '<strong>' . auth()->user()->name .  " </strong> Has Change Order To Fulfilled<strong>";
        $add_History_sale->created_at = now();
        $add_History_sale->updated_at = now();
        $add_History_sale->save();

        return view('preparation.invoice_order', compact('data','refunds','shipping_cost','order','prepare','auth_user'));

    }

    public function cancelled_Orders(Request $request)
    {
        $daterange = null;
        $sort_search = null;
        $reason = null;
        $paginate_num = 0;

        $orders = CancelledOrder::orderBy('created_at', 'desc');
        if($request->daterange)
        {
            $daterange = $request->daterange;
            $date = explode(' - ', $daterange);
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[0])->format('Y-m-d');
            $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', $date[1])->format('Y-m-d');
            $orders = $orders->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
        }

        if($request->search)
        {
            $sort_search = $request->search;
            $orders = $orders->where('reason', 'like', '%' . $sort_search . '%')->orWhere('note', 'like', '%' . $sort_search . '%')->orWhereHas('order', function ($q) use ($sort_search) {
                $q->where('order_number', 'like', '%' . $sort_search . '%');
            });
        }

        if($request->reason)
        {
            $reason = $request->reason;
            $orders = $orders->where('reason', 'like', '%' . $reason . '%');
        }
        if ($request->paginate) {
            $paginate_num = $request->paginate;
        }else {
            $paginate_num = 15;
        }
        $orders_count = $orders->count();
        $orders = $orders->simplePaginate($paginate_num);
        return view('reports.cancelled', compact('orders','orders_count','sort_search','daterange','reason','paginate_num'));
    }

    public function bulk_order_shipped(Request $request)
    {
        $sales = array();
        $key = 1;
        $auth_user = Auth::user()->id;
        $sale_id = $request['id'];

        $csvData=array('AWB,Name,Addr1,Addr2,Phone,Mobile,City,zone,Contents,Weight,Peices,Shipping Cost,Special Instructions,Ref,Contact Person,COD,AWBxAWB');
        $csvAccountingData=array('AWB,Name,Addr1,Mobile,City,zone,subtotal,shipping,total,Payment Method,Special Instructions,Shipping Note');

        foreach ($sale_id as $id) {

            $sale_data = order2::findorfail($id);
            $sale_prepare_data = Prepare::where('order_id',$id)->first();
            $order_details = $sale_data->shipping_address;

            $address = preg_replace( "/\r|\n/", "", $order_details['address1'] );
            $address = str_replace(['-','/','"',"_",'.',','],'',$address);

            if (is_array($sale_data->payment_gateway_names) && isset($sale_data->payment_gateway_names[0]) && ($sale_data->payment_gateway_names[0]  == "fawrypay (pay by card or at any fawry location)" || $sale_data->payment_gateway_names[0]  == "Paymob"))
            {   
                $total = 0;
                $payment_method = $sale_data->payment_gateway_names[0];
                $note = "";

            }
                
            else{
                $total = $sale_data->total_price;
                $payment_method = "Cash On Delivery";
                $note = $sale_data->note;
            }
                

            $order_number = "spg" . $sale_data->order_number;


            $sale_data->carrier_id = $request['shipping_company'];
            $sale_data->fulfillment_status = 'shipped';
            $sale_data->status = '10';
            $sale_data->save();

            if(isset($sale_prepare_data))
            {
                $sale_prepare_data->delivery_status = 'shipped';
                $sale_prepare_data->status = '10';
                $sale_prepare_data->save();
            }

            


            if($request['shipping_company'] == 1) {
                $shipping_company = 1;
                $company_name = "Best Express";
            }else {
                $shipping_company = 2;
                $company_name = "Sprint";
            }

            $shipping_cost = 0;
            foreach ($sale_data['shipping_lines'] as $ship) {
                $shipping_cost += $ship['price'];
            }


            $add_History_sale = new OrderHistory();
            $add_History_sale->order_id = $sale_data->id;
            $add_History_sale->user_id = $auth_user;
            $add_History_sale->action = "Shipped";
            $add_History_sale->note = '<strong>' . auth()->user()->name .  " </strong> Has Change Order To Shipped By : <strong>" . $company_name . "</strong> ";
            $add_History_sale->created_at = now();
            $add_History_sale->updated_at = now();
            $add_History_sale->save();

        $sales[] =  $sale_data;

            $csvData[]=   $order_number . ','
                . $order_details['name']  . ','
                . $address  . ','
                . $order_details['address2']  . ','
                . $order_details['phone']  . ','
                . $order_details['phone']  . ','
                . $order_details['province']  . ','
                . $order_details['city']  . ','
                . '1' . ','
                . '1' . ','
                . '1' . ','
                . $shipping_cost . ','
                . $note . ','
                . ' ' . ','
                . ' ' . ','
                . $total .  ','
                ;


            $total_account = $sale_data->total_price;


            $csvAccountingData[] = $order_number . ','
                . $order_details['name'] . ','
                . $address . ','
                . $order_details['phone'] . ','
                . $order_details['province'] . ','
                . $order_details['city'] . ','
                . $sale_data->subtotal_price  . ','
                . $shipping_cost . ','
                . $total_account . ','
                . $payment_method  . ','
                . $sale_data->note . ','
                . ',';



        }
        $filename= 'pickup-' . date('Ymd').'-'.date('his'). ".xlsx";


        $file_path= public_path().'/download/'.$filename;

        $file = fopen($file_path, "w+");
        foreach ($csvData as $cellData){
            fputcsv($file, explode(',', $cellData));
        }
        fclose($file);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');

        $objPHPExcel = $reader->load($file_path);
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $filenamexlsx= 'pickup-' . date('Ymd').'-'.date('his'). ".xlsx";
        $file_pathxlsx= public_path().'/download/'. $filenamexlsx;

        $objWriter->save($file_pathxlsx);

        //Acount
        $accountFileName= 'pickup-accounting' . date('Ymd').'-'.date('his'). ".xlsx";
        $account_file_path= public_path().'/download/'.$accountFileName;
        $accountFile = fopen($account_file_path, "w+");
        foreach ($csvAccountingData as $cellDataAccount){
            fputcsv($accountFile, explode(',', $cellDataAccount));
        }
        fclose($accountFile);

        $reader_account = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');

        $objPHPExcelAccount = $reader_account->load($account_file_path);
        $objWriterAccount = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcelAccount, 'Xlsx');

        $file_pathxlsxAccount= public_path().'/download/'. $accountFileName;

        $objWriterAccount->save($file_pathxlsxAccount);


        //  create a new collection instance from the array
        $size = count(collect($sale_id));
        $totalData = $size;
        $totalFiltered = $totalData;

        $create_pickup = new Pickup();
        $create_pickup->pickup_id = date('Ymd'). date('his');
        $create_pickup->user_id = Auth::user()->id;
        $create_pickup->shipment_count = $totalData;
        $create_pickup->company_id = $shipping_company;
        $create_pickup->file_name = $filenamexlsx;
        $create_pickup->file_accounting_name = $accountFileName;
        $create_pickup->created_at = now();
        $create_pickup->updated_at = now();
        $create_pickup->save();

        $data = [ 'message' => 'Pickup Created  With total ' . $totalData . 'Shipments '];
        return response()->json($data);

    }

    public function bulk_returns_shipped(Request $request)
    {
        $sales = array();
        $key = 1;
        $auth_user = Auth::user()->id;
        $sale_id = $request['id'];

        $csvData=array('AWB,Name,Addr1,Addr2,Phone,Mobile,City,zone,Contents,Weight,Peices,Special Instructions,Ref,Contact Person,COD,AWBxAWB');
        $csvAccountingData=array('AWB,Order Number,Name,Addr1,Mobile,City,zone,shipping cost, shipping on,total,Return Reason,Return Note,Special Instructions');

        foreach ($sale_id as $id) {

            $sale_data = ReturnedOrder::findorfail($id);
            $old_order = order2::where('id',$sale_data->order_id)->first();
            $order_details = $old_order->shipping_address;

            $address = preg_replace( "/\r|\n/", "", $order_details['address1'] );
            $address = str_replace(['-','/','"',"_",'.',','],'',$address);
                

            $order_number = "spg" . $sale_data->order_number;
            $return_number = "Lvr" . $sale_data->return_number;

            if($request['shipping_company'] == 1) {
                $shipping_company = 1;
                $company_name = "Best Express";
            }else {
                $shipping_company = 2;
                $company_name = "Sprint";
            }

            $shipping_cost = 0;
            foreach ($old_order['shipping_lines'] as $ship) {
                $shipping_cost += $ship['price'];
            }

            if($sale_data->shipping_on == "client")
            {
                $total = ($sale_data->amount + $shipping_cost) * -1;
            } else
                $total = $sale_data->amount * -1;


            // $add_History_sale = new OrderHistory();
            // $add_History_sale->order_id = $sale_data->id;
            // $add_History_sale->user_id = $auth_user;
            // $add_History_sale->action = "Returned";
            // $add_History_sale->note = '<strong>' . auth()->user()->name .  " </strong> Has Done return By : <strong>" . $company_name . "</strong> ";
            // $add_History_sale->created_at = now();
            // $add_History_sale->updated_at = now();
            // $add_History_sale->save();

        $sales[] =  $sale_data;

            $csvData[]=   $return_number . ','
                . $order_details['name']  . ','
                . $address  . ','
                . $order_details['address2']  . ','
                . $order_details['phone']  . ','
                . $order_details['phone']  . ','
                . $order_details['province']  . ','
                . $order_details['city']  . ','
                . '1' . ','
                . '1' . ','
                . '1' . ','
                . $sale_data->note . ','
                . ' ' . ','
                . ' ' . ','
                . $total .  ','
                .'1'.','
                ;


            $csvAccountingData[] = 
                $return_number . ','
                .$order_number . ','
                . $order_details['name'] . ','
                . $address . ','
                . $order_details['phone'] . ','
                . $order_details['province'] . ','
                . $order_details['city'] . ','
                . $shipping_cost . ','
                . $sale_data->shipping_on . ','
                . $total  . ','
                . $sale_data->reason . ','
                . $sale_data->note . ','
                . ',';

            $sale_data->status = "Returned";
            $sale_data->save();

        }
        $filename= 'return-pickup-' . date('Ymd').'-'.date('his'). ".xlsx";


        $file_path= public_path().'/download/'.$filename;

        $file = fopen($file_path, "w+");
        foreach ($csvData as $cellData){
            fputcsv($file, explode(',', $cellData));
        }
        fclose($file);

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');

        $objPHPExcel = $reader->load($file_path);
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $filenamexlsx= 'return-pickup-' . date('Ymd').'-'.date('his'). ".xlsx";
        $file_pathxlsx= public_path().'/download/'. $filenamexlsx;

        $objWriter->save($file_pathxlsx);

        //Acount
        $accountFileName= 'return-pickup-accounting' . date('Ymd').'-'.date('his'). ".xlsx";
        $account_file_path= public_path().'/download/'.$accountFileName;
        $accountFile = fopen($account_file_path, "w+");
        foreach ($csvAccountingData as $cellDataAccount){
            fputcsv($accountFile, explode(',', $cellDataAccount));
        }
        fclose($accountFile);

        $reader_account = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');

        $objPHPExcelAccount = $reader_account->load($account_file_path);
        $objWriterAccount = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcelAccount, 'Xlsx');

        $file_pathxlsxAccount= public_path().'/download/'. $accountFileName;

        $objWriterAccount->save($file_pathxlsxAccount);


        //  create a new collection instance from the array
        $size = count(collect($sale_id));
        $totalData = $size;
        $totalFiltered = $totalData;

        $create_pickup = new ReturnPickup();
        $create_pickup->pickup_id = date('Ymd'). date('his');
        $create_pickup->user_id = Auth::user()->id;
        $create_pickup->shipment_count = $totalData;
        $create_pickup->company_id = $shipping_company;
        $create_pickup->file_name = $filenamexlsx;
        $create_pickup->file_accounting_name = $accountFileName;
        $create_pickup->created_at = now();
        $create_pickup->updated_at = now();
        $create_pickup->save();


        $data = [ 'message' => 'Return Pickup Created  With total ' . $totalData . 'Shipments '];
        return response()->json($data);

    }

    public function update_payment_status(Request $request,$id=null)
    {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $headers = getShopifyHeadersForStore($store, 'PUT');
        if($id)
        {
            $pending = PendingOrder::find($id);
            
            if($pending)
            {
                $endpoint = getShopifyURLForStore('orders/'.$id.'.json', $store);
                $payload['order'] = [
                    'payment_gateway_names' =>'["Cash on Delivery (COD)"]',
                    'note' => "Payment Changed to Cash on Delivery (COD)"
                ];
                $response1 = $this->makeAnAPICallToShopify('PUT', $endpoint, null, $headers,$payload);
                if ($response1['statusCode'] === 201 || $response1['statusCode'] === 200) {
                    
                    if (isset($response1['body'])) {

                        $pending->payment_gateway_names = '["Cash on Delivery (COD)"]';
                        $pending->note = "Payment Changed to Cash on Delivery (COD)";
                        $pending->financial_status = "paid";
                        $pending->save();
                        $order = order2::where('id',$pending->id)->first();
                        if (!$order)
                            $order = new order2();
                        foreach($pending->toArray() as $key=>$value) {
                            if ($key == "table_id")
                                continue;
                            $order->$key = $value;
                        }
                        $order->save();
                        return redirect()->back()->with('success', 'Order Financial Status Changed Successfully');
                    }
                }
            }
            
        }
        else if(isset($request->status) && $request->status == "fawry")
        {
            $order = order2::find($request->order_id);
            $pending = PendingOrder::where('id',$request->order_id)->first();
            $endpoint = getShopifyURLForStore('orders/'.$request->order_id.'.json', $store);
            if($pending)
            {
                $payload['order'] = [
                    'financial_status' => "paid",
                    'payment_gateway_names' =>'["fawrypay (pay by card or at any fawry location)"]',
                    'note' => "transaction id : ".$request->trx
                ];
                $response1 = $this->makeAnAPICallToShopify('PUT', $endpoint, null, $headers,$payload);

                $payload = $this->markAsPaidMutation($request->order_id);
                $api_endpoint = 'graphql.json';
                

                $endpoint = getShopifyURLForStore($api_endpoint, $store);
                $headers = getShopifyHeadersForStore($store);
                
                $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
                if ($response1['statusCode'] === 201 || $response1['statusCode'] === 200 && $response['statusCode'] === 201 || $response['statusCode'] === 200) {
                    
                    if (isset($response['body']) && isset($response['body']['data'])) {

                        $pending->financial_status = "paid";
                        //$pending->payment_gateway_names[0] = "fawrypay (pay by card or at any fawry location)";
                        $pending->transaction_id = $request->trx;
                        $pending->save();
                        OneOrder::dispatchNow($user, $store, $request->order_id);
                        return redirect()->back()->with('success', 'Order Financial Status Changed Successfully');
                    }
                }
            }
        }
        return redirect()->back()->with('error', "Something Went Wrong!");
    }

    public function pickups(Request $request)
    {
        $shipping = null;
        $date = $request->date;
        $pickups = Pickup::orderBy('created_at', 'desc');
        $shipping_companies = ['BestExpress','Sprint'];

        if ($date != null) {
            $pickups = $pickups->whereDate('created_at', '=', $date);
        }
        if($request->shipping)
        {
            $shipping = $request->shipping;
            $pickups = $pickups->where('company_id', '=', $shipping);
        }
        $pickups = $pickups->simplePaginate(15)->appends($request->query());
        
        return view('pickups.index', compact('pickups','shipping_companies','shipping','date'));
    }

    public function return_pickups(Request $request)
    {
        $shipping = null;
        $date = $request->date;
        $pickups = ReturnPickup::orderBy('created_at', 'desc');
        $shipping_companies = ['BestExpress','Sprint'];

        if ($date != null) {
            $pickups = $pickups->whereDate('created_at', '=', $date);
        }
        if($request->shipping)
        {
            $shipping = $request->shipping;
            $pickups = $pickups->where('company_id', '=', $shipping);
        }
        $pickups = $pickups->simplePaginate(15)->appends($request->query());
        
        return view('pickups.index', compact('pickups','shipping_companies','shipping','date'));
    }

    public function generate_invoice($id)
    {
        $order = order2::find($id);
        if(!$order)
        $order = Sale::find($id);
        $refunds = Refund::where('order_name', $order->name)->pluck('line_item_id')->toArray();
        $order_details = PrepareProductList::where('order_id', $id)->where('table_id', $order->table_id)->whereNotIn('product_id',$refunds)->get();
        $auth_user = Auth::user()->id;
        $order_shipping_address = $order->shipping_address;
        $shipping_cost = 0;
        foreach ($order['shipping_lines'] as $ship) {
            $shipping_cost += $ship['price'];
        }
        if (is_array($order->payment_gateway_names) && isset($order->payment_gateway_names[0]) && ($order->payment_gateway_names[0] == "fawrypay (pay by card or at any fawry location)" || $order->payment_gateway_names[0] == "Paymob"))
            $total = 0;
        else
            $total = $order->total_price;

        return view('preparation.single_invoice_order', compact('total','order','shipping_cost', 'order_shipping_address','auth_user','order_details'));
    }

    public function generate_return_invoice($id)
    {
        $return = ReturnedOrder::findOrFail($id);
        $auth_user = Auth::user()->id;
        $order_shipping_address = $return->order->shipping_address;
        $shipping_cost = 0;
        foreach ($return->order['shipping_lines'] as $ship) {
            $shipping_cost += $ship['price'];
        }
        if($return->shipping_on == "client")
        {
            $total = ($return->amount + $shipping_cost) * -1;
        } else
            $total = $return->amount * -1;

        
        return view('preparation.return_single_invoice_order', compact('total','return','shipping_cost', 'order_shipping_address','auth_user'));
    }

    public function order_history($id)
    {
        $order = order2::findOrFail($id);

        $order_history = OrderHistory::where('order_id',$id)->get();
        $order_shipping_address = $order->shipping_address;

        return view('preparation.order_history', compact('order', 'order_history','order_shipping_address'));
    }

    public function update_delivery_status(Request $request)
    {
        if($request->status == 'cancelled') {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            $order = order2::findOrFail($request->order_id);
            $order->fulfillment_status = $request->status;
            $order->status = '14';


            $order->save();
            $get_prepare_order = Prepare::where('order_id',$order->id)->first();

            if($get_prepare_order){

                $get_prepare_order->delete();
                $get_prepare_order_details = PrepareProductList::where('order_id',$order->id)->get();
                if(count($get_prepare_order_details)){
                    foreach ($get_prepare_order_details as $detail) {
                        $detail_record = PrepareProductList::find($detail->id);
                        $detail_record->delete();
                    }
                }

            }
            $payload = [
                'reason' => 'OTHER',
                'staffNote' => $request->note
            ];
            $api_endpoint = 'orders/'.$order->id.'/cancel.json';
            $endpoint = getShopifyURLForStore($api_endpoint, $store);
            $headers = getShopifyHeadersForStore($store);
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);

            if($response['statusCode'] === 201 || $response['statusCode'] === 200)
            {
                Log::info('Response for Cancel Order');
                Log::info(json_encode($response));

                $add_History_sale = new OrderHistory();
                $add_History_sale->order_id = $order->id;
                $add_History_sale->user_id = auth()->user()->id;
                $add_History_sale->action = "Cancel";
                $add_History_sale->note = " Order Has Been Cancelled By : <strong>" . auth()->user()->name ."</strong>";
                $add_History_sale->created_at = now();
                $add_History_sale->updated_at = now();
                $add_History_sale->save();

                $cancel_order = new CancelledOrder();
                $cancel_order->order_id = $order->id;
                $cancel_order->user_id = auth()->user()->id;
                $cancel_order->reason = $request->reason;
                $cancel_order->note = $request->note;
                $cancel_order->created_at = now();
                $cancel_order->updated_at = now();
                $cancel_order->save();
                return redirect()->route('prepare.cancelled-orders')->with('success','Order Cancelled Successfully');
            }
            else{
                return redirect()->route('prepare.cancelled-orders')->with('error','Something went wrong');

            }
        }
    }

    public function fulfillOrderItems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_status.*' => 'required',]
            , [
                'product_status.*.required' => 'This field is required.',
            ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $request = $request->all();
        $posted_data = [];
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $order = $store->getOrders()->where('table_id', (int) $request['order_id'])->first();
        $prepare = $store->getPrepares()->where('table_id',(int) $request['order_id'])->first();
        if ($prepare->delivery_status == "hold")
            $old_page = "prepare.hold";
        else
            $old_page = "prepare.new";
        $prepare->delivery_status = "prepared";
        $order->fulfillment_status = "prepared";
        foreach($request['line_item_id'] as $key => $item)
        {
            $prepare_product = PrepareProductList::where('product_id', $item)->where('order_id', $order->id)->first();

            if($request['product_status'][$key] == 'prepared')
            {
                $prepare_product->product_status = 'prepared';
            }
            else{
                $prepare_product->product_status = $request['product_status'][$key];
                $prepare->delivery_status = "hold";
                $prepare->updated_at = now();
                $order->fulfillment_status = "hold";
            }
            $prepare_product->save();
        }
        $order->save();
        $prepare->save();

        if($prepare->delivery_status == "prepared")
        {
            $add_History_sale = new OrderHistory();
            $add_History_sale->order_id = $order->id;
            $add_History_sale->user_id = Auth::user()->id;
            $add_History_sale->action = "Prepared";
            $add_History_sale->note = '<strong>' . auth()->user()->name .  " </strong> Has Change Order To Prepared";
            $add_History_sale->created_at = now();
            $add_History_sale->updated_at = now();
            $add_History_sale->save();
        }
        else{
            $prepare_product = PrepareProductList::whereIn('product_id', $request['line_item_id'])->where('order_id', $order->id)->get();
            foreach($prepare_product as $prod){
                $add_History_sale = new OrderHistory();
                $add_History_sale->order_id = $order->id;
                $add_History_sale->user_id = Auth::user()->id;
                $add_History_sale->action = $prod->product_status;
                $add_History_sale->item = $prod->product_name;
                $add_History_sale->note = '<strong>' . auth()->user()->name .  " </strong> Has Change Order To <strong>".$prod->product_status."</strong>";
                $add_History_sale->created_at = now();
                $add_History_sale->updated_at = now();
                $add_History_sale->save();
            }
        }

        if(Auth::user()->role_id == 5)
        {
            return redirect()->route($old_page)->with('success', 'Order Has been Fulfilled');
        }
            

        return redirect()->route('prepare.all')->with('success', 'Order Has been Fulfilled');
    }

    public function resync_order(Request $request)
    {
        $id = str_replace('#','',$request->order_id);
        $id = '#' . $id;
        $old = order2::where('name',$id)->first();
        $order_id = null;
        $resync = null;
        if($old)
        {
            $order_id = $old->id;
            $prepare = Prepare::where('order_id', $old->id)->first();
            if($prepare)
            {

                $products = PrepareProductList::where('order_id',$old->id)->get();
                foreach($products as $product)
                {
                    $product->delete();
                }
            }
            $resync = new ResyncedOrder();
            $resync->order_id = $old->name;
            $resync->old_status = $old->fulfillment_status;
            $resync->reason = $request->reason;
            $resync->assign_to = $prepare?$prepare->assign_to:null;
            $resync->synced_by = Auth::user()->id;
            $resync->old_total = $old->total_price;
            $resync->created_at = now();
            $resync->updated_at = now();
            $resync->save();

            $old->delete();
            if($prepare)
            $prepare->delete();
            
        }
        $user = Auth::user();
        $store = $user->getShopifyStore;
        

        $user = Auth::user();
        $store = $user->getShopifyStore;
        OneOrder::dispatchNow($user, $store, $order_id);
        if($resync)
        {
            $new = order2::where('name',$id)->first();
            if (!$new)
                dd($resync, order2::where('name', $id)->first());
            $resync->new_total = $new->total_price;
            $resync->save();

            $add_History_sale = new OrderHistory();
            $add_History_sale->order_id = $new->id;
            $add_History_sale->user_id = Auth::user()->id;
            $add_History_sale->action = "Edited";
            $add_History_sale->created_at = now();
            $add_History_sale->updated_at = now();
            $add_History_sale->note = " Order Has Been Edited and Re-Synced By : <strong>" . auth()->user()->name ."</strong>";
            $add_History_sale->save();

        }

        
        return redirect()->route('prepare.resynced-orders')->with('success','Order Re-Synced Successfully');


    }

    public function resynced_orders(Request $request)
    {
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        
        $orders = ResyncedOrder::orderBy('created_at','desc');
        
        if($request->delivery_status)
        {
            $delivery_status = $request->delivery_status;
            $orders = $orders->where('old_status', $delivery_status);
        }
        if($date !=null)
        {
            $orders = $orders->whereDate('created_at', '=',$date);
        }

        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('order_id', 'like', '%' . $sort_search . '%')
                ->orWhere('reason', 'like', '%' . $sort_search . '%')
                ->orWhere('old_status', 'like', '%' . $sort_search . '%');
        }
        $orders = $orders->simplePaginate(15)->appends($request->query());
        return view('orders.resynced_orders', compact('orders','date','delivery_status','sort_search'));

    }

    public function fulfillOrder(FulfillOrder $request) {
        try {
            $sendAndAcceptresponse = null;
            $request = $request->all();
            $user = Auth::user();
            $store = $user->getShopifyStore;
            $order = $store->getOrders()->where('table_id', (int) $request['order_id'])->first();
            $fulfillment_order = $this->getFulfillmentLineItem($request, $order);

            if($fulfillment_order !== null) {
                $check = $this->checkIfCanBeFulfilledDirectly($fulfillment_order);
                if($check) {
                    $payload = $this->getFulfillmentV2PayloadForFulfillment($fulfillment_order->line_items, $request);
                    $api_endpoint = 'graphql.json';
                } else {
                    if($store->hasRegisteredForFulfillmentService())
                        $sendAndAcceptresponse = $this->sendAndAcceptFulfillmentRequests($store, $fulfillment_order);
                    $payload = $this->getPayloadForFulfillment($fulfillment_order->line_items, $request);
                    $api_endpoint = 'fulfillments.json';
                }

                $endpoint = getShopifyURLForStore($api_endpoint, $store);
                $headers = getShopifyHeadersForStore($store);
                $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);

                if($response['statusCode'] === 201 || $response['statusCode'] === 200)
                    OneOrder::dispatch($user, $store, $order->id);

                Log::info('Response for fulfillment');
                Log::info(json_encode($response));
                return response()->json(['response' => $response, 'sendAndAcceptresponse' => $sendAndAcceptresponse ?? null]);
            }
            return response()->json(['status' => false]);
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    private function sendAndAcceptFulfillmentRequests($store, $fulfillment_order) {
        try {
            $responses = [];
            $responses[] = $this->callFulfillmentRequestEndpoint($store, $fulfillment_order);
            $responses[] = $this->callAcceptRequestEndpoint($store, $fulfillment_order);
            return ['status' => true, 'message' => 'Done', 'responses' => $responses];
        } catch(Exception $e) {
            return ['status' => false, 'error' => $e->getMessage().' '.$e->getLine()];
        }
    }

    private function callFulfillmentRequestEndpoint($store, $fulfillment_order) {
        $endpoint = getShopifyURLForStore('fulfillment_orders/'.$fulfillment_order->id.'/fulfillment_request.json', $store);
        $headers = getShopifyHeadersForStore($store);
        $payload = [
            'fulfillment_request' => [
                'message' => 'Please fulfill ASAP'
            ]
        ];
        return $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
    }

    private function callAcceptRequestEndpoint($store, $fulfillment_order) {
        $endpoint = getShopifyURLForStore('fulfillment_orders/'.$fulfillment_order->id.'/fulfillment_request/accept.json', $store);
        $headers = getShopifyHeadersForStore($store);
        $payload = [
            'fulfillment_request' => [
                'message' => 'Accepted the request on '.date('F d, Y')
            ]
        ];
        return $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
    }

    public function products()
    {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $products = $store->getProducts()
                          ->select(['table_id', 'title', 'product_type', 'vendor', 'created_at', 'tags'])
                          ->orderBy('created_at', 'desc')
                          ->get();
        return view('products.index', ['products' => $products]);
    }
    public function product_variants(Request $request)
    {
        $user = Auth::user();
        $sort_search = null;
        $store = $user->getShopifyStore;
        $products = $store->getVariants()
                          ->select(['image_id','inventory_quantity', 'title', 'product_id', 'price', 'created_at', 'sku','barcode'])
                          ->orderBy('created_at', 'desc');
        if ($request->search) {
            $sort_search = $request->search;
            $products = $products->where('sku', 'like', '%' . $sort_search . '%')->orWhereHas('product', function ($q)use($sort_search) {
                return $q->where('title', 'like', '%' . $sort_search . '%');
            });
        }
        $products = $products->simplePaginate(15);
        return view('products.variants', ['products' => $products,'sort_search'=>$sort_search]);
    }
    

    public function syncProducts() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Product::dispatch($user, $store);
            return back()->with('success', 'Product sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function syncCustomers() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Customer::dispatch($user, $store);
            return back()->with('success', 'Customer sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    //Sync orders for Store using either GraphQL or REST API
    public function syncOrders() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            //Order::dispatch($user, $store, 'GraphQL'); //For using GraphQL API
            Order::dispatch($user, $store,"&fulfillment_status=unfulfilled"); //For using REST API
            return back()->with('success', 'Order sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function pending_payment_orders(Request $request) {
        $date = $request->date;
        $sort_search = null;
        $delivery_status = null;
        $payment_status = '';
        $prepare_users_list = [];
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $orders = $store->getPendings()->whereNull('transaction_id')->where('financial_status',"!=","paid");
        if ($request->search) {
            $sort_search = $request->search;
            $orders = $orders->where('name', 'like', '%' . $sort_search . '%')
                ->orWhere('id', 'like', '%' . $sort_search . '%')
                ->orWhere('fulfillment_status', 'like', '%' . $sort_search . '%');
        }
        if($date !=null)
        {
            $orders = $orders->whereDate('created_at_date', '=',$date);
        }

        if($request->delivery_status)
        {
            $delivery_status = $request->delivery_status;
            $orders = $orders->where('fulfillment_status', $delivery_status);
        }

        $orders = $orders->orderBy('table_id', 'asc')
                        ->simplePaginate(15)->appends($request->query());


        $prepare_users = User::where('role_id', '5')->get();
        if(count($prepare_users)) {
            foreach ($prepare_users as $key => $prepare) {

                $prepare_users_list['id'][$key] = $prepare->id;
                $prepare_users_list['name'][$key] = $prepare->name;
            }
        }
        return view('orders.pending', compact('orders','prepare_users_list','date','sort_search','delivery_status','payment_status'));
    }
    public function sync_pending_payment_orders(){
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            //Order::dispatch($user, $store, 'GraphQL'); //For using GraphQL API
            Order::dispatch($user, $store,"&financial_status=pending&fulfillment_status=unfulfilled",'pending_orders'); //For using REST API
            return back()->with('success', 'Order sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function acceptCharge(Request $request) {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            $charge_id = $request->charge_id;
            $user_id = $request->user_id;
            $endpoint = getShopifyURLForStore('application_charges/'.$charge_id.'.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if($response['statusCode'] === 200) {
                $body = $response['body']['application_charge'];
                if($body['status'] === 'active') {
                    return redirect()->route('members.create')->with('success', 'Sub user created!');
                }
            }
            User::where('id', $user_id)->delete();
            return redirect()->route('members.create')->with('error', 'Some problem occurred while processing the transaction. Please try again.');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function customers() {
        return view('customers.index');
    }

    public function list(Request $request) {
        try {
            if($request->ajax()) {
                $request = $request->all();
                $store = Auth::user()->getShopifyStore; //Take the auth user's shopify store
                $customers = $store->getCustomers(); //Load the relationship (Query builder)
                $customers = $customers->select(['name','email', 'phone_number', 'created_at']); //Select columns
                if(isset($request['search']) && isset($request['search']['value']))
                    $customers = $this->filterCustomers($customers, $request); //Filter customers based on the search term
                $count = $customers->count(); //Take the total count returned so far
                $limit = $request['length'];
                $offset = $request['start'];
                $customers = $customers->offset($offset)->limit($limit); //LIMIT and OFFSET logic for MySQL
                if(isset($request['order']) && isset($request['order'][0]))
                    $customers = $this->orderCustomers($customers, $request); //Order customers based on the column
                $data = [];
                $query = $customers->toSql(); //For debugging the SQL query generated so far
                $rows = $customers->get(); //Fetch from DB by using get() function
                if($rows !== null)
                    foreach ($rows as $key => $item)
                        $data[] = array_merge(
                                        ['#' => $key + 1], //To show the first column, NOTE: Do not show the table_id column to the viewer
                                        $item->toArray()
                                );
                return response()->json([
                    "draw" => intval(request()->query('draw')),
                    "recordsTotal"    => intval($count),
                    "recordsFiltered" => intval($count),
                    "data" => $data,
                    "debug" => [
                        "request" => $request,
                        "sqlQuery" => $query
                    ]
                ], 200);
            }

        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()], 500);
        }
    }

    //Returns a Query builders after setting the logic for ordering customers by specified column
    public function orderCustomers($customers, $request) {
        $column = $request['order'][0]['column'];
        $dir = $request['order'][0]['dir'];
        $db_column = null;
        switch($column) {
            case 0: $db_column = 'id'; break;
            case 1: $db_column = 'name'; break;
            case 2: $db_column = 'email'; break;
            case 3: $db_column = 'phone_number'; break;
            case 4: $db_column = 'created_at'; break;
            default: $db_column = 'id';
        }
        return $customers->orderBy($db_column, $dir);
    }

    //Returns a Query builder after setting the logic for filtering customers by the search term
    public function filterCustomers($customers, $request) {
        $term = $request['search']['value'];
        return $customers->where(function ($query) use ($term) {
            $query->where(
                        DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%".$term."%"
                    )
                  ->orWhere('email', 'LIKE', '%'.$term.'%')
                  ->orWhere('phone', 'LIKE', '%'.$term.'%');
        });
    }

    public function syncLocations() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Locations::dispatch($user, $store);
            return back()->with('success', 'Locations synced successfully');
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    public function syncOrder($id) {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $order = $store->getOrders()->where('table_id', $id)->select('id')->first();
        OneOrder::dispatchNow($user, $store, $order->id);
        return redirect()->route('shopify.order.show', $id)->with('success', 'Order synced!');
    }
    //PREPARATION
}
