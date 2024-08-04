<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="" />
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="<?php echo asset('vendor/font-awesome/css/font-awesome.min.css'); ?>" type="text/css">
    <!-- Drip icon font-->
    <link rel="stylesheet" href="<?php echo asset('vendor/dripicons/webfont.css'); ?>" type="text/css">

    <script type="text/javascript" src="<?php echo asset('vendor/jquery/jquery.min.js'); ?>"></script>
    <style type="text/css">
        * {
            font-size: 16px;
            line-height: 24px;
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
                font-size: 14px;
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
</head>

<body>

    <div style="max-width:400px;margin:0 auto">
        <div class="hidden-print">
            <table>
                <tr>
                    <td><a href="s" class="btn btn-info"><i class="fa fa-arrow-left"></i>
                            {{ trans('file.Back') }}</a> </td>
                    <!-- <td><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i> {{ trans('file.Print') }}</button></td> -->
                </tr>
            </table>
            <br>
        </div>
        <div class="centered">
            <img src="{{ asset('assets/img/logo.png') }}" width="60%">
            <h2>Online Return Order</h2>
        </div>
        @php
            $qty = 0;

            use Milon\Barcode\DNS1D;
            $barcode = new DNS1D();

            echo ' <p style="text-align: center;display: block";font-size:20px>' .
                $barcode->getBarcodeSVG('Lvr' . $return->return_number, 'C128', 3, 37, 'Black', false) .
                '</p>';
        @endphp

        <p> Return Placing Date: {{ date('Y-m-d', strtotime($return->created_at)) }}<br>
            Shipping Date : {{ date('Y-m-d') }} <br>
            Return Number : Lvr{{ $return->return_number }} <br>
            Order Number : spg{{ $return->order_number }} <br>
            Customer Name : {{ $order_shipping_address['name'] }}<br>
            City: {{ $order_shipping_address['city'] }}<br>
            Zone: {{ $order_shipping_address['province'] }}<br>
            Phone: {{ $order_shipping_address['phone'] }}<br>
            Address: {{ $order_shipping_address['address1'] }}<br>
            Cashier: {{ $auth_user }}
        </p>

        <table>

            <thead>

            </thead>

            <tbody>

            </tbody>

            <tfoot>

                <tr style="border-top: 2px solid #000 !important;border-bottom: 2px solid #000 !important;">
                    <th colspan="2">Shipping Fees</th>
                    <th style="text-align:right">{{ $return->shipping_on == 'client' ? $shipping_cost : 0 }}</th>
                </tr>
                <tr style="border-top: 2px solid #000 !important;border-bottom: 2px solid #000 !important;">
                    <th colspan="2">Total QTY</th>
                    <th style="text-align:right">{{ $return->qty }}</th>
                </tr>

                <tr style="border-top: 2px solid #000 !important;border-bottom: 2px solid #000 !important;">
                    <th colspan="2">Total Return Amount</th>
                    <th style="text-align:right">{{ $total }}</th>
                </tr>
                <tr style="border-top: 2px solid #000 !important;border-bottom: 2px solid #000 !important;">
                    <th colspan="2">Shipping On</th>
                    <th style="text-align:right">{{ ucfirst($return->shipping_on) }}</th>
                </tr>


            </tfoot>
        </table>
        <table>
            <tbody>



                <div class="centered"></div>

                <tr>
                    <td colspan="5" class="centered">

                    </td>
                </tr>

                <tr>
                    <td colspan="5" class="centered">
                        <div class="centered">

                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="centered" colspan="3">
                        <h2><i class="fa fa-headphones" aria-hidden="true"></i>+201094026877</h2>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="centered">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                        <span style="font-size: 12px">spicegirlseg.com</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <script type="text/javascript">
        function auto_print() {
            window.print();
            window.location.href = '{{ route('orders.returned') }}';
        }
        setTimeout(auto_print, 1500);
    </script>

</body>

</html>
