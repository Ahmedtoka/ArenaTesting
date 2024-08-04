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
                        <li class="breadcrumb-item">New Assigned Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Orders</h5>

                        <!-- Table with stripped rows -->
                        <table class="table datatable" id="example">
                            <thead>
                                <tr>
                                    <th scope="col">Order No.</th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col" class="text-center">Payment Status</th>
                                    <th scope="col" class="text-center">Delivery Status</th>
                                    <th scope="col" class="text-center">Assigned To</th>
                                    <th scope="col" class="text-center">Subtotal</th>
                                    <th scope="col" class="text-center">Shipping</th>
                                    <th scope="col" class="text-center">Total</th>
                                    <th scope="col">Customer Phone</th>
                                    <th scope="col">Created Date</th>
                                    <th scope="col">Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($orders)
                                    @foreach ($orders as $key => $order)
                                        @php
                                            $total_shipping = 0;
                                            foreach ($order->order['shipping_lines'] as $ship) {
                                                $total_shipping += $ship['price'];
                                            }
                                        @endphp
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

                                        <tr>
                                            <td><a class="btn-link"
                                                    href="{{ route('shopify.order.prepare', str_replace('#', '', $order->order->name)) }}">{{ $order->order->name }}</a>
                                            </td>
                                            <td>{{ isset($shipping_address['name']) ? $shipping_address['name'] : '' }}</td>
                                            <td class="text-center">{{ $order->order->getPaymentStatus() }}</td>
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
                                            <td class="text-center">{{ $order->user->name }}</td>
                                            <td class="text-center">{{ $order->order->subtotal_price }}</td>
                                            <td class="text-center">{{ $total_shipping }}</td>
                                            <td class="text-center">{{ $order->order->total_price }}</td>
                                            <td>{{ isset($shipping_address['name']) ? $shipping_address['name'] : '' }}</td>
                                            <td>{{ date('Y-m-d h:i:s', strtotime($order->created_at)) }}</td>
                                            <td></td>
                                        </tr>
                                    @endforeach
                                @endisset
                            </tbody>
                        </table>
                        <div class="text-center pb-2">
                            {{ $orders->links() }}
                        </div>
                        <!-- End Table with stripped rows -->
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script type="text/javascript">
        $("ul#preparation").siblings('a').attr('aria-expanded', 'true');
        $("ul#preparation").addClass("show");
        $("#new").addClass("active");
    </script>
@endsection
