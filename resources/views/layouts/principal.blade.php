<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SISTEMA DE PUNTO DE VENTA</title>
  <!-- Tell the browser to be responsive to screen width -->
  <link rel="shortcut icon" href="img/logo_hp.png">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
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

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<script src="{{asset('adminlte/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('js/jqueryprint.js')}}"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
<!-- Bootstrap 3.3.7 -->
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

<script>
  $(document).ready(function() {


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
