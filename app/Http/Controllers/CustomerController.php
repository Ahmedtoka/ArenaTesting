<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Deposit;
use App\CustomerGroup;
use App\Models\Customer;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use App\Mail\UserNotification;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;

class CustomerController extends Controller
{
    use RequestTrait;
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('customers-index')){
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_customer_all = Customer::where('is_active', true)->get();
            return view('customer.index', compact('lims_customer_all', 'all_permission'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('customers-add')){
            $lims_customer_group_all = CustomerGroup::where('is_active',true)->get();
            return view('customer.create', compact('lims_customer_group_all'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'phone_number' => [
                'max:255',
            ],
        ]);
        $lims_customer_data = $request->all();
        $lims_customer_data['city'] = 'Cairo';
        $lims_customer_data['address'] = 'Cairo';

        $lims_customer_data['is_active'] = true;
        $message = 'Customer created successfully';
        if($lims_customer_data['email']){
            try{
                
            }
            catch(\Exception $e){
                $message = 'Customer created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }   
        }
        $customer = Customer::create($lims_customer_data);
        if($lims_customer_data['pos'])
            return redirect('pos')->with(['message' => $message, 'customer' => $customer->id]);
        else
            return redirect('customer')->with('create_message', $message);
    }
    public function getCustomerSale($phone)
    {

        $lims_customer_list = Customer::select('id', 'name', 'phone_number')->where('phone_number', $phone)->get();

        return $lims_customer_list;
    }

    public function storeCustomer(Request $request)
    {
        $this->validate($request, [
            'phone_number' => [
                'max:255',
            ],
        ]);

        $user = Auth::user();
        $store = $user->getShopifyStore;
        $headers = getShopifyHeadersForStore($store, 'GET');
        $phone = "2".$request->phone_number;
        $endpoint = getShopifyURLForStore('customers/search.json?fields=id&query=phone:'.$phone, $store);
        //dd($endpoint);
        $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
        $customer_id = null;
        if (($response['statusCode'] === 201 || $response['statusCode'] === 200) && (isset($response['body']['customers'][0]))) {
            
            $customer_id = $response['body']['customers'][0]['id'];
        }
        else{
            $headers = getShopifyHeadersForStore($store, 'POST');
            $endpoint = getShopifyURLForStore('customers.json', $store);
            $payload['customer'] = [
                "first_name" => $request->name,
                "last_name" => "",
                "phone" => "+2".$request->phone_number,
                "email" => "maadi".date('his')."@spicegirls.com",
                "addresses" => [
                    [
                        "address1" => "Maadi",
                        "city" => "Cairo",
                        "province" => "Cairo",
                        "country" => "Egypt",
                        'zip' => "123"
                    ]
                ],
            ];
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers,$payload);
            if ($response['statusCode'] === 201 || $response['statusCode'] === 200) {
                if(isset($response['body']['customer']))
                {
                    $customer_id = $response['body']['customer']['id'];
                }
            } 
            else if ($response['statusCode'] == 422) {
                    dd($response);
                // Get the error message from the response
                    $responseBody = $response['message'];
                    $start = strpos($responseBody, '{');
                    $end = strrpos($responseBody, '}') + 1;
                    $jsonString = substr($responseBody, $start, $end - $start);
                    $responseData = json_decode($jsonString, true);

                    // Extract the first error message without specifying the key
                    $errorMessages = reset($responseData['errors']);
                    $firstErrorMessage = reset($errorMessages);
                
                 // Output the error message
                 return response()->json(['errors'=> $firstErrorMessage]);
            }
            else
            return response()->json(['errors'=> 'Something Went Wrong']);
        }
        
        if($customer_id)
        {
            $new_customer = new Customer;
            $new_customer->name = $request->name;
            $new_customer->shopify_id = $customer_id;
            $new_customer->email = $request->email;
            $new_customer->city = $request->city;
            $new_customer->state = $request->state;
            $new_customer->country = $request->country;
            $new_customer->address = $request->address;
            $new_customer->phone_number = $request->phone_number;
            $new_customer->customer_group_id = 1;
            $new_customer->created_at = now();
            $new_customer->updated_at = now();
            $new_customer->save();


            return response()->json(['errors'=>'']);
        }
        return response()->json(['errors'=> 'Something Went Wrong']);

        

    }



    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('customers-edit')){
            $lims_customer_data = Customer::find($id);
            return view('customer.edit', compact('lims_customer_data'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'phone_number' => [
                'max:255',
                    Rule::unique('customers')->ignore($id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $input = $request->all();
        $input['city'] = 'Cairo';
        $input['address'] = 'Cairo';

        $lims_customer_data = Customer::find($id);
        $lims_customer_data->update($input);
        return redirect('customer')->with('edit_message', 'Data updated Successfully');
    }

    public function importCustomer(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('customers-add')){
            $upload=$request->file('file');
            $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
            if($ext != 'csv')
                return redirect()->back()->with('not_permitted', 'Please upload a CSV file');
            $filename =  $upload->getClientOriginalName();
            $filePath=$upload->getRealPath();
            //open and read
            $file=fopen($filePath, 'r');
            $header= fgetcsv($file);
            $escapedHeader=[];
            //validate
            foreach ($header as $key => $value) {
                $lheader=strtolower($value);
                $escapedItem=preg_replace('/[^a-z]/', '', $lheader);
                array_push($escapedHeader, $escapedItem);
            }
            //looping through othe columns
            while($columns=fgetcsv($file))
            {
                if($columns[0]=="")
                    continue;
                foreach ($columns as $key => $value) {
                    $value=preg_replace('/\D/','',$value);
                }
               $data= array_combine($escapedHeader, $columns);
               $lims_customer_group_data = CustomerGroup::where('name', $data['customergroup'])->first();
               $customer = Customer::firstOrNew(['name'=>$data['name']]);
               $customer->customer_group_id = $lims_customer_group_data->id;
               $customer->name = $data['name'];
               $customer->company_name = $data['companyname'];
               $customer->email = $data['email'];
               $customer->phone_number = $data['phonenumber'];
               $customer->address = $data['address'];
               $customer->city = $data['city'];
               $customer->state = $data['state'];
               $customer->postal_code = $data['postalcode'];
               $customer->country = $data['country'];
               $customer->is_active = true;
               $customer->save();
               $message = 'Customer Imported Successfully';
               if($data['email']){
                    try{
                        Mail::send( 'mail.customer_create', $data, function( $message ) use ($data)
                        {
                            $message->to( $data['email'] )->subject( 'New Customer' );
                        });
                    }
                    catch(\Exception $e){
                        $message = 'Customer imported successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
                    }
                }
            }
            return redirect('customer')->with('import_message', $message);
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }


    public function deleteBySelection(Request $request)
    {
        $customer_id = $request['customerIdArray'];
        foreach ($customer_id as $id) {
            $lims_customer_data = Customer::find($id);
            $lims_customer_data->is_active = false;
            $lims_customer_data->save();
        }
        return 'Customer deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_customer_data = Customer::find($id);
        $lims_customer_data->is_active = false;
        $lims_customer_data->save();
        return redirect('customer')->with('not_permitted','Data deleted Successfully');
    }
}
