<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SISTEMA DE PUNTO DE VENTA</title>
  <!-- Tell the browser to be responsive to screen width -->
  <link rel="shortcut icon" href="img/logo_nova.png">
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
 
  <link href="{{asset('css/keyboard.css')}}" rel="stylesheet">


<script src="{{asset('adminlte/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('js/jqueryprint.js')}}"></script>
<script src="{{asset('js/jquery-ui.js')}}"></script>

<script src="{{asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('adminlte/bower_components//datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('adminlte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
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




  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

     @yield('contenido')
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  
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


</body>
</html>
