@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
@endsection
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Orders</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Orders</li>
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
                                <div class="col-sm-2">
                                    <h6 class="d-inline-block pt-10px">Assign To Shipping Company</h6>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-select aiz-selectpicker" name="shipping_company"
                                        id="shipping_company">
                                        <option value="0">Choose Shipping Company</option>
                                        <option value="1">Best Express</option>
                                        <option value="2">Sprint</option>
                                    </select>
                                </div>
                                <div class="col-sm-2 text-right">
                                    <h6 class="d-inline-block pt-10px">Show Orders</h6>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-select aiz-selectpicker" name="paginate" id="paginate">
                                        <option value="0">Choose Number To SHow</option>
                                        <option value="15">15 Order</option>
                                        <option value="50">50 Order</option>
                                        <option value="100">100 Order</option>
                                        <option value="1000">All</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-sm-3">
                                    <h6 class="d-inline-block pt-10px">Total Orders Ready To Shipped</h6>
                                </div>
                                <div class="col-sm-4">
                                    <h6 class="d-inline-block pt-10px">{{ $orders_count }} Orders</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Reviewed Orders</h5>
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
                                        <th scope="col">Order No</th>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col" class="text-center">Payment Status</th>
                                        <th scope="col" class="text-center">Subtotal</th>
                                        <th scope="col" class="text-center">Shipping</th>
                                        <th scope="col" class="text-center">Total</th>
                                        <th scope="col">Customer Phone</th>
                                        <th scope="col" class="text-center">Delivery Status</th>

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
                                                    foreach ($order['shipping_lines'] as $ship) {
                                                        $total_shipping += $ship['price'];
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="form-group">
                                                            <div class="aiz-checkbox-inline">
                                                                <label class="aiz-checkbox">
                                                                    <input type="checkbox" class="check-one" name="id[]"
                                                                        value="{{ $order->id }}">
                                                                    <span class="aiz-square-check"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><a class="btn-link"
                                                            href="{{ route('shopify.order.prepare', $order->order_number) }}">spg{{ $order->order_number }}</a>
                                                    </td>
                                                    @php
                                                        $shipping_address = $order['shipping_address'];

                                                        if (!is_array($order['shipping_address'])) {
                                                            $shipping_address = json_decode($order['shipping_address']);
                                                            $shipping_address = $shipping_address
                                                                ? $shipping_address
                                                                : (is_array($order['customer'])
                                                                    ? $order['customer']
                                                                    : json_decode($order['customer']));
                                                        }

                                                    @endphp

                                                    <td>{{ isset($shipping_address['name']) ? $shipping_address['name'] : '' }}
                                                    </td>
                                                    <td class="text-center">{{ $order->getPaymentStatus() }}</td>
                                                    <td class="text-center">{{ $order->subtotal_price }}</td>
                                                    <td class="text-center">{{ $total_shipping }}</td>
                                                    <td class="text-center">{{ $order->total_price }}</td>
                                                    <td>{{ isset($shipping_address['phone']) ? $shipping_address['phone'] : '' }}
                                                    </td>
                                                    <td>
                                                        @if ($order->fulfillment_status == 'processing')
                                                            <span
                                                                class="badge badge-inline badge-danger">{{ $order->fulfillment_status }}</span>
                                                        @elseif ($order->fulfillment_status == 'distributed')
                                                            <span
                                                                class="badge badge-inline badge-info">{{ $order->fulfillment_status }}</span>
                                                        @elseif ($order->fulfillment_status == 'prepared')
                                                            <span
                                                                class="badge badge-inline badge-success">{{ $order->fulfillment_status }}</span>
                                                        @elseif ($order->fulfillment_status == 'shipped')
                                                            <span
                                                                class="badge badge-inline badge-primary">{{ $order->fulfillment_status }}</span>
                                                        @elseif ($order->fulfillment_status == 'hold')
                                                            <span
                                                                class="badge badge-inline badge-warning">{{ $order->fulfillment_status }}</span>
                                                        @elseif ($order->fulfillment_status == 'reviewed')
                                                            <span
                                                                class="badge badge-inline badge-secondary">{{ $order->fulfillment_status }}</span>
                                                        @elseif ($order->fulfillment_status == 'cancelled')
                                                            <span
                                                                class="badge badge-inline badge-danger">{{ $order->fulfillment_status }}</span>
                                                        @elseif ($order->fulfillment_status == 'fulfilled')
                                                            <span
                                                                class="badge badge-inline badge-dark">{{ $order->fulfillment_status }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ date('Y-m-d h:i:s', strtotime($order->created_at_date)) }}</td>
                                                    <td>
                                                        <div class="row">
                                                            @if ($order->fulfillment_status == 'prepared')
                                                                <div class="col-5">
                                                                    <div class="row">
                                                                        <a class="btn btn-primary"
                                                                            href="{{ route('prepare.review', $order->id) }}"
                                                                            title="Review Order">
                                                                            <i class="bi bi-check-lg"></i>
                                                                        </a>
                                                                        Review
                                                                    </div>
                                                                </div>
                                                                <div class="col-1"></div>
                                                            @elseif ($order->fulfillment_status == 'fulfilled')
                                                                <div class="col-5  mr-2 ml-2">
                                                                    <div class="row mb-1">
                                                                        <a class="btn btn-dark"
                                                                            href="{{ route('prepare.generate-invoice', $order->id) }}"
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
    </section>
@endsection

@section('scripts')
    <script type="text/javascript">
        $("ul#operation").siblings('a').attr('aria-expanded', 'true');
        $("ul#operation").addClass("show");
        $("#reviewed").addClass("active");
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
        $("#shipping_company").change(function() {
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
                        url: "{{ route('bulk-order-shipped') }}",
                        type: 'POST',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response == 0) {
                                window.location.href =
                                    '{{ route('pickups.index', ['msg' => 'success']) }}';
                            } else {
                                window.location.href =
                                    '{{ route('pickups.index', ['msg' => 'failed']) }}';
                            }
                        }
                    });
                } else {}
            }

            console.log(selected_name);
            console.log(selected_user);
            console.log(data);
        });
    </script>
@endsection
