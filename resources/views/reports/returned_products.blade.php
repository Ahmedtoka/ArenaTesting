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
                <h1>Returned Products</h1>
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
                                        'UNKNOWN',
                                        'SIZE_TOO_SMALL',
                                        'SIZE_TOO_LARGE',
                                        'UNWANTED',
                                        'NOT_AS_DESCRIBED',
                                        'WRONG_ITEM',
                                        'DEFECTIVE',
                                        'COLOR',
                                        'OTHER',
                                    ];
                                @endphp
                                <div class="col-2 justify-content-center m-2">

                                    <div class="shadow2 justify-content-center text-center"
                                        style="background-color: rgb(166, 166, 164); color:white">
                                        <strong>All Returned Products</strong>
                                        <br>
                                        {{ $data->count() }}
                                    </div>

                                </div>
                                @foreach ($reasons as $key => $status)
                                    <div class="col-2 justify-content-center m-2">

                                        <div class="shadow2 justify-content-center text-center"
                                            style="background-color: rgb(166, 166, 164); color:white">
                                            <strong>{{ ucfirst(str_replace('_', ' ', $status)) }}</strong>
                                            <br>
                                            {{ $data->where('reason', $status)->count() }}
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
                                        <label for="date">Filter By Daterange</label>
                                        <input type="text" value="{{ $daterange }}" id="daterange" name="daterange"
                                            class="form-control">
                                    </div>

                                </div>
                                <div class="col-sm-3 justify-content-center">
                                    <div class="form-group mb-0">
                                        <label for="date">Search Product</label>
                                        <input type="text" class="form-control" id="search"
                                            name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                            placeholder="{{ 'Type Product Name & Hit Enter' }}">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="">
                                        <label for="date">Filter By Return Reason</label>
                                        <select class="form-select aiz-selectpicker" name="reason" id="reason">
                                            <option value="" @if ($reason == '') selected @endif>Select
                                            </option>
                                            <option value="UNKNOWN" @if ($reason == 'UNKNOWN') selected @endif>
                                                Unknown</option>
                                            <option value="SIZE_TOO_SMALL"
                                                @if ($reason == 'SIZE_TOO_SMALL') selected @endif>Size
                                                was too small</option>
                                            <option value="SIZE_TOO_LARGE"
                                                @if ($reason == 'SIZE_TOO_LARGE') selected @endif>Size was too large
                                            </option>
                                            <option value="UNWANTED" @if ($reason == 'UNWANTED') selected @endif>
                                                Customer changed their mind</option>
                                            <option value="NOT_AS_DESCRIBED"
                                                @if ($reason == 'NOT_AS_DESCRIBED') selected @endif>Item not as described
                                            </option>
                                            <option value="WRONG_ITEM" @if ($reason == 'WRONG_ITEM') selected @endif>
                                                Received the wrong item</option>
                                            <option value="DEFECTIVE" @if ($reason == 'DEFECTIVE') selected @endif>
                                                Damaged or defective</option>
                                            <option value="Color" @if ($reason == 'Color') selected @endif>Color
                                            </option>
                                            <option value="OTHER" @if ($reason == 'OTHER') selected @endif>Other
                                            </option>

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

                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" onclick="exporttt()"
                                            class="btn btn-warning">{{ 'Export Sheet' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Returned Products</h5>

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
                                        <th scope="col">Return No.</th>
                                        <th scope="col" class="text-center">Product Image</th>
                                        <th scope="col" class="text-center">Product Name</th>
                                        <th scope="col" class="text-center">Return Reason</th>
                                        <th scope="col" class="text-center">Order Quantity</th>
                                        <th scope="col" class="text-center">Returned Quantity</th>
                                        <th scope="col" class="text-center">Amount</th>
                                        <th scope="col" class="text-center">Created Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($data)
                                        @foreach ($data as $key => $product)
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <div class="aiz-checkbox-inline">
                                                            <label class="aiz-checkbox">
                                                                <input type="checkbox" class="check-one" name="id[]"
                                                                    value="{{ $product['id'] }}">
                                                                <span class="aiz-square-check"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center"><a class="btn-link"
                                                        href="{{ route('shopify.order.prepare', str_replace('#', '', $product['order_id'])) }}">Spgs{{ $product['order_id'] }}</a>
                                                </td>
                                                <td class="text-center">
                                                    Lvr{{ $product['return_number'] }}
                                                </td>
                                                <td class="text-center">
                                                    <img height="200" width="180"
                                                        src="{{ $product['product_img'] ?? null }}" alt="Image here">
                                                </td>
                                                <td class="text-center">
                                                    {{ $product['product_name'] }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $product['reason'] }}
                                                </td>
                                                <td class="text-center">{{ $product['old_qty'] }}</td>
                                                <td class="text-center">{{ $product['returned_qty'] }}</td>
                                                <td class="text-center">{{ $product['amount'] * $product['returned_qty'] }}
                                                </td>
                                                <td class="text-center">{{ Carbon\Carbon::parse($product['created_at']) }}
                                                </td>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                                <tfoot>

                                    <tr>
                                        <th class="text-center">{{ $returns_count }}</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>


                                </tfoot>
                            </table>
                            {{--  <div class="text-center pb-2">
                                {{ $data->links() }}
                            </div>  --}}
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
        $("ul#reports").siblings('a').attr('aria-expanded', 'true');
        $("ul#reports").addClass("show");
        $("#returned_products").addClass("active");
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
