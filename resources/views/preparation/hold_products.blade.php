@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
    <style>
        .shadow2 {
            -moz-box-shadow: 3px 3px 5px 6px #ccc;
            -webkit-box-shadow: 3px 3px 5px 6px #ccc;
            box-shadow: 3px 3px 5px 6px #ccc;
            border-radius: 4%;
            /*supported by all latest Browser*/
            -moz-border-radius: 4%;
            /*For older Browser*/
            -webkit-border-radius: 4%;
            /*For older Browser*/

            width: 180px;
            height: 75px;
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
                        <li class="breadcrumb-item">Hold Products</li>
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
                                <div class="col-10 justify-content-center">

                                    <div class="shadow2 justify-content-center text-center"
                                        style="background-color: rgb(166, 166, 164); color:white">
                                        <strong>Hold Orders</strong>
                                        <br>
                                        {{ $orders->where('delivery_status', 'hold')->count() }}
                                    </div>

                                </div>
                                <div class="col-2 justify-content-center">

                                    <div class="shadow2 justify-content-center text-center"
                                        style="background-color: rgb(166, 166, 164); color:white">
                                        <strong>Hold Products</strong>
                                        <br>
                                        {{ $products->count() }}
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                @php
                                    $statuses = ['factory', 'shortage', 'branch', 'warehouse', 'wholesale'];
                                @endphp
                                <div class="col-1"></div>
                                @foreach ($statuses as $key => $status)
                                    <div class="col-2 justify-content-center">

                                        <div class="shadow2 justify-content-center text-center"
                                            style="background-color: rgb(166, 166, 164); color:white">
                                            <strong>{{ ucfirst($status) }} Products</strong>
                                            <br>
                                            {{ $products->where('product_status', $status)->count() }}
                                        </div>

                                    </div>
                                @endforeach
                                <div class="col-1"></div>

                            </div>
                        </div>
                        <div class="card-header row gutters-5">
                            <div class="row col-12">

                                <div class="col-sm-3">
                                    <div class="form-group mb-0">
                                        <label for="date">Filter By Order Date</label>
                                        <input type="date" class="form-control" value="{{ $date }}"
                                            name="date" placeholder="{{ 'Filter by order date' }}" data-format="DD-MM-Y"
                                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-0">
                                        <label for="date">Filter By Hold Date</label>
                                        <input type="date" class="form-control" value="{{ $hold_date }}"
                                            name="hold_date" placeholder="{{ 'Filter by hold date' }}" data-format="DD-MM-Y"
                                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="">
                                        <label for="date">Filter By Product Status</label>
                                        <select class="form-select aiz-selectpicker" name="delivery_status"
                                            id="delivery_status">
                                            <option value="">Select</option>
                                            @foreach ($statuses as $key => $status)
                                                <option value="{{ $status }}"
                                                    @if ($delivery_status == $status) selected @endif>
                                                    {{ ucfirst($status) }} </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group mb-0 mt-4">
                                        <button type="submit" class="btn btn-primary">{{ 'Filter' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form class="" action="{{ route('export-hold-products') }}" id="sort_orders" method="POST">
                        @csrf
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-10"></div>
                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" onclick="exporttt()"
                                            class="btn btn-warning">{{ 'Export Sheet' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Hold Products</h5>

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
                                        <th scope="col" class="text-center">Product Image</th>
                                        <th scope="col" class="text-center">Product Name</th>
                                        <th scope="col" class="text-center">Product Status</th>
                                        <th scope="col" class="text-center">Assigned To</th>
                                        <th scope="col">Created Date</th>
                                        <th scope="col">Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($products)
                                        @foreach ($products as $key => $product)
                                            @php

                                                //$end = Carbon\Carbon::createFromFormat('Y-m-d', $product->updated_at);

                                                $start = Carbon\Carbon::parse($product->created_at);
                                                $end = now();
                                                $options = [
                                                    'join' => ', ',
                                                    'parts' => 2,
                                                    'syntax' => Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                                ];

                                                $duration = $end->diffForHumans($start, $options);
                                                //
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <div class="aiz-checkbox-inline">
                                                            <label class="aiz-checkbox">
                                                                <input type="checkbox" class="check-one" name="id[]"
                                                                    value="{{ $product->id }}">
                                                                <span class="aiz-square-check"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><a class="btn-link"
                                                        href="{{ route('shopify.order.prepare', str_replace('#', '', $product->order->name)) }}">{{ $product->order->name }}</a>
                                                </td>
                                                <td class="text-center">
                                                    <img height="200" width="180"
                                                        src="{{ $product->variant_image ?? null }}" alt="Image here">
                                                </td>
                                                <td>
                                                    Name : {{ $product->product_name }}
                                                    <br>
                                                    Price : {{ $product->price }}
                                                    <br>
                                                    Qty : {{ $product->order_qty }}
                                                    <br>
                                                    Variant : {{ $product->variation_id }}
                                                    <br>
                                                    SKU : {{ $product->product_sku }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($product->product_status == 'warehouse')
                                                        <span
                                                            class="badge badge-inline badge-danger">{{ $product->product_status }}</span>
                                                    @elseif ($product->product_status == 'wholesale')
                                                        <span
                                                            class="badge badge-inline badge-info">{{ $product->product_status }}</span>
                                                    @elseif ($product->product_status == 'branch')
                                                        <span
                                                            class="badge badge-inline badge-warning">{{ $product->product_status }}</span>
                                                    @elseif ($product->product_status == 'factory')
                                                        <span
                                                            class="badge badge-inline badge-dark">{{ $product->product_status }}</span>
                                                    @elseif ($product->product_status == 'shortage')
                                                        <span
                                                            class="badge badge-inline badge-secondary">{{ $product->product_status }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $product->prepare->user->name }}</td>
                                                <td>{{ Carbon\Carbon::parse($product->created_at) }}</td>
                                                </td>

                                                <td>{{ $duration }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                            </table>
                            <div class="text-center pb-2">
                                {{ $products->links() }}
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
        $("#hold_products").addClass("active");
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
    </script>
@endsection
