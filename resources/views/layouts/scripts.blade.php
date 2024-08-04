<!-- Vendor JS Files -->
@php $version = time(); @endphp
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}?v={{ $version }}"></script>
<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}?v={{ $version }}"></script>
<script src="{{ asset('assets/vendor/chart.js/chart.min.js') }}?v={{ $version }}"></script>
<script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}?v={{ $version }}"></script>
<script src="{{ asset('assets/vendor/quill/quill.min.js') }}?v={{ $version }}"></script>
{{-- <script src="{{asset('assets/vendor/simple-datatables/simple-datatables.js')}}?v={{$version}}"></script> --}}
<script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}?v={{ $version }}"></script>
<script src="{{ asset('assets/vendor/php-email-form/validate.js') }}?v={{ $version }}"></script>
<!-- Template Main JS File -->
<script src="{{ asset('assets/js/main.js') }}"></script>
<script type="text/javascript" src="<?php echo asset('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo asset('assets/vendor/jquery/jquery-ui.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo asset('assets/vendor/jquery/bootstrap-datepicker.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo asset('assets/vendor/jquery/jquery.timepicker.min.js'); ?>"></script>
<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
<!-- Include Moment.js -->
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<!-- Include Date Range Picker JS -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>


<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

<!-- Socket IO Script. Enable it if you want it.-->
<!-- <script src="https://cdn.socket.io/4.0.1/socket.io.min.js?v={{ $version }}"></script> -->
<!-- <script src="{{ asset('assets/js/socketio_script.js') }}?v={{ $version }}"></script> -->
