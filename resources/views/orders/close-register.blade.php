@extends('layouts.app')
@section('content')


    <style type="text/css">
        .cash-register-table {}

        .cash-register-table th,
        .cash-register-table td,
        .cash-register-table tr {
            padding: 0;
            line-height: 0;
        }

        .cash-register-table td {
            padding: 15px;
        }

        h4 {
            line-height: 30px !important;
        }

        @media print {
            * {
                font-size: 18px;
                line-height: 16px;
            }

            h4 {
                line-height: 30px !important;
                font-family: 'system-ui';
                text-transform: capitalize;
                color: rgb(0, 0, 0, 100) !important;
                padding: 0;
                margin: 0;
                font-weight: bold !important;
            }

            td.amount {
                text-align: end;
            }

            td,
            th {
                padding: 5px 0;
            }

            .hidden-print {
                display: none !important;
            }

            .big-title {
                font-weight: bold;
            }

            .sub-address {
                text-align: left;
                padding: 0;
            }

            @page {
                margin: 0;
            }

            body {
                margin: 0.5cm 0.5cm 1.6cm;
            }
        }
    </style>
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('Close Day') }} - {{ $register->id }}- {{ $register->branch->name }} <br> Open
                                At : {{ $register->created_at }} <br> Closed at : {{ $register->updated_at }}</h4>
                        </div>
                        <form action="{{ route('register.close.post', $register->id) }}" method="POST">
                            @csrf
                            @if (!$register->register_close_amount)
                                <div class="card-body">
                                    <div class="row">
                                        <div class="container">
                                            <div class="col-md-6 offset-md-3" style="text-align:center;">
                                                <h3 class="pb-2">أدخل مبلغ الدرج كاش</h3>

                                                <div class="form-group">
                                                    <input type="text" name="register_close_amount" class="form-control"
                                                        required>
                                                </div>
                                                <input type="hidden" name="close_register" value="save-cash-on-hand">
                                                <div class="form-group m-2">
                                                    <button class="btn btn-primary"
                                                        type="submit">{{ trans('Submit') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{--						@php  dd($register_details);@endphp --}}
                            @if ($register->register_close_amount)
                                <div class="card-body" id="closeRegister">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <table class="table cash-register-table">
                                                <tbody>
                                                    {{-- <tr>
                                                <td>
                                                 رصيد اول الدرج
                                               </td>
                                               <td>
                                                 <span class="display_currency" data-currency_symbol="true">L.E {{$register_details->open_amount}}</span>
                                               </td>
                                             </tr> --}}
                                                    <tr>
                                                        <td>
                                                            <span>Total Sales</span>
                                                        </td>
                                                        <td class="amount">
                                                            <span class="display_currency"
                                                                data-currency_symbol="true">{{ number_format((float) $register_details->total_pure_sale, 2, '.', '') }}
                                                                L.E </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <span>Cash Sales</span>
                                                        </td>
                                                        <td class="amount">
                                                            <span class="display_currency"
                                                                data-currency_symbol="true">{{ number_format((float) $register_details->total_cash, 2, '.', '') }}
                                                                L.E</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <span>Visa Sales</span>
                                                        </td>
                                                        <td class="amount">
                                                            <span class="display_currency"
                                                                data-currency_symbol="true">{{ number_format((float) $register_details->total_card, 2, '.', '') }}
                                                                L.E </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <span>Premium Sales</span>
                                                        </td>
                                                        <td class="amount">
                                                            <span class="display_currency"
                                                                data-currency_symbol="true">{{ number_format((float) $register_details->total_cheque, 2, '.', '') }}
                                                                L.E </span>
                                                        </td>
                                                    </tr>
                                                    {{-- <tr class="success">
                                                  <th>
                                                    إجمالي مصاريف الفرع             </th>
                                                  <td>
                                                    <strong><span class="display_currency" data-currency_symbol="true" style="color:red">L.E {{$register_details->total_expense}}</span></b><br>
                                                    <small>
                                                    </td>
                                                </tr> --}}
                                                    <tr class="success">
                                                        <td>
                                                            <span>Total Cash Returns</span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true"
                                                                    style="color:red">{{ number_format((float) $register_details->total_cash_refund, 2, '.', '') }}
                                                                    L.E</span></strong><br>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>Total Visa Returns </span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true"
                                                                    style="color:red">{{ number_format((float) $register_details->total_card_refund, 2, '.', '') }}
                                                                    L.E</span></strong><br>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>Total Online Returns </span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true"
                                                                    style="color:red">{{ number_format((float) $register_details->total_online_refund, 2, '.', '') }}
                                                                    L.E </span></strong><br>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>Total Returns </span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true"
                                                                    style="color:red">{{ number_format((float) $register_details->total_refund, 2, '.', '') + number_format((float) $register_details->total_online_refund, 2, '.', '') }}
                                                                    L.E</span></strong><br>
                                                        </td>
                                                    </tr>
                                                    <tr class="success" style="background-color:#ddd;">
                                                        <td>
                                                            <span>NetSales </span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true"
                                                                    style="color:red">{{ number_format((float) $register_details->total_pure_sale - $register_details->total_refund, 2, '.', '') }}
                                                                    L.E </span></strong><br>
                                                        </td>
                                                    </tr>

                                                    <tr class="success">
                                                        <td>
                                                            <span>Total Cash In Register</span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">{{ $register_details->open_amount + $register_details->total_cash - $register_details->total_cash_refund - $register_details->total_online_refund - $register_details->total_expense }}
                                                                    L.E </span></strong>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>Total Visa In Register </span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">{{ $register_details->total_card - $register_details->total_card_refund }}
                                                                    L.E </span></strong>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>Total Premium In Register</span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">{{ $register_details->total_cheque - $register_details->total_cheque_refund }}
                                                                    L.E </span></strong>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>No. of Cash receipts</span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">{{ $register_details->total_Cash_only }}
                                                                    - Include</span></strong>
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true"> (
                                                                    {{ $register_details->total_cash_refund_only }} )
                                                                    Returned</span></strong>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>No. of Visa receipts</span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">{{ $register_details->total_card_slips }}
                                                                    - Include </span></strong>
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">(
                                                                    {{ $register_details->total_card_refunds_only }} )
                                                                    Returned</span></strong>
                                                        </td>
                                                    </tr>
                                                    <tr class="success">
                                                        <td>
                                                            <span>No. of Online receipts</span>
                                                        </td>
                                                        <td class="amount">
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">{{ $register_details->total_cheques }}
                                                                    - Include </span></strong>
                                                            <strong><span class="display_currency"
                                                                    data-currency_symbol="true">(
                                                                    {{ $register_details->total_online_refund_only }} )
                                                                    Returned</span></strong>
                                                        </td>
                                                    </tr>
                                                    @if ($register->close_status == 'negative')
                                                        <tr class="success" style="color:red">
                                                            <td>
                                                                <span> Deficit Amount</span>
                                                            </td>
                                                            <td class="amount">
                                                                <strong><span class="display_currency"
                                                                        data-currency_symbol="true">{{ number_format((float) $register->close_status_amount, 2, '.', '') }}
                                                                        L.E </span></strong>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if ($register->close_status == 'positive')
                                                        <tr class="success" style="color:green">
                                                            <td>
                                                                <span>Excess Amount</span>
                                                            </td>
                                                            <td class="amount">
                                                                <strong><span class="display_currency"
                                                                        data-currency_symbol="true">{{ number_format((float) $register->close_status_amount, 2, '.', '') }}
                                                                        L.E </span></strong>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-4" style="display: none;">
                                            <div class="form-group">
                                                <label for="total_cash">إجمالي الكاش:*</label>
                                                <input class="form-control input_number" required=""
                                                    placeholder="Total Cash" name="total_cash" type="text"
                                                    value="{{ $register_details->total_cash - $register_details->total_cash_refund + $register_details->open_amount - $register_details->total_expense }}"
                                                    id="total_cash">
                                            </div>
                                        </div>
                                        <div class="col-sm-4" style="display: none;">
                                            <div class="form-group">
                                                <label for="total_card_slips">إجمالي الفيزا:*</label> <i
                                                    class="fa fa-info-circle text-info hover-q no-print "
                                                    aria-hidden="true" data-container="body" data-toggle="popover"
                                                    data-placement="auto bottom"
                                                    data-content="Total number of card payments used in this register"
                                                    data-html="true" data-trigger="hover"></i> <input
                                                    class="form-control" required="" placeholder="Total Card Slips"
                                                    name="total_card_slips" type="text"
                                                    value="{{ $register_details->total_card - $register_details->total_card_refund }}"
                                                    id="total_card_slips">
                                            </div>
                                        </div>
                                        <div class="col-sm-4" style="display: none;">
                                            <div class="form-group">
                                                <label for="closing_amount">أدخل مبلغ التوريد الكاش:* لا يزيد عن
                                                    {{ $register->register_close_amount }} جنيه</label> <i
                                                    class="fa fa-info-circle text-info hover-q no-print "
                                                    aria-hidden="true" data-container="body" data-toggle="popover"
                                                    data-placement="auto bottom"
                                                    data-content="Total number of cheques used in this register"
                                                    data-html="true" data-trigger="hover"></i> <input
                                                    class="form-control" required="" placeholder="Closing Cash Amount"
                                                    name="closing_amount" type="number"
                                                    value="{{ $register->register_close_amount }}"
                                                    max="{{ $register->register_close_amount }}" step=".01">
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="closing_note">Notes:</label>
                                                <textarea class="form-control" placeholder="Closing Note" rows="3" name="closing_note" cols="50"
                                                    id="closing_note"></textarea>
                                            </div>
                                            <input type="hidden" name="close_register" value="save-close-info">
                                            <div class="form-group m-2">
                                                <button class="btn btn-primary hidden-print" onclick="window.print()"
                                                    type="submit">{{ trans('Submit') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection
