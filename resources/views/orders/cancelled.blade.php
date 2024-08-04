@extends('layouts.app')
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Orders</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Cancelled Orders</li>
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
                        <h5 class="card-title">Cancelled Orders</h5>

                        <!-- Table with stripped rows -->
                        <table class="table datatable" id="example">
                            <thead>
                                <tr>
                                    <th scope="col">Order No.</th>
                                    <th scope="col" class="text-center">Reason</th>
                                    <th scope="col" class="text-center">Cancelled By</th>
                                    <th scope="col" class="text-center">Note</th>
                                    <th scope="col">Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($orders)
                                    @foreach ($orders as $key => $order)
                                        <tr>
                                            <td><a class="btn-link"
                                                    href="{{ route('shopify.order.show', $order->order->table_id) }}">{{ $order->order->name }}</a>
                                            </td>
                                            <td class="text-center">{{ $order->reason }}</td>
                                            <td class="text-center">{{ $order->user->name }}</td>
                                            <td class="text-center">{{ $order->note }}</td>
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
    <script type='text/javascript'>
        $("ul#operation").siblings('a').attr('aria-expanded', 'true');
        $("ul#operation").addClass("show");
        $("#cancelled").addClass("active");
    </script>
@endsection
