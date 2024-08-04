@extends('layouts.app')
@section('content')
    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Inventory Transfer Details</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Transfer Details</li>
                    </ol>
                </nav>
            </div>
            <div class="col-12">
                @can('write-products')
                    <table class="table table-secondary">
                        <tbody>
                            <tr>
                                <td> Transfer Reference : {{ $transfer->ref }}</td>
                                <td> Transfer Date : {{ $transfer->created_at }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endcan
            </div>
        </div>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transfer Items</h5>
                        {{-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> --}}
                        <!-- Table with stripped rows -->
                        <table class="table datatable table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Variation</th>
                                    <th scope="col">Inventory Item ID</th>
                                    <th scope="col">Quantity Before</th>
                                    <th scope="col">Quantity After</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($details)
                                    @if ($details !== null)
                                        @foreach ($details as $key => $detail)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $detail->product_name }}</td>
                                                <td>{{ $detail->variation }}</td>
                                                <td>{{ $detail->line_item_id }}</td>
                                                <td>{{ $detail->qty_before }}</td>
                                                <td>
                                                    {{ $detail->qty_after }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endisset
                            </tbody>
                        </table>
                        <!-- End Table with stripped rows -->

                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
