<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ 'System POS' }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/bootstrap/css/bootstrap.min.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/bootstrap-toggle/css/bootstrap-toggle.min.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/bootstrap/css/bootstrap-datepicker.min.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/jquery-timepicker/jquery.timepicker.min.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/bootstrap/css/awesome-bootstrap-checkbox.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/bootstrap/css/bootstrap-select.min.css'); ?>" type="text/css">
    <!-- Font Awesome CSS-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Drip icon font-->
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/dripicons/webfont.css'); ?>" type="text/css">
    <!-- Google fonts - Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,500,700">
    <!-- jQuery Circle-->
    <link rel="stylesheet" href="<?php echo asset('assets2/css/grasp_mobile_progress_circle-1.0.0.min.css'); ?>" type="text/css">
    <!-- Custom Scrollbar-->
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css'); ?>" type="text/css">
    <!-- virtual keybord stylesheet-->
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/keyboard/css/keyboard.css'); ?>" type="text/css">
    <!-- date range stylesheet-->
    <link rel="stylesheet" href="<?php echo asset('assets2/vendor/daterange/css/daterangepicker.min.css'); ?>" type="text/css">
    <!-- table sorter stylesheet-->
    <link rel="stylesheet" type="text/css" href="<?php echo asset('assets2/vendor/datatable/dataTables.bootstrap4.min.css'); ?>">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/fixedheader/3.1.6/css/fixedHeader.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo asset('assets2/css/style.default.css'); ?>" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('assets2/css/dropzone.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets2/css/style.css'); ?>">
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->

    <script type="text/javascript" src="<?php echo asset('assets2/vendor/jquery/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/jquery/jquery-ui.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/jquery/bootstrap-datepicker.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/jquery/jquery.timepicker.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/popper.js/umd/popper.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/bootstrap/js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/bootstrap-toggle/js/bootstrap-toggle.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/bootstrap/js/bootstrap-select.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/keyboard/js/jquery.keyboard.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/keyboard/js/jquery.keyboard.extension-autocomplete.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/js/grasp_mobile_progress_circle-1.0.0.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/jquery.cookie/jquery.cookie.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/chart.js/Chart.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/jquery-validation/jquery.validate.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/js/charts-custom.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/js/front.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/daterange/js/moment.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/daterange/js/knockout-3.4.2.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/daterange/js/daterangepicker.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/tinymce/js/tinymce/tinymce.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/js/dropzone.js'); ?>"></script>

    <!-- table sorter js-->
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/pdfmake.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/vfs_fonts.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/jquery.dataTables.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/dataTables.bootstrap4.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/dataTables.buttons.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/buttons.bootstrap4.min.js'); ?>">
        ">
    </script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/buttons.colVis.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/buttons.html5.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/buttons.print.min.js'); ?>"></script>

    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/sum().js'); ?>"></script>
    <script type="text/javascript" src="<?php echo asset('assets2/vendor/datatable/dataTables.checkboxes.min.js'); ?>"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js">
    </script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js">
    </script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js">
    </script>
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="<?php echo asset('assets2/css/custom-default.css'); ?>" type="text/css" id="custom-style">
</head>

<body onload="myFunction()">
    <div id="loader"></div>
    <div class="pos-page">

        <div style="display:none;" id="content" class="animate-bottom">
            @yield('content')
        </div>
    </div>
    @yield('scripts')
    <script type="text/javascript">
        function myFunction() {
            setTimeout(showPage, 150);
        }

        function showPage() {
            document.getElementById("loader").style.display = "none";
            document.getElementById("content").style.display = "block";
        }



        $("div.alert").delay(3000).slideUp(750);
        $('select').selectpicker({
            style: 'btn-link',
        });
    </script>
</body>

</html>
