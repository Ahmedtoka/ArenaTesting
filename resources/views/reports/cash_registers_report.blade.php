@extends('layout.app') @section('content')
    @if (empty($registers))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ 'No Data exist between this date range!' }}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">Cash Register For Branches</h3>
                </div>

                {!! Form::open(['route' => 'reports.cash_registers', 'method' => 'post']) !!}
                <div class="row mb-3">
                    <div class="col-md-5 offset-md-1 mt-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('Choose Your Date') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input type="text" class="daterangepicker-field form-control"
                                        value="{{ $start_date }} To {{ $end_date }}" required />
                                    <input type="hidden" name="start_date" value="{{ $start_date }}" />
                                    <input type="hidden" name="end_date" value="{{ $end_date }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('Choose Warehouse') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <input type="hidden" name="warehouse_id_hidden" value="{{ $warehouse_id }}" />
                                <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    @if (Auth::user()->warehouse_id)
                                        @foreach ($lims_warehouse_list as $warehouse)
                                            @if ($warehouse->id == Auth::user()->warehouse_id)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endif
                                        @endforeach
                                    @else
                                        <option value="0">{{ trans('All Warehouse') }}</option>
                                        @foreach ($lims_warehouse_list as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3">
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit">{{ trans('Submit') }}</button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="col-md-12 form-group">
                    <div class="row">
                        <!-- Count item widget-->
                        <div class="col-sm-3">
                            <div class="wrapper count-title text-left">
                                <div class="name"><strong style="color: #733686"> Total Sales :
                                        {{ number_format((float) $cash_all_amount, 2, '.', '') + number_format((float) $credit_all_amount, 2, '.', '') }}</strong>
                                </div>
                                <div class="name"><strong style="color: #733686"> Total Invoices :
                                        {{ $all_sales_register_count }}</strong></div>
                                <div class="name"><strong style="color: #733686"> Total Items :
                                        {{ $all_sales_register_item }}</strong></div>
                                <div class="name"><strong style="color: #733686"> Total Qty :
                                        {{ $all_sales_register_qty }}</strong></div>
                                <div class="name"><strong style="color: #733686"> Cash Sales :
                                        {{ number_format((float) $cash_all_amount, 2, '.', '') }} -
                                        {{ $all_cash_register_count }} Invoices</strong></div>
                                <div class="name"><strong style="color: #733686"> Credit Sales :
                                        {{ number_format((float) $credit_all_amount, 2, '.', '') }} -
                                        {{ $all_credit_register_count }} Invoices</strong></div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="wrapper count-title text-left">
                                <div class="name"><strong style="color: #297ff9">Total Returns :
                                        {{ number_format((float) $refund_cash_all_amount, 2, '.', '') + number_format((float) $refund_credit_all_amount, 2, '.', '') + number_format((float) $online_all_amount, 2, '.', '') }}</strong>
                                </div>
                                <div class="name"><strong style="color: #297ff9">Total Invoices :
                                        {{ $all_cash_register_refund_count + $all_credit_register_refund_count + $refund_online_all_amount }}</strong>
                                </div>
                                <div class="name"><strong style="color: #297ff9">Total Items :
                                        {{ $all_cash_register_refund_item + $all_credit_register_refund_item }}</strong>
                                </div>
                                <div class="name"><strong style="color: #297ff9">Total Qty :
                                        {{ $all_cash_register_refund_qty + $all_credit_register_refund_qty }}</strong>
                                </div>
                                <div class="name"><strong style="color: #297ff9"> Cash :
                                        {{ number_format((float) $refund_cash_all_amount, 2, '.', '') }} LE -
                                        {{ $all_cash_register_refund_count }} Invoices</strong></div>
                                <div class="name"><strong style="color: #297ff9">Credit :
                                        {{ number_format((float) $refund_credit_all_amount, 2, '.', '') }} LE -
                                        {{ $all_credit_register_refund_count }} Invoices</strong></div>
                                <div class="name"><strong style="color: #297ff9">Online :
                                        {{ number_format((float) $online_all_amount, 2, '.', '') }} LE -
                                        {{ $refund_online_all_amount }} Invoices</strong></div>


                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="wrapper count-title text-left">
                                <div class="name"><strong style="color: #00c689">Net Sales :
                                        {{ number_format((float) $registers_sum[0]->totalSales, 2, '.', '') }}</strong>
                                </div>
                                <p class="italic"><small>* All the Sales that created</small></p>
                                <div class="name"><strong style="color: #00c689">Net Cash :
                                        {{ number_format((float) $registers_sum[0]->TotalCash, 2, '.', '') }}</strong></div>
                                <p class="italic"><small>* Total Cash without return cash</small></p>
                                <div class="name"><strong style="color: #00c689">Net Credit :
                                        {{ number_format((float) $registers_sum[0]->TotalCredit, 2, '.', '') }}</strong>
                                </div>
                                <p class="italic"><small>* Total Credit without return Credit</small></p>
                            </div>
                        </div>
                        <!-- Count item widget-->
                        <div class="col-sm-3">
                            <div class="wrapper count-title text-left">
                                <div class="name"><strong style="color: #ff8952">Total Cash Register :
                                        {{ number_format((float) $cash_all_register_amount, 2, '.', '') }}</strong></div>
                                <p class="italic"><small>* All the Cash Register from Cashiers</small></p>
                                <div class="name"><strong style="color: #ff8952">Deficit Amount :
                                        {{ number_format((float) $cash_negative_amount, 2, '.', '') }}</strong></div>
                                <p class="italic"><small>* Total Deficit Amount for Branchs</small></p>
                                <div class="name"><strong style="color: #ff8952">Excess Amount
                                        {{ number_format((float) $cash_positive_amount, 2, '.', '') }}</strong></div>
                                <p class="italic"><small>* Total Excess Amount for Branchs</small></p>
                            </div>
                        </div>
                        <!-- Count item widget-->
                    </div>
                </div>
            </div>
        </div>
        <h1></h1>

        <div class="table-responsive mb-4">
            <table id="report-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Branch Name</th>
                        <th>Cashier Name</th>
                        <th>Open At</th>
                        <th>Closed Status</th>
                        <th>Closed At</th>
                        <th>Total Sales</th>
                        <th>Close Amount</th>
                        <th>Cash</th>
                        <th>Credit</th>
                        <th>Status</th>
                        <th>Different</th>
                        <th>Manage</th>
                    </tr>
                </thead>
                <tbody>

                    @if (!empty($registers))
                        @foreach ($registers as $key => $pro_id)
                            @php
                                $Sales_date = DB::table('sales')
                                    ->join('warehouses', 'warehouses.id', '=', 'sales.warehouse_id')
                                    ->where('sales.id', $pro_id->s_id)
                                    ->first();
                            @endphp

                            <tr>
                                <td>{{ $key }}</td>
                                <td>
                                    {{ $lims_warehouse_list->find($registers[$key]->warehouse_id)->name }}
                                </td>
                                <td>{{ $lims_user_list->find($registers[$key]->user_id)->name }} </td>
                                <td>
                                    {{ $pro_id->created_at }}
                                </td>
                                @if ($pro_id->status == 'close')
                                    <td> <span class="badge badge-danger mt-2">Closed</span></td>
                                @else
                                    <td> <span class="badge badge-success mt-2">Open</span> </td>
                                @endif
                                @if ($pro_id->closed_at == null)
                                    <td> <span class="badge badge-info mt-2">Pending</span></td>
                                @else
                                    <td> {{ $pro_id->closed_at }} </td>
                                @endif
                                <td> {{ number_format((float) $pro_id->total_sales_amount, 2, '.', '') }}</td>
                                <td> {{ number_format((float) $pro_id->closing_amount, 2, '.', '') }}</td>
                                <td> {{ number_format((float) $pro_id->total_cash, 2, '.', '') }}</td>
                                <td> {{ number_format((float) $pro_id->total_card_slips, 2, '.', '') }}</td>
                                @if ($pro_id->close_status == 'equal')
                                    <td> <span class="badge badge-success mt-2">Good</span></td>
                                    <td>-</td>
                                @elseif($pro_id->close_status == 'negative')
                                    <td> <span class="badge badge-danger mt-2">Negative</span></td>
                                    <td> {{ number_format((float) $pro_id->close_status_amount, 2, '.', '') }}</td>
                                @elseif($pro_id->close_status == 'positive')
                                    <td> <span class="badge badge-info mt-2">Positive</span></td>
                                    <td> {{ number_format((float) $pro_id->close_status_amount, 2, '.', '') }}</td>
                                @else
                                    <td>-</td>
                                    <td>-</td>
                                @endif
                                <td><a href="{{ route('register.show', $pro_id->id) }}"> <span
                                            class="badge badge-primary mt-2">open</span> </a></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <th></th>
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th>0.00</th>
                    <th>0.00</th>
                    <th>0.00</th>
                    <th>0.00</th>
                </tfoot>
            </table>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#report").siblings('a').attr('aria-expanded', 'true');
        $("ul#report").addClass("show");
        $("ul#report #product-report-menu").addClass("active");

        $('#warehouse_id').val($('input[name="warehouse_id_hidden"]').val());
        $('.selectpicker').selectpicker('refresh');

        $('#report-table').DataTable({
            "order": [],
            'language': {
                'lengthMenu': '_MENU_ {{ trans('records per page') }}',
                "info": '<small>{{ trans('Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ trans('Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'columnDefs': [{
                    "orderable": false,
                    'targets': 0
                },
                {
                    'render': function(data, type, row, meta) {
                        if (type === 'display') {
                            data =
                                '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    'checkboxes': {
                        'selectRow': true,
                        'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                    },
                    'targets': [0]
                }
            ],
            'select': {
                style: 'multi',
                selector: 'td:first-child'
            },
            'lengthMenu': [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            dom: '<"row"lfB>rtip',
            buttons: [{
                    extend: 'pdf',
                    text: '{{ trans('PDF') }}',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'csv',
                    text: '{{ trans('CSV') }}',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: '{{ trans('Print') }}',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'colvis',
                    text: '{{ trans('Column visibility') }}',
                    columns: ':gt(0)'
                }
            ],
            drawCallback: function() {
                var api = this.api();
                datatable_sum(api, false);
            }
        });

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();


                $(dt_selector.column(4).footer()).html(dt_selector.column(4, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(5).footer()).html(dt_selector.column(5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.column(6, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.column(7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.column(8, {
                    page: 'current'
                }).data().sum());

            } else {


                $(dt_selector.column(4).footer()).html(dt_selector.column(4, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(5).footer()).html(dt_selector.column(5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.column(6, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.column(7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.column(8, {
                    page: 'current'
                }).data().sum());

            }
        }
    </script>
@endsection
