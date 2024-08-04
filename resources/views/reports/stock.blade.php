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
                <h1>Stock Report</h1>
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
                    <form class="" action="{{ route('reports.stock') }}" id="sort_orders" method="GET">
                        @csrf
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-1">
                                    <h6 class="d-inline-block pt-10px">{{ 'Choose Date Range' }}</h6>
                                </div>
                                <div class="col-4 justify-content-center">

                                    <div class="container">
                                        <input type="text" value="{{ $daterange }}" id="daterange" name="daterange"
                                            class="form-control">
                                    </div>

                                </div>
                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" onclick="exporttt()"
                                            class="btn btn-warning">{{ 'Export Sheet' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Stock Report</h5>

                            <table class="table datatable" id="example">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Preview</th>
                                        <th scope="col" class="text-center">Product</th>
                                        <th scope="col" class="text-center">Price</th>
                                        <th scope="col" class="text-center">Sold</th>
                                        <th scope="col" class="text-center">Current Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mostSellingProducts as $key => $sell)
                                        @php
                                            $stock = \App\Models\ProductVariant::where(
                                                'sku',
                                                $sell->product_sku,
                                            )->first();
                                            if ($stock) {
                                                $current_stock = $stock->inventory_quantity;
                                            } else {
                                                $current_stock = $sell->total;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <img height="200" width="180" src="{{ $sell->variant_image ?? null }}"
                                                    alt="Image here">
                                            </td>
                                            <td class="text-center">{{ $sell->product_name }}</td>
                                            <td class="text-center">{{ $sell->price }}</td>
                                            <td class="fw-bold text-center">{{ $sell->total }}</td>
                                            <td class="fw-bold text-center">{{ $current_stock }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="text-center pb-2">
                                {{ $mostSellingProducts->links() }}
                            </div>
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
        $("#stock").addClass("active");
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
