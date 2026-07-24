<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SISTEMA DE GESTION COMERCIAL HOLAPE BY DEVSOFT</title>
  <link rel="shortcut icon" href="img/icono_hp.ico">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="stylesheet" href="{{ asset('css/sweetalert2/sweetalert2.min.css') }}">

  <script src="{{ asset('js/sweetalert2/sweetalert2.all.min.js') }}"></script>

  <meta name="csrf-token" content="{{csrf_token()}}">
  <link href="{{asset('css/bootstrap-select.min.css')}}" rel="stylesheet">




  <link rel="stylesheet" href="{{asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('adminlte/bower_components/font-awesome/css/font-awesome.min.css')}}">
   <link href="{{asset('css/font-awesome6/css/all.min.css')}}" rel="stylesheet">

  <link rel="stylesheet" href="{{asset('adminlte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('adminlte/bower_components/Ionicons/css/ionicons.min.css')}}">
  <link rel="stylesheet" href="{{asset('adminlte/bower_components/jvectormap/jquery-jvectormap.css')}}">
  <link rel="stylesheet" href="{{asset('adminlte/dist/css/AdminLTE.min.css')}}">

  <link rel="stylesheet" href="{{asset('adminlte/dist/css/skins/_all-skins.css')}}">

  <link rel="stylesheet" href="{{asset('css/select2.min.css')}}">

  <link rel="stylesheet" href="{{ asset('css/holap-design.css') }}">

  <!--<link rel="stylesheet" href="{{ asset('css/estilos-globales.css') }}">
  <link rel="stylesheet" href="{{ asset('css/estilos-globales2.css') }}">
  <link rel="stylesheet" href="{{ asset('css/estilos-globales3.css') }}">
  <link rel="stylesheet" href="{{ asset('css/estilos-globales4.css') }}">
  <link rel="stylesheet" href="{{ asset('css/estilos-globales5gpt.css') }}">-->

 
  <script src="{{asset('adminlte/bower_components/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{asset('js/jqueryprint.js')}}"></script>
  <script src="{{asset('js/jquery-ui.js')}}"></script>

  <script src="{{asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>

  <script src="{{asset('adminlte/bower_components/fastclick/lib/fastclick.js')}}"></script>
  <script src="{{asset('adminlte/dist/js/adminlte.js')}}"></script>
  <script src="{{asset('adminlte/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js')}}"></script>
  <script src="{{asset('adminlte/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js')}}"></script>
  <script src="{{asset('adminlte/plugins/jvectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
  <script src="{{asset('adminlte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
  <script src="{{asset('adminlte/bower_components/chart.js/Chart.js')}}"></script>
  <script src="{{asset('js/jquery-ui.min.js')}}"></script>
  <script src="{{asset('js/jquery.mousewheel.js')}}" ></script>
  <script type="text/javascript" src="{{asset('js/jquery.validate.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('js/selectjquery.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('js/html2canvas.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('js/select2.min.js')}}"></script>


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

    })
  </script>

</head>
@php
// Tus consultas PHP que ya tenías
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

<link href="{{asset('css/estilos/estilos.css')}}" rel="stylesheet">


<body class="hold-transition skin-blue sidebar-collapse sidebar-mini">
 <div class="wrapper">

  <header class="main-header">
    <a href="/SisFact" class="logo">
      <span class="logo-lg" style="font-size:8pt;">{{$obt_dat_emp->NomEmpresa}}</span>
    </a>
    <nav class="navbar navbar-static-top">
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
      
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
      
          <li class="dropdown user user-menu">
            <a href="#" >
              <img src="" >
              <span class="hidden-xs">{{Auth::user()->name}} {{Auth::user()->apeusu}}</span>
            </a>

   
          </li>
            <li class="dropdown user user-menu">
            <a href="{{ route('logout') }}"><img src="/icon/salir.png" width="24px" height="24px">
              <span></span>
              <span class="pull-right-container">
                <i class=""></i>
              </span></a>

          </li>
         
        </ul>
      </div>
    </nav>
  </header>

  @if(Auth::user()->hasRole('admin'))

          @include('layouts.administrador')

  @elseif(Auth::user()->hasRole('caja'))

          @include('layouts.cajero')
  @elseif(Auth::user()->hasRole('mozo'))


          @include('layouts.mozo')
  @elseif(Auth::user()->hasRole('contador'))


          @include('layouts.contador')

  @endif




  <div class="content-wrapper">
    

   @yield('contenido')

  </div>
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



<script>
  // Envuelve la inicialización de Chart.js en una función que se llama solo si el elemento existe.
  // Esto previene el error "getContext" en páginas que no tienen estos canvas.
  $(function () {
    // Solo si el elemento 'areaChart' existe
    if ($('#areaChart').length) {
        var meses =[];
        var valores =[];

        // Tus bloques PHP para poblar 'meses' y 'valores'
        // Asegúrate de que las variables $vent_men y $meses (PHP) estén definidas en el controlador
        // que renderiza la vista donde se usarán estos gráficos (ej. DashboardController).
        // Si no están definidas en un contexto, este PHP generará un error o estará vacío.
        <?php 
        // Solo incluye este código PHP si $vent_men está garantizado a existir en la vista que usa este layout
        // y que usa estos gráficos.
        if(isset($vent_men) && !empty($vent_men)){ 
            foreach($vent_men as $key => $value){ 
                foreach($value as $key1 => $value1){
        ?>
                    valores.push('<?php echo $value1; ?>');
        <?php 
                } 
            }
        }
        ?>

        <?php 
        // Solo incluye este código PHP si $meses (PHP) está garantizado a existir
        if(isset($meses) && !empty($meses)){ 
            setlocale(LC_ALL, 'spanish');
            foreach($meses as $i => $valor){ 
                foreach($valor as $j => $mes_num){ // Se corrigió el segundo foreach para usar $mes_num
                    $dateObj   = DateTime::createFromFormat('!m', $mes_num); // Usar $mes_num
                    $monthName = strftime('%B', $dateObj->getTimestamp());
        ?>
                    meses.push('<?php echo $monthName; ?>');
        <?php 
                } 
            } 
        }
        ?>

        // Inicialización de Chart.js (Ahora solo si el elemento #areaChart existe)
        var areaChartCanvas = $('#areaChart').get(0).getContext('2d');
        var areaChart       = new Chart(areaChartCanvas);

        var areaChartData = {
          labels: meses,
          datasets: [
            {
              label               : 'Electronics',
              fillColor           : 'rgba(210, 214, 222, 1)',
              strokeColor         : 'rgba(210, 214, 222, 1)',
              pointColor          : 'rgba(210, 214, 222, 1)',
              pointStrokeColor    : '#c1c7d1',
              pointHighlightFill  : '#fff',
              pointHighlightStroke: 'rgba(220,220,220,1)',
              data                : valores
            },
          ]
        };

        var areaChartOptions = {
            // Opciones de tu gráfico de área
            responsive: true,
            maintainAspectRatio: true,
            // ... otras opciones
        };
        areaChart.Line(areaChartData, areaChartOptions); // Asumiendo que es un gráfico de línea (Area Chart)


        // -------------
        // - BAR CHART -
        // -------------
        // Solo si el elemento 'barChart' existe
        if ($('#barChart').length) {
            var barChartCanvas = $('#barChart').get(0).getContext('2d');
            var barChart = new Chart(barChartCanvas);
            var barChartData = areaChartData; // Puedes usar los mismos datos si aplica
            var barChartOptions = {
              scaleBeginAtZero        : true,
              scaleShowGridLines      : true,
              scaleGridLineColor      : 'rgba(0,0,0,.05)',
              scaleGridLineWidth      : 1,
              scaleShowHorizontalLines: true,
              scaleShowVerticalLines  : true,
              barShowStroke           : true,
              barStrokeWidth          : 2,
              barValueSpacing         : 5,
              barDatasetSpacing       : 1,
              legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
              responsive              : true,
              maintainAspectRatio     : true
            };
            barChartOptions.datasetFill = false;
            barChart.Bar(barChartData, barChartOptions);
        }
    }
  });
</script>

<script>
$(document).ready(function() {
    // 1. A todas las cajas antiguas les pone la sombra elegante
    $(".box").addClass("shadow-box");
    
    // 2. A todos los encabezados azules feos les pone el color corporativo moderno
    $(".box-header").addClass("custom-header").css("background-color", "");
    
    // 3. A todos los botones del sistema les da el efecto 3D al pasar el mouse
    $(".btn").addClass("btn-elegant");
    
    // 4. A las alertas rojas y verdes les pone bordes curvos
    $(".alert").addClass("alert-elegant");
    
    // 5. Centra el texto de todas las tablas
    $(".table").addClass("table-vertical-align");
    
    // 6. Si tienes cabeceras de tablas antiguas, las pinta del color secundario
    $("thead tr:nth-child(2)").addClass("custom-subheader");

    $(".modal-header").removeAttr("style");
    
    // 2. Nos aseguramos de que todos los modales tengan el efecto suave de entrada (fade)
    $(".modal").addClass("fade");
});
</script>

@stack('scripts')

@yield('scripts')
</body>
</html>