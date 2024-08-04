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
                        <li class="breadcrumb-item">Re-Synced Orders</li>
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

                                <div class="col-sm-4">
                                    <div class="form-group mb-0">
                                        <input type="date" class="form-control" value="{{ $date }}"
                                            name="date" placeholder="{{ 'Filter by date' }}" data-format="DD-MM-Y"
                                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group mb-0">
                                        <input type="text" class="form-control" id="search"
                                            name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                            placeholder="{{ 'Type Order code & hit Enter' }}">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="">
                                        <select class="form-select aiz-selectpicker" name="old_status" id="old_status">
                                            <option value="">{{ 'Filter by Delivery Status' }}</option>
                                            <option value="processing" @if ($delivery_status == 'processing') selected @endif>
                                                {{ 'Processing' }} </option>
                                            <option value="distributed" @if ($delivery_status == 'distributed') selected @endif>
                                                {{ 'Distributed' }} </option>
                                            <option value="prepared" @if ($delivery_status == 'prepared') selected @endif>
                                                {{ 'Prepared' }}</option>
                                            <option value="hold" @if ($delivery_status == 'hold') selected @endif>
                                                {{ 'Hold' }}</option>
                                            <option value="reviewed" @if ($delivery_status == 'reviewed') selected @endif>
                                                {{ 'Reviewed' }}</option>
                                            <option value="shipped" @if ($delivery_status == 'shipped') selected @endif>
                                                {{ 'Shipped' }}</option>
                                            <option value="cancelled" @if ($delivery_status == 'cancelled') selected @endif>
                                                {{ 'Cancel' }}</option>
                                            <option value="fulfilled" @if ($delivery_status == 'fulfilled') selected @endif>
                                                {{ 'Fulfilled' }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary">{{ 'Filter' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Re-Synced Orders</h5>
                            {{--  <!-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> -->  --}}

                            <!-- Table with stripped rows -->
                            <table class="table datatable" id="example">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Order No</th>
                                        <th scope="col" class="text-center">Resynced By</th>
                                        <th scope="col" class="text-center">Assigned To</th>
                                        <th scope="col" class="text-center">Edit Reason</th>
                                        <th scope="col" class="text-center">Old Total</th>
                                        <th scope="col" class="text-center">New Total</th>
                                        <th scope="col" class="text-center">Old Delivery Status</th>
                                        <th scope="col" class="text-center">Created Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($orders)
                                        @foreach ($orders as $key => $order)
                                            @if ($order)
                                                <tr>
                                                    <td class="text-center">
                                                        {{ $order->order_id }}
                                                    </td>
                                                    <td class="text-center">{{ $order->editor->name }}</td>
                                                    <td class="text-center">
                                                        {{ $order->assignee ? $order->assignee->name : '-' }}
                                                    </td>
                                                    <td class="text-center">{{ $order->reason }}</td>
                                                    <td class="text-center">{{ $order->old_total }}</td>
                                                    <td class="text-center">{{ $order->new_total }}</td>
                                                    <td class="text-center">
                                                        @if ($order->old_status == 'processing')
                                                            <span
                                                                class="badge badge-inline badge-danger">{{ $order->old_status }}</span>
                                                        @elseif ($order->old_status == 'distributed')
                                                            <span
                                                                class="badge badge-inline badge-info">{{ $order->old_status }}</span>
                                                        @elseif ($order->old_status == 'prepared')
                                                            <span
                                                                class="badge badge-inline badge-success">{{ $order->old_status }}</span>
                                                        @elseif ($order->old_status == 'shipped')
                                                            <span
                                                                class="badge badge-inline badge-primary">{{ $order->old_status }}</span>
                                                        @elseif ($order->old_status == 'hold')
                                                            <span
                                                                class="badge badge-inline badge-warning">{{ $order->old_status }}</span>
                                                        @elseif ($order->old_status == 'reviewed')
                                                            <span
                                                                class="badge badge-inline badge-secondary">{{ $order->old_status }}</span>
                                                        @elseif ($order->old_status == 'cancelled')
                                                            <span
                                                                class="badge badge-inline badge-danger">{{ $order->old_status }}</span>
                                                        @elseif ($order->old_status == 'fulfilled')
                                                            <span
                                                                class="badge badge-inline badge-dark">{{ $order->old_status }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ date('Y-m-d h:i:s', strtotime($order->created_at)) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endisset
                                </tbody>
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
    <script type="text/javascript">
        $("ul#edited").siblings('a').attr('aria-expanded', 'true');
        $("ul#edited").addClass("show");
        $("#resynced").addClass("active");
    </script>
@endsection
