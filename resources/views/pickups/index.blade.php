@extends('layouts.app')
@section('content')
    <div class="pagetitle">
        <div class="row">
            <div class="col-8">
                <h1>Orders</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Pickups</li>
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


                            </div>
                        </div>
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-2">
                                    <h6 class="d-inline-block pt-10px">{{ 'Choose Pickup Date' }}</h6>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group mb-0">
                                        <input type="date" class="form-control" value="{{ $date }}"
                                            name="date" value="date" placeholder="{{ 'Filter by date' }}"
                                            data-format="DD-MM-Y" data-separator=" to " data-advanced-range="true"
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-select aiz-selectpicker" name="shipping" id="branch_id">
                                        <option value="0">Choose Shipping Company</option>
                                        <option value="1" @if ($shipping == 1) selected @endif>BestExpress
                                        </option>
                                        <option value="2" @if ($shipping == 2) selected @endif>Sprint
                                        </option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-body">
                            <table class="table aiz-table mb-0">
                                <thead>
                                    <tr>
                                        <!--<th>#</th>-->
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
                                        <th>#</th>
                                        <th>Order Date</th>
                                        <th>Order Reference</th>
                                        <th data-breakpoints="md">CreatedBy</th>
                                        <th data-breakpoints="md">Shipping Company</th>
                                        <th data-breakpoints="md">Count</th>
                                        <th data-breakpoints="md">Order Status</th>
                                        <th class="text-right" width="15%">options</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($pickups as $key => $pickup)
                                        @php
                                            $get_user_name = \App\Models\User::find($pickup->user_id)->name;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <div class="aiz-checkbox-inline">
                                                        <label class="aiz-checkbox">
                                                            <input type="checkbox" class="check-one" name="id[]"
                                                                value="{{ $pickup->id }}">
                                                            <span class="aiz-square-check"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $key + 1 }}
                                            </td>
                                            <td>
                                                {{ date('d/m/Y', strtotime($pickup->created_at)) }}
                                            </td>
                                            <td>
                                                {{ $pickup->pickup_id }}
                                            </td>
                                            <td>
                                                {{ $get_user_name }}
                                            </td>
                                            <td>
                                                @if ($pickup->company_id == 1)
                                                    <span>BestExpress</span>
                                                @else
                                                    <span>Sprint</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $pickup->shipment_count }}
                                            </td>
                                            <td>
                                                <span class="">Ordered</span>
                                            </td>
                                            <td class="text-right">
                                                <a class="btn btn-danger" download
                                                    href="{{ '/download/' . $pickup->file_name }}" title="Download">
                                                    <i class="bi bi-wallet"></i>
                                                </a>
                                                <a class="btn btn-primary" download
                                                    href="{{ '/download/' . $pickup->file_accounting_name }}"
                                                    title="Download">
                                                    <i class="bi bi-wallet-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="aiz-pagination">
                                {{ $pickups->appends(request()->input())->links() }}
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
        $("ul#pickups").siblings('a').attr('aria-expanded', 'true');
        $("ul#pickups").addClass("show");
        //$("ul#pickups #index").addClass("active");

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
