@extends('layouts.app')
@section('content')

    <div class="pagetitle">
        <div class="row">

            <div class="col-8">
                <h1>Fawry Pending Payment Orders</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Pending Orders</li>
                    </ol>
                </nav>
            </div>
            <div class="col-4">
                <a href="{{ route('orders.sync_pending') }}" style="float: right" class="btn btn-primary">Sync Pending Payment
                    Orders</a>
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

                        <div class="card-body mt-1">

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
                                        <th scope="col" class="text-center">Shipping</th>
                                        <th scope="col" class="text-center">Total</th>
                                        <th scope="col">Customer Phone</th>
                                        <th scope="col">Created Date</th>
                                        <th scope="col" class="text-right">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($orders)
                                        @foreach ($orders as $key => $order)
                                            @if ($order)
                                                @php
                                                    $total_shipping = 0;
                                                    if (
                                                        isset($order['shipping_lines']) &&
                                                        $order['shipping_lines'] &&
                                                        is_array($order['shipping_lines'])
                                                    ) {
                                                        foreach ($order['shipping_lines'] as $ship) {
                                                            $total_shipping += $ship['price'];
                                                        }
                                                    }

                                                    $history = \App\Models\OrderHistory::where(
                                                        'order_id',
                                                        $order->id,
                                                    )->count();
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
                                                            href="{{ route('shopify.order.show', $order->table_id) }}">spg{{ $order->order_number }}</a>
                                                    </td>
                                                    @php
                                                        $shipping_address = $order['shipping_address'];

                                                    @endphp

                                                    <td>{{ isset($shipping_address['name']) ? $shipping_address['name'] : '' }}
                                                    </td>
                                                    <td class="text-center">{{ $order->getPaymentStatus() }}</td>
                                                    <td class="text-center">{{ $order->subtotal_price }}</td>
                                                    <td class="text-center">{{ $total_shipping }}</td>
                                                    <td class="text-center">{{ $order->total_price }}</td>

                                                    <td>{{ isset($shipping_address['phone']) ? $shipping_address['phone'] : '' }}
                                                    </td>
                                                    <td>{{ date('Y-m-d h:i:s', strtotime($order->created_at)) }}</td>

                                                    <td class="text-right">
                                                        <div class="row">
                                                            @if ($history > 0)
                                                                <div class="col-2">
                                                                    <a class="btn btn-warning"
                                                                        href="{{ route('prepare.order-history', $order->id) }}"
                                                                        title="Order History">
                                                                        <i class="bi bi-clock-history"></i>
                                                                    </a>
                                                                    <br>
                                                                    <span>History</span>
                                                                </div>
                                                            @endif
                                                            <div class ="col-2">

                                                                <a class="btn btn-danger"
                                                                    onclick="cancel_order({{ $order->id }})"
                                                                    title="Cancel Order">
                                                                    <i class="bi bi-x-square"></i>
                                                                </a>
                                                                <br>

                                                                <span>Cancel</span>

                                                            </div>
                                                            <div class ="col-2">
                                                                <a class="btn btn-primary"
                                                                    onclick="add_trx({{ $order->id }})"
                                                                    title="Add Transaction">
                                                                    <i class="bi bi-check"></i>
                                                                </a>
                                                                <br>

                                                                <span>Add Trx</span>


                                                            </div>
                                                            <div class="col-3">
                                                                <a class="btn btn-success"
                                                                    href="{{ route('orders.update_payment_status', $order->id) }}"
                                                                    title="Convert to Cash">
                                                                    <i class="bi bi-recycle"></i>
                                                                </a>
                                                                <br>
                                                                <span>Cash</span>
                                                            </div>

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
    <div class="modal fade" id="add-trx-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Transaction to Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body fulfillment_form">
                    <form action="{{ route('orders.update_payment_status') }}" class="row g-3" method="GET">
                        <input type="hidden" name="order_id" />
                        <input type="hidden" name="status" value="fawry" />

                        <div class="col-md-6">
                            <label for="note">Transaction Number*</label>
                            <input type="text" name="trx" class="form-control"
                                placeholder="Enter Transaction number and Hit Enter" required>
                        </div>
                        <div class="col-4 justify-content-center">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $("ul#operation").siblings('a').attr('aria-expanded', 'true');
        $("ul#operation").addClass("show");
        $("#pending").addClass("active");

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

        function add_trx(id) {
            $('input[name=order_id]').val(id);
            $('#add-trx-modal').modal('show');
        }
    </script>
@endsection
