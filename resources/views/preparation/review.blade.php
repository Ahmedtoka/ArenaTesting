@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <div class="row">
            <div class="col-12">
                <div class="col-9">
                    <h1>Order {{ $order->name }}</h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('shopify.orders') }}">Orders</a></li>
                            <li class="breadcrumb-item">Order {{ $order->name ?? '' }}</li>
                        </ol>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-12">
                <div class="">
                    <div class="col-xxl-12 col-md-12">
                        <div class="card info-card sales-card">
                            <div class="card-body pb-0 mt-2">
                                <h5 class="card-title">Order Details</h5>
                                <table class="table table-borderless">
                                    <thead>
                                        <th>Payment Status</th>
                                        <th class="text-center">Fulfillment Status</th>
                                        <th style="float:right">Order Date</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $order->getPaymentStatus() }}</td>
                                            <td class="text-center">{{ $order->getFulfillmentStatus() }}</td>
                                            <td style="float: right;">
                                                {{ date('F d, Y', strtotime($order['created_at_date'])) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <form action="{{ route('prepare.review.post') }}" method="POST" id="prepare_orders_store">
                    @csrf
                    <div class="card-body">
                        <h5 class="card-title">Items</h5>
                        <input type="hidden" id="order_id" name="order_id" value="{{ $order['order_number'] }}">
                        @if ($prepare->products)
                            <div class="row">
                                @foreach ($prepare->products as $item)
                                    <div class="col-md-3 mb-2">
                                        <div>
                                            @isset($product_images)
                                                @isset($item->variant_image)
                                                    <a href="#" data-bs-toggle="modal"
                                                        data-bs-target="#imagesmodal-{{ $item['product_id'] }}">
                                                        <img height="300" width="auto" src="{{ $item->variant_image }}"
                                                            alt="Image here">
                                                    </a>
                                                @endisset
                                            @endisset
                                            <br>
                                        </div>
                                        <div>
                                            <strong>
                                                <br>
                                                {{ $item['product_name'] }}<br>
                                                <small>Qty : {{ $item['order_qty'] }}</small>
                                                <br>
                                                <small>Variation : {{ $item['variation_id'] }}</small>
                                                <br>
                                                <small>Price : {{ $item['price'] ?? '' }}</small><br>
                                                <small>Sku : {{ $item['product_sku'] ?? '' }}</small><br>
                                            </strong>
                                        </div>

                                        <div>
                                            @if (in_array($item['product_id'], $refunds))
                                                <span class="badge bg-dark">Removed</span>
                                                <input name = "product_status[]" type="hidden" value="prepared">
                                            @elseif ($item['product_status'] == 'prepared')
                                                <span class="badge bg-success">Prepared</span>
                                                <input name = "product_status[]" type="hidden" value="prepared">
                                            @else
                                                <span class="badge bg-danger">Prepared</span>
                                                <input name = "product_status[]" type="hidden" value="hold">
                                            @endif
                                            <input type="hidden" name="line_item_id[]" value="{{ $item['product_id'] }}">
                                            <input type="hidden" name="qty[]" value="{{ $item['order_qty'] }}">
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-md-12">
                                    <div class="form-group mt-4">
                                        <input type="submit" value="Submit" class="btn btn-primary">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
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

                    <div class="card-footer">
                        <table class="table table-hover table-xl mb-0 total-table">
                            <tbody>
                                @if (!empty($order->getDiscountBreakDown()))
                                    @foreach ($order->getDiscountBreakDown() as $title => $discount)
                                        <tr>
                                            <td class="text-truncate text-left">Discount Code</td>
                                            <td class="text-truncate text-left"><b>{{ $title ?? '' }}</b>
                                            </td>
                                            <td class="text-truncate text-right"><span style="float:right">-
                                                    {{ $order_currency . ' ' . number_format($discount, 2) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if (count($order->getDiscountBreakDown()) > 1)
                                        <tr>
                                            <td class="text-truncate text-left">Total Discount</td>
                                            <td class="text-truncate text-left"></td>
                                            <td class="text-truncate text-right"><span style="float:right">-
                                                    {{ $order_currency . ' ' . number_format($order->total_discounts, 2) }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                <tr>
                                    <td class="text-truncate text-left">Subtotal</td>
                                    <td class="text-truncate text-left">{{ count($order['line_items']) }}
                                        {{ count($order['line_items']) > 1 ? 'Items' : 'Item' }}</td>
                                    <td class="text-truncate text-right"><span style="float:right">{{ $order_currency }}
                                            {{ number_format($order['subtotal_price'], 2) }}</span></td>
                                </tr>
                                @if (!empty($order['shipping_lines']))
                                    @php $total_shipping = 0; @endphp
                                    @foreach ($order['shipping_lines'] as $ship)
                                        <tr>
                                            <td class="text-truncate text-left">Shipping</td>
                                            <td class="text-truncate text-left">
                                                {{ strlen($ship['title']) < 20 ? $ship['title'] : 'Standard Shipping' }}
                                            </td>
                                            <td class="text-truncate text-right"><span
                                                    style="float:right">{{ $order_currency . ' ' . number_format($ship['price'], 2) }}</span>
                                            </td>
                                        </tr>
                                        @php $total_shipping += $ship['price']; @endphp
                                    @endforeach
                                    @if (!empty($order['shipping_lines']) && count($order['shipping_lines']) > 0)
                                    @endif
                                @endif

                                <tr>
                                    <td class="text-truncate text-left text-bold">TOTAL AMOUNT</td>
                                    <td class="text-truncate text-left"></td>
                                    <td class="text-truncate text-right text-bold"><span
                                            style="float:right">{{ $order_currency . ' ' }}{{ number_format($order['total_price'], 2) }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </form>
            </div>
            @include('modals.fulfill_item')
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Shipping Address</h5>
                        <div class="alert alert-light" role="alert">

                            <p>
                                {{ isset($shipping_address['name']) ? $shipping_address['name'] : '' }} <br>
                                {{ isset($shipping_address['phone']) ? $shipping_address['phone'] : '' }} <br>
                                {{ isset($shipping_address['address1']) ? $shipping_address['address1'] : '' }} <br>
                                {{ isset($shipping_address['address2']) ? $shipping_address['address2'] : '' }} <br>
                                {{ isset($shipping_address['province']) ? $shipping_address['province'] : '' }}
                                {{ isset($shipping_address['city']) ? $shipping_address['city'] : '' }} <br>
                                {{ isset($shipping_address['country']) ? $shipping_address['country'] : '' }}
                                {{ isset($shipping_address['zip']) ? $shipping_address['zip'] : '' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Billing Address</h5>
                        <div class="alert alert-light" role="alert">
                            <p>
                                {{ $order['billing_address']['name'] ?? '' }} <br>
                                {{ $order['billing_address']['phone'] ?? '' }} <br>
                                {{ $order['billing_address']['address1'] ?? '' }} <br>
                                {{ $order['billing_address']['address2'] ?? '' }} <br>
                                {{ $order['billing_address']['province'] ?? '' }}
                                {{ $order['billing_address']['city'] ?? '' }}
                                <br>
                                {{ $order['billing_address']['country'] ?? '' }}
                                {{ $order['billing_address']['zip'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // $('.actions').change(function () {
            //     var val = $(this).val();
            //     if(val == 'fulfill_items') {
            //         $('.items_card').removeClass('col-lg-8').addClass('col-lg-12');
            //         $('.fulfill-th').css({'display':'block'});
            //         $('.fulfill-td').css({'display':'block'});
            //     }
            // });

            $('.fulfill_this_item').click(function() {
                var lineItemId = $(this).data('line_item_id');
                $('.fulfill_submit').css({
                    'display': 'block'
                });
                $('.fulfill_loading').css({
                    'display': 'none'
                });
                $('#lineItemId').val(parseInt(lineItemId));
                var qty = parseInt($(this).data('qty'));
                var select_html = '';
                for (var i = 1; i <= qty; i++) {
                    select_html += "<option value=" + i + ">" + i + "</option>";
                }
                $('#no_of_packages').html(select_html);
                $('.fulfillment_form').find('input:text').val('');
                $('.fulfillment_form').find('input:checkbox').prop('checked', false);
                $('#fulfill_items_modal').modal('show');
            });

            $('.fulfill_submit').click(function(e) {
                e.preventDefault();
                $(this).attr('disabled', true);
                $('.fulfill_submit').css({
                    'display': 'none'
                });
                $('.fulfill_loading').removeAttr('style');
                var data = {};
                $('.fulfillment_form').find('[id]').each(function(i, v) {
                    var input = $(this); // resolves to current input element.
                    data[input.attr('id')] = input.val();
                });
                data['order_id'] = $('#order_id').val();
                data['lineItemId'] = $('#lineItemId').val();
                data['notify_customer'] = $('#notify_customer').prop('checked') ? 'on' : 'off';
                $.ajax({
                    type: 'POST',
                    url: "{{ route('shopify.order.fulfill') }}",
                    data: data,
                    async: false,
                    success: function(response) {
                        console.log(response);
                        //window.top.location.reload();
                    }
                });
            });
        });
        $('form#prepare_orders_store').submit(function() {
            $(this).find(':input[type=submit]').prop('disabled', true);
        });
    </script>
@endsection
