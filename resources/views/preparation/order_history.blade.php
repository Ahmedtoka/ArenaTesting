@extends('layouts.app')
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
                    <div class="card-header">
                        <h1 class="h2 fs-16 mb-0">Order Details</h1>
                    </div>
                    <div class="card-body">
                        <div class="row gutters-5">
                            @php
                                $delivery_status = $order->fulfillment_status;
                                $payment_status = $order->payment_status;
                                $payment_type = $order->payment_status;
                                $admin_user_id = App\Models\User::where('role_id', 1)->first()->id;
                            @endphp

                        </div>
                        <div class="mb-4 m-2">

                        </div>
                        <div class="row gutters-5">
                            <div class="col text-md-left text-center">
                                @if ($order->shipping_address)
                                    <address>
                                        <strong class="text-main">
                                            {{ $order->shipping_address['name'] }}
                                        </strong><br>
                                        {{ $order->email }}<br>
                                        {{ $order->shipping_address['phone'] }}<br>
                                        {{ $order->shipping_address['address1'] }}, {{ $order->shipping_address['city'] }},

                                        {{ $order->shipping_address['country'] }}
                                    </address>
                                @else
                                    <address>
                                        <strong class="text-main">
                                            {{ $order->name }}
                                        </strong><br>
                                        {{ $order->email }}<br>
                                        {{ $order->phone }}<br>
                                    </address>
                                @endif
                            </div>
                            <div class="col-md-4 ml-auto">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="text-main text-bold">Order #</td>
                                            <td class="text-info text-bold text-right"> {{ $order->id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-main text-bold">Order Status</td>
                                            <td class="text-right">
                                                @if ($delivery_status == 'delivered')
                                                    <span class="badge badge-inline badge-success">
                                                        {{ ucfirst(str_replace('_', ' ', $delivery_status)) }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-inline badge-info">
                                                        {{ ucfirst(str_replace('_', ' ', $delivery_status)) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-main text-bold">Order Date </td>
                                            <td class="text-right">{{ $order->created_at_date }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-main text-bold">
                                                Total amount
                                            </td>
                                            <td class="text-right">
                                                {{ $order->total_price }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-main text-bold">Payment method</td>
                                            <td class="text-right">
                                                {{ $order->payment_status }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <hr class="new-section-sm bord-no">
                        <div class="row">
                            <div class="col-lg-12 table-responsive">
                                <table class="table-bordered aiz-table invoice-summary table">
                                    <thead>
                                        <tr class="bg-trans-dark">
                                            <th>#</th>
                                            <th>Created</th>
                                            <th>Order Number</th>
                                            <th>Action</th>
                                            <th>Action By</th>
                                            <th data-breakpoints="lg" class="min-col">Item</th>
                                            <th data-breakpoints="lg" class="min-col">Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order_history as $key => $history)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $history->created_at }}</td>
                                                <td>{{ $order->id }}</td>
                                                <td>{{ $history->action }}</td>
                                                <td>{!! $history->note !!}</td>
                                                <td>{{ $history->item }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
