@extends('layouts.top-head')
@section('content')
    @if ($errors->has('phone_number'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ $errors->first('phone_number') }}
        </div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif

    <section class="forms pos-section">
        <div class="container-fluid">
            <div class="row">
                <audio id="mysoundclip1" preload="auto">
                    <source src="{{ url('beep/beep-timber.mp3') }}">
                    </source>
                </audio>
                <audio id="mysoundclip2" preload="auto">
                    <source src="{{ url('beep/beep-07.mp3') }}">
                    </source>
                </audio>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body" style="padding-bottom: 0">
                            {!! Form::open(['route' => 'orders.store', 'method' => 'post', 'files' => true, 'class' => 'payment-form']) !!}
                            @csrf
                            @php
                                $keybord_active = 0;

                                $customer_active = DB::table('permissions')
                                    ->join(
                                        'role_has_permissions',
                                        'permissions.id',
                                        '=',
                                        'role_has_permissions.permission_id',
                                    )
                                    ->where([
                                        ['permissions.name', 'customers-add'],
                                        ['role_id', \Auth::user()->role_id],
                                    ])
                                    ->first();
                            @endphp
                            <input name="warehouse_id" value="{{ $warehouse_id }}" id="warehouse_id" type="hidden">


                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group m-2">
                                                            @if (session()->has('customer'))
                                                                @php
                                                                    $getCustomer = \App\Models\Customer::find(
                                                                        session()->get('customer'),
                                                                    );
                                                                @endphp
                                                                <input type="number" name="get_customer_sale"
                                                                    id="get_customer_sale"
                                                                    placeholder="Search for Customer by Phone"
                                                                    class="form-control"
                                                                    value="{{ $getCustomer->phone_number }}" autofocus
                                                                    required minlength="11" maxlength="15"
                                                                    autocomplete="off">
                                                            @else
                                                                <input type="number" name="get_customer_sale"
                                                                    id="get_customer_sale"
                                                                    placeholder="Search for Customer by Phone"
                                                                    class="form-control" autofocus required
                                                                    autocomplete="off" @endif
                                                                minlength="11"
                                                                maxlength="15">

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group" id="customer-section">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group m-2">
                                                            <select name="sale_by_employee_select" class="form-select">
                                                                <option value="">Choose Employee..</option>
                                                                @foreach ($employees as $key => $emp)
                                                                    <option value="{{ $emp->id }}">
                                                                        {{ $emp->employee_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group m-2">
                                                            <select name="paid_by_id_select" class="form-select">
                                                                <option value="1">Cash</option>
                                                                <option value="3">Credit Card</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row card-details"></div>

                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="search-box form-group">
                                                <input type="text" name="product_code_name" id="lims_productcodeSearch"
                                                    placeholder="Scan/Search product by name/code" class="form-control"
                                                    autofocus />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="table-responsive transaction-list">
                                            <table id="myTable"
                                                class="table table-hover table-striped order-list table-fixed">
                                                <thead>
                                                    <tr>
                                                        <th class="col-sm-4">{{ trans('product') }}</th>
                                                        <th class="col-sm-2">{{ trans('Price') }}</th>
                                                        <th class="col-sm-3">{{ trans('Quantity') }}</th>
                                                        <th class="col-sm-3">{{ trans('Subtotal') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row" style="display: none;">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_qty" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_discount" value="0.00" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_tax" value="0.00" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="total_price" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="item" />
                                                <input type="hidden" name="order_tax" />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" name="grand_total" />
                                                <input type="hidden" name="coupon_discount" />
                                                <input type="hidden" name="sale_status" value="1" />
                                                <input type="hidden" name="coupon_active">
                                                <input type="hidden" name="coupon_id">
                                                <input type="hidden" name="coupon_discount" />
                                                <input type="hidden" name="offer_discount" />
                                                <input type="hidden" name="pos" value="1" />
                                                <input type="hidden" name="draft" value="0" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 totals" style="border-top: 2px solid #e4e6fc; padding-top: 10px;">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <span class="totals-title">{{ trans('Items') }}</span><span
                                                    id="item">0</span>
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="totals-title">{{ trans('Total') }}</span><span
                                                    id="subtotal">0.00</span>
                                            </div>
                                            {{-- <div class="col-sm-4">
                                                <span class="totals-title">{{trans('Discount')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#order-discount"> <i class="fa fa-document-edit"></i></button></span><span id="discount">0.00</span>
                                            </div> --}}
                                            <div class="col-sm-4">
                                                <span class="totals-title">{{ trans('Coupon') }} <button type="button"
                                                        class="btn btn-link btn-sm" data-toggle="modal"
                                                        data-target="#coupon-modal"><i
                                                            class="fa fa-document-edit"></i></button></span><span
                                                    id="coupon-text">0.00</span>
                                            </div>
                                            {{-- <div class="col-sm-4">
                                                <span class="totals-title">{{trans('Tax')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#order-tax"><i class="fa fa-document-edit"></i></button></span><span id="tax">0.00</span>
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="totals-title">{{trans('Shipping')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#shipping-cost-modal"><i class="fa fa-document-edit"></i></button></span><span id="shipping-cost">0.00</span>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="payment-amount">
                            <h2>{{ trans('grand total') }} <span id="grand-total">0.00</span></h2>
                            <input type="number" name="paying_amount" class="form-control numkey" required
                                step="any" value="0">
                            <h4 class="mt-1">{{ trans('Change') }} <span id="change">0.00</span></h4>

                        </div>

                        <div class="payment-options">
                            <div class="column-5">
                                <input style="background: #00cec9" type="submit" class="btn btn-custom payment-btn"
                                    id="cash-btn" value="Create ( F1 )">
                            </div>
                            <div class="column-5">
                                <button style="background-color: #d63031;" type="button" class="btn btn-custom"
                                    id="cancel-btn" onclick="return confirmCancel()"><i class="fa fa-close"></i>
                                    Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- payment modal -->
                <div id="add-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">{{ trans('Finalize Sale') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="fa fa-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">

                                            <div class="col-md-6 mt-1" style="display: none;">
                                                <label>{{ trans('Paying Amount') }} *</label>
                                                <input type="hidden" name="paid_amount" class="form-control numkey"
                                                    step="any">
                                            </div>
                                            <div class="col-md-6 mt-1">

                                            </div>



                                            <!-- <div class="form-group col-md-12 mt-3">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="card-element form-control">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="card-errors" role="alert"></div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div> -->

                                            <div class="form-group col-md-12 cheque">
                                                <label>Premium Card Number *</label>
                                                <input type="text" name="cheque_no" class="form-control">
                                            </div>
                                            <div class="form-group col-md-12" style="display: none;">
                                                <label>{{ trans('Payment Note') }}</label>
                                                <textarea id="payment_note" rows="2" class="form-control" name="payment_note"></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 form-group" style="display: none;">
                                                <label>{{ trans('Sale Note') }}</label>
                                                <textarea rows="3" class="form-control" name="sale_note"></textarea>
                                            </div>
                                            <div class="col-md-6 form-group" style="display: none;">
                                                <label>{{ trans('Staff Note') }}</label>
                                                <textarea rows="3" class="form-control" name="staff_note"></textarea>
                                            </div>
                                        </div>
                                        <div class="mt-3" id="addCashAfterSubmit">
                                            <button id="submit-btn" type="submit"
                                                class="btn btn-primary">{{ trans('submit') }}</button>
                                        </div>
                                    </div>

                                    <div class="col-md-2 qc" data-initial="1">
                                        <h4><strong>{{ trans('Quick Cash') }}</strong></h4>
                                        <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="10"
                                            type="button">10</button>
                                        <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="20"
                                            type="button">20</button>
                                        <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="50"
                                            type="button">50</button>
                                        <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="100"
                                            type="button">100</button>
                                        <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="500"
                                            type="button">500</button>
                                        <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="1000"
                                            type="button">1000</button>
                                        <button class="btn btn-block btn-danger qc-btn sound-btn" data-amount="0"
                                            type="button">{{ trans('Clear') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- shipping_cost modal -->
                <div id="shipping-cost-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ trans('Shipping Cost') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="fa fa-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" name="shipping_cost" class="form-control numkey"
                                        step="any">
                                </div>
                                <button type="button" name="shipping_cost_btn" class="btn btn-primary"
                                    data-dismiss="modal">{{ trans('submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                {!! Form::close() !!}

                <!-- product list -->
                <div class="col-md-6">
                    <!-- navbar-->
                    <header class="header">
                        <nav class="navbar">
                            <div class="container-fluid">
                                <div class="navbar-holder d-flex align-items-center justify-content-between">

                                    <div class="navbar-header">

                                        <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
                                            <li class="nav-item"><a id="btnFullscreen" title="Full Screen"><i
                                                        class="fa fa-expand"></i></a></li>
                                            <li class="nav-item">
                                                <a href="{{ route('sales.all') }}" id="today-sale-btn" title="Sales"><i
                                                        class="fa fa-shopping-bag"></i></a>
                                            </li>

                                            <?php
                                            $warehouse_name = \App\Models\Warehouse::where('id', Auth::user()->warehouse_id)->first();
                                            ?>
                                            <li class="nav-item">
                                                <a rel="nofollow" data-target="#" href="#" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"
                                                    class="nav-link dropdown-item"><i class="fa fa-user"></i>
                                                    <span>{{ ucfirst(Auth::user()->name) }}</span> <i
                                                        class="fa fa-angle-down"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                        </nav>
                    </header>

                    <div class="row">
                        <div class="col-md-12">
                            <span class="pos-logo"><img src="https://branches.levoilescarfs.com/logo/fav.png"></span>
                            <span class="pos-logo"><img src="https://branches.levoilescarfs.com/logo/logo.png"></span>
                            <h1 class="pos-branch">{{ $warehouse_name->name }}</h1>
                        </div>
                        {{--  <div class="col-md-6">
                            <button style="background: #ca5601" type="button" class="btn btn-custom return-branches-btn"
                                data-toggle="modal" data-target="#add-branch-return-model" id="add-return-branches"><i
                                    class="fa fa-recycle"></i> Branches Return </button>
                        </div>
                        <div class="col-md-6">
                            <button style="background: #43930d" type="button" class="btn btn-custom return-btn"
                                data-toggle="modal" data-target="#add-return-model" id="add-return"><i
                                    class="fa fa-web"></i> Online Return </button>
                        </div>  --}}
                        <div class="col-md-12">
                            <a href="{{ route('register.close', $cashRegister->id) }}"
                                class="btn btn-custom close-day"><i class="fa fa-clock"></i>
                                {{ trans('Close Day') }}</a>
                        </div>
                    </div>
                </div>
                <!-- product edit modal -->
                <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 id="modal_header" class="modal-title"></h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="fa fa-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <div class="form-group">
                                        <label>{{ trans('Quantity') }}</label>
                                        <input type="text" name="edit_qty" class="form-control numkey">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('Unit Discount') }}</label>
                                        <input type="text" name="edit_discount" class="form-control numkey">
                                    </div>
                                    <div class="form-group" style="display: none!important;">
                                        <label>{{ trans('Unit Price') }}</label>
                                        <input type="text" name="edit_unit_price" class="form-control numkey"
                                            step="any">
                                    </div>
                                    <div id="edit_unit" class="form-group" style="display: none!important;">
                                        <label>{{ trans('Product Unit') }}</label>
                                        <select name="edit_unit" class="form-select selectpicker">
                                        </select>
                                    </div>
                                    <button type="button" name="update_btn"
                                        class="btn btn-primary">{{ trans('update') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- add customer modal -->
                <div id="addCustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true" class="modal fade text-left">
                    <div role="document" class="modal-dialog">
                        <div class="modal-content">
                            {!! Form::open(['method' => 'post', 'route' => 'customer.store', 'files' => true, 'class' => 'customer-form']) !!}
                            @csrf
                            <div class="modal-header">
                                <h5 id="exampleModalLabel" class="modal-title">{{ trans('Add Customer') }}</h5>
                                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                        aria-hidden="true"><i class="fa fa-cross"></i></span></button>
                            </div>
                            <div class="modal-body">
                                <p class="italic">
                                    <small>{{ trans('The field labels marked with * are required input fields') }}.</small>
                                </p>
                                <div class="form-group">
                                    <label>{{ trans('name') }} *</strong> </label>
                                    <input type="text" name="name" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label style="">{{ trans('Phone Number') }} *</label>
                                    <input type="number" name="phone_number" required class="form-control"
                                        id="phone_number_after" minlength="11" maxlength="15" readonly>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="pos" value="1">
                                    <input type="submit" value="{{ trans('submit') }}" class="btn btn-primary">
                                </div>

                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
@section('scripts')
    <script type="text/javascript">
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #sale-pos-menu").addClass("active");

        var valid;

        // array data depend on warehouse
        var lims_product_array = [];
        var products_available = [];
        var product_code = [];
        var product_name = [];
        var product_qty = [];
        var product_type = [];
        var product_id = [];
        var product_list = [];
        var qty_list = [];

        // array data with selection
        var product_price = [];
        var product_discount = [];
        var tax_rate = [];
        var tax_name = [];
        var tax_method = [];
        var unit_name = [];
        var unit_operator = [];
        var unit_operation_value = [];
        var gift_card_amount = [];
        var gift_card_expense = [];

        // temporary array
        var temp_unit_name = [];
        var temp_unit_operator = [];
        var temp_unit_operation_value = [];


        var deposit = [];
        var product_row_number = 1;
        var rowindex;
        var customer_group_rate;
        var row_product_price;
        var pos;
        var keyboard_active = <?php echo json_encode($keybord_active); ?>;
        var role_id = <?php echo json_encode(\Auth::user()->role_id); ?>;
        var warehouse_id = <?php echo json_encode($warehouse_id); ?>;
        var biller_id = <?php echo json_encode(\Auth::user()->biller_id); ?>;
        var coupon_list = [];
        var currency = "EGP";
        $("#warehouse_id").val(warehouse_id);
        var id = warehouse_id;


        $(window).keydown(function(event) {

            if (event.keyCode == 112) {
                console.log(warehouse_id);
                console.log("3333");
                var rownumber = $('table.order-list tbody tr:last').index();
                var customer_id = $('#get_customer_sale').val();
                temp_data = $('#lims_productcodeSearch').val();
                if (!customer_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                    alert('Please select Customer!');
                } else if (!warehouse_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
                    alert('Please select Warehouse!');
                }
                if (rownumber < 0) {
                    alert("Please insert product to order table!")
                    e.preventDefault();
                }
                $("#cash-btn").click();
                console.log("cash-btn! F1 event captured!");
                event.preventDefault();
            }
            if (event.keyCode == 13) {
                console.log("Hey! Enter captured!");
                event.preventDefault();
            }
            if (event.keyCode == 113) {
                console.log(warehouse_id);
                console.log("444");
                var rownumber = $('table.order-list tbody tr: last ').index();
                var customer_id = $(' #get_customer_sale ').val();
                var warehouse_id = $('#warehouse_id').val();
                temp_data = $('#lims_productcodeSearch').val();
                if (!customer_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length -
                        1));
                    alert('Please select Customer!');
                } else if (!warehouse_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length -
                        1));
                    alert('Please select Warehouse!');
                }
                if (rownumber < 0) {
                    alert("Please insert product to order table!");
                    e.preventDefault();
                }
                $("#credit-card-btn").click();
                console.log("Hey! f2 event captured!");
                event.preventDefault();
            }
            if (event.ctrlKey && event.keyCode == 83) {
                var rownumber = $('table.order-list tbody tr:last').index();
                if (rownumber < 0) {
                    alert("Please insert product to order table!");
                    e.preventDefault();
                } else {
                    $("#submit-btn").click();
                    console.log("submit-btn! Ctrl+S event captured!");
                    event.preventDefault();
                }
            }
        });

        $('select[name=customer_id]').val($("input[name='customer_id_hidden']").val());

        var id = $("#warehouse_id").val();
        console.log(warehouse_id);

        $.get('sales/getproduct/' + id, function(data) {
            lims_product_array = [];
            product_code = data[0];
            product_name = data[1];
            product_qty = data[2];
            product_type = data[3];
            product_id = data[4];
            product_list = data[5];
            qty_list = data[6];
            $.each(product_code, function(index) {
                lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')');

                products_available[product_code[index]] = product_qty[index];
            });
        });

        if (keyboard_active == 1) {
            $('#lims_productcodeSearch').bind('keyboardChange', function(e, keyboard,
                el) {
                var customer_id = $('#customer_id_ajax').val();
                temp_data = $('#lims_productcodeSearch').val();
                if (!customer_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length -
                        1));
                    alert('Please select Customer!');
                } else if (!warehouse_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length -
                        1));
                    alert('Please select Warehouse!');
                }
            });
        } else {
            $('#lims_productcodeSearch').on('input', function() {
                console.log(warehouse_id);
                console.log("dedede");
                var data = $('#lims_productcodeSearch').val() + " ";
                var customer_id = $('#customer_id_ajax').val();
                var warehouse_id = $('#warehouse_id').val();
                temp_data = $('#lims_productcodeSearch').val();
                if (!customer_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length -
                        1));
                    alert('Please select Customer!');
                } else if (!warehouse_id) {
                    $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length -
                        1));
                    alert('Please select Warehouse!');
                } else {
                    productSearch(data);
                }

            });
        }

        $('#get_customer_sale').on('focusout', function() {
            console.log("hi");
            var id = $(this).val();
            console.log(id);
            var section = $('#customer-section');
            var minLength = 11;
            var maxLength = 11;
            var options = '';
            section.html('');

            if (id.length < minLength) {
                options =
                    '<i class="fa fa-close" style="font-size: 23px; color: #fff;padding: 5px;background-color: red;margin: 3px 15px;"></i>' +
                    '<span > Phone Number Must Be 11 Digit </span> ';
                section.append(options);
            } else if (id.length > maxLength) {

                options =
                    '<i class="fa fa-close" style="font-size: 23px; color: #fff;padding: 5px;background-color: red;margin: 3px 15px;"></i>' +
                    '<span> Phone Number Must Be 11 Digit </span> ';
                section.append(options);
            } else {

                var url = 'get_customer_sale/' + id;
                $.ajax({
                    type: "GET",
                    url: url,
                    data: id,
                    cache: false,
                    success: function(data) {

                        if (data.length > 0) {
                            for (var i = 0; i < data
                                .length; i++) { // Loop through the data &construct the options 
                                options += '<span>Customer Name is : ' + data[i].name + '</span>';
                                options +=
                                    '<input type="hidden" id="customer_id_ajax" name="customer_id_ajax" value="' +
                                    data[i].id + '">';
                            }
                            $.get('sales/getproduct/' + warehouse_id, function(data) {
                                console.log("get products");
                                lims_product_array = [];
                                product_code = data[0];
                                product_name = data[1];
                                product_qty = data[2];
                                product_type = data[3];
                                $.each(product_code, function(index) {
                                    lims_product_array.push(product_code[
                                            index] + ' (' +
                                        product_name[index] + ')');
                                });
                            });
                        } else {
                            options +=
                                '<i class="fa fa-close" style="font-size: 23px; color: #fff;padding: 5px;background-color: red;margin: 3px 15px;"></i>' +
                                '<span > No Customer Found >> </span> ';
                            options +=
                                '<input type="hidden" id="customer_id_ajax" name="customer_id_ajax">';
                            options +=
                                ' <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#addCustomer" ><i class="fa fa-plus"></i></button>';
                            var customer_id = $('#get_customer_sale').val();
                            var customer_Phone = $('#phone_number_after');
                            customer_Phone.val(customer_id);
                        }
                        options += '</select>'; // Append to the html 
                        section.append(options);
                    }
                });
            }
        });

        $('body').on('click',
            function(e) {
                $('.filter-window').hide('slide', {
                    direction: 'right'
                }, 'fast');
            });

        function populateProduct(data) {
            var tableData =
                '<table id="product-table" class="table no-shadow product-list"> <thead class="d-none"> <tr> <th></th> <th></th> <th></th> <th></th> <th></th> </tr></thead> <tbody><tr>';
            if (Object.keys(data)
                .length != 0) {
                $.each(data['name'], function(index) {
                    var product_info = data['code'][index] + ' (' + data['name'][index] + ')';
                    if (index % 5 == 0 && index != 0) tableData +=
                        '</tr><tr><td class="product-img sound-btn" title="' + data['name'][index] +
                        '" data-product = "' + product_info + '"><img  src="' + data['image'][index] +
                        '" width="100%" /><p>' + data['name'][index] + '</p><span>' + data['code'][
                            index
                        ] + '</span></td>';
                    else tableData += '<td class="product-img sound-btn" title="' + data['name'][index] +
                        '" data-product = "' + product_info + '"><img  src="' + data['image'][index] +
                        '" width="100%" /><p>' + data['name'][index] + '</p><span>' + data['code'][
                            index
                        ] + '</span></td>';
                });
                if (data['name'].length % 5) {
                    var number = 5 - (data['name'].length % 5);
                    while (number > 0) {
                        tableData += '<td style="border:none;"></td>';
                        number--;
                    }
                }
                tableData += '</tr> < /tbody > </table>';
                $(".table-container")
                    .html(tableData);
                $('#product-table')
                    .DataTable({
                        "order": [],
                        'pageLength': product_row_number,
                        'language': {
                            'paginate': {
                                'previous': '<i class="fa fa-angle-left"></i>',
                                'next': '<i class="fa fa-angle-right"></i>'
                            }
                        },
                        dom: 'tp'
                    });
                $('table.product-list')
                    .hide();
                $('table.product-list')
                    .show(500);
            } else {
                tableData += '<td class="text-center">No data avaialable</td> < /tr > </tbody> < /table > '
                $(".table-container")
                    .html(tableData);
            }
        }

        $('select[name="customer_id"]').on('change', function() {
            var id = $(this).val();
            $.get('sales/getcustomergroup/' + id, function(data) {
                customer_group_rate = (data / 100);
            });
        });

        var lims_productcodeSearch = $('#lims_productcodeSearch');

        lims_productcodeSearch.autocomplete({

            source: function(request, response) {

                var matcher = new RegExp("^" +
                    $.ui.autocomplete.escapeRegex(request.term), "i");
                response($.grep(lims_product_array, function(item) {
                    return matcher.test(item);
                }));
            },
            response: function(event, ui) {
                if (ui.content.length == 1) {
                    var data = ui.content[0].value;
                    $(this).autocomplete("close");
                    productSearch(data);
                };
            },
            select: function(event, ui) {
                var data = ui.item.value;
                productSearch(data);
            }
        });

        $('#myTable').keyboard({
            accepted: function(event, keyboard, el) {
                checkQuantity(el.value, true);
            }
        });

        $("#myTable").on('click', '.plus', function() {
            rowindex = $(this).closest('tr').index();
            var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') input[name="qty[]"]').val(qty);
            console.log(qty, rowindex, $('table.order-list tbody tr:nth-child(' + (rowindex + 1) +
                ') input[name="qty[]"]').val());
            checkQuantity(String(qty), true);
        });

        $("#myTable").on('click', '.minus', function() {
            rowindex = $(this).closest('tr').index();
            var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) +
                ') input[name="qty[]"]').val()) - 1;
            if (qty > 0) {
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') input[name="qty[]"]').val(qty);
            } else {
                qty = 1;
            }
            checkQuantity(String(qty), true);
        });

        //Change quantity
        $("#myTable").on('input', '.qty', function() {
            console.log("hi");
            rowindex = $(this).closest('tr').index();
            if ($(this).val() < 1 && $(this).val() != '') {
                $('table.order-list tbody tr:nth-child(' + (rowindex +
                        1) +
                    ') .qty').val(1);
                alert("Quantity can't be less than 1");
            }
            checkQuantity($(this).val(), true);
        });
        $("#myTable").on('click', '.qty', function() {
            rowindex = $(this).closest('tr').index();
        });
        $(document).on('click', '.sound-btn', function() {
            var
                audio = $("#mysoundclip1")[0];
            audio.play();
        });
        $(document).on('click', '.product-img', function() {
            var
                customer_id = $('#get_customer_sale').val();
            var
                warehouse_id = $('select[name="warehouse_id" ]').val();
            if (!customer_id) alert('Please select Customer!');
            else if (!warehouse_id) alert('Please select Warehouse!');
            else {
                var data = $(this).data('product');
                data = data.split(" ");
                pos = product_code.indexOf(data[0]);
                if (pos < 0)
                    alert(
                        'Product is not avaialable in the selected warehouse'
                    );
                else {
                    productSearch(data[0]);
                }
            }
        });
        //Delete product
        $(" table.order-list tbody").on("click", ".ibtnDel", function(
            event) {
            var audio = $("#mysoundclip2")[0];
            audio.play();
            rowindex = $(this).closest('tr').index();
            product_price.splice(rowindex, 1);
            product_discount.splice(rowindex, 1);
            tax_rate.splice(rowindex, 1);
            tax_name.splice(rowindex, 1);
            tax_method.splice(rowindex, 1);
            unit_name.splice(rowindex,
                1);
            unit_operator.splice(rowindex, 1);
            unit_operation_value.splice(rowindex, 1);
            $(this).closest("tr").remove();
            calculateTotal();
        }); //Edit product 
        $("table.order-list").on("click", ".edit-product",
            function() {
                rowindex = $(this).closest('tr').index();
                edit();
            }); //Update product 
        $('button[name="update_btn"]').on("click", function() {
            var
                edit_discount = $('input[name="edit_discount" ]').val();
            var
                edit_qty = $('input[name="edit_qty" ]').val();
            var
                edit_unit_price = $('input[name="edit_unit_price" ]').val();
            if (parseFloat(edit_discount) > parseFloat(edit_unit_price)) {
                alert('Invalid Discount Input!');
                return;
            }

            if (edit_qty < 1) {
                $('input[name="edit_qty" ]').val(1);
                edit_qty = 1;
                alert("Quantity can't be less than 1");
            }
            var tax_rate_all = [];
            tax_rate[rowindex] =
                parseFloat(tax_rate_all[$('select[name="edit_tax_rate"] ').val()]);
            tax_name[rowindex] = $('select[name="edit_tax_rate" ] option: selected ').text();
            product_discount[rowindex] = $('input[name="edit_discount"] ').val();
            if (product_type[pos] == 'standard ') {
                var row_unit_operator = unit_operator[rowindex].slice(0,
                    unit_operator[rowindex].indexOf(","));
                var
                    row_unit_operation_value = unit_operation_value[
                        rowindex].slice(0,
                        unit_operation_value[rowindex].indexOf(",")
                    );
                if (row_unit_operator == '*') {
                    product_price[rowindex] = $('input[name="edit_unit_price"]').val() / row_unit_operation_value;
                } else {
                    product_price[rowindex] = $('input[name="edit_unit_price"]').val() * row_unit_operation_value;
                }
                var
                    position = $('select[name="edit_unit" ]').val();
                var
                    temp_operator = temp_unit_operator[
                        position];
                var
                    temp_operation_value =
                    temp_unit_operation_value[
                        position];
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                    '.sale-unit').val(temp_unit_name[position]);
                temp_unit_name.splice(position,
                    1);
                temp_unit_operator.splice(position, 1);
                temp_unit_operation_value.splice(position,
                    1);
                temp_unit_name.unshift($('select[name="edit_unit" ] option: selected ')
                    .text());
                temp_unit_operator.unshift(
                    temp_operator
                );
                temp_unit_operation_value
                    .unshift(
                        temp_operation_value
                    );
                unit_name[
                        rowindex] = temp_unit_name
                    .toString() +
                    ',';
                unit_operator[rowindex] =
                    temp_unit_operator.toString() +
                    ',';
                unit_operation_value[
                        rowindex] =
                    temp_unit_operation_value
                    .toString() +
                    ',';
            }
            checkQuantity(edit_qty, false);
        });
        $('button[name="shipping_cost_btn" ]').on(
            "click",
            function() {
                calculateGrandTotal();
            });
        $(".payment-btn").on("click", function(e) {
            var
                audio = $("#mysoundclip2")[0];
            audio.play();
            if (confirm(
                    "Are you sure want to Create?")) {
                $('input[name="paid_amount"]').val($("#grand-total").text());
                var total_qty = 0;
                $("table.order-list tbody .qty")
                    .each(function(
                        index) {
                        if ($(this).val() ==
                            '') {
                            alert('Please Fill Product Qty ');
                            e.preventDefault();
                        }
                    });
                if (parseFloat(
                        $(
                            'input[name="paying_amount" ]'
                        )
                        .val()
                    ) <
                    parseFloat(
                        $(
                            'input[name="paid_amount" ]'
                        )
                        .val()
                    )) {
                    alert('Paying amount cannot be bigger than recieved amount ');
                    e.preventDefault();
                } else if ($('input[name="paying_amount" ]')
                    .val() ==
                    0
                ) {
                    alert
                        (
                            'Please Type the Paying amount '
                        );
                    e.preventDefault();
                } else if (
                    $(
                        ' select[name="sale_by_employee_select" ]'
                    )
                    .val() ==
                    " "
                ) {
                    alert("please Select Employee ");
                    return false;
                    e.preventDefault();
                }
            } else {
                return false;
            }
        });
        $('input[name="paying_amount"]').on("focusout", function(e) {
            var paid_amount =
                parseFloat(
                    $(
                        '#grand-total'
                    )
                    .text()
                );
            var
                paying_amount =
                $(
                    this
                )
                .val();
            change(
                paying_amount,
                paid_amount
            );
            console.log(
                $(this).val()
            );
            console.log(
                paid_amount
            );
        });
        $('input[name="paid_amount"]').on("input", function(e) {
            console.log("Paid Hi");
            if ($(this)
                .val() >
                parseFloat(
                    $(
                        'input[name="paying_amount"]'
                    )
                    .val()
                )
            ) {
                e
                    .preventDefault();
                alert
                    ('Paying amount cannot be bigger than recieved amount ');
                $(
                        this
                    )
                    .val(
                        ''
                    );
            } else if (
                $(
                    this
                )
                .val() >
                parseFloat(
                    $(
                        '#grand-total'
                    )
                    .text()
                )
            ) {
                e
                    .preventDefault();
                alert
                    ('Paying amount cannot be bigger than grand total ');
                $(
                        this
                    )
                    .val(
                        ''
                    );
            }

            change
                ($(
                        'input[name="paying_amount"]'
                    )
                    .val(),
                    $(
                        this
                    )
                    .val()
                );
            var id =
                $(
                    'select[name="paid_by_id_select"]'
                )
                .val();
            if (id ==
                2
            ) {
                var balance =
                    gift_card_amount[
                        $(
                            "#gift_card_id_select"
                        )
                        .val()
                    ] -
                    gift_card_expense[
                        $(
                            "#gift_card_id_select"
                        )
                        .val()
                    ];
                if ($(
                        this
                    )
                    .val() >
                    balance
                )
                    alert(
                        'Amount exceeds card balance! Gift Card balance: ' +
                        balance
                    );
            } else if (
                id ==
                6
            ) {
                if ($(
                        'input[name="paid_amount"]'
                    )
                    .val() >
                    deposit[
                        $(
                            '#customer_id'
                        )
                        .val()
                    ]
                )
                    alert(
                        'Amount exceeds customer deposit! Customer deposit:' + deposit[$('#customer_id').val()]);
            }
        });

        function change(paying_amount, paid_amount) {
            $("#change").text(parseFloat(paying_amount - paid_amount).toFixed(2));
        }

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        function productSearch(data) {
            console.log("prod search", data, "hi");
            $.ajax({
                type: 'GET',
                url: 'sales/lims_product_search',
                data: {
                    data: data
                },
                success: function(data) {
                    if (!data)
                        alert("Product Not Found");
                    else {
                        var flag = 1;
                        $(".product-code").each(function(i) {
                            if ($(this).val() == data[1]) {
                                rowindex = i;
                                var pre_qty = $('table.order-list tbody tr:nth-child(' + (
                                    rowindex + 1) + ') .qty').val();
                                console.log("pre", pre_qty);
                                if (pre_qty) var qty = parseFloat(pre_qty) + 1;
                                else var qty = 1;
                                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) +
                                    ') .qty').val(qty);
                                flag = 0;
                                checkQuantity(String(qty), true);
                                flag = 0;
                            }
                        });
                        $("input[name='product_code_name']").val('');
                        if (flag) {
                            addNewProduct(data);
                        }
                    }
                }
            });
        }

        function addNewProduct(data) {
            var newRow = $("<tr>");
            var cols = '';
            temp_unit_name = (data[6]).split(',');
            cols += '<td class="col-sm-4 product-title"><strong>' + data[0] + '</strong> [' + data[1] + '] </td>';
            cols += '<td class="col-sm-2 product-price"></td>';
            cols +=
                '<td class="col-sm-3"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"><span class="fa fa-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" value="1" step="any" required><span class="input-group-btn"><button type="button" class="btn btn-default plus"><span class="fa fa-plus"></span></button></span></div></td>';
            cols += '<td class="col-sm-2 sub-total"></td>';
            cols +=
                '<td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="fa fa-cross"></i></button></td>';
            cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '" />';
            cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '" />';
            cols += '<input type="hidden" class="sale-unit" name="sale_unit[]" value="' + temp_unit_name[0] + '" />';
            cols += '<input type="hidden" class="net_unit_price" name="net_unit_price[]" />';
            cols += '<input type="hidden" class="discount-value" name="discount[]" />';
            cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] + '" />';
            cols += '<input type="hidden" class="tax-value" name="tax[]" />';
            cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
            newRow.append(cols);
            if (keyboard_active == 1) {
                $("table.order-list tbody").append(newRow).find('.qty').keyboard({
                    usePreview: false,
                    layout: 'custom',
                    display: {
                        'accept': '&#10004;',
                        'cancel': '&#10006;'
                    },
                    customLayout: {
                        'normal': ['1 2 3', '4 5 6', '7 8 9', '0 {dec} {bksp}', '{clear} {cancel} {accept}']
                    },
                    restrictInput: true,
                    preventPaste: true,
                    autoAccept: true,
                    css: {
                        container: 'center-block dropdown-menu',
                        buttonDefault: 'btn btn-default',
                        buttonHover: 'btn-primary',
                        buttonAction: 'active',
                        buttonDisabled: 'disabled'
                    },
                });
            } else {
                $("table.order-list tbody").append(newRow);
            }
            console.log(data[2]);
            product_price.push(parseFloat(data[2]));
            product_discount.push('0.00');
            tax_rate.push(parseFloat(data[3]));
            tax_name.push(data[4]);
            tax_method.push(data[5]);
            unit_name.push(data[6]);
            unit_operator.push(data[7]);
            unit_operation_value.push(data[8]);
            rowindex = newRow.index();
            checkQuantity(1, true);
        }


        function edit() {
            var row_product_name_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find(
                'td:nth-child(1)').text();
            $('#modal_header').text(row_product_name_code);
            var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
            $('input[name="edit_qty"]').val(qty);
            $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed(2));
            var tax_name_all = [];
            pos = tax_name_all.indexOf(tax_name[rowindex]);
            $('select[name="edit_tax_rate"]').val(pos);
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code')
                .val();
            pos = product_code.indexOf(row_product_code);
            if (product_type[pos] == 'standard') {
                unitConversion();
                temp_unit_name = (unit_name[rowindex]).split(',');
                temp_unit_name.pop();
                temp_unit_operator = (unit_operator[rowindex]).split(',');
                temp_unit_operator.pop();
                temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
                temp_unit_operation_value.pop();
                $('select[name="edit_unit"]').empty();
                $.each(temp_unit_name, function(key, value) {
                    $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
                });
                $("#edit_unit").show();
            } else {
                row_product_price = product_price[rowindex];
                $("#edit_unit").hide();
            }
            $('input[name="edit_unit_price"]').val(row_product_price.toFixed(2));
        }

        function checkQuantity(sale_qty, flag) {
            var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code')
                .val();
            console.log(product_code, lims_product_array);
            pos = product_code.indexOf(row_product_code);
            total_qty = sale_qty
            available = products_available[row_product_code];
            if (sale_qty > available)
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(available);
            else {
                if (!flag) {
                    $('#editModal').modal('hide');
                    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                }
                calculateRowProductData(sale_qty);
            }
        }

        function calculateRowProductData(quantity) {
            console.log("qty", quantity);
            row_product_price = product_price[rowindex];

            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[
                rowindex] * quantity).toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex]
                .toFixed(2));

            var sub_total_unit = row_product_price - product_discount[rowindex];
            var net_unit_price = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
            var tax = (sub_total_unit - net_unit_price) * quantity;
            var sub_total = sub_total_unit * quantity;

            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price
                .toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text(sub_total_unit
                .toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(4)').text(sub_total
                .toFixed(2));
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total
                .toFixed(2));
            calculateTotal();



        }

        function calculateTotal() {
            //Sum of quantity
            var total_qty = 0;
            var qty = [];
            $("table.order-list tbody .qty").each(function(index) {
                if ($(this).val() == '') {
                    total_qty += 0;
                    qty.push(0);
                } else {
                    total_qty += parseFloat($(this).val());
                    qty.push(parseFloat($(this).val()));
                }
            });
            $('input[name="total_qty"]').val(total_qty);

            //Sum of discount
            var total_discount = 0;
            $("table.order-list tbody .discount-value").each(function() {
                total_discount += parseFloat($(this).val());
            });

            $('input[name="total_discount"]').val(total_discount.toFixed(2));

            //Sum of tax
            var total_tax = 0;
            $(".tax-value").each(function() {
                total_tax += parseFloat($(this).val());
            });

            $('input[name="total_tax"]').val(total_tax.toFixed(2));

            //Sum of subtotal
            var total = 0;
            var subtotals = [];
            $(".sub-total").each(function() {
                total += parseFloat($(this).text());
                subtotals.push(parseFloat($(this).text()));
            });
            $('input[name="total_price"]').val(total.toFixed(2));



            var products = [];
            $('input[name="product_code[]"]').each(function() {

                products.push($(this).val());
            });


            //calc product prices 
            var prices = [];
            $(".product-price").each(function() {
                prices.push(parseFloat($(this).text()));
            });

            var offer = 0;

            calculateGrandTotal(offer);
        }


        function calculateGrandTotal(offer = 0) {
            var item = $('table.order-list tbody tr:last').index();
            var total_qty = parseFloat($('input[name="total_qty"]').val());
            var subtotal = parseFloat($('input[name="total_price"]').val());
            order_discount = 0.00;
            $("#discount").text(order_discount.toFixed(2));
            var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());
            if (!shipping_cost) shipping_cost = 0.00;
            item = ++item + '(' + total_qty + ')';
            order_tax = 0.0;
            var grand_total = ((subtotal + order_tax + shipping_cost) - order_discount) - offer;
            $('input[name="grand_total"]').val(grand_total.toFixed(2));
            coupon_discount = 0.00;
            grand_total -= coupon_discount;
            $('#item').text(item);
            $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
            $('#subtotal').text(subtotal.toFixed(2));
            $('#tax').text(order_tax.toFixed(2));
            $('input[name="order_tax"]').val(order_tax.toFixed(2));
            $('#shipping-cost').text(shipping_cost.toFixed(2));
            $('#grand-total').text(grand_total.toFixed(2));
            $('input[name="paying_amount"]').val($("#grand-total").text());
            $('input[name="grand_total"]').val(grand_total.toFixed(2));
        }

        function hide() {
            $(".card-element").hide();
            $(".card-errors").hide();
            $(".cheque").hide();
            $(".gift-card").hide();
            $('input[name="cheque_no"]').attr('required', false);
        }

        function cheque() {
            $(".cheque").show();
            $('input[name="cheque_no"]').attr('required', true);
            $(".card-element").hide();
            $(".card-errors").hide();
            $(".gift-card").hide();
        }

        function creditCard() {
            $.getScript("vendor/stripe/checkout.js");
            $(".card-element").show();
            $(".card-errors").show();
            $(".cheque").hide();
            $(".gift-card").hide();
            $('input[name="cheque_no"]').attr('required', false);
        }

        function deposits() {
            if ($('input[name="paid_amount"]').val() > deposit[$('#customer_id').val()]) {
                alert('Amount exceeds customer deposit! Customer deposit: ' + deposit[$('#customer_id').val()]);
            }
            $('input[name="cheque_no"]').attr('required', false);
            $('#add-payment select[name="gift_card_id_select"]').attr('required', false);
        }

        function confirmCancel() {
            var audio = $("#mysoundclip2")[0];
            audio.play();
            if (confirm("Are you sure want to cancel?")) {
                cancel($('table.order-list tbody tr:last').index());
                location.reload();
            }
            return false;
        }

        $(document).on('submit', '.customer-form', function(e) {
            e.preventDefault();
            let name = $('input[name="name"]').val();
            console.log("ssaq");
            let phone_number = $('input[name="phone_number"]').val();
            let customer_group_id = $('input[name="customer_group_id"]').val();
            let pos = $('input[name="pos"]').val();
            $.ajax({
                url: "/customer/storeCustomer",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    name: name,
                    phone_number: phone_number,
                    customer_group_id: customer_group_id,
                    pos: pos,
                },
                success: function(response) {
                    if (response.errors != "")
                        alert(response.errors);
                    $("#addCustomer").hide();
                    $(".modal-backdrop").hide();
                    $("#get_customer_sale").focus();
                    $("#lims_productcodeSearch").focus().delay(1000);
                },
            });
        });

        $('select[name="sale_by_employee_select"]').change(function() {
            $('input[name="sale_by_id"]').val($('select[name="sale_by_employee_select"]').val());
        });

        $('select[name="paid_by_id_select"]').change(function() {
            var id = $('select[name="paid_by_id_select"]').val();
            var details = $('.card-details');
            if (id == 3) {
                var cols = '';
                cols +=
                    '<div class="col-md-6"><input type="text" class="form-control" name="card_ref" required placeholder="Machine Reference" /></div>';
                cols +=
                    '<div class="col-md-6"><input type="number" class="form-control" name="card_last_digit" minlength="3" required placeholder="Last 4 Digit" /></div>';
                details.append(cols);
            } else {
                details.html(" ");
            }
        });
        $(document).on('submit', '.payment-form', function(e) {
            var rownumber = $('table.order-list tbody tr:last').index(),
                card_last = $('input[name="card_last_digit"]').val(),
                paid_by = $('select[name="paid_by_id_select"]').val();
            console.log(paid_by);
            if (rownumber < 0) {
                alert("Please insert product to order table! ");
                e.preventDefault();
            }
            $("table.order-list tbody.qty").each(function(index) {
                if ($(this).val() == '') {
                    e.preventDefault();
                }
            });
            if (parseFloat($('input[name="paying_amount"]').val()) < parseFloat($('input[name="paid_amount"]')
                    .val())) {
                e.preventDefault();
            } else if ($('input[name="paying_amount"]').val() == 0) {
                e.preventDefault();
            } else if ($('select[name="sale_by_employee_select"]').val() == " ") {
                return false;
                e.preventDefault();
            }
            if (paid_by == 3) {
                if (card_last.length < 4) {
                    alert("Please insert 4 digit ");
                    return false;
                    e.preventDefault();
                } else if (card_last.length > 4) {
                    alert(" Please insert 4 digit not more");
                    return false;
                    e.preventDefault();
                }
            }
            $('button[type="submit"]').unbind('click');
            $('button[type="submit"]').remove();
            $('input[type="submit"]').unbind('click');
            $('input[type="submit"]').remove();
            $('input[name="paid_by_id"]').val($('select[name="paid_by_id_select"]').val());
            $('input[name="order_tax_rate"]').val($('select[name="order_tax_rate_select"]').val());
        });

        $('#product-table').DataTable({
            "order": [],
            'pageLength': product_row_number,
            'language': {
                'paginate': {
                    'previous': '<i class="fa fa-angle-left"></i>',
                    'next': '<i class="fa fa-angle-right"></i>'
                }
            },
            dom: 'tp'
        });

        $(document).bind("contextmenu", function(e) {
            return false;
        });
        $(document).keydown(function(event) {
            if (event.keyCode == 123) {
                //Prevent F12
                return false;
            } else if (event.ctrlKey && event.shiftKey && event.keyCode == 73) {
                // Prevent Ctrl +Shift +I
                return false;
            }
        }); //prevent ctrl + s 

        $(document).bind('keydown', function(e) {
            if (e.ctrlKey && (e.which == 83)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endsection
