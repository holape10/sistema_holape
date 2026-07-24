<html>

<head>
  <style>
    * {

      font-family: 'font-family: Georgia';
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
      width: 3cm;
      text-align:left;
      max-width: 3cm;
    }

    td.cantidad,
    th.cantidad {
      width: 2.2cm;
      max-width: 2.2cm;
      text-align:left;
      word-break: break-all;
    }

    td.codigo,
    th.codigo {
      width: 3cm;
      max-width: 3cm;
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

  <div class="ticket">
    <center><img src="/{{$sucursal->logosuc}}"  style="padding-left:0px;" width="150px" height="150px"></center>
   
    @if(!empty($sucursal->nombre_comercial))
     <font style="font-size:14pt;"><strong><center>{{$sucursal->nombre_comercial}}</center></strong></font>
      <font style="font-size:10pt;"><strong><center>De: {{$empresa->NomEmpresa}}</center></strong></font>
    @else
    
      <font style="font-size:14pt;"><strong><center>{{$empresa->NomEmpresa}}</center></strong></font>
    @endif

        @if(!empty($sucursal->descripcion1))
        
          <font style="font-size:11pt;"><strong><center>{{$sucursal->descripcion1}}</center></strong></font>
        @endif
        @if(!empty($sucursal->descripcion2))
        <font style="font-size:11pt;"><strong><center>{{$sucursal->descripcion2}}</center></strong></font>
      
        @endif

   
    <font size="2"><strong><center>{{$sucursal->direccion}}</center></strong></font>
      <font size="2"><strong><center>{{$sucursal->telefono}}</center></strong></font>
        <font size="2"><strong><center>{{$sucursal->departamento}} - {{$sucursal->provincia}} - {{$sucursal->distrito}}</center></strong></font>
      <BR>

    <div style="border: 2px 2px 2px 2px solid  black;border-style: solid;">
        <div style="margin-top:10px;margin-bottom:10px;">
          <font  style="font-size:12pt;font-weight:bold;"><center>  @if($cabecera->tdocod!='13') R.U.C: {{$empresa->IdEmpresa}}<BR>@endif{{$cabecera->tdodes}}<br>{{$cabecera->serdoc}}-{{$cabecera->numdoc}}</center></font>
        </div>
        
    </div>
   

     <table id="table">
      <tr id="table">
        <td id="table"><font style="font-size:12pt;font-weight:bold;">Fecha:</font></td>
        <td id="table"><font style="font-size:12pt;">{{date('d-m-Y',strtotime($cabecera->ccafem))}} {{date('H:i:s',strtotime($cabecera->fecha_hora))}}</font></td>
        
      </tr>
      <tr id="table">
        <td id="table"><font style="font-size:12pt;font-weight:bold;">Sr(es): </font></td>
        <td colspan="3" id="table"><font style="font-size:12pt;"> {{$cabecera->clinom}}</font></td>
      </tr>
      <tr id="table">
        <td id="table"><font style="font-size:12pt;font-weight:bold;">RUC:</font></td>

        <td id="table"><font style="font-size:12pt;">{{$cabecera->clinum}}</font></td>
      </tr>

      <tr id="table">
        <td id="table"><font style="font-size:12pt;font-weight:bold;">Dirección:</font></td>
        <td colspan="3" id="table"><font style="font-size:12pt;"> {{$cabecera->clidir}}</font></td>
      </tr>
      <tr id="table">
        <td id="table"><font style="font-size:12pt;font-weight:bold;">M. Pago:</font></td>
        <td colspan="3" id="table"><font style="font-size:12pt;">{{$cabecera->estadopago}} </font></td>
      </tr>
      <tr id="table">
        <td id="table"><font style="font-size:12pt;font-weight:bold;">Vendedor:</font></td>
        <td colspan="3" id="table"><font style="font-size:12pt;">@if(!empty($data_vendedor)){{$data_vendedor->name}} {{$data_vendedor->apeusu}} @endif</font></td>

      <!--</tr>
       <tr id="table">
        <td id="table"><font style="font-size:12pt;font-weight:bold;">Cajero:</font></td>
        <td colspan="3" id="table"><font style="font-size:12pt;">{{$data_cajero->name}} {{$data_cajero->apeusu}} </font></td>
      </tr>-->
      
        @if($cabecera->tdocod=='07' or $cabecera->tdocod=='08')
        
          <tr id="table">
             <td id="table"><font style="font-size:12pt;font-weight:bold;">TIPO N.C:</font></td>
            <td colspan="3" id="table"><font style="font-size:12pt;"> {{$cabecera->ncdes}}</font></td>

          </tr>
          <tr id="table">
             <td id="table"><font style="font-size:12pt;font-weight:bold;">DOC. REF:</font></td>
            <td colspan="3" id="table"><font style="font-size:12pt;">: {{$cabecera->serie_ref}}-{{$cabecera->num_ref}}</font></td>

          </tr>
      @endif


      <br>

    </table><br>

    <table style=" border-bottom: 0px;">
      <thead>
       
        <tr>
          <th class="cantidad"><font style="font-size:12pt;">Cant.</font></th>
          <th class="codigo"><font style="font-size:12pt;">Código</font></th>
           <th class="producto"><font style="font-size:12pt;">Descripción</font></th>
          <th class="precio"><font style="font-size:12pt;">P.Unit</font></th>
          <th class="precio"><font style="font-size:12pt;">Total</font></th>
        </tr>
      </thead>
      <tbody >
       @foreach($detalle as $det)
      
      <tr >
        <td  class="cantidad"><font style="font-size:11pt;vertical-align:top;font-weight:bold;">{{number_format($det->cdecan,2,'.','')}}</font></td>
         <td  class="codigo"><font style="font-size:11pt;vertical-align:top;">{{$det->procod}}</font></td>
        <td    class="producto"><font style="font-size:11pt;vertical-align:top;">{{$det->cdedes}}</font></td>
        <td  class="precio"><font style="font-size:11pt;vertical-align:top;">{{number_format($det->cdepuni,2,'.','')}}</font></td>
        <td  class="precio"><font style="font-size:11pt;vertical-align:top;">{{number_format($det->cdevve,2,'.','')}}</font></td>
      </tr>
      @endforeach
    
      </tbody>

</table>

    <table style=" border-bottom: 0px;">
      

     @if($cabecera->tdocod!='13')

      
      @if($cabecera->ccatvg != 0.00)
      <tr id="table">
       <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">OP. GRAVA. {{$cabecera->simbolo}}</font> </td>
       <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
       <td class="precio" id="table" style="text-align:right;font-weight:bold;" ><font style="font-size:12pt;">{{number_format($cabecera->ccatvg,2,'.','')}}</font></td>
     </tr>
     @endif
      @if($cabecera->ccatexo != 0.00)
      <tr id="table">
       <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">OP. EXONE. {{$cabecera->simbolo}}</font> </td>
       <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
       <td class="precio" id="table" style="text-align:right;"><font style="font-size:12pt;">{{number_format($cabecera->ccatexo,2,'.','')}}</font></td>
     </tr>
     @endif

        <tr id="table">
       <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">IGV {{$cabecera->simbolo}}</font> </td>
       <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
       <td class="precio" id="table" style="text-align:right;"><font style="font-size:12pt;font-weight:bold;">{{number_format($cabecera->ccaigv,2,'.','')}}</font></td>
     </tr>
     <tr id="table">
       <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">ICBPER {{$cabecera->simbolo}}</font> </td>
       <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
       <td class="precio" id="table" style="text-align:right;"><font style="font-size:12pt;font-weight:bold;">{{number_format($cabecera->tot_icbper,2,'.','')}}</font></td>
     </tr>

     @endif
    
     <tr id="table">
      <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">TOTAL {{$cabecera->simbolo}}</font> </td>
      <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
      <td class="precio" id="table" style="text-align:right;"><font style="font-size:12pt;font-weight:bold;">{{$cabecera->ccaitv}}</font></td>
    </tr>

    <tr  hidden="hidden" id="table">
      <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">PAGA {{$cabecera->simbolo}}</font> </td>
      <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
      <td class="precio" id="table" style="text-align:right;"><font style="font-size:12pt;font-weight:bold;">{{$cabecera->paga}}</font></td>
    </tr>
    <tr  hidden="hidden" id="table">
      <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">VUELTO {{$cabecera->simbolo}}</font> </td>
      <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
      <td class="precio" id="table" style="text-align:right;"><font style="font-size:12pt;font-weight:bold;">{{$cabecera->vuelto}}</font></td>
    </tr>

  </tbody>

</table>
<p> @if($cabecera->tdocod!='13')<font size="1">{{$totalletras}} Soles</p></font>@endif
<p class="centrado" style="margin-top:20px;font-size:12pt;font-weight:bold;">  @if($cabecera->tdocod!='13') Representaci&oacute;n Impresa de la {{$cabecera->tdodes}} @endif
  <center>  @if($cabecera->tdocod!='13')<img src="{{ asset($imgqr) }}" width="80px" height="80px"> <br>@endif 
   <p class="centrado" style="margin-top:20px;font-size:8pt;">@if($sucursal->serv_selv=='1')"BIENES  Y/O SERVICIOS TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA" @endif<br>
  </div>

  <table style=" border-bottom: 0px;">
    <tbody>
      @foreach ($medios as $m)
         <tr id="table"  >
      <td class="producto" id="table"><font style="font-size:12pt;font-weight:bold;">{{$m->nom_med_pag}} {{$cabecera->simbolo}}</font> </td>
      <td colspan="2" class="cantidad" id="table" style="width: 4cm;"></td>
      <td class="precio" id="table" style="text-align:right;"><font style="font-size:12pt;font-weight:bold;">{{$m->monto}}</font></td>
    </tr>
      @endforeach
    </tbody>
  </table>

  <br>.

</body>
</html>

