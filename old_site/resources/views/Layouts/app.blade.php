<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title> @yield("title") | {{ config("app.name") }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets') }}/static/logo.png">
    <!-- CSS files -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield("title") | {{ config("app.name") }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets') }}/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets') }}/dist/css/adminlte.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets') }}/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('assets') }}/datatables-buttons/css/buttons.bootstrap4.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        @import url('https://rsms.me/inter/inter.css');

        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }

        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        .select2-container--bootstrap-5 .select2-selection {
            font-size: unset !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option {
            font-size: unset !important;
        }

        input[readonly] {
            background-color: #eee;
        }

        .modal table td {
            padding: 0.20rem !important;
        }

        table td {
            white-space: nowrap !important;
            /* padding: 4px !important; */
            padding: .40rem;
        }

        .page-header {
            /* display: none !important; */
        }

        .select2-selection__clear {
            display: none !important;
        }


        .symbolcontainer {
        display: flex;
        justify-content: right;
        gap: 20px;
        align-items: right;

        background-color: #f4f4f4;
    }

    .symbol {
        width: 15px;
        /* height: 60px; */
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        border-radius: 50%;
        background-color: #3498db;
        /* Single color for all symbols */
        color: white;
        font-size: 18px;
        font-weight: bold;
        text-align: center;
    }

    .symbol::before {
        content: attr(data-inner);
        /* Displays the number inside */
        width: 30px;
        height: 30px;
        border-radius: 50%;
        position: absolute;
        color: black;
        font-size: 14px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    ::-webkit-scrollbar {
  width: 8px !important;
  height: 8px !important;
  background: #ededed !important;
}

::-webkit-scrollbar-thumb {
  border: 0 !important;
  background-color: #dc3545c4 !important;
  border-radius: 12px !important;
}
.page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}
.modal-header{
background-color: #dc3545;
    color: white;
}
    </style>
    @stack("styles")
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- <div class="page" style="background: url('{{ asset('assets') }}/dist/img/vivid.jpg') no-repeat center center fixed; background-size: cover;"> -->
        @include("Layouts.parts.header")
        @include("Layouts.parts.navbar")
        @include("Parts::flash-message")
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    @yield("header")
                </div>
            </section>
            <x-loader />
            <section class="content">
                <div class="container-fluid">
                    @yield("content")
                </div>
            </section>
        </div>
        @include("Layouts.parts.footer")
    </div>
    <!-- Tabler Core -->
    <!-- jQuery -->
    <script src="{{ asset('assets') }}/jquery/jquery.min.js"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('assets') }}/dist/js/adminlte.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets') }}/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/_form.js') }}"></script>
    <script src="{{ asset('js/_condition.js') }}"></script>
    <!-- DataTables & Plugins -->
    <script src="{{ asset('assets') }}/datatables/jquery.dataTables.min.js"></script>
    <script src="{{ asset('assets') }}/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- <script src="{{ asset('assets') }}/datatables-responsive/js/dataTables.responsive.min.js"></script> -->
    <script src="{{ asset('assets') }}/datatables-buttons/js/dataTables.buttons.min.js"></script>

    <script src="{{ asset('assets') }}/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="{{ asset('assets') }}/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="{{ asset('assets') }}/jszip/jszip.min.js"></script>
    <script src="{{ asset('assets') }}/pdfmake/pdfmake.min.js"></script>
    <script src="{{ asset('assets') }}/pdfmake/vfs_fonts.js"></script>
    <script src="{{ asset('assets') }}/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="{{ asset('assets') }}/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="{{ asset('assets') }}/datatables-buttons/js/buttons.colVis.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- {{-- Sweet Alert --}} -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch@3.4.0/dist/js/bootstrap-switch.min.js"></script>


    <script>
        $(document).ready(function() {
            $('.dropdown').hover(
                function() {
                    $(this).find('.dropdown-menu').eq(0).addClass('show');
                },
                function() {
                    $(this).find('.dropdown-menu').removeClass('show');
                }
            );
        })
    </script>
    @stack("javascript")

</body>

</html>
