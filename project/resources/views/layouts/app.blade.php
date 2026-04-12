<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema Frequência Escolar - SME')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @yield('custom_css')

</head>
    <style>
        body {
            background-image: url("{{asset('images/backgroung.jpg')}}");
        }
        :root {
            --primary-color: #024f9c;
            --secondary-color: #0176bc;
            --accent-color: #009ee3;
            --yellow-id: #fdbb2d;
            --bg-light: #f4f6f9;
        }
        .btn-primary {
            background-color: #0176bc;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #024f9c;
            transform: translateY(-1px);
        }
        .btn-primary:active {
            background-color: #024f9c !important;
            transform: translateY(-1px);
        }
        .btn-outline-primary{
            border-color: #0176bc;
            color: #0176bc;
            transition: all 0.2s;
        }
        .btn-outline-primary:hover{
            background-color: #0176bc;
            border-color: #0176bc;
            transform: translateY(-1px);
        }
        .btn-outline-primary:active{
            background-color: #0176bc !important;
            border-color: #0176bc !important;
            transform: translateY(-1px);
        }
        .dropdown-item:active,
        .dropdown-item:focus {
            background-color: transparent !important;
            color: inherit !important;
        }

            /*DataTable*/
         .dataTables_wrapper .dataTables_filter {
             padding: 1rem;
         }
        .dataTables_wrapper .dataTables_info {
            padding: 1rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding: 1rem;
        }
        .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
            border: 1px solid #ddd;
            outline: none;
        }

        .page-item.active .page-link {
            background-color: #0176bc !important;
            border-color: #0176bc !important;
            color: #fff !important;
        }

        .page-link:hover {
            color: #0176bc !important;
            background-color: #f8f9fa !important;
            border-color: #dee2e6 !important;
        }

        .page-link {
            color: #212529 !important;
            box-shadow: none !important;
        }
        table.dataTable thead th {
            position: relative;
            padding-right: 20px !important;
            vertical-align: middle;
        }

    </style>
    <body style="font-family: 'Open Sans', sans-serif;">

        @if (!request()->routeIs('login'))
        <x-notifier />
            {{ $slot ?? '' }}
        @endif

        <section>
            @yield('main')
        </section>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        @yield('scripts')

        @yield('js')
    </body>
</html>
