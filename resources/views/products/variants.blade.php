@extends('layouts.app')
@section('content')
    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Product Variants</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Products</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">


                    <form method="get" action="#">
                        <div class="card-header row gutters-5">
                            <div class="row col-12">

                                <div class="col-sm-3">
                                    <div class="form-group mb-0">
                                        <input type="text" class="form-control" id="search"
                                            name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                            placeholder="{{ 'Type Product Name & hit Enter' }}">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary">{{ 'Filter' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="card-body">
                        <h5 class="card-title">Your Product Variants</h5>
                        {{-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> --}}
                        <!-- Table with stripped rows -->
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Variation</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col">Created Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    use Milon\Barcode\DNS1D;
                                    $barcodes = null;
                                @endphp
                                @isset($products)
                                    @if ($products !== null)
                                        @foreach ($products as $key => $product)
                                            @php
                                                if (!empty($product->sku)) {
                                                    $barcode = new DNS1D();
                                                    $barcodes = $barcode->getBarcodePNG($product->sku, 'C128');
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $product->product['title'] }}</td>
                                                <td>{{ $product['title'] }}</td>
                                                <td>{{ $product['sku'] }}</td>
                                                <td>{{ date('Y-m-d', strtotime($product['created_at'])) }}</td>
                                                <td><a id="submit-button"
                                                        onclick='printBarcode("{{ $product->sku }}","{{ $product->product->title . '-' . $product->title }}","{{ $barcodes }}")'
                                                        class="btn btn-primary">Print Barcode</a>
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
        <div id="print-barcode" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modal_header" class="modal-title">{{ trans('Barcode') }}</h5>&nbsp;&nbsp;
                        <button id="print-btn" type="button" class="btn btn-default btn-sm"><i class="dripicons-print"></i>
                            {{ trans('Print') }}</button>
                        <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div id="label-content">
                            <table class="barcodelist" style="width: 100%" cellpadding="5px" cellspacing="10px">
                                <tr>
                                    <td>
                                        Name :<br>
                                        Price:<br>
                                        Code :<br>
                                        <img src="data:image/png;base64," alt="barcode"
                                            style="margin-top:10px;margin-bottom:10px;" /><br>
                                    </td>
                                </tr>
                            </table>
                        </div>
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
        $("#variants").addClass("active");

        function printBarcode(code, name, barcodes) {
            var htmltext = '<table class="barcodelist" style="width: 100%" cellpadding="5px" cellspacing="10px">';
            htmltext += '<tr>';
            htmltext += '<td">';
            htmltext += name + '<br>';
            htmltext += '<img src="data:image/png;base64,' + barcodes +
                '" alt="barcode" style="margin-top:10px;margin-bottom:10px;"/><br>';
            htmltext += '<strong>' + code + '</strong><br>';
            htmltext += '</td>';
            htmltext += '</tr>';
            htmltext += '</table">';
            $('#label-content').html(htmltext);
            $('#print-barcode').modal('show');
        }

        $("#print-btn").on("click", function() {
            var divToPrint = document.getElementById('print-barcode');
            var newWin = window.open('', 'Print-Window');
            newWin.document.open();
            newWin.document.write(
                '<style type="text/css">@media print { #modal_header { display: none } #print-btn { display: none } #close-btn { display: none } } table.barcodelist { page-break-inside:auto } table.barcodelist tr { page-break-inside:avoid; page-break-after:auto }</style><body onload="window.print()">' +
                divToPrint.innerHTML + '</body>');
            newWin.document.close();
            setTimeout(function() {
                newWin.close();
            }, 10);
        });
    </script>
@endsection
