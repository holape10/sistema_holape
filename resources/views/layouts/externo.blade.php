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
   <link href="{{asset('css/font-awesome6/css/all.min.css')}}" rel="stylesheet">

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
  <script src="{{asset('adminlte/dist/js/adminlte.js')}}"></script>
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


<link href="{{asset('css/estilos/estilos.css')}}" rel="stylesheet">


<body class="hold-transition skin-blue sidebar-collapse sidebar-mini">
 <div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="/SisFact" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels 
      <span class="logo-mini"><b>A</b>LT</span>-->
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg" style="font-size:8pt;"></span>
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
              
              <span class="label label-warning"></span>
            </a>
              <ul class="dropdown-menu">

                <li>
                  <ul class="menu">
                    <li>
                      <a href="/facturacionelectronica">
                        <i class="fa fa-warning text-yellow"></i>
                      </a>
                    </li>
                  </ul>
                </li>

                <li>
                  <ul class="menu">
                    <li>
                      <a href="/facturacionelectronica">
                        <i class="fa fa-warning text-yellow"></i> 
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
              <span class="hidden-xs"></span>
            </a>

   
          </li>
            
         
        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->




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



<script>
  $(function () {

      var meses =[];
     var valores =[];

  <?php 
 
 if(!empty($vent_men)){
  

 foreach($vent_men as $key => $value){ 
   foreach($value as $key1 => $value1){

   
 ?>
    valores.push('<?php echo $value1; ?>');
 
 <?php } }?>

  <?php 
 setlocale(LC_ALL, 'spanish');

 foreach($meses as $i => $valor){ 
   foreach($valor as $i => $valor){

    $dateObj   = DateTime::createFromFormat('!m', $valor);
    $monthName = strftime('%B', $dateObj->getTimestamp());
   // $monthName = $dateObj->format('F');

   
 ?>
    meses.push('<?php echo $monthName; ?>');
 
 <?php 

    } } 


  }?>



    /* ChartJS
     * -------
     * Here we will create a few charts using ChartJS
     */

    //--------------
    //- AREA CHART -
    //--------------

    // Get context with jQuery - using jQuery's .get() method.
    var areaChartCanvas = $('#areaChart').get(0).getContext('2d')
    // This will get the first returned node in the jQuery collection.
    var areaChart       = new Chart(areaChartCanvas)

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
    }





    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas                   = $('#barChart').get(0).getContext('2d')
    var barChart                         = new Chart(barChartCanvas)
    var barChartData                     = areaChartData
    var barChartOptions                  = {
      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero        : true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines      : true,
      //String - Colour of the grid lines
      scaleGridLineColor      : 'rgba(0,0,0,.05)',
      //Number - Width of the grid lines
      scaleGridLineWidth      : 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines  : true,
      //Boolean - If there is a stroke on each bar
      barShowStroke           : true,
      //Number - Pixel width of the bar stroke
      barStrokeWidth          : 2,
      //Number - Spacing between each of the X value sets
      barValueSpacing         : 5,
      //Number - Spacing between data sets within X values
      barDatasetSpacing       : 1,
      //String - A legend template
      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to make the chart responsive
      responsive              : true,
      maintainAspectRatio     : true
    }

    barChartOptions.datasetFill = false
    barChart.Bar(barChartData, barChartOptions)
  })
</script>


</body>
</html>
