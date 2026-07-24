<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SISTEMA DE GESTIÓN COMERCIAL - WILCAT SYSTEMS E.I.R.L.</title>
  <!-- Tell the browser to be responsive to screen width -->
  <link rel="shortcut icon" href="img/wilcat.ico">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <meta name="csrf-token" content="{{csrf_token()}}">
  <!-- Bootstrap 3.3.7 -->
 

  <link href="{{asset('css/bootstrap-select.min.css')}}" rel="stylesheet">

  <link rel="stylesheet" href="{{asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('adminlte/bower_components/font-awesome/css/font-awesome.min.css')}}">

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
            type: "GET",
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
@include('administrador.integracion.modalclientes')
@include('administrador.integracion.modalventas')
@include('administrador.integracion.modalcompras')
@include('administrador.integracion.modalventasimportar')

@php
 
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


@endphp
<body class="skin-blue-light layout-top-nav">
<div class="wrapper">


        <header class="main-header">
        <nav class="navbar navbar-static-top">
          <div class="container-fluid">
          <div class="navbar-header">
            <a href="/pos" class="navbar-brand"><b> </b></a>
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
              <i class="fa fa-bars"></i>
            </button>
          </div>

          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav">
             
         <!--    <li><a href="/dashboard"><i ></i>DASHBOARD</a></li>-->

        <!--  <li ><a href="/ingresovehiculo"><i ></i>VALETPARKING</a></li>-->

        <!--<li><a href="/mesas"><i ></i>REST-BAR</a></li>-->

            @if(Auth::User()->hasRole('caja') ||    Auth::User()->hasRole('vendedor') ||    Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))

            <!--<li><a href="/listarpedidoalbergue"><i></i>PEDIDOS ALBERGUES</a></li>-->
            <!--<li><a href="/pedidos"><i></i>PEDIDOS</a></li>-->
          @if(Auth::User()->hasRole('caja') ||  Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))
            
          <li><a href="/pos"><i></i>PV</a></li>
          <!--<li><a href="/operaciones">CONTROL DE TRABAJO</a></li>-->
         <!--<li><a href="/pvgrifo"><i></i>GRIFOS</a></li>-->
          <!-- <li><a href="/listarpedidos"><i></i>ALBERGUES</a></li>-->
        
                        @endif
            @endif

          

            <!-- <li><a href="/equiposreparacion"><i></i>EQUIPOS EN REPARACIÓN</a></li>

              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">ORDENES DE SERVICIOS <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="/ordenes"><i></i>Administrar Ordenes</a></li>
                  <li><a href="/historialordenes"><i></i>Historial de Ordenes</a></li>
                  <li><a href="/ordenesclientes"><i></i>Ordenes de Clientes</a></li>
                </ul>
              </li>-->

           

             
 @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') ||    Auth::User()->hasRole('caja') )
            
               
               <!-- <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">TALLER <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                   <li><a href="/indexcotizaciones"><i ></i>COTIZACION / OT / OP</a></li> 
                    <li class=""><a href="{{route('tiposvehiculos.index') }}"><i></i>Ficha Vehiculos</a></li> 
               
                </ul>
              </li>-->


                <li class="dropdown">

                <a href="#" class="dropdown-toggle" data-toggle="dropdown">VENTAS<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
				  @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') ||    Auth::User()->hasRole('caja') )
                  <li><a href="/SisFact"><i></i>PANEL DE VENTAS</a></li>
			  @endif


               

                   <li><a href="/indexpedidos"><i></i>PEDIDOS</a></li>
                   <li><a href="/contingencia"><i></i>CONTINGENCIA</a></li>

           
                  @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') ||    Auth::User()->hasRole('vendedor')  ||    Auth::User()->hasRole('caja'))

                  <li><a href="/indexcotizacion"><i></i>PROFORMAS</a></li>
                  @endif
                  
                  @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') ||    Auth::User()->hasRole('caja') )
                  <li><a href="/cuentascobrar"><i></i>CUENTAS COBRAR</a></li>
                  @endif
                  <li><a href="/guiasremision"><i></i>GUIAS REMISION</a></li>
                  
                  <li class="divider"></li>

                       <li class="dropdown-submenu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">REPORTES</a>
                            <ul class="dropdown-menu">

                      <li><a href="/arqueodiario"><i></i>Arqueo de Caja</a></li>
                       <li class="divider"></li>
                       <li><a href="/reportes/1"><i></i>Registro de Ventas</a></li>
                       <li><a href="/reportes/2"><i></i>Registro de Ventas por Vendedor</a></li>
                       <li><a href="/reportes/3"><i></i>Registro de Ventas por Cliente</a></li>
                      
                      <li class="divider"></li>
                       <li><a href="/reportes/4"><i></i>Registro de Proformas</a></li>
                       <li><a href="/reportes/5"><i></i>Registro de Pedidos</a></li>
                      <li class="divider"></li> 
                       <li><a href="/reportes/6">Registro de Ventas por Producto</a></li>
                       <li><a href="/reportes/20"><i></i>Resumen Ventas por Producto</a></li>
                       <li><a href="/reportes/7"><i></i>Productos (+/-) Vendidos</a></li>
                       <li><a href="/reportes/8"><i></i>Rentabilidad</a></li>
                       <li class="divider"></li> 
                       <li><a href="/reportes/10"><i></i>Comisiones por Vendedor</a></li>
                        <li><a href="/reportes/9"><i></i>Comisiones por Producto</a></li>
                           
                        <li class="divider"></li> 
                        <li><a href="/reportecuentas">Reporte Cuentas por Cobrar</a></li>
                        <li class="divider"></li>
                        <li hidden="hidden"><a href="/reportealbergues">Reportes Albergues</a></li>
                        <li><a href="/reportemediopago">Reporte Medio de pago</a></li>
                       
                   </ul>
                        </li>

                      <li hidden="hidden" class="divider"></li>

                       <li hidden="hidden" class="dropdown-submenu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">REPORTES VALETPARKING</a>
                            <ul class="dropdown-menu">
                       <li><a href="/reportes/14"><i></i>Registro - Ingreso Vehiculos</a></li>
                      
                     
                       
                   </ul>
                        </li>

               
               
              
                  
                </ul>
              </li>
 @endif
          
               @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
               <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">COMPRAS<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                 
                 
				     @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
                  <li><a href="/compras">Compras</a></li>
                  <li><a href="/ordenescompra">Ordenes Compra</a></li>
			      <li><a href="/gastos"><i></i>Gastos / Ingresos</a></li>
			      @endif
				      <li class="divider"></li>
             <li><a href="/ordenesservicios">Ordenes Servicios</a></li>
               <li class="divider"></li>
				      @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )
                  
                  <li><a href="/cuentaspagar">Cuentas Pagar</a></li>
                       
                  
                  <li class="divider"></li>

                  <li><a href="/inventarios">Inventarios</a></li>
                
                  <li class="divider"></li>
                <!--  <li><a href="/almacen">Movimientos Productos</a></li>-->
                  <li><a href="/transferencias">Transferencias</a></li> 
                    <li><a href="/salidasproductos">Salidas Productos</a></li> 
                  <li><a href="/ingresosproductos">Ingresos Productos</a></li> 

                  <li class="divider"></li>
                   <li><a href="/salidas">Salidas Productos - Areas</a></li>
                   @endif
                  <li class="divider"></li>
                

                       <li class="dropdown-submenu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">REPORTES</a>
                            
                            <ul class="dropdown-menu">
                                <li><a href="/kardex"><i></i>Kardex</a></li>
                                <li><a href="/stockproductos"><i></i>Stock Productos</a></li>
                                <li class="divider"></li> 
                                 <!--<li><a href="/reportesinventario"><i></i>Reporte de Inventario</a></li>-->
                                  <li class="divider"></li> 
                                <li><a href="/reportes/11"><i></i>Registro de Compras</a></li>
                                <li><a href="/reportes/12"><i></i>Registro de Compras por Proveedor</a></li>
                                <li><a href="/reportes/13"><i></i>Registro de Compras por Productos</a></li>
                                <li><a href="/reportes/14"><i></i>Registro de Gastos</a></li>
                                 <li class="divider"></li> 
                                  <li hidden="hidden"><a href="/rptpagospersonal"><i></i>Registro de Pagos Personal</a></li>
                            </ul>
                        </li>
                </ul>
              </li>
                
        @endif 


        <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">RRHH<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">                 
             @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
                  <li><a href="/gastospersonal">PAGOS A PERSONAL</a></li>               
            @endif
                </ul>
        </li>
            
             @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
               <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">PRODUCCION<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">     
                  <!--<li><a href="/almacen">Movimientos Productos</a></li>-->                 
                   <li><a href="/indexsalidas">Salidas de Productos</a></li>
                    <li><a href="/indexingresos">Ingreso de Productos</a></li>
                </ul>
              </li>                
              @endif

              @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">CONTROL DE CAJA <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="/caja">Listar Caja</a></li>                   
                    @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))
                    <li><a href="/movimientoscaja">Movimientos Caja</a></li>
                    @endif               
                </ul>
              </li>
              @endif

               @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))
               <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">BANCOS <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                     <li class=""><a href="/conceptosbancarios"><i></i>Conceptos Bancarios</a></li>
                     <li class=""><a href="/cuentasbancarias"><i></i>Cuentas Bancarias</a></li>
                    <li><a href="/movimientosbancarios">Movimientos Bancarios</a></li>
                </ul>
              </li>
              @endif

              @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))
             <!-- <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">INVENTARIO <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">

                 
                           
                </ul>
              </li>-->
              @endif

          

              @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
             <!--  <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">CONSULTAS <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                <li><a href="/registros/pedidos"><i></i>Registro de Pedidos</a></li>
                  <li><a href="/imprimirreportes">Reportes Tickets</a></li>
                 
                    <li><a href="/reportecompra">Reportes Compras</a></li>
                  
                 <li><a href="/reportestock">Reportes Productos</a></li>
                 
                  
                </ul>
              </li>-->
              @endif

                 @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') ||    Auth::User()->hasRole('caja') )
                      <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown">CPE <span class="caret"></span></a>
                      <ul class="dropdown-menu" role="menu">
                        <li class=""><a href="/facturacionelectronica"><i></i> Envío de Comprobantes</a></li>
                       
                         <li class=""><a href="/listarresumenes"><i></i> Resumen Diario</a></li>
                       
                      </ul>
                    </li>
  @endif
         
           

          @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">MANTENIMIENTO <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
				  @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                  <li class=""><a href="/administrador/empresas"><i></i> Empresas</a></li>
                  <li class=""><a href="/administrador/usuarios"><i></i> Usuarios</a></li>
          @endif

        

                  <li hidden="hidden" class=""><a href="{{route('tecnicos.index') }}"><i></i> T&eacute;cnicos</a></li>
               @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                  <!--<li class="divider"></li>
                 <li class=""><a href="/procesos"><i></i> Procesos</a></li>
                   <li class=""><a href="/maquinas"><i></i> Máquinas</a></li>-->
                  <li class="divider"></li>
                  <li class=""><a href="/tipocambio"><i></i> Tipo Cambio</a></li>
                  <li class="divider"></li>
                  <li class=""><a href="/negocios"><i></i> Sucursales</a></li>
                  <li class=""><a href="/areas"><i></i> Areas</a></li>
                  <li class=""><a href="/almacen/listaralmacenes"><i></i> Almacenes</a></li>
                  <li class="divider"></li>
                  <li class=""><a href="/bancos"><i></i>Bancos</a></li>  
                  <li class=""><a href="/tiposdocumentos"><i></i> Documentos Caja Banco</a></li>
                  <li class=""><a href="{{route('mediospagos.index') }}"><i></i> Medios de Pago</a></li> 
                  <li class="" hidden="hidden"><a href="/tiposcaja"><i></i> Tipos Caja</a></li>   
                  <li class="divider"></li>
				          <li hidden="hidden" class=""><a href="{{route('programas.index') }}"><i></i> Programas</a></li>
                  <li class=""><a href="{{route('categorias.index') }}"><i></i> Familias</a></li>
                  <li class=""><a href="{{route('subcategorias.index') }}"><i></i> Subfamilias</a></li>
                  <li class=""><a href="{{route('tipoproducto.index') }}"><i></i> Tipo Productos</a></li>
                @endif
                  <li class=""><a href="{{route('marcas.index') }}"><i></i> Marcas</a></li>
                  <li class=""><a href="{{route('modelos.index') }}"><i></i> Modelos</a></li>
				 
         
                  <li class=""><a href="{{route('productos.index') }}"><i></i> Productos</a></li>
                  <li hidden="hidden" class="divider"></li>
				              <li hidden="hidden" class=""><a href="{{route('tiposvehiculos.index') }}"><i></i> Tipos Vehiculos</a></li>
                     <li hidden="hidden" class=""><a href="/tarifas"><i></i> Tarifas</a></li>
                  <li class="divider"></li>

                  <li class=""><a href="{{route('clientes.index') }}"><i></i> Clientes</a></li>
                  <li class=""><a href="{{route('proveedor.index') }}"><i></i> Proveedores</a></li>
  <li class="divider"></li>
     <li class=""><a href="{{route('pisos.index') }}"><i></i> Pisos</a></li>
                  <li class=""><a href="{{route('mesa.index') }}"><i></i> Mesas</a></li>
  <li class="divider"></li>
        @if( Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin') )
                  <li class=""><a href="{{route('tipogastos.index') }}"><i></i> Tipo Gastos / Ingresos</a></li>

                @endif    
                </ul>
              </li>
            @endif

         

              @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">INTEGRACION <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                  <li class=""><a href="" data-target="#modal-ventas" data-toggle="modal"><i></i> Migrar Ventas</a></li>
                  <li class=""><a href="" data-target="#modal-compras" data-toggle="modal"><i></i> Migrar Compras</a></li>
                  <li class="divider"></li>
                  <li class=""><a href="" data-target="#modal-clientes" data-toggle="modal"><i></i> Migrar Clientes - Proveedores</a></li>
                  <li class="divider"></li>
                  <li class=""><a href="" data-target="#modal-ventas-importar" data-toggle="modal"><i></i> Importar Ventas</a></li>
                 

                 
                </ul>
              </li>
              @endif

             @if(Auth::User()->hasRole('admin') ||    Auth::User()->hasRole('superadmin'))
                <li><a href="/utilitarios"><i></i>UTILITARIOS</a></li>
            @endif

            </ul>

            <ul class="nav navbar-nav navbar-right">
          
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
              <i class="fa fa-bell-o"></i>
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

    <li><a href="{{ route('logout') }}"><img src="/icon/salir.png" width="24px" height="24px">
            <span></span>
            <span class="pull-right-container">
              <i class=""></i>
            </span></a>
    </li>
            </ul>
          </div><!-- /.navbar-collapse -->
          </div><!-- /.container-fluid -->
        </nav>
      </header>



  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

     @yield('contenido')
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">

    </div>
    <strong>WILCAT SYSTEMS <a href="http://demo.wilcatsystems.pe" target="_blank"> </a> .</strong>
  </footer>

  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->


</div>
<!-- ./wrapper -->




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
