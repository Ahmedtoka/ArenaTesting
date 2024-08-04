@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
    <style>
        .shadow {
            -moz-box-shadow: 3px 3px 5px 6px #ccc;
            -webkit-box-shadow: 3px 3px 5px 6px #ccc;
            box-shadow: 3px 3px 5px 6px #ccc;
            border-radius: 4%;
            /*supported by all latest Browser*/
            -moz-border-radius: 4%;
            /*For older Browser*/
            -webkit-border-radius: 4%;
            /*For older Browser*/

            width: 130px;
            height: 50px;
        }

        .shadow2 {
            border-radius: 4%;

            width: 130px;
            height: 115px;
        }
    </style>
@endsection
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Orders</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Assigned Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <form class="" action="" id="sort_orders" method="GET">
                        <div class="card-header row gutters-5">
                            <div class="row col-12">

                                <div class="col-2 justify-content-center">

                                    <div class="shadow justify-content-center text-center"
                                        style="background-color: rgb(239, 208, 99); color:white">
                                        New Orders
                                        <br>
                                        {{ $all_orders->where('fulfillment_status', 'processing')->count() }}
                                    </div>

                                </div>
                                <div class="col-2 justify-content-center">

                                    <div class="shadow justify-content-center text-center"
                                        style="background-color: rgb(33, 241, 68); color:white">
                                        Prepared Orders
                                        <br>
                                        {{ $all_orders->where('fulfillment_status', 'prepared')->count() }}
                                    </div>

                                </div>
                                <div class="col-2 justify-content-center">

                                    <div class="shadow justify-content-center text-center"
                                        style="background-color: rgb(231, 138, 66); color:white">
                                        Hold Orders
                                        <br>
                                        {{ $all_orders->where('fulfillment_status', 'hold')->count() }}
                                    </div>

                                </div>
                                <div class="col-2 justify-content-center">

                                    <div class="shadow justify-content-center text-center"
                                        style="background-color: rgb(15, 15, 14); color:white">
                                        Cancelled Orders
                                        <br>
                                        {{ $all_orders->where('fulfillment_status', 'cancelled')->count() }}
                                    </div>

                                </div>
                                <div class="col-2 justify-content-center">

                                    <div class="shadow justify-content-center text-center"
                                        style="background-color: rgb(5, 64, 85); color:white">
                                        Fulfilled Orders
                                        <br>
                                        {{ $all_orders->where('fulfillment_status', 'fulfilled')->count() }}
                                    </div>

                                </div>
                                <div class="col-2 justify-content-center">

                                    <div class="shadow justify-content-center text-center"
                                        style="background-color: rgb(3, 36, 22); color:white">
                                        Shipped Orders
                                        <br>
                                        {{ $all_orders->where('fulfillment_status', 'shipped')->count() }}
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="card-header row gutters-5">
                            <div class="row col-12">

                                <div class="col-2">
                                    <h6 class="d-inline-block pt-10px">{{ 'Choose Order Date' }}</h6>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group mb-0">
                                        <input type="date" class="form-control" value="{{ $date }}"
                                            name="date" placeholder="{{ 'Filter by date' }}" data-format="DD-MM-Y"
                                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-1"></div>
                                <div class="col-1 text-right">
                                    <h6 class="d-inline-block pt-10px text-right">{{ 'Search Order' }}</h6>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-0">
                                        <input type="text" class="form-control" id="search"
                                            name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                            placeholder="{{ 'Type Order code & hit Enter' }}">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary">{{ 'Filter' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-sm-3">
                                    <h6 class="d-inline-block pt-10px">{{ 'Filter Orders' }}</h6>
                                </div>
                                <div class="col-sm-3">
                                    <div class="">
                                        <select class="form-select aiz-selectpicker" name="delivery_status"
                                            id="delivery_status">
                                            <option value="">{{ 'Filter by Delivery Status' }}</option>
                                            <option value="processing" @if ($delivery_status == 'processing') selected @endif>
                                                {{ 'Processing' }} </option>
                                            <option value="distributed" @if ($delivery_status == 'distributed') selected @endif>
                                                {{ 'Distributed' }} </option>
                                            <option value="prepared" @if ($delivery_status == 'prepared') selected @endif>
                                                {{ 'Prepared' }}</option>
                                            <option value="hold" @if ($delivery_status == 'hold') selected @endif>
                                                {{ 'Hold' }}</option>
                                            <option value="reviewed" @if ($delivery_status == 'reviewed') selected @endif>
                                                {{ 'Reviewed' }}</option>
                                            <option value="shipped" @if ($delivery_status == 'shipped') selected @endif>
                                                {{ 'Shipped' }}</option>
                                            <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>
                                                {{ 'Cancel' }}</option>
                                            <option value="fulfilled" @if ($delivery_status == 'fulfilled') selected @endif>
                                                {{ 'Fulfilled' }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row col-12">
                                <div class="col-7">
                                    <h5 class="card-title">Assigned Orders</h5>
                                </div>
                                <div class="col-2">
                                    <h5 class="card-title">Re-Assign:</h5>
                                </div>
                                <div class="col-3 justify-content-center">
                                    <select class="form-select mt-2" name="prepare_emp" id="prepare_emp">
                                        <option value="0">{{ 'Choose Prepare Emp' }}</option>
                                        @if (isset($prepare_users_list['name']))
                                            @foreach ($prepare_users_list['name'] as $key => $user_prepare)
                                                <a class="dropdown-item" href="#"> {{ $user_prepare }}</a>
                                                <option value="{{ $prepare_users_list['id'][$key] }}">{{ $user_prepare }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            {{--  <!-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> -->  --}}

                            <!-- Table with stripped rows -->
                            <table class="table datatable" id="example">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-group">
                                                <div class="aiz-checkbox-inline">
                                                    <label class="aiz-checkbox">
                                                        <input type="checkbox" class="check-all">
                                                        <span class="aiz-square-check"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </th>
                                        <th scope="col">Order No.</th>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col" class="text-center">Payment Status</th>
                                        <th scope="col" class="text-center">Subtotal</th>
                                        <th scope="col">Shipping</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Customer Phone</th>
                                        <th scope="col">Delivery Status</th>
                                        <th scope="col" class="text-center">Assigned To</th>

                                        <th scope="col">Created Date</th>
                                        <th scope="col">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($orders)
                                        @foreach ($orders as $key => $order)
                                            @if ($order)
                                                @php
                                                    $total_shipping = 0;
                                                    foreach ($order->order['shipping_lines'] as $ship) {
                                                        $total_shipping += $ship['price'];
                                                    }
                                                    $returns = 0;
                                                    $return = \App\Models\ReturnedOrder::where(
                                                        'order_number',
                                                        $order->order->order_number,
                                                    )->first();
                                                    if ($return) {
                                                        $returns = \App\Models\ReturnDetail::where(
                                                            'return_id',
                                                            $return->id,
                                                        )->sum('amount');
                                                    }

                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="form-group">
                                                            <div class="aiz-checkbox-inline">
                                                                <label class="aiz-checkbox">
                                                                    <input type="checkbox" class="check-one" name="id[]"
                                                                        value="{{ $order->order_id }}">
                                                                    <span class="aiz-square-check"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><a class="btn-link"
                                                            href="{{ route('shopify.order.prepare', $order->order->order_number) }}">spg{{ $order->order->order_number }}</a>
                                                    </td>
                                                    @php
                                                        $shipping_address = $order->order['shipping_address'];

                                                    @endphp
                                                    <td>{{ isset($shipping_address['name']) ? $shipping_address['name'] : '' }}
                                                    </td>
                                                    <td class="text-center">{{ $order->order->getPaymentStatus() }}</td>
                                                    <td class="text-center">{{ $order->order->subtotal_price }}</td>
                                                    <td class="text-center">{{ $total_shipping }}</td>
                                                    <td class="text-center">{{ $order->order->total_price - $returns }}</td>
                                                    <td>{{ isset($shipping_address['phone']) ? $shipping_address['phone'] : '' }}
                                                    </td>
                                                    <td>
                                                        @if ($order->delivery_status == 'processing')
                                                            <span
                                                                class="badge badge-inline badge-danger">{{ $order->delivery_status }}</span>
                                                        @elseif ($order->delivery_status == 'distributed')
                                                            <span
                                                                class="badge badge-inline badge-info">{{ $order->delivery_status }}</span>
                                                        @elseif ($order->delivery_status == 'prepared')
                                                            <span
                                                                class="badge badge-inline badge-success">{{ $order->delivery_status }}</span>
                                                        @elseif ($order->delivery_status == 'shipped')
                                                            <span
                                                                class="badge badge-inline badge-primary">{{ $order->delivery_status }}</span>
                                                        @elseif ($order->delivery_status == 'hold')
                                                            <span
                                                                class="badge badge-inline badge-warning">{{ $order->delivery_status }}</span>
                                                        @elseif ($order->delivery_status == 'reviewed')
                                                            <span
                                                                class="badge badge-inline badge-secondary">{{ $order->delivery_status }}</span>
                                                        @elseif ($order->delivery_status == 'cancelled')
                                                            <span
                                                                class="badge badge-inline badge-danger">{{ $order->delivery_status }}</span>
                                                        @elseif ($order->delivery_status == 'fulfilled')
                                                            <span
                                                                class="badge badge-inline badge-dark">{{ $order->delivery_status }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $order->user->name }}</td>
                                                    <td>{{ date('Y-m-d h:i:s', strtotime($order->order->created_at)) }}</td>


                                                    <td class="text-right">
                                                        <div class="row">
                                                            <div class="col-3 mr-2 ml-2">
                                                                <div class="row  mb-1">
                                                                    <a class="btn btn-warning"
                                                                        href="{{ route('prepare.order-history', $order->order_id) }}"
                                                                        title="Order History">
                                                                        <i class="bi bi-clock-history"></i>
                                                                    </a>
                                                                    History
                                                                </div>
                                                            </div>

                                                            @if ($order->delivery_status != 'shipped')
                                                                <div class ="col-3  mr-2 ml-2">
                                                                    <div class="row mb-1">
                                                                        <a class="btn btn-danger"
                                                                            onclick="cancel_order({{ $order->order_id }})"
                                                                            title="Cancel Order">
                                                                            <i class="bi bi-x-square"></i>
                                                                        </a>
                                                                        Cancel
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if ($order->delivery_status == 'prepared' && auth()->user()->role_id != 6)
                                                                <div class="col-3  mr-2 ml-2">
                                                                    <div class="row mb-1">
                                                                        <a class="btn btn-primary"
                                                                            href="{{ route('prepare.review', $order->order_id) }}"
                                                                            title="Review Order">
                                                                            <i class="bi bi-check-lg"></i>
                                                                        </a>
                                                                        Review
                                                                    </div>
                                                                </div>
                                                            @elseif ($order->delivery_status == 'fulfilled')
                                                                <div class="col-3  mr-2 ml-2">
                                                                    <div class="row mb-1">
                                                                        <a class="btn btn-dark"
                                                                            href="{{ route('prepare.generate-invoice', $order->order_id) }}"
                                                                            title="Generate Invoice">
                                                                            <i class="bi bi-printer"></i>
                                                                        </a>
                                                                        Invoice
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endisset
                                </tbody>
                            </table>
                            <div class="text-center pb-2">
                                {{ $orders->links() }}
                            </div>
                            <!-- End Table with stripped rows -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="cancel-order-modal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body fulfillment_form">
                        <form action="{{ route('orders.update_delivery_status') }}" class="row g-3" method="POST">
                            @csrf
                            <input type="hidden" name="order_id" />
                            <input type="hidden" name="status" value="cancelled" />

                            <div class="col-md-6">
                                <label for="reason">Reason</label>
                                <select class="form-select" name="reason" data-minimum-results-for-search="Infinity">
                                    <option value="" disabled="">Select</option>
                                    <option value="CUSTOMER_REQUEST">Customer changed or canceled order</option>
                                    <option value="BROUGHT_FROM_STORE">Customer Brought From Store</option>
                                    <option value="ORDER_LATE">Order Late Recieve</option>
                                    <option value="WRONG_SHIPPING_INFO">Wrong Shipping Info</option>
                                    <option value="REPEATED_ORDER">Repeated Order</option>
                                    <option value="FAKE_ORDER">FAKE ORDER</option>
                                    <option value="ORDER_CONFIRMED_BY_MISTAKE">Client Confirmed Order By Mistake</option>
                                    <option value="INVENTORY">Items unavailable</option>
                                    <option value="ORDER_UPDATED_AFTER_SHIPPING">Client Updated the Order After Being
                                        Shipped
                                    </option>
                                    <option value="OTHER">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="note">Cancelling Note*</label>
                                <input type="text" name="note" class="form-control"
                                    placeholder="Enter Reason and Hit Enter" required>
                            </div>
                            <div class="col-4 justify-content-center">
                                <button type="submit" class="btn btn-danger">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script type="text/javascript">
        $("ul#operation").siblings('a').attr('aria-expanded', 'true');
        $("ul#operation").addClass("show");
        $("#prepares").addClass("active");
        $(document).on("change", ".check-all", function() {
            if (this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });
        $("#prepare_emp").change(function() {
            var data = new FormData($('#sort_orders')[0]);
            var selected_name = $(this).find("option:selected").text();
            var selected_user = this.value;
            data.append('emp_name', selected_name);

            if (selected_user == 0) {

            } else {
                if (confirm('Are You Sure to Assign These order to ' + selected_name)) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('bulk-order-assign') }}",
                        type: 'POST',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response == 0) {
                                location.reload();
                            } else {
                                location.reload();
                            }
                        }
                    });
                } else {}
            }

            console.log(selected_name);
            console.log(selected_user);
            console.log(data);
        });

        function cancel_order(id) {
            $('input[name=order_id]').val(id);
            $('#cancel-order-modal').modal('show');
        }
    </script>
@endsection
