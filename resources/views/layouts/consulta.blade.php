<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

     <title>Sistema de Facturación Electrónica</title>
     <link rel="shortcut icon" href="img/icono.ico">

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
      <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
       <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <style>
    .card-header-laravel{
        background:transparent;
        border:transparent;
    }
    .card-laravel{
       border:transparent;
        width: 50%;
    }

    .help-block {
      color:red;
    }
</style>
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
               <strong> <font color="#42aaf4"></font><font color=""></font><font color="#50f441"></font><font color=""> CONSULTA DE COMPROBANTES - INVOICE F&Aacute;CIL</font></strong>
            </a>
        </div>
    </nav>
    <main class="py-4">
        @yield('content')
    </main>



</body>
</html>
