<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SISTEMA DE PUNTO DE VENTA</title>
  <!-- Tell the browser to be responsive to screen width -->
  <link rel="shortcut icon" href="img/icono.ico">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <meta name="csrf-token" content="{{csrf_token()}}">
  <!-- Bootstrap 3.3.7 -->


  <link href="{{asset('css/bootstrap-select.min.css')}}" rel="stylesheet">

  <link rel="stylesheet" href="{{asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
  <!-- Font Awesome
   <link href="{{asset('css/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet"> -->
 <link rel="stylesheet" href="{{asset('adminlte/bower_components/font-awesome/css/font-awesome.min.css')}}">
   <link href="{{asset('css/font-awesome5/css/all.min.css')}}" rel="stylesheet">

  <link rel="stylesheet" href="{{asset('adminlte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="{{asset('adminlte/bower_components/Ionicons/css/ionicons.min.css')}}">
  <!-- jvectormap -->
  <link rel="stylesheet" href="{{asset('adminlte/bower_components/jvectormap/jquery-jvectormap.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('adminlte/dist/css/AdminLTE.min.css')}}">

  <link rel="stylesheet" href="{{asset('adminlte/dist/css/skins/_all-skins.css')}}">

  <link rel="stylesheet" href="{{asset('css/select2.min.css')}}">


  <script src="{{asset('adminlte/bower_components/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{asset('js/jqueryprint.js')}}"></script>
  <script src="{{asset('js/jquery-ui.js')}}"></script>

  <script src="{{asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>

  <!-- FastClick -->
  <script src="{{asset('adminlte/bower_components/fastclick/lib/fastclick.js')}}"></script>
  <!-- AdminLTE App -->
  <script src="{{asset('adminlte/dist/js/adminlte.min.js')}}"></script>
  <!-- Sparkline -->
  <script src="{{asset('adminlte/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js')}}"></script>
  <!-- jvectormap  -->
  <script src="{{asset('adminlte/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js')}}"></script>
  <script src="{{asset('adminlte/plugins/jvectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
  <!-- SlimScroll -->
  <script src="{{asset('adminlte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
  <!-- ChartJS -->
  <script src="{{asset('adminlte/bower_components/chart.js/Chart.js')}}"></script>
  <!-- AdminLTE dashboard demo (This is only for demo purposes) -->

  <!-- AdminLTE for demo purposes -->
  <script src="{{asset('js/jquery-ui.min.js')}}"></script>

  <script src="{{asset('js/jquery.mousewheel.js')}}" ></script>

  <script type="text/javascript" src="{{asset('js/jquery.validate.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('js/selectjquery.min.js')}}"></script>

  <script type="text/javascript" src="{{asset('js/html2canvas.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('js/select2.min.js')}}"></script>

  <style type="">
    /* To Dropdown navbar dropdown on hover */
    .navbar-nav > li:hover > .dropdown-menu {
      display: block;
    }
    .dropdown-submenu {
      position: relative;
    }

    .dropdown-submenu>.dropdown-menu {
      top: 0;
      left: 100%;
      margin-top: -6px;
      margin-left: -1px;
      -webkit-border-radius: 0 6px 6px 6px;
      -moz-border-radius: 0 6px 6px;
      border-radius: 0 6px 6px 6px;
    }

    .dropdown-submenu:hover>.dropdown-menu {
      display: block;
    }

    .dropdown-submenu>a:after {
      display: block;
      content: " ";
      float: right;
      width: 0;
      height: 0;
      border-color: transparent;
      border-style: solid;
      border-width: 5px 0 5px 5px;
      border-left-color: #ccc;
      margin-top: 5px;
      margin-right: -10px;
    }

    .dropdown-submenu:hover>a:after {
      border-left-color: #fff;
    }

    .dropdown-submenu.pull-left {
      float: none;
    }

    .dropdown-submenu.pull-left>.dropdown-menu {
      left: -100%;
      margin-left: 10px;
      -webkit-border-radius: 6px 0 6px 6px;
      -moz-border-radius: 6px 0 6px 6px;
      border-radius: 6px 0 6px 6px;
    }
  </style>

  <script>
    $(document).ready(function() {

      $("#btncompras").on("click", function() {

        var inicio = $("#fecin").val();
        var fin = $("#fecfin").val();

        $("#divcompras").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "POST",
          dataType: 'json',
          url: '/comprasdbf/'+inicio+'/'+fin,
        }).done(function(respuesta){

          window.location.href = "/SisFact";


        });

      });

      $("#btnventas").on("click", function() {

        var inicio = $("#fecin").val();
        var fin = $("#fecfin").val();

        $("#divventas").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: '/ventasdbf/'+inicio+'/'+fin,
        }).done(function(respuesta){

          window.location.href = "/SisFact";


        });

      });


         $("#btnConCar").on("click", function() {

 

         var formulario = $("#formConcar").serializeArray();
        $("#div_ven_con").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "POST",
          dataType: 'json',
          url: '/ventasconcardbf',
          data: formulario,
        }).done(function(respuesta){

          window.location.href = "/SisFact";


        });

      });


       $("#btnventasimportar").on("click", function() {

        var inicio = $("#fecin").val();
        var fin = $("#fecfin").val();

        $("#divventas1").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: '/ventasimportardbf',
        }).done(function(respuesta){

          window.location.href = "/SisFact";


        });

      });


      $("#btnclientes").on("click", function() {

        alert('asdsa');
        $("#divclientes").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: '/clientesdbf',
          data: formulario,
        }).done(function(respuesta){

          window.location.href = "/SisFact";



        });


      });



  /*   $('#listcomp').DataTable({
        "scrollY": 500,
        "scrollX": true,
    "ordering": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.105.16/i18n/Spanish.json"
        }
     })


     $('#tblpedidos').DataTable({
    "ordering": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.105.16/i18n/Spanish.json"
        }
     })

    $('#tblCompra').DataTable({

    "ordering": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.105.16/i18n/Spanish.json"
        }
      })*/

    })
  </script>

</head>
@php

$conf_conc = DB::tABLE('configuracion_concar')->first();

$inconsistencias = DB::TABLE('cpe_cabecera')
->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
->where(function ($query) {   
$query->Where('ccacodsun','>','0')
->orwhereNull('ccacodsun');
})
->where(function ($query) {
$query->where('tdocod','01')
->orWhere('tdocod','03')
->orWhere('tdocod','07')
->orWhere('tdocod','08');
})   
->where(function ($query) {
$query->where('ccacodsun','<>','8')
->whereNull('ccabaj');
})    
->get();

$enviar = DB::TABLE('cpe_cabecera')
->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
->where(function ($query) {   
$query->whereNull('ccacodsun');
})
->where(function ($query) {
$query->where('tdocod','01')
->orWhere('tdocod','03')
->orWhere('tdocod','07')
->orWhere('tdocod','08');
})    
->whereNull('ccabaj')
->where('enviado','0')
->get();

$obt_dat_emp = DB::TABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

@endphp

@include('administrador.integracion.modalclientes')
@include('administrador.integracion.modalventas')
@include('administrador.integracion.modalconcar')
@include('administrador.integracion.modalcompras')
@include('administrador.integracion.modalventasimportar')



<body class="hold-transition skin-blue sidebar sidebar-mini">
 <div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="/SisFact" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels 
      <span class="logo-mini"><b>A</b>LT</span>-->
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg" style="font-size:8pt;">{{$obt_dat_emp->NomEmpresa}}</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
      
          <!-- Notifications: style can be found in dropdown.less -->
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="far fa-bell"></i>
              <span class="label label-warning">{{count($inconsistencias)+count($enviar)}}</span>
            </a>
              <ul class="dropdown-menu">

                <li>
                  <ul class="menu">
                    <li>
                      <a href="/facturacionelectronica">
                        <i class="fa fa-warning text-yellow"></i> {{count($inconsistencias)}} Documentos Inconsistentes
                      </a>
                    </li>
                  </ul>
                </li>

                <li>
                  <ul class="menu">
                    <li>
                      <a href="/facturacionelectronica">
                        <i class="fa fa-warning text-yellow"></i> {{count($enviar)}} Documentos por Enviar
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>

          </li>
      
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" >
              <img src="" >
              <span class="hidden-xs">{{Auth::user()->name}} {{Auth::user()->apeusu}}</span>
            </a>

         <!--   <ul  class="dropdown-menu">
           
              <li class="user-header">
                
                <p>
                 {{Auth::user()->name}} {{Auth::user()->apeusu}}
                  <small>Member since Nov. 2012</small>
                </p>
              </li>
            
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center">
                    <a href="#">Followers</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Sales</a>
                  </div>
                  <div class="col-xs-4 text-center">
                    <a href="#">Friends</a>
                  </div>
                </div>
      
              </li>
         
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="#" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>-->
          </li>
            <li class="dropdown user user-menu">
            <a href="{{ route('logout') }}"><img src="/icon/salir.png" width="24px" height="24px">
              <span></span>
              <span class="pull-right-container">
                <i class=""></i>
              </span></a>

          </li>
          <!--<li>
            <a href="#" data-toggle="control-sidebar"><i class="fas fa-gears"></i></a>
          </li>-->
        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar control-sidebar control-sidebar-dark">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div  class="pull-left image">
         <img src="" class="img-circle" >
        </div>
        <div class="pull-left info">
          <p>{{Auth::user()->email}}</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      

      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MENU PRINCIPAL</li>
        <li hidden="hidden" class="treeview">
          <a href="#">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../../index.html"><i class="far fa-dot-circle"></i>  Dashboard v1</a></li>
            <li><a href="../../index2.html"><i class="far fa-dot-circle"></i>  Dashboard v2</a></li>
          </ul>
        </li>
        
        <!--<li class="treeview active">
          <a href="#">
            <i class="fa fa-files-o"></i>
            <span>Layout Options</span>
            <span class="pull-right-container">
              <span class="label label-primary pull-right">4</span>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="top-nav.html"><i class="far fa-dot-circle"></i>  Top Navigation</a></li>
            <li><a href="boxed.html"><i class="far fa-dot-circle"></i>  Boxed</a></li>
            <li><a href="fixed.html"><i class="far fa-dot-circle"></i>  Fixed</a></li>
            <li class="active"><a href="collapsed-sidebar.html"><i class="far fa-dot-circle"></i>  Collapsed Sidebar</a>
            </li>
          </ul>
        </li>-->

   
         <!--   <li><a href="/consola"><i class="fas fa-utensils"></i> <span>Comandas</span></a></li>
            <li><a href="/pedidos"><i class="fa fa-shopping-cart"></i> <span>Pedidos</span></a></li>-->
            <li><a href="/pos"><i class="fa fa-cash-register"></i> <span>Punto Venta</span></a></li>
                 <li><a href="/vrapida"><i class="fa fa-cash-register"></i> <span>Venta Rápida</span></a></li>
          

        <li class="treeview">
          <a href="#">
            <i class="fas fa-chart-line"></i>
            <span>Ventas</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              <li><a href="/SisFact"><i class="far fa-dot-circle"></i> Panel Ventas</a></li>
              <li><a href="/indexcomandas"><i class="far fa-dot-circle"></i> Comandas</a></li>
              <li><a href="/indexpedidos"><i class="far fa-dot-circle"></i> Pedidos</a></li>
              <li><a href="/indexcotizacion"><i class="far fa-dot-circle"></i> Proformas</a></li>
              <li><a href="/guiasremision"><i class="far fa-dot-circle"></i> Guías Remisión</a></li>
              
              <li class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                   <li><a href="/arqueodiario"><i class="far fa-dot-circle"></i> Arqueo de Caja</a></li>
                      <li class="divider"></li>
                      <li><a href="/reportes/1"><i class="far fa-dot-circle"></i> Ventas</a></li>
                      <li><a href="/reportes/2"><i class="far fa-dot-circle"></i> Ventas por Vendedor</a></li>
                      <li><a href="/reportes/3"><i class="far fa-dot-circle"></i> Ventas por Cliente</a></li>
                      <li class="divider"></li>
                      <li><a href="/reportes/4"><i class="far fa-dot-circle"></i> Proformas</a></li>
                      <li><a href="/reportes/5"><i class="far fa-dot-circle"></i> Pedidos</a></li>
                      <li class="divider"></li> 
                      <li><a href="/reportes/6"><i class="far fa-dot-circle"></i> Ventas por Producto</a></li>
                     <!-- <li><a href="/reportes/20"><i class="far fa-dot-circle"></i> Resumen Ventas </a></li>-->
                      <li><a href="/reportes/7"><i class="far fa-dot-circle"></i> Productos (+/-) Vendidos</a></li>
                      <li><a href="/reportes/8"><i class="far fa-dot-circle"></i> Rentabilidad</a></li>
                      <li class="divider"></li> 
                      <li><a href="/reportes/10"><i class="far fa-dot-circle"></i> Comisiones-Vendedor</a></li>
                      <li><a href="/reportes/9"><i class="far fa-dot-circle"></i> Comisiones-Producto</a></li>
                      <li class="divider"></li> 
                      <li><a href="/reportecuentas"><i class="far fa-dot-circle"></i> Cuentas por Cobrar</a></li>
                      <li class="divider"></li>
                      <li hidden="hidden"><a href="/reportealbergues">Reportes Albergues</a></li>
                </ul>
              </li>
          </ul>
        </li>

          <li class="treeview">
          <a href="#">
            <i class="fas fas fa-coins"></i>
            <span>Cuentas Cobrar</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              
               <li><a href="/cuentascobrar"><i class="far fa-dot-circle"></i> Cuentas Cobrar</a></li>
               <li class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
               
                      <li><a href="/reportecuentas"><i class="far fa-dot-circle"></i> Cuentas por Cobrar</a></li>
                
                </ul>
              </li>

              
          </ul>
        </li>

         <li class="treeview">
          <a href="#">
            <i class="fas fa-cart-arrow-down"></i>
            <span>Compras</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
               @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
               <li><a href="/compras"><i class="far fa-dot-circle"></i> Compras</a></li>
               <li><a href="/ordenescompra"><i class="far fa-dot-circle"></i> Ordenes Compra</a></li>
               <li><a href="/gastos"><i class="far fa-dot-circle"></i> Gastos / Ingresos</a></li>
               @endif
               <li><a href="/ordenesservicios"><i class="far fa-dot-circle"></i> Ordenes Servicios</a></li>
             
              
       
              
              <li class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">

                  <li><a href="/reportes/11"><i></i>Compras</a></li>
                  <li><a href="/reportes/12"><i></i>Compras por Proveedor</a></li>
                  <li><a href="/reportes/13"><i></i>Compras por Productos</a></li>
                  <li><a href="/reportes/14"><i></i>Gastos</a></li>
                
                </ul>
              </li>
          </ul>
        </li>
           <li class="treeview">
          <a href="#">
            <i class="fas fas fa-coins"></i>
            <span>Cuentas Pagar</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              
               <li><a href="/cuentaspagar"><i class="fas fa-hand-holding-usd"></i> Cuentas Pagar</a></li>
               <li hidden="hidden" class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
               
                      <li><a href="/reportecuentas"><i class="far fa-dot-circle"></i> Cuentas por Cobrar</a></li>
                
                </ul>
              </li>

              
          </ul>
        </li>
          <li class="treeview">
          <a href="#">
            <i class="fas fa-warehouse"></i> 
            <span> Almacén</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
             
               @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )
                <li class=""><a href="/almacen/listaralmacenes"><i class="far fa-dot-circle"></i>  Almacenes</a></li>
               <li><a href="/inventarios"><i class="far fa-dot-circle"></i> Inventarios</a></li>
               <li><a href="/transferencias"><i class="far fa-dot-circle"></i> Transferencias</a></li>
                <li><a href="/salidas"><i class="far fa-dot-circle"></i> Salidas Productos</a></li>
                 <li><a href="/ingresos"><i class="far fa-dot-circle"></i> Ingresos Productos</a></li> 
               <li hidden="hidden"><a href="/salidasproductos"><i class="far fa-dot-circle"></i> Salidas Productos</a></li> 
               <li hidden="hidden"><a href="/ingresosproductos"><i class="far fa-dot-circle"></i> Ingresos Productos</a></li> 
               <li><a href="/salidas"><i class="far fa-dot-circle"></i> Salidas Productos - Areas</a></li>

             
               @endif
              
              <li class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                   <li><a href="/reporteinventario"><i></i>Inventario</a></li>
                  <li><a href="/kardex"><i></i>Kardex</a></li>
                  <li><a href="/stockproductos"><i></i>Stock Productos</a></li>
                </ul>
              </li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fas fa-money-bill-alt"></i>
            <span>Control Caja</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              
               <li><a href="/caja"><i class="far fa-dot-circle"></i> Listar Cajas</a></li>
              
          </ul>
        </li>

            <li class="treeview">
          <a href="#">
            <i class="fas fa-cloud-upload-alt"></i>
            <span>SUNAT</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              
              <li class=""><a href="/facturacionelectronica"><i class="far fa-dot-circle"></i>  Envío de Comprobantes</a></li>

                  <li class=""><a href="/listarresumenes"><i class="far fa-dot-circle"></i>  Resumen Diario</a></li>

              
          </ul>
        </li>
             <li class="treeview">
          <a href="#">
            <i class="fas fas fa-users-cog"></i>
            <span>Contactos</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              
             
                 <li class=""><a href="{{route('clientes.index') }}"><i class="far fa-dot-circle" aria-hidden="true"></i> Clientes</a></li>
                 <li class=""><a href="{{route('proveedor.index') }}"><i class="far fa-dot-circle"></i>  Proveedores</a></li>
                 <li class=""><a href="/administrador/usuarios"><i class="far fa-dot-circle"></i> Usuarios</a></li>

              
          </ul>
        </li>

          <li class="treeview">
          <a href="#">
            <i class="fas fa-toolbox"></i>
            <span>Mantenimiento</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              
              @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                  <li class=""><a href="/administrador/empresas"><i class="far fa-dot-circle"></i> Empresas</a></li>
                  <li class=""><a href="/negocios"><i class="far fa-dot-circle"></i>  Sucursales</a></li>
                 <li class=""><a href="/confconcar"><i class="far fa-dot-circle"></i>  Parámetros Concar</a></li>
                 <li class=""><a href="/areas"><i class="far fa-dot-circle"></i>  Areas</a></li>
                  <li class=""><a href="/habitaciones"><i class="far fa-dot-circle"></i>  Habitaciones</a></li>
                  
                  @endif



                  <li hidden="hidden" class=""><a href="{{route('tecnicos.index') }}"><i class="far fa-dot-circle"></i> T&eacute;cnicos</a></li>
                  @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                  <!--<li class="divider"></li>
                 <li class=""><a href="/procesos"><i></i> Procesos</a></li>
                 <li class=""><a href="/maquinas"><i></i> Máquinas</a></li>-->
                 
                 <li class=""><a href="/tipocambio"><i class="far fa-dot-circle"></i>  Tipo Cambio</a></li>
                 
                
            
               <!--  
                 <li class=""><a href="/bancos"><i class="far fa-dot-circle"></i> Bancos</a></li>  
                 <li class=""><a href="/tiposdocumentos"><i class="far fa-dot-circle"></i>  Documentos Caja Banco</a></li>  -->
                 <li class=""><a href="{{route('mediospagos.index') }}"><i class="far fa-dot-circle"></i>  Medios de Pago</a></li> 
              <!--   <li class="" hidden="hidden"><a href="/tiposcaja"><i class="far fa-dot-circle"></i>  Tipos Caja</a></li> -->  
               
                 <li hidden="hidden" class=""><a href="{{route('programas.index') }}"><i class="far fa-dot-circle"></i>  Programas</a></li>
                 <li class=""><a href="{{route('categorias.index') }}"><i class="far fa-dot-circle"></i>  Familias</a></li>
                 <li class=""><a href="{{route('subcategorias.index') }}"><i class="far fa-dot-circle"></i>  Subfamilias</a></li>
                 <li class=""><a href="{{route('tipoproducto.index') }}"><i class="far fa-dot-circle"></i>  Tipo Productos</a></li>
                 @endif
                 <li class=""><a href="{{route('marcas.index') }}"><i class="far fa-dot-circle"></i>  Marcas</a></li>
                 <li class=""><a href="{{route('modelos.index') }}"><i class="far fa-dot-circle"></i>  Modelos</a></li>


                 <li class=""><a href="{{route('productos.index') }}"><i class="far fa-dot-circle"></i>  Productos</a></li>
                  <li class=""><a href="{{route('tipoequipo.index') }}"><i class="far fa-dot-circle"></i>  Tipo Equipos</a></li>
                <li class=""><a href="{{route('equipos.index') }}"><i class="far fa-dot-circle"></i>  Equipos</a></li>
                 <li class=""><a href="{{route('repuestos.index') }}"><i class="far fa-dot-circle"></i>  Repuestos</a></li>
                 <li class=""><a href="{{route('servicios.index') }}"><i class="far fa-dot-circle"></i>  Servicios</a></li>
                 <li hidden="hidden" class="divider"></li>
                 <li hidden="hidden" class=""><a href="{{route('tiposvehiculos.index') }}"><i class="far fa-dot-circle"></i>  Tipos Vehiculos</a></li>
                 <li hidden="hidden" class=""><a href="/tarifas"><i class="far fa-dot-circle"></i>  Tarifas</a></li>
                 

                 
            
                 @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                 <li class=""><a href="{{route('tipogastos.index') }}"><i class="far fa-dot-circle"></i>  Tipo Gastos / Ingresos</a></li>

                 @endif    
              
          </ul>
        </li>

            @if(Auth::user()->hasRole('recepcion') || Auth::user()->hasRole('admin') )
         <li class="treeview">
          <a href="#">
            <i class="fas fa-chart-line"></i>
            <span>Recepci&oacute;n</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">

              <li><a href="/ordenescompu"><i class="far fa-dot-circle"></i> Recepcionar</a></li>
              <li><a href="/equiposentregarcompu"><i class="far fa-dot-circle"></i> Equipos por Entregar</a></li>
              <li><a href="/equiposentregadoscompu"><i class="far fa-dot-circle"></i> Equipos Entregados</a></li>

           
          <!--<li><a href="/ordeneselectro"><i class="far fa-dot-circle"></i> Recepcionar</a></li>
              <li><a href="/equiposentregar"><i class="far fa-dot-circle"></i> Equipos por Entregar</a></li>
              <li><a href="/equiposentregados"><i class="far fa-dot-circle"></i> Equipos Entregados</a></li>
              <li class=""><a href="{{route('clientes.index') }}"><i class="far fa-dot-circle" aria-hidden="true"></i> Clientes</a></li>-->


          </ul>
        </li>
       @endif

        @if(Auth::user()->hasRole('supervisor') || Auth::user()->hasRole('admin') )
          <li class="treeview">
          <a href="#">
            <i class="fas fa-chart-line"></i>
            <span>Supervisor</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">

            
               <!-- <li><a href="/ordenessuper"><i class="far fa-dot-circle"></i> Asignar Coordinador</a></li>-->
          
              <!-- <li><a href="/ordenessuper"><i class="far fa-dot-circle"></i> Recepcionar Supervisor</a></li>-->
   
                  <li><a href="/ordenesteccompu"><i class="far fa-dot-circle"></i> Asignar Técnico</a></li>

                     <li><a href="/ordenesrateccompu"><i class="far fa-dot-circle"></i> Reasignar Técnico</a></li>
                 
              


              <!-- <li><a href="/ordenessuper"><i class="far fa-dot-circle"></i> Recepcionar Supervisor</a></li>
   
                  <li><a href="/ordenestec"><i class="far fa-dot-circle"></i> Asignar Técnico</a></li>

                     <li><a href="/ordenesratec"><i class="far fa-dot-circle"></i> Reasignar Técnico</a></li>
                 
              <li><a href="/ordenesaprobadas"><i class="far fa-dot-circle"></i> Ordenes Aprobadas CC</a></li>
       
              <li><a href="/ordenesobservadas"><i class="far fa-dot-circle"></i> Ordenes Observadas CC</a></li>
       -->

          </ul>
        </li>
           @endif

          @if(Auth::user()->hasRole('tecnico') || Auth::user()->hasRole('admin') )
            <li class="treeview">
            <a href="#">
              <i class="fas fa-chart-line"></i>
              <span>T&eacute;cnico</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">  
             <li><a href="/atencionotcompu"><i class="far fa-dot-circle"></i> Atenci&oacute;n</a></li> 
                <!--<li><a href="/atencionot"><i class="far fa-dot-circle"></i> Atenci&oacute;n</a></li>-->
            </ul>
          </li>
          @endif

           @if(Auth::user()->hasRole('calidad') || Auth::user()->hasRole('admin')  || Auth::user()->hasRole('supervisor') )

             <li class="treeview">
          <a href="#">
            <i class="fas fa-chart-line"></i>
            <span>Control de Calidad</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              <li><a href="/atencioncc"><i class="far fa-dot-circle"></i> Control Calidad</a></li>
              <li><a href="/ordenesaprobadas"><i class="far fa-dot-circle"></i> Ordenes Aprobadas CC</a></li>    
              <li><a href="/ordenesobservadas"><i class="far fa-dot-circle"></i> Ordenes Observadas CC</a></li>
            
          </ul>
        </li>
         @endif

          <li><a href="/consultarordenes"><i class="fa fa-file"></i><span>Consultar Ordenes</span></a></li>

            @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))

              <li class="treeview">
            <a href="#">
              <i class="fas fa-chart-line"></i>
              <span>Integración</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">  

             <li><a href="" data-target="#modal-ventas" data-toggle="modal"><i class="far fa-dot-circle"></i> Migrar Ventas</a></li> 
                <li><a href="" data-target="#modal-compras" data-toggle="modal"><i class="far fa-dot-circle"></i> Migrar Compras</a></li> 
                 <li><a href="" data-target="#modal-clientes" data-toggle="modal"><i class="far fa-dot-circle"></i> Migrar Clientes - Proveedores</a></li> 
                  <li><a href="" data-target="#modal-concar" data-toggle="modal"><i class="far fa-dot-circle"></i> Migrar Concar</a></li> 
          </li>


              @endif


        <!--  <li class="treeview">
          <a href="#">
            <i class="fas fa-toolbox"></i>
            <span>REST-BAR</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
                @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                 <li class=""><a href="{{route('pisos.index') }}"><i class="far fa-dot-circle"></i>  Pisos</a></li>
                 <li  class=""><a href="{{route('mesa.index') }}"><i class="far fa-dot-circle"></i>  Mesas</a></li>
                 @endif    
              
          </ul>
        </li>-->

        <!-- <li class="treeview">
          <a href="#">
            <i class="fas fa-toolbox"></i>
            <span>Farmacias</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
                @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                 <li class=""><a href="{{route('laboratorio.index') }}"><i class="far fa-dot-circle"></i>  Laboratorio</a></li>
                 <li class=""><a href="{{route('tiposmedicamentos.index') }}"><i class="far fa-dot-circle"></i>  Tipos Medicamentos</a></li>
                  <li class=""><a href="{{route('principioactivo.index') }}"><i class="far fa-dot-circle"></i>  Principios Activos</a></li>
             
                 @endif    
              
          </ul>
        </li>-->



    
     
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- =============================================== -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    

   @yield('contenido')

  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer" style="display:none;">
  
    <strong><a href="">ELECTRONIC SERVIS 2021 - SISTEMA DE GESTION COMERCIAL</a></strong>
  </footer>

</div>

 <script>



  $("#comp").autocomplete({
    source: '{!!URL::route('buscarcomprobantelista')!!}',
    dataType: "json",
    minLength: 6,
    autoFocus:true,
    select: function(event,ui) {   }
  })


  $("#serdocmod").autocomplete({
    source: '{!!URL::route('buscarcomprobante')!!}',
    dataType: "json",
    minLength: 3,
    autoFocus:true,
    select: function(event,ui) {
      $('#numdocmod').val(ui.item.numdoc);
      $('#clinum').val(ui.item.clinum);
      $('#clinom').val(ui.item.clinom);
      $('#clidir').val(ui.item.clidir);
      $('#clicor').val(ui.item.clicor);
      $('#tdomod').val(ui.item.tdomod);
      $('#tdides').val(ui.item.tdides);
      $('#mondoc').val(ui.item.monnom);
      $('#camdoc').val(ui.item.tipcambio);
      $('#topdes').val(ui.item.topdes);
      $('#tdicod').val(ui.item.tdicod);
      $('#tdo_cod').val(ui.item.tdocod);
      $('#tipmon').val(ui.item.moncod);
      $('#tdo_cod').val(ui.item.tdocod);
      $('#idcabecera').val(ui.item.idcabecera);

    }
  })

  $("#compbaja").autocomplete({
    source: '{!!URL::route('buscardocumentosbajas')!!}',
    dataType: "json",
    minLength: 6,
    autoFocus:true,
    select: function(event,ui) {
    }
  })

  $("#fecEmi").autocomplete({
    source: '{!!URL::route('consultarcambio')!!}',
    dataType: "json",
    minLength: 3,
    autoFocus:true,
    select: function(event,ui) {

      if($("#mondoc").val()=='1'){

       $('#camdoc').val(0);
     } else {
      $('#camdoc').val(ui.item.cam);
    }

  }
})


  $(document).ready(function()
  {
    var comprobante = $("#comprobante").val();
    if(comprobante != '0'){

      $('#btnPrint').click();
    }


  });

</script>
<script>
  $("#btnExport").click(function(e) {
    var tab_text="<table border='2px'><tr bgcolor='#87AFC6'>";
    var textRange; var j=0;
    tab = document.getElementById('dtHorizontalExample'); // id of table

    for(j = 0 ; j < tab.rows.length ; j++) 
    {     
      tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
        //tab_text=tab_text+"</tr>";
      }

      tab_text=tab_text+"</table>";
    tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
    tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
    tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE "); 

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
    {
      txtArea1.document.open("txt/html","replace");
      txtArea1.document.write(tab_text);
      txtArea1.document.close();
      txtArea1.focus(); 
      sa=txtArea1.document.execCommand("SaveAs",true,"Say Thanks to Sumit.xls");
    }  
    else                 //other browser not tested on IE 11
      sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));  

    return (sa);
  });

  $("body").on("click", "#btnExportar", function () {
    html2canvas($('#dtHorizontalExample')[0], {
      onrendered: function (canvas) {
        var data = canvas.toDataURL();
        var docDefinition = {
          content: [{
            image: data,
            width: 500
          }]
        };
        pdfMake.createPdf(docDefinition).download("cuentasporcobrar.pdf");
      }
    });
  });
</script>


</body>
</html>
