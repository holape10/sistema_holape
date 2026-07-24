<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SISTEMA DE PUNTO DE VENTA</title>
  <!-- Tell the browser to be responsive to screen width -->
  <link rel="shortcut icon" href="img/icono_hp.ico">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
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
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">

  <link href="{{asset('css/keyboard.css')}}" rel="stylesheet">

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<script src="{{asset('adminlte/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('js/jqueryprint.js')}}"></script>
<script src="{{asset('js/jquery-ui.js')}}"></script>

<script src="{{asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('adminlte/bower_components//datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('adminlte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<!-- FastClick -->
<script src="{{asset('adminlte/bower_components/fastclick/lib/fastclick.js')}}"></script>
<!-- AdminLTE App --a>
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
<script src="{{asset('adminlte/dist/js/pages/dashboard2.js')}}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{asset('js/jquery-ui.min.js')}}"></script>
<script src="{{asset('js/jquery.keyboard.js')}}" ></script>
<script src="{{asset('js/jquery.keyboard.js')}}" ></script>
<script src="{{asset('js/jquery.mousewheel.js')}}" ></script>

<script type="text/javascript" src="{{asset('js/jquery.validate.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/selectjquery.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/pdfmake.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/html2canvas.min.js')}}"></script>

<script src="{{asset('js/ckeditor.js')}}"></script>
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



     $('#listcomp').DataTable({
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
     })

  })
</script>

</head>
@include('administrador.integracion.modalclientes')
@include('administrador.integracion.modalventas')
@include('administrador.integracion.modalcompras')
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
             
       


            </ul>

            <ul class="nav navbar-nav navbar-right">
         
  
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
    <strong>WILCAT SYSTEMS <a href=""></a>.</strong>
  </footer>


</div>


</body>
</html>
