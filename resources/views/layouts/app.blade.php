<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SISTEMA DE PUNTO DE VENTA</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">


    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
 
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{asset('js/jquery.keyboard.js')}}" ></script>
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
  .navbar-brand {
   
    width: 100%;
    left: 0;
    top: 0;
    text-align: center;
    margin: auto;
}
.navbar-toggle {
    z-index:3;
}

.hero-image {
  background-image: url("fondo1.jpg");
  background-color: #cccccc;
  background-position: center;
  background-repeat: no-repeat;
  background-size: 100% 100%;
  height:820px;
  position: relative;
}

</style>
@php

  $sucursales = DB::TABLE('empresa_negocios')->get();
  $terminales = DB::TABLE('configuracion_impresoras')->get();
@endphp
<style>
    .card-header-laravel{
        background:transparent;
        border:transparent;
    }
    .card-laravel{
       border:transparent;
        width: 50%;
    }
    

</style>
 <script>
        $( document ).ready(function() {

            var email = $("#email").val();
               $.ajax({
                 type: "get",
                 url:"/consultarempresas",
                 data:{email:email}, 
                 success: function(data){
                  $.each(data,function(key, registro) {
                    $("#IdEmpresa").append('<option value='+registro.id+'>'+registro.text+'</option>');
                  });        
                },
             }) 
          
          $('#email').on('change',function (){
               var email = $("#email").val();
               $("#IdEmpresa option").remove();
               $.ajax({
                 type: "get",
                 url:"/consultarempresas",
                 data:{email:email}, 
                 success: function(data){
                  $.each(data,function(key, registro) {
                    $("#IdEmpresa").append('<option value='+registro.id+'>'+registro.text+'</option>');
                  });        
                },
            })  
           })


        
     
    
});


    </script>

</head>

<body class="hero-image">

  <!--<nav class="navbar navbar-light bg-light navbar-brand-center">
        <div class="container">
     
            <a style="font-family:'Emblema One';font-size: 22px;" class="navbar-brand navbar-brand-center" href=""><strong>WILCAT SYSTEMS</strong></a>
              
              
        </div>
    </nav>-->
<br><br>
    <div class="container-fluid">

        @yield('content')
    

</div>

</body>
</html>
