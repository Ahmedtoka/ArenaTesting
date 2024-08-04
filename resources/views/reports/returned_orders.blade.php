@extends('layouts.app')
@section('css')
    <link href="{{ asset('assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet">
@endsection
@section('content')

    <div class="pagetitle">
        <div class="row">

            <div class="col-8">
                <h1>Returned Orders</h1>
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

                                <div class="col-sm-2 justify-content-center">
                                    <div class="form-group mb-0">
                                        <label for="date">Filter By Date</label>
                                        <input type="date" class="form-control" value="{{ $date }}"
                                            name="date" placeholder="{{ 'Filter by Return date' }}" data-format="DD-MM-Y"
                                            data-separator=" to " data-advanced-range="true" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-sm-3 justify-content-center">
                                    <div class="">
                                        <label for="date">Filter By Return Stauts</label>
                                        <select class="form-select aiz-selectpicker" name="delivery_status"
                                            id="delivery_status">
                                            <option value="" @if ($delivery_status == '') selected @endif>Select
                                            </option>
                                            <option value="In Progress" @if ($delivery_status == 'In Progress') selected @endif>
                                                In Progress</option>
                                            <option value="Returned" @if ($delivery_status == 'Returned') selected @endif>
                                                Returned</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3 justify-content-center">
                                    <div class="form-group mb-0">
                                        <label for="date">Search Return</label>
                                        <input type="text" class="form-control" id="search"
                                            name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset
                                            placeholder="{{ 'Type Order or Return No & Hit Enter' }}">
                                    </div>
                                </div>
                                <div class="col-sm-2 justify-content-center">
                                    <label for="paginate">Show Returns</label>
                                    <select class="form-select aiz-selectpicker" name="paginate" id="paginate">
                                        <option value="0">Choose Number To SHow</option>
                                        <option value="15">15 Return</option>
                                        <option value="50">50 Return</option>
                                        <option value="100">100 Return</option>
                                        <option value="1000">All</option>
                                    </select>
                                </div>
                                <div class="col-sm-1 m-2 justify-content-center">
                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-header row gutters-5">
                            <div class="row col-12">
                                <div class="col-sm-3">
                                    <h6 class="d-inline-block pt-10px">Total Returns</h6>
                                </div>
                                <div class="col-sm-4">
                                    <h6 class="d-inline-block pt-10px">{{ $orders_count }} Orders</h6>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Returned Orders</h5>
                            {{--  <!-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> -->  --}}

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
                                        <th scope="col" class="text-center">Return No</th>
                                        <th scope="col" class="text-center">Order No</th>
                                        <th scope="col" class="text-center">Status</th>
                                        <th scope="col" class="text-center">Note</th>
                                        <th scope="col" class="text-center">Old Order Amount</th>
                                        <th scope="col" class="text-center">Return Amount</th>
                                        <th scope="col" class="text-center">New Order Amount</th>
                                        <th scope="col" class="text-center">Quantity</th>
                                        <th scope="col" class="text-center">Returned By</th>
                                        <th scope="col" class="text-center">Shipping On</th>
                                        <th scope="col" class="text-center">Created Date</th>
                                        <th scope="col" class="text-center">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($returns)
                                        @foreach ($returns as $key => $return)
                                            @if ($return)
                                                <tr>
                                                    <td>
                                                        <div class="form-group">
                                                            <div class="aiz-checkbox-inline">
                                                                <label class="aiz-checkbox">
                                                                    <input type="checkbox" class="check-one" name="id[]"
                                                                        value="{{ $return->id }}">
                                                                    <span class="aiz-square-check"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        Lvr{{ $return->return_number }}
                                                    </td>
                                                    <td class="text-center">
                                                        <a class="btn-link"
                                                            href="{{ route('shopify.order.prepare', $return->order_number) }}">Spgs{{ $return->order_number }}</a>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($return->status == 'In Progress')
                                                            <span
                                                                class="badge badge-inline badge-danger">{{ $return->status }}</span>
                                                        @elseif ($return->status == 'Returned')
                                                            <span
                                                                class="badge badge-inline badge-info">{{ $return->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $return->note }}</td>
                                                    <td class="text-center">
                                                        {{ $return->order->total_price + $return->amount }}</td>
                                                    <td class="text-center">{{ $return->amount }}</td>
                                                    <td class="text-center">{{ $return->order->total_price }}</td>
                                                    <td class="text-center">
                                                        {{ $return->qty }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $return->user ? $return->user->name : '-' }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ ucfirst($return->shipping_on) }}
                                                    </td>
                                                    <td>{{ date('Y-m-d h:i:s', strtotime($return->created_at)) }}</td>
                                                    <td class="text-center">
                                                        <div class="col-5  mr-2 ml-2">
                                                            <div class="row mb-1">
                                                                <a class="btn btn-dark"
                                                                    href="{{ route('prepare.generate-return-invoice', $return->id) }}"
                                                                    title="Generate Invoice">
                                                                    <i class="bi bi-printer"></i>
                                                                </a>
                                                                Invoice
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
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
                            <div class="text-center pb-2">
                                {{ $returns->links() }}
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
    <script type="text/javascript" src="<?php echo asset('vendor/jquery/jquery.min.js'); ?>"></script>
    <script type="text/javascript">
        $("ul#reports").siblings('a').attr('aria-expanded', 'true');
        $("ul#reports").addClass("show");
        $("#returned_report").addClass("active");
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
        $("#shipping_company").change(function() {
            var data = new FormData($('#sort_orders')[0]);
            var selected_name = $(this).find("option:selected").text();
            var selected_user = this.value;
            data.append('emp_name', selected_name);

            if (selected_user == 0) {

            } else {
                if (confirm('Are You Sure to Assign These order to ' + selected_name)) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('bulk-returns-shipped') }}",
                        type: 'POST',
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response == 0) {
                                window.location.href =
                                    '{{ route('return-pickups.index', ['msg' => 'success']) }}';
                            } else {
                                window.location.href =
                                    '{{ route('return-pickups.index', ['msg' => 'failed']) }}';
                            }
                        }
                    });
                } else {}
            }

            console.log(selected_name);
            console.log(selected_user);
            console.log(data);
        });
    </script>
@endsection
