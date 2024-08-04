@extends('layouts.app')
@section('content')
    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Products</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Products</li>
                    </ol>
                </nav>
            </div>
            <div class="col-4">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td><a href="{{ route('shopify.warehouse_products.sync') }}" style="float: right"
                                    class="btn btn-primary">Sync Products</a></td>
                            <td><a href="{{ route('locations.sync') }}" style="float: right;" class="btn btn-success">Sync
                                    Locations</a></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            {{ auth()->user()->warehouse ? auth()->user()->warehouse->name : 'Warehouse' }}
                            Products</h5>
                        {{-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> --}}
                        <!-- Table with stripped rows -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Product Variation</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col">Warehouse</th>
                                    <th scope="col">Created Date</th>
                                    <th scope="col">QTY</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($products)
                                    @if ($products !== null)
                                        @foreach ($products as $key => $product)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $product->title }}</td>
                                                <td>{{ $product->sku }}</td>
                                                <td>{{ $product->warehouse->name }}</td>
                                                <td>{{ date('Y-m-d', strtotime($product['created_at'])) }}</td>
                                                <td>{{ $product->inventory_quantity }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endisset
                            </tbody>
                        </table>
                        <div class="text-center pb-2">
                            {{ $products->links() }}
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
        $("ul#operation").siblings('a').attr('aria-expanded', 'true');
        $("ul#operation").addClass("show");
        $("#product_warehouse").addClass("active");
    </script>
@endsection
