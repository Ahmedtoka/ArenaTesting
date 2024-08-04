@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
@endsection
@section('content')

    <div class="pagetitle">
        <div class="row">
            <div class="col-9">
                <h1>Staff Report</h1>
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

                    <form class="" action="{{ route('reports.staff') }}" id="sort_orders" method="GET">
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-1">
                                    <h6 class="d-inline-block pt-10px">{{ 'Choose Order Date' }}</h6>
                                </div>
                                <div class="col-4 justify-content-center">

                                    <div class="container">
                                        <input type="text" value="{{ $daterange }}" id="daterange" name="daterange"
                                            class="form-control">
                                    </div>

                                </div>
                                <div class="col-2">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary" name="action"
                                            value="filter">{{ 'Filter' }}</button>
                                    </div>
                                </div>

                                <div class="col-2">

                                </div>

                                <div class="col-3">
                                    <button type="submit" style="float: right" class="btn btn-danger" name="action"
                                        value="export">Export
                                        Report</button>
                                </div>

                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title">Staff Report</h5>

                            <table class="table datatable" id="example">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">Staff Name</th>
                                        <th scope="col" class="text-center">Total Orders</th>
                                        <th scope="col" class="text-center">New Orders</th>
                                        <th scope="col" class="text-center">Prepared Orders</th>
                                        <th scope="col" class="text-center">Hold Orders</th>
                                        <th scope="col" class="text-center">Fulfilled Orders</th>
                                        <th scope="col" class="text-center">Shipped Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($all_prepares)
                                        @foreach ($prepare_users_list['name'] as $key => $user)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $prepare_users_list['name'][$key] }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-inline badge-secondary">
                                                        {{ $prepare_users_list['all'][$key] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-inline badge-info">
                                                        {{ $prepare_users_list['new'][$key] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-inline badge-success">
                                                        {{ $prepare_users_list['prepared'][$key] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-inline badge-warning">
                                                        {{ $prepare_users_list['hold'][$key] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-inline badge-primary">
                                                        {{ $prepare_users_list['fulfilled'][$key] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-inline badge-dark">
                                                        {{ $prepare_users_list['shipped'][$key] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                                <tfoot>

                                    <tr>
                                        <th class="text-center">{{ count($prepare_users_list['name']) }}</th>
                                        <th class="text-center">{{ array_sum($prepare_users_list['all']) }}</th>
                                        <th class="text-center">{{ array_sum($prepare_users_list['new']) }}</th>
                                        <th class="text-center">{{ array_sum($prepare_users_list['prepared']) }}</th>
                                        <th class="text-center">{{ array_sum($prepare_users_list['hold']) }}</th>
                                        <th class="text-center">{{ array_sum($prepare_users_list['fulfilled']) }}</th>
                                        <th class="text-center">{{ array_sum($prepare_users_list['shipped']) }}</th>
                                    </tr>


                                </tfoot>
                            </table>
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
        $("#staff").addClass("active");

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
