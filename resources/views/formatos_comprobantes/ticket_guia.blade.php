<html>

<head>
  <style>
    * {

      font-family: 'Arial Black';
    }


    #table {
      border: 0px ;
    }

    td,
    th,
    tr,
    table {
      border-top: 1px solid black;
      border-bottom: 1px solid black;
      border-collapse: collapse;

    }

    td.producto,
    th.producto {
      width: 3.5cm;
      text-align:left;
      max-width: 3.5cm;
    }

    td.cantidad,
    th.cantidad {
      width: 2.5cm;
      max-width: 2.5cm;
      text-align:left;
      word-break: break-all;
    }

    td.precio,
    th.precio {
      width: 3cm;
      max-width: 3cm;
      text-align:right;
      word-break: break-all;
    }

    .centrado {
      text-align: center;
      align-content: center;
    }


    .ticket {
      width: 8cm;
      max-width: 8cm;
    }

    #alinear
    padding-left:1500px;
  }

  img {
    max-width: inherit;
    width: inherit;
  }
</style>
<script>

  window.print();


</script>

</head>

<body>
  <?php       $fecha=now();
  $hora = date("d-m-Y h:m:s", strtotime($fecha));
  $hoy = date("d-m-Y")?>
  <div class="ticket">
  
    @if(!empty($sucursal->nombre_comercial))
      <font style="font-size:11pt"><strong><center>{{$sucursal->nombre_comercial}}</center></strong></font>
    @endif
      <font size="2"><strong><center>{{$empresa->NomEmpresa}}<br>{{$empresa->IdEmpresa}}</center></strong></font>
      <font size="2"><center>{{$sucursal->direccion}}<br>{{$sucursal->departamento}} - {{$sucursal->provincia}} - {{$sucursal->distrito}} @if(!empty($sucursal->telefono)) {{$sucursal->correo}} || {{$sucursal->telefono}} @endif </center>  </font>
   

 
           @if(!empty($sucursal->logosuc))
    <center><img src="/{{$sucursal->logosuc}}"  style="padding-left:0px;" width="200px" height="80px"></center>
    @endif

     <center> <font style="font-size:9pt">

        <BR>{{$cabpdf->tdodes}}
        <br>{{$cabpdf->serieguia}}-{{$cabpdf->numeroguia}}<br>
      @if($empresa->IdEmpresa=='20604946787')

      Venta de Bufandas, Ponchos, Pashminas, Sombreros, Gorros, Nacional e Importados<br>
        Ventas por mayor y menor / Pedidos a Provincias <br>
        Cel: 974797237 - 969778304 - 952813925<br>
    

        @else

        Venta de Sombreros, Bufandas, Chompas de Hilo y Lana Nacional e Importadas para Damas y Caballeros
Ventas por mayor y menor / Envíos a Provincias
Cel: 935382602 / 914135703 /952314555

        @endif
      </font></center>
       
       <table id="table">

          <tr id="table">
            <td id="table"><font style="font-size:9pt;">Fecha Traslado:</font></td>
            <td id="table"><font style="font-size:9pt;">{{date('d-m-Y',strtotime($cabpdf->fechatraslado))}}</font></td>
          </tr>
           <tr  id="table">
            <td id="table"><font style="font-size:9pt;">Motivo:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->motivo}}</font></td>
          </tr>
          <tr id="table">
            <td id="table"><font style="font-size:9pt;">@if($cabpdf->tdocod=='01') RUC: @else DNI: @endif </font></td>

            <td id="table"><font style="font-size:9pt;">{{$cabpdf->ruccliente}}</font></td>
          </tr>


          <tr id="table">
            <td id="table"><font style="font-size:9pt;">Raz. Social:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->nomcliente}}</font></td>
          </tr>
          <tr id="table">
            <td id="table"><font style="font-size:9pt;">Ubigeo Partida:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->desubigeopartida}}</font></td>
          </tr>
          <tr id="table">
            <td id="table"><font style="font-size:9pt;">Punto Partida:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->direccionpartida}}</font></td>
          </tr>
           <tr id="table">
            <td id="table"><font style="font-size:9pt;">Ubigeo LLegada:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->desubigeollegada}}</font></td>
          </tr>
          <tr  id="table">
            <td id="table"><font style="font-size:9pt;">Punto LLegada:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->direccionllegada}}</font></td>
          </tr>


          <br>

        </table><br>

        <table style=" border-bottom: 0px;">
          <thead>
         
            <tr>
              <th class="cantidad"><font style="font-size:9pt;">Cant.</font></th>
            
              <th class="producto"><font style="font-size:9pt;">Detalle</font></th>
              <th class="precio"><font style="font-size:9pt;">U.M</font></th>
           
            </tr>
          </thead>
          <tbody >
        @foreach($detpdf as $det)
          
          <tr >
            <td  class="cantidad"><font style="font-size:9pt;">{{number_format($det->cantidad,2,'.','')}}</font></td>
  
             <td    class="producto" ><font style="font-size:9pt;">{{$det->pronom}}</font></td>
            <td  class="precio"><font style="font-size:9pt;">{{$det->umenom}}</font></td>
          
          </tr>
          @endforeach


         

      </tbody>

    </table>
  
      
       <table id="table">

          <tr id="table">
            <td id="table"><font style="font-size:9pt;">Modalidad Transporte:</font></td>
            <td id="table"><font style="font-size:9pt;">{{$cabpdf->modalidad}}</font></td>
          </tr>
          <tr  id="table">
            <td id="table"><font style="font-size:9pt;">Peso Total Aprox. (KGM):</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->pesobruto}}</font></td>
          </tr>
        
          <tr id="table">
            <td id="table"><font style="font-size:9pt;">Cantidad Bultos:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> {{$cabpdf->bultos}}</font></td>
          </tr>

        
             <tr id="table">
          <td id="table"><br></td>
        </tr>

          <tr  id="table">
            <td id="table"><font style="font-size:9pt;">Conductor:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> @if(!empty($cabpdf->nomconductor)) {{$cabpdf->nomconductor}} @else -- @endif</font></td>
          </tr>
        
          <tr id="table">
            <td id="table"><font style="font-size:9pt;">DNI del Conductor:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> @if(!empty($cabpdf->rucconductor)) {{$cabpdf->rucconductor}} @else -- @endif </font></td>
          </tr>
          <tr  id="table">
            <td id="table"><font style="font-size:9pt;">Placa del Vehiculo:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;"> @if(!empty($cabpdf->placa)) {{$cabpdf->placa}} @else -- @endif</font></td>
          </tr>
     
          <tr id="table">
            <td id="table"><font style="font-size:9pt;">Licencia:</font></td>
            <td colspan="3" id="table"><font style="font-size:9pt;">  @if(!empty($cabpdf->licencia)) {{$cabpdf->licencia}} @else -- @endif</font></td>
          </tr>

         

          <br>

        </table><br>






    </body>
    </html>

