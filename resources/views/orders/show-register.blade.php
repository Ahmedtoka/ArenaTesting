@extends('layouts.app')
@section('content')

    <style type="text/css">
        * {
            font-size: 25px;
            line-height: 20px;
            font-family: 'system-ui';
            text-transform: capitalize;
            color: rgb(0, 0, 0, 100) !important;
            padding: 0;
            margin: 0;
            font-weight: bold;
        }

        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor: pointer;
        }

        .inv-number-bc {
            width: 90%;
            height: 60px;
            margin: 0 auto;
            text-align: center;
            display: block;
        }

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .ask-for-exchange {
            width: 100%;
            height: 100%;
            position: fixed;
            content: "";
            top: 0;
            left: 0;
            background-color: #000000bf;
            text-align: center;

        }

        .ask-for-exchange h4 {
            font-size: 50px;
            margin-top: 180px;
            color: #fff !important;
            margin-bottom: 90px;
        }

        .yes-exchange,
        .no-exchange {
            color: #fff !important;
            padding: 15px 30px;
            border-radius: 12px;
            margin: 50px;
            text-decoration: none;
            font-size: 30px;
        }

        .yes-exchange {
            background-color: brown;
        }

        .no-exchange {
            background-color: #2a3da5;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }

        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }

        tr {
            border-bottom: 1px dotted #ddd;
        }

        td,
        th {
            padding: 5px 0;
            width: 50%;
        }

        table {
            width: 100%;
        }

        tfoot tr th:first-child {
            text-align: left;
        }

        .big-title {
            font-weight: bold;
        }

        .sub-address {
            padding: 0 !important;
            font-size: 14px;
        }

        .centered {
            text-align: center;
            align-content: center;
        }

        small {
            font-size: 11px;
        }

        .exchange-popup {
            position: absolute;
            top: 0;
            left: 0;
            background-color: #00000082;
            padding-top: 250px;
            width: 100%;
            height: 100%;
            text-align: center;

        }

        .box-container-for-popup {
            width: 60%;
            margin: 0 auto;
            background-color: #ffffffdb;
            height: 200px;
            border-radius: 30px;
            padding-top: 100px;
        }

        .exchange-popup h1 {
            font-size: 30px;
            color: #971212 !important;
            line-height: 1.3;

        }

        .exchange-popup a {
            padding: 10px;
            display: inline-block;
            width: 27%;
            border-radius: 15px;
            text-decoration: none;
            margin: 15px;

        }

        @media print {
            * {
                font-size: 25px;
                line-height: 20px;
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
                margin: 0.5cm;
                margin-bottom: 1.6cm;
            }
        }
    </style>
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.Close Day') }} - {{ $register->id }} - {{ $register->branch->name }} -
                                {{ $register->created_at }}</h4>
                        </div>

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
                                                    <td>
                                                        <span class="display_currency"
                                                            data-currency_symbol="true"></span>{{ number_format((float) $register_details->total_pure_sale, 2, '.', '') }}
                                                        L.E
                                                    </td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <span>Cash Sales</span>
                                                    </td>
                                                    <td>
                                                        <span class="display_currency"
                                                            data-currency_symbol="true">{{ number_format((float) $register_details->total_cash, 2, '.', '') }}
                                                            L.E</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <span>Visa Sales</span>
                                                    </td>
                                                    <td>
                                                        <span class="display_currency"
                                                            data-currency_symbol="true">{{ number_format((float) $register_details->total_card, 2, '.', '') }}
                                                            L.E </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <span>Premium Sales</span>
                                                    </td>
                                                    <td>
                                                        <span class="display_currency"
                                                            data-currency_symbol="true">{{ number_format((float) $register_details->total_cheque, 2, '.', '') }}
                                                            L.E </span>
                                                    </td>
                                                </tr>
                                                {{-- <tr class="success">
                                                  <th>
                                                    إجمالي مصاريف الفرع             </th>
                                                  <td>
                                                    <b><span class="display_currency" data-currency_symbol="true" style="color:red">L.E {{$register_details->total_expense}}</span></b><br>
                                                    <small>
                                                    </td>
                                                </tr> --}}
                                                <tr class="success">
                                                    <td>
                                                        <span>Total Cash Returns</span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true"
                                                                style="color:red">{{ number_format((float) $register_details->total_cash_refund, 2, '.', '') }}
                                                                L.E </span></b><br>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>Total Visa Returns </span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true"
                                                                style="color:red">{{ number_format((float) $register_details->total_card_refund, 2, '.', '') }}
                                                                L.E </span></b><br>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>Total Online Returns </span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true"
                                                                style="color:red">{{ number_format((float) $register_details->total_cheque_refund, 2, '.', '') }}
                                                                L.E </span></b><br>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>Total Returns </span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true"
                                                                style="color:red">{{ number_format((float) $register_details->total_refund, 2, '.', '') }}
                                                                L.E</span></b><br>
                                                    </td>
                                                </tr>
                                                <tr class="success" style="background-color:#ddd;">
                                                    <td>
                                                        <span>NetSales </span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true"
                                                                style="color:red">
                                                                {{ number_format((float) $register_details->total_pure_sale - $register_details->total_refund, 2, '.', '') }}
                                                                L.E </span></b><br>
                                                    </td>
                                                </tr>

                                                <tr class="success">
                                                    <td>
                                                        <span>Total Cash In Register</span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true">L.E
                                                                {{ $register_details->open_amount + $register_details->total_cash - $register_details->total_cash_refund - $register_details->total_expense }}</span></b>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>Total Visa In Register </span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true">L.E
                                                                {{ $register_details->total_card - $register_details->total_card_refund }}</span></b>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>Total Premium In Register</span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency" data-currency_symbol="true">L.E
                                                                {{ $register_details->total_cheque - $register_details->total_cheque_refund }}</span></b>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>No. of Cash receipts</span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency"
                                                                data-currency_symbol="true">{{ $register_details->total_Cash_only }}</span></b>
                                                        <b><span class="display_currency" data-currency_symbol="true"> -
                                                                Include ( {{ $register_details->total_cash_refund_only }} )
                                                                Returned</span></b>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>No. of Visa receipts</span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency"
                                                                data-currency_symbol="true">{{ $register_details->total_card_slips }}</span></b>
                                                        <b><span class="display_currency" data-currency_symbol="true"> -
                                                                Include ( {{ $register_details->total_card_refunds_only }}
                                                                ) Returned</span></b>
                                                    </td>
                                                </tr>
                                                <tr class="success">
                                                    <td>
                                                        <span>No. of Premium receipts</span>
                                                    </td>
                                                    <td>
                                                        <b><span class="display_currency"
                                                                data-currency_symbol="true">{{ $register_details->total_cheques }}</span></b>
                                                        <b><span class="display_currency" data-currency_symbol="true"> -
                                                                Include ( {{ $register_details->total_cheque_refund_only }}
                                                                ) Returned</span></b>
                                                    </td>
                                                </tr>
                                                @if ($register->close_status == 'negative')
                                                    <tr class="success" style="color:red">
                                                        <td>
                                                            <span> Deficit Amount</span>
                                                        </td>
                                                        <td>
                                                            <b><span class="display_currency"
                                                                    data-currency_symbol="true">L.E
                                                                    {{ $register->close_status_amount }}</span></b>
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if ($register->close_status == 'positive')
                                                    <tr class="success" style="color:green">
                                                        <td>
                                                            <span>Excess Amount</span>
                                                        </td>
                                                        <td>
                                                            <b><span class="display_currency"
                                                                    data-currency_symbol="true">L.E
                                                                    {{ $register->close_status_amount }}</span></b>
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
                                            <input class="form-control input_number" required="" placeholder="Total Cash"
                                                name="total_cash" type="text"
                                                value="{{ $register_details->total_cash - $register_details->total_cash_refund + $register_details->open_amount - $register_details->total_expense }}"
                                                id="total_cash">
                                        </div>
                                    </div>
                                    <div class="col-sm-4" style="display: none;">
                                        <div class="form-group">
                                            <label for="total_card_slips">إجمالي الفيزا:*</label> <i
                                                class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true"
                                                data-container="body" data-toggle="popover" data-placement="auto bottom"
                                                data-content="Total number of card payments used in this register"
                                                data-html="true" data-trigger="hover"></i> <input class="form-control"
                                                required="" placeholder="Total Card Slips" name="total_card_slips"
                                                type="text"
                                                value="{{ $register_details->total_card - $register_details->total_card_refund }}"
                                                id="total_card_slips">
                                        </div>
                                    </div>
                                    <div class="col-sm-4" style="display: none;">
                                        <div class="form-group">
                                            <label for="closing_amount">أدخل مبلغ التوريد الكاش:* لا يزيد عن
                                                {{ $register->register_close_amount }} جنيه</label> <i
                                                class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true"
                                                data-container="body" data-toggle="popover" data-placement="auto bottom"
                                                data-content="Total number of cheques used in this register"
                                                data-html="true" data-trigger="hover"></i> <input class="form-control"
                                                required="" placeholder="Closing Cash Amount" name="closing_amount"
                                                type="number" value="{{ $register->register_close_amount }}"
                                                max="{{ $register->register_close_amount }}" step=".01">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <button class="btn btn-primary hidden-print" onclick="auto_print()"
                                                type="submit" id="print-cash"> Print</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection

@section('scripts')
    <script type="text/javascript" src="<?php echo asset('vendor/jquery/jquery.min.js'); ?>"></script>
    <script type="text/javascript">
        document.addEventListener('contextmenu', event => event.preventDefault());

        function auto_print() {
            $("#print-cash").hide();
            window.print();
            history.back();
        }
    </script>
@endsection
