<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>


        <title>{{ config('app.name') }} | @yield('title', $page_title ?? '')</title>


        <meta name="description" content="@yield('page_description', $page_description ?? '')"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>


        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">

        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css"
              integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
        <link href="{{ asset('css/dashboard/style.bundle.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/dashboard/light.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/dashboard/header/light.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/dashboard/menu/light.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/dashboard/aside/light.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ asset('css/dashboard/datatables.bundle.css') }}" rel="stylesheet" type="text/css"/>
        <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.min.css"
              rel="stylesheet" type="text/css"/>
        @yield('style')
    </head>

    <body class="page-loading">

        <div class="page-loader page-loader-logo">
            <img alt="Laravel" src="https://www1.v12software.com/wp-content/uploads/LOGO_V12-1.png" width="100"/>
            <div class="spinner spinner-primary"></div>
        </div>

        <div class="d-flex flex-column flex-root">
            <div class="d-flex flex-row flex-column-fluid page">
                @include('partials.dashboard.aside')
                <div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">
                    @include('partials.dashboard.header')
                    @yield('content')
                    @include('partials.dashboard.footer')
                </div>
            </div>
        </div>
        <script src="//code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"
                integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn"
        crossorigin="anonymous"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"
        type="text/javascript"></script>
        <script src="{{ asset('js/plugins.bundle.js') }}"></script>
        <script src="{{ asset('js/prismjs.bundle.js') }}"></script>
        <script src="{{ asset('js/scripts.bundle.js') }}"></script>
        <script>
window.onload = function () {
    var loader = document.getElementsByClassName("page-loader");
    loader[0].style.display = 'none';
}
$(function () {
    $('#dateStart, #dateEnd').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
});
        </script>
    </body>
</html>
