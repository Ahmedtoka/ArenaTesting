@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
@endsection
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Cancelled Orders</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Reports</li>
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
                            <div class="row col-12 justify-content-center">
                                @php
                                    $reasons = [
                                        'CUSTOMER_REQUEST',
                                        'BROUGHT_FROM_STORE',
                                        'ORDER_LATE',
                                        'WRONG_SHIPPING_INFO',
                                        'REPEATED_ORDER',
                                        'FAKE_ORDER',
                                        'ORDER_CONFIRMED_BY_MISTAKE',
                                        'INVENTORY',
                                        'ORDER_UPDATED_AFTER_SHIPPING',
                                        'OTHER',
                                    ];
                                @endphp
                                <div class="col-2 justify-content-center m-2">

                                    <div class="shadow2 justify-content-center text-center"
                                        style="background-color: rgb(166, 166, 164); color:white">
                                        <strong>All Orders</strong>
                                        <br>
                                        {{ $orders->count() }}
                                    </div>

                                </div>
                                @foreach ($reasons as $key => $status)
                                    <div class="col-2 justify-content-center m-2">

                                        <div class="shadow2 justify-content-center text-center"
                                            style="background-color: rgb(166, 166, 164); color:white">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $status)) }}</strong>
                                            <br>
                                            {{ $orders->where('reason', $status)->count() }}
                                        </div>

                                    </div>
                                @endforeach
                                <div class="col-1"></div>

                            </div>
                        </div>
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-4 justify-content-center">

                                    <div class="container">
                                        <label for="daterange">Choose Date Range</label>
                                        <input type="text" value="{{ $daterange }}" id="daterange" name="daterange"
                                            class="form-control">
                                    </div>

                                </div>
                                <div class="col-sm-3 justify-content-center">
                                    <div class="form-group mb-0">
                                        <label for="date">Search Order</label>
                                        <input type="text" class="form-control" id="search"
                                            name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                            placeholder="{{ 'Type Order Name & Hit Enter' }}">
                                    </div>
                                </div>
                                <div class="col-sm-3 justify-content-center">
                                    <div class="">
                                        <label for="date">Filter By Cancel Reason</label>
                                        <select class="form-select aiz-selectpicker" name="reason" id="reason">
                                            <option value="" @if ($reason == '') selected @endif>Select
                                            </option>
                                            @foreach ($reasons as $key => $value)
                                                <option value="{{ $value }}"
                                                    @if ($reason == $value) selected @endif>
                                                    {{ ucfirst(str_replace('_', ' ', $value)) }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto justify-content-center">
                                    <div class="form-group mb-0 mt-4">
                                        <button type="submit" class="btn btn-primary">{{ 'Filter' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Cancelled Orders</h5>

                            <!-- Table with stripped rows -->
                            <table class="table datatable" id="example">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Order No.</th>
                                        <th scope="col" class="text-center">Reason</th>
                                        <th scope="col" class="text-center">Cancelled By</th>
                                        <th scope="col" class="text-center">Note</th>
                                        <th scope="col" class="text-center">Cancelled Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($orders)
                                        @foreach ($orders as $key => $order)
                                            <tr>
                                                <td class="text-center"><a class="btn-link"
                                                        href="{{ route('shopify.order.show', $order->order->table_id) }}">Spgs{{ $order->order->order_number }}</a>
                                                </td>
                                                <td class="text-center">{{ $order->reason }}</td>
                                                <td class="text-center">{{ $order->user->name }}</td>
                                                <td class="text-center">{{ $order->note }}</td>
                                                <td class="text-center">
                                                    {{ date('Y-m-d h:i:s', strtotime($order->created_at)) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                                <tfoot>

                                    <tr>
                                        <th class="text-center">{{ $orders_count }}</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>


                                </tfoot>
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
    <script type='text/javascript'>
        $("ul#reports").siblings('a').attr('aria-expanded', 'true');
        $("ul#reports").addClass("show");
        $("#cancelled").addClass("active");
        $(document).ready(function() {
            $('#daterange').daterangepicker({
                opens: 'left',
                locale: {
                    format: 'MM/DD/YYYY'
                }
            });
        });
    </script>
@endsection
