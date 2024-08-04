@php
    $sidebar_key = Auth::user()->getSidebarKey();
    $show_sidebar = Session::get($sidebar_key);
@endphp
<!DOCTYPE html>
<html lang="en">
@include('layouts.head')

<body>
    @include('layouts.header')
    <!-- ======= Sidebar ======= -->
    @if (auth()->user()->role_id == 2)
        getOrders
        @include('superadmin.aside')
    @elseif ($show_sidebar)
        @include('layouts.aside')
    @endif
    <main id="main" class="main">
        @if (Auth::check())
            <input type="hidden" name="user_id" id="user_id" value="{{ Auth::user()->id }}">
        @endif
        @include('layouts.success_message')
        <!-- End Sidebar-->
        @yield('content')
    </main>
    @include('layouts.footer')
    @include('layouts.scripts')
    @yield('scripts')
    <script>
        var count = 0;

        function resync() {
            $('#resync-modal').modal('show');
        }

        function warehouse() {
            $('#inventory-modal').modal('show');
        }

        function returnn(id) {

            var selected_name = $("#return_order" + id).find("option:selected").val();
            console.log(selected_name, id);

            if (selected_name == "") {
                count = count - 1;
                $("#" + id).hide();
                $("#items" + id).val("");
                if (count < 1) {

                    $("#return_order_submit").hide();
                }

            } else {
                count = count + 1;
                $("#items" + id).val(id);
                console.log(selected_name);
                $("#" + id).show();
                $("#return_order_submit").show();
            }
        }
    </script>
    @if (!$show_sidebar)
        <script>
            $(document).ready(function() {
                $('.toggle-sidebar-btn').click();
            })
        </script>
    @endif
</body>

</html>
