@extends('layouts.empresas')
@section('contenido')

<style>
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}

input[readonly]
{
  background-color:#eee;
}


input[type=number] { -moz-appearance:textfield; }

#formnota label.error {
  color:red;
}

</style>
<script>

</script>
<script>
  $.validator.setDefaults({ 
    ignore: [],
    // any other default options and/or rules
  });

  $(document).ready(function()
  {   
    var $svalor=0;
    var iCnt = 0;      

    $("#serdocmod").on('change',function(){

      var serie = $('#serdocmod').val();
      var posi = serie.indexOf("-",0);
      var seriemod = serie.substring(0,posi);
      $('#serdocmod').val(seriemod);
      $('#serdocmod').prop("readonly",true);

    })



    jQuery.validator.addMethod("alphanumeric", function(value, element) {
      return this.optional(element) || /^[\w.]+$/i.test(value);
    }, "Letters, numbers, and underscores only please"),


    $('#formnota').validate({

      rules: {


        serdocmod:{
          required:true,
          maxlength: 4
        },
        numdocmod:{
          required:true,
          maxlength: 8
        },

        fecEmi:{
          required: true,
          date: true
        },
        Descuentos:{
          number:true,
          min:0
        },
        otros:{
          number:true,
          min:0
        },
        otrosc:{
          number:true,
          min:0
        },
        fecEmi:"required",
        obser:{
          required:true,
          maxlength: 250
        }

      },


      messages: {
        serdocmod:{
          required:"Ingresar Número de Serie",
          maxlength:"Ingresar 4 Caracteres como máximo"
        },
        numdocmod:{
          required:"Ingresar Número de documento"
        },

        fecEmi:{
          required:"Ingresar la fecha de emisión",
          date:"Ingresar una fecha válida"
        },
        descuentos:{
          number:"Ingresar un monto válido",
          min:"Ingresar un monto igual o mayor a 0"
        },
        otrosc:{
          number:"Ingresar un monto válido",
          min:"Ingresar un monto igual o mayor a 0"
        },
        otros:{
          number:"Ingresar un monto válido",
          min:"Ingresar un monto igual o mayor a 0"
        },
        obser:{
          required:"Ingresar el motivo o sustento de la nota",
          maxlength:"El número máximo de caracteres es de 250"
        }
      }

    })


    $("#camdoc").prop('readonly',true);
    

        //Agregar al modal filas con los elementos para registrar las guías de remisión

        $('#add').click(function() {
          iCnt = iCnt + 1;
          $('.alertitem').hide();

            // Añadir caja de texto.
            $('#detFact').append('<tr><td><input  type="number"  step="any" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" class="cant form-control input-sm" /></td><td><select style="width:100px" name="unid[]"  class="form-control input-sm">@foreach ($unidades as $und)<option  value="{{$und->umecod}}">{{$und->umenom}}</option>@endforeach</select></td><td><input type="text"  name="codpro[]" id="codpro"   placeholder="" class="codpro form-control input-sm"></td><td><input class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" ></td><td><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden" ><input type="number" step="any" id="vunit" name="vunit[]" style="text-align:right;" value="0" min="0"  OnKeyUp="Calcular(this);" class="vunit form-control input-sm" /></td><td><input type="text"  id="preuni" style="text-align:right;"  name="preuni[]" OnKeyUp="Calcular(this);" class="form-control input-sm" /></td><td hidden="hidden" ><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly value="0" style="text-align:right;" name="vsub[]"/></td><td hidden ><input type="text" class="form-control input-sm" size="20px" id="vigv" readonly value="0" style="text-align:right;" name="vigv[]"/></td><td><input type="text" readonly value="0" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm" /></td>   <td><button type="button" onClick="deleteRow(this);" class="btndel btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
            $('.codpro').on('change',function () {
             validartabla();
           })

            $('.detpro').on('change',function(){
              validartabla();

            })

            $('.cant').on('change',function () {
             validartabla();
           })


            $(".codpro").autocomplete({
              source: '{!!URL::route('consultarproducto')!!}',
              dataType: "json",
              minLength: 3,
              autoFocus:true,
              select: function(event,ui) {   
               $(this).closest('tr').find("td:eq(3) > input").val(ui.item.pronom);
               $(this).closest('tr').find("td:eq(5) > input").val(ui.item.provun);
               $(this).closest('tr').find("td:eq(1) > select").val(ui.item.umecod).attr('selected', 'selected');
               $(this).closest('tr').find("td:eq(2) > input").prop("readonly",true);
               $(this).closest('tr').find("td:eq(3) > input").prop("readonly",false);
               $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
               $(this).closest('tr').find("td:eq(5) > input").prop("readonly",false);
               $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);
             }
           })



            $(".codpro").on('dblclick', function (){
              $(this).closest('tr').find("td:eq(2) > input").prop("readonly",false);
              $(this).closest('tr').find("td:eq(3) > input").prop("readonly",true);
              $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
              $(this).closest('tr').find("td:eq(0) > input").prop("readonly",true);
              $(this).closest('tr').find("td:eq(0) > input").val(0);
              $(this).closest('tr').find("td:eq(2) > input").val('');
              $(this).closest('tr').find("td:eq(3) > input").val('');
              $(this).closest('tr').find("td:eq(5) > input").val(0.00);
              $(this).closest('tr').find("td:eq(6) > input").val(0.00);
              $(this).closest('tr').find("td:eq(7) > input").val(0.00);
              $(this).closest('tr').find("td:eq(8) > input").val(0.00);
              calculartotal();
              validartabla();
            })
          })



$("#cambia").click(function(){
  $("#texto").toggle(1000);
})


//INICIO SUBMIT-----------------------------------------------------------------

// Creamos un candado global
var nota_en_proceso = false;

$( "#formnota" ).submit(function( event ) {
  
  // 1. Si el candado está cerrado, bloqueamos cualquier clic adicional al instante
  if (nota_en_proceso) {
      event.preventDefault();
      return false;
  }

  // 2. Realizamos validaciones
  if ($('#detFact >tbody >tr').length == 0){
    $('#alertitem').show();
    event.preventDefault(); 
    return false;
  }

  var condet = 0, conpro = 0, concant = 0;
  $('#detFact >tbody >tr').each(function(){
    var det = $(this).find("td:eq(3) > input").val();
    var pro = $(this).find("td:eq(2) > input").val();
    var cant = $(this).find("td:eq(0) > input").val();
    if(pro==''){ conpro++; }
    else if(det==''){ condet++; }
    else if(cant<1){ concant++; }
  });

  // Mostramos alertas si falta algo
  if(conpro > 0){ $('.alertpro').show(); event.preventDefault(); return false; } else { $('#alertpro').hide(); }
  if(condet > 0){ $('.alertdet').show(); event.preventDefault(); return false; } else { $('#alertdet').hide(); }
  if(concant > 0){ $('.alertcant').show(); event.preventDefault(); return false; } else { $('#alertcant').hide(); }

  // Verificamos si la validación general de jQuery (campos obligatorios) tiene errores
  if (!$(this).valid()) {
      return false;
  }

  // 3. SI TODO ESTÁ PERFECTO: Cerramos el candado y cambiamos el botón
  nota_en_proceso = true; // Bloqueo absoluto de JS
  
  var $btn = $("#btn");
  $btn.prop('disabled', true); // Bloqueo visual y físico del botón
  $btn.html('<i class="fa fa-spinner fa-spin"></i> PROCESANDO...'); 

  // Dejamos que el formulario se envíe a Laravel con normalidad...
});

//FIN ---------------------------------------------------------------------------------

$('#isc').on('change', function() {
  if($('#isc').val()==0 || $('#isc').val()==""){
    $('#isc').val($svalor.toFixed(2))
  }
})

$('#inaf').on('change', function() {
  if($('#inaf').val()==0 || $('#inaf').val()==""){
    $('#inaf').val($svalor.toFixed(2))
  }
})

$('#grat').on('change', function() {
  if($('#grat').val()==0 || $('#grat').val()==""){
    $('#grat').val($svalor.toFixed(2))
  }
})

$('#grav').on('change', function() {
  if($('#grav').val()==0 || $('#grav').val()==""){
    $('#grav').val($svalor.toFixed(2))
  }
})
$('#exon').on('change', function() {
  if($('#exon').val()==0 || $('#exon').val()==""){
    $('#exon').val($svalor.toFixed(2))
  }
})
$('#igv').on('change', function() {
  if($('#igv').val()==0 || $('#igv').val()==""){
    $('#igv').val($svalor.toFixed(2))
  }
})

$('#desc').on('change', function() {
  if($('#desc').val()==0 || $('#desc').val()==""){
    $('#desc').val($svalor.toFixed(2))
  }
})

$('#otros').on('change', function() {
  if($('#otros').val()==0 || $('#otros').val()==""){
    $('#otros').val($svalor.toFixed(2))
  }
})

$('#otrosc').on('change', function() {
  if($('#otrosc').val()==0 || $('#otrosc').val()==""){
    $('#otrosc').val($svalor.toFixed(2))
  }
})


}); 
</script>


<script  type="text/javascript">


  function deleteRow(btn) {

    var row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
    calculartotal();

    if ($('#detFact >tbody >tr').length == 0){
      $('.alertitem').show();
    }
    validartabla();
  };

  function Calcular(ele) {


    var tr = ele.parentNode.parentNode;

    $(tr).each(function() {
      var totgrat=0, totgrav=0, totinef=0,totexon=0,totigvi=0;
      var totitemgrat=0, totitemgrav=0, totiteminef=0, totitemexon=0,totitemivap=0;
      var calculo;
      var tigv = $(this).find("td:eq(4) > select").val();

      calculo = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val(); 

      $(this).find("td:eq(9) > input").val(calculo.toFixed(2));


       //Calculo por tipos de IGV
     
   if(tigv=='10') {
        totitemgrav = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
        igv = totitemgrav * 0.18;

        $(this).find("td:eq(7) > input").val((calculo/1.1055).toFixed(2));

        $(this).find("td:eq(8) > input").val(((calculo/1.1055)*0.18).toFixed(2));
      
   }

       
       if(tigv=='17') {

        totitemivap = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();
        ivap = totitemivap * 0.04;
        $(this).find("td:eq(9) > input").val((calculo)*1.04.toFixed(2));
        $(this).find("td:eq(8) > input").val((calculo*0.04).toFixed(2));
        // $('#total').val(total);

      }  

      if (tigv=='20' || tigv=='21')
      {

        totitemexon = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();
        $(this).find("td:eq(9) > input").val(calculo.toFixed(2));
        $(this).find("td:eq(8) > input").val(0);
          //totexon=  totexon + parseFloat($('#exon').val())+totitemexon; 
            //$('#exon').val(totexon);

          }
          if (tigv=='31' || tigv=='32' || tigv=='33' || tigv=='34' || tigv=='35' || tigv=='36' || tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='16' )
          {
            totitemgrat = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();
            $(this).find("td:eq(9) > input").val(calculo.toFixed(2));
            $(this).find("td:eq(8) > input").val(0);
          //totgrat =  totgrat + parseFloat($('#grat').val())+totitemgrat; 
            //$('#grat').val(totgrat);
          } 
          if (tigv=='30' || tigv=='40')
          {
            totiteminaf = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();
            $(this).find("td:eq(9) > input").val(calculo.toFixed(2));
            $(this).find("td:eq(8) > input").val(0);
          //totinaf =  totinaf + parseFloat($('#inaf').val())+totiteminaf; 
           //$('#inaf').val(totinaf);
         }

       });
    calculartotal();

  };

  function validartabla(){
   var condet = 0,conpro=0,concant=0;
   $('#detFact >tbody >tr').each(function(){
    var det = $(this).find("td:eq(3) > input").val();
    var pro = $(this).find("td:eq(2) > input").val();
    var cant = $(this).find("td:eq(0) > input").val();
    if(pro==''){
      conpro++;
    }else if(det==''){
      condet++
    }else if(cant<1){
      concant++               }
    })
   if(conpro>0){
    $('.alertpro').show(); 
  }else{
    $('#alertpro').hide();
  }   

  if(condet>0) {
    $('.alertdet').show(); 
  }else{
    $('#alertdet').hide(); 
  }

  if(concant>0){
    $('.alertcant').show();
  }else{
    $('#alertcant').hide(); 
  }
  if(conpro>0 || condet >0 || concant>0){
    event.preventDefault(); 
  }
};


function calculartotal(){

 var totgrav = 0,totinaf=0,totexon=0,totgrat=0,topivap=0,total,gravdesc=0,totigv=0;
 $("#detFact tbody tr").each(function(){
    //sum= sum + parseFloat($(this).find("td:eq(5) > input").val()) ;


    var tigv = $(this).find("td:eq(4) > select").val();
       //Calculo por tipos de IGV
       if(tigv=='10'){

        totgrav = totgrav + parseFloat($(this).find("td:eq(9) > input").val());
        


      }  
      if(tigv=='17') {

        totivap = totivap + parseFloat($(this).find("td:eq(9) > input").val());
        // $(this).find("td:eq(6) > input").val(totitem.toFixed(2));
        // total =  total + parseFloat($('#total').val())+totitem; 
        // $('#total').val(total);

      }  

      if (tigv=='20' || tigv=='21')
      {

        totexon = totexon + parseFloat($(this).find("td:eq(9) > input").val());


      }
      if (tigv=='31' || tigv=='32' || tigv=='33' || tigv=='34' || tigv=='35' || tigv=='36' || tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='16' )
      {
        totgrat = totgrat + parseFloat($(this).find("td:eq(9) > input").val());

      } 
      if (tigv=='30' || tigv=='40')
      {
        totinaf = totinaf + parseFloat($(this).find("td:eq(9) > input").val());

      }



      


     
     $('#inaf').val(totinaf.toFixed(2));
     $('#grat').val(totgrat.toFixed(2));
       $('#grav').val((totgrav/1.1055).toFixed(2));
       $('#exon').val(totexon.toFixed(2));
       $('#igv').val(((totgrav/1.1055)*0.18).toFixed(2));
     


       var total =  parseFloat(totgrav)  + parseFloat(totexon)+ parseFloat(totinaf);
       $('#total').val((total).toFixed(2));

     });

 if ($('#detFact >tbody >tr').length == 0){
   var $svalor=0
   $('#inaf').val($svalor.toFixed(2));
   $('#grat').val($svalor.toFixed(2));
   $('#grav').val($svalor.toFixed(2));
   $('#exon').val($svalor.toFixed(2));
   $('#igv').val($svalor.toFixed(2));
   $('#desc').val($svalor.toFixed(2));
   $('#otros').val($svalor.toFixed(2));
   $('#otrosc').val($svalor.toFixed(2));
   $('#total').val($svalor.toFixed(2))
 };

}   
</script>



<br>

{!!Form::open(array('url'=>'/SisFact/registrarnota','autocomplete'=>'off','method'=>'POST','id'=>'formnota','role'=>'form','files'=>'true'))!!}
{{Form::token()}}
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-default">
        <div class="panel-heading" style="background:blue;">
          @if($ncdcod=='07')
          <font size="4" color="white"><center><strong>Datos Nota de Crédito</strong></center></font>
          @elseif($ncdcod=='08')
          <font size="4" color="white"><center><strong>Datos Nota de Débito</strong></center></font>
          @endif
        </div>

              
                  
                    
                    <div class="panel-body">
                      <input type='hidden' name='txt_tdocod' id="txt_tdocod" value='{{$ncdcod}}'>
                       <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                            
                             <label>Tipo Nota de Crédito</label>
                           
                             <div class="input-group">
                               <select name="tipnot" id="tipnot" class="form-control">
                                    @if($ncdcod=='07')
                                        @foreach ($nota as $not)

                                          <option  value="{{$not->nccod}}">{{$not->ncdes}}</option>
                                        @endforeach
                                
                                    @elseif($ncdcod=='08')

                                           @foreach ($nota as $not)
                                           <option  value="{{$not->ndcod}}">{{$not->nddes}}</option>
                                           @endforeach
                                          

                                    @endif

                            
                               </select>

                          
                            </div>
                          </div>
                        </div>


                        <div class="col-lg-2 col-md-2 col-ti-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Serie</label>
                                
                          
                                  @if($tdocod=='01')
                                    <input name="serdoc"  value="{{$senuncd->FcseEmpresa}}" id="serdoc" class="form-control" readonly="readonly">
                                  @elseif($tdocod=='03')
                                    <input name="serdoc"  value="{{$senuncd->BcseEmpresa}}" id="serdoc" class="form-control" readonly="readonly">
                                  @endif
                                
                            </div>
                        </div>

                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>N° Doc.</label>
                           
                                  @if($tdocod=='01')
                                       <input type="number"  id="numdoc" name="numdoc" value="{{$senuncd->FcnuEmpresa+1}}" class="form-control" readonly="readonly">
                                  @elseif($tdocod=='03')
                                        <input type="number"  id="numdoc" name="numdoc" value="{{$senuncd->BcnuEmpresa+1}}" class="form-control" readonly="readonly">
                                  @endif
                                
                              
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Emision</label>
                                <input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control" readonly="readonly">
                            </div>
                        </div>
                       

              
                        <input type="hidden" name="tdocod" id="tdocod" value={{$tdocod}} class="form-control">

                    </div>
        <div class="panel-body">
         <input type='hidden' name='txt_tdocod' id="txt_tdocod" value='{{$ncdcod}}'>

        
      <input type="hidden" name="tdocod" id="tdocod" value={{$tdocod}} class="form-control">

    </div>
  </div>  
</div>

</div>
</div>

<div class="container-fluid">
 <div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading" style="background:blue;">
        <font color="white"><strong>DOCUMENTO A MODIFICAR</strong></font>
      </div>
      <div class="panel-body">
        <div class="row">

          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Serie</label>
              <input type="text"  name="serdocmod" id="serdocmod" value="{{$cabecera->serdoc}}"  placeholder="" class="form-control" readonly="readonly">
            </div>
          </div>

          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label for="clinum">Número</label>
              <input type="text"  name="numdocmod" id="numdocmod" value="{{$cabecera->numdoc}}"  placeholder="" class="form-control" readonly="readonly">
            </div>
          </div>

          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Operación</label>
              <input name="topdes" id="topdes" class="form-control" value="{{$cabecera->topdes}}" readonly="readonly">
            </div>
          </div>

          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Tipo Documento</label>
              <input name="tdomod" id="tdomod" class="form-control" value="{{$cabecera->tdodes}}" readonly="readonly">
              <input type="hidden" name="tdo_cod" id="tdo_cod" class="form-control" value="{{$cabecera->tdocod}}" readonly="readonly">
            </div>
          </div>


          <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Moneda</label>
              <input type="text"  name="mondoc" id="mondoc" value="{{$cabecera->monnom}}"  placeholder="" class="form-control" readonly="readonly">
              <input type="hidden"  name="tipmon" id="tipmon"  value="{{$cabecera->moncod}}"  placeholder="" class="form-control" readonly="readonly">
            </div>
          </div>

          <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Tipo de Cambio</label>
              <input  type='text' name="camdoc"  value="{{$cabecera->tipcambio}}"  id="camdoc" class="form-control" readonly="readonly">

            </div>
          </div>

        </div>
      </div>
    </div>  
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-default">
  <div class="panel-heading" style="background:blue;">
        <font color="white"><strong>DATOS DEL CLIENTE</strong></font>
      </div>
      <div class="panel-body">

        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
        <input type="hidden"  name="idcabecera" id="idcabecera" value="{{$cabecera->IdCpe_cabecera}}"  class="form-control" readonly="readonly">
        <div class="row">

          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Tipo Documento</label>
              <input type="text"  name="tdides" id="tdides" value="{{$cabecera->tdides}}"  placeholder="" class="form-control" readonly="readonly">
              <input type="hidden"  name="tdicod" id="tdicod" value="{{$cabecera->tdicod}}"  placeholder="" class="form-control" readonly="readonly">
            </div>
          </div>

          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label for="clinum">N° Documento</label>
              <input type="text"  name="clinum" id="clinum" value="{{$cabecera->ccandi}}"  placeholder="" class="form-control" readonly="readonly">

            </div>
          </div>

          <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Nombre ó Razón Social</label>
              <input type="text" name="clinom" id="clinom" value="{{$cabecera->ccanom}}" class="form-control" readonly="readonly">

            </div>
          </div>

        </div>

      
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Dirección</label>
              <input name="clidir" id="clidir" value="{{$cabecera->clidir}}" class="form-control" readonly="readonly">
            </div>
          </div>

          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Correo Electrónico</label>
              <input name="clicor" id="clicor" value="{{$cabecera->clicor}}" class="form-control" readonly="readonly">
            </div>
          </div>

        
      </div>
    </div>  
  </div>
</div>


</div>

 <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading" style="background:blue;">
                      <font color="white"><strong>DETALLE NOTA DE CREDITO</strong></font>
                    </div>
                    
                    <div  class="panel-body">
                
                      
                    
                        <table id="detFact" class="table">
                            <thead>
                                <th>Cant.</th>
                                <th>Unidad</th>
                                <th>Producto o Servicio</th>
                                <th>Detalle</th>
                                <th>Tipo IGV</th>
                                <th hidden="hidden">Valor Unitario</th>
                                   <th>Precio Unitario</th>
                                <th hidden="hidden">Subtotal</th>
                                <th hidden >IGV</th>
                                <th>Total</th>
                              
                                <th><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus" readonly="readonly"></span></button></th>
                            </thead>
                            <tbody>
                              @foreach($detalle as $det)
                              <tr>
                                <td>
                               
                                    <input type="number"  step="any" id="cant" size="10" value="{{number_format($det->cdecan,2,'.','')}}" name="cant[]"   OnKeyUp="Calcular(this);" class="cant form-control input-sm">
                                 
                                </td>

                                <td>
                                  <input type="text" readonly="readonly"  value="{{$det->umenom}}" class="codpro form-control input-sm">
                                  <select style="visibility:hidden;width:100px" name="unid[]"  class="form-control input-sm">
                                      <option  value="{{$det->umecod}}" selected>{{$det->umenom}}</option>
                                      @foreach ($unidades as $und)
                                        <option  value="{{$und->umecod}}">{{$und->umenom}}</option>
                                      @endforeach
                                  </select>
                                </td>

                               
                                <td>
                                   <input type="text" readonly="readonly" name="codpro[]" id="codpro"  value="{{$det->IdProducto}}"  class="codpro form-control input-sm">
                                </td>
                             
                            
                                  <td>
                                    <input class="detpro form-control input-sm" name="detpro[]" value="{{$det->cdedes}}" id="detpro" size="100">
                                  </td>
                             
                                <td>
                                  <input type="text" readonly="readonly"  value="{{$det->tigdes}}" class="form-control input-sm">
                                  <select id="tigv" name="tigv[]"  class="form-control input-sm" style="visibility:hidden;">
                                    <option value="{{$det->tigcod}}" selected>{{$det->tigdes}}</option>
                                    @foreach ($igv as $tigv)
                                      @if($tigv==$det->cdevve)
                                        <option value="{{$tigv->tigcod}}" selected>{{$tigv->tigdes}}</option>
                                      @else
                                        <option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>
                                      @endif
                                    @endforeach
                                  </select>
                                 
                                </td>
                            
                                 <td hidden>
                                   <input type="text"   id="vunit" style="text-align:right;" value="{{number_format($det->cdevun,2,'.','')}}" name="vunit[]" class="form-control input-sm" />
                                </td>
                                
                                <td>
                               
                                  <input type="number" id="preuni" value="{{number_format($det->cdepuni,2,'.','')}}" name="preuni[]" style="text-align:right;"   OnKeyUp="Calcular(this);" class="form-control input-sm">
                                 
                                </td>
                                
                                <td hidden="hidden">
                                  <input type="text" class="form-control input-sm" size="20px" id="vsub" value="{{number_format($det->cdepve,2,'.','')}}" readonly value="0" style="text-align:right;" name="vsub[]"/>
                                </td>

                                <td hidden>
                                  <input type="text" class="form-control input-sm" size="20px" id="vigv" value="{{number_format($det->cdeigv,2,'.','')}}" readonly="readonly" value="0" style="text-align:right;" name="vigv[]"/>
                                </td>

                                <td>
                                   <input type="text" readonly="readonly" id="vtot" style="text-align:right;" value="{{number_format($det->cdevve,2,'.','')}}" name="vtot[]" class="form-control input-sm" />
                                </td>
                              

                                <td>
                                   
                                  <button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm btndel"><span class="glyphicon glyphicon-minus"></span></button>
                                   
                                </td>
                              </tr>
                              @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>

        </div>
     
        
                <div class="col-lg-8">
                    <div class="panel panel-default">
                       <div class="panel-heading" style="background:blue;">
                          <font color="white"> <strong>Descripción del Motivo o Sustento</strong></font>
                        </div>
                      
                        <div class="panel-body">
                          <div class="form-group">
                              <textarea required="required" maxlength="100" class="form-control" id="obser" name="obser" rows="1">ANULACION DE LA OPERACION</textarea>
                           </div>
                       </div>
                    </div>

                    
                    <button type="submit" id="btn" name="btn"  class="btn btn-primary"><strong>REGISTRAR NOTA DE CRÉDITO</strong></button>
                    <a href="{{config('global.ruta')}}/SisFact"><button type="button" class="btn btn-danger"><strong>CANCELAR</strong></button></a>
                </div>
                <div class="col-lg-4" >
                    <div class="panel panel-default">
                        <div  class="panel-body">
                            <table class="table" border="0">
                                    
                                    <tr>
                                        <td><strong>Exoneradas</strong></td>
                                        <td><input clas="form-control" type="number"  step="any" value="{{number_format($cabecera->ccatexo,2,'.','')}}"  style="text-align:right;" id="exon" name="exon" value='0.00' readonly="readonly"></td>
                                    </tr>
                                 
                                    <tr>
                                        <td><strong>Gravadas</strong></td>
                                        <td><input clas="form-control" type="text" style="text-align:right;"  value="{{number_format($cabecera->ccatvg,2,'.','')}}" id="grav" name="grav" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>IGV 18%</strong></td>
                                        <td><input clas="form-control" type="number" value="{{number_format($cabecera->ccaigv,2,'.','')}}" step="any" style="text-align:right;" id="igv" name="igv" value='0.00' readonly="readonly"></td>
                                    </tr>
                             
                                  
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td><input clas="form-control" type="number" min="0" value="{{number_format($cabecera->ccaitv,2,'.','')}}"  step="any" style="text-align:right;" id="total" name="total"  readonly="readonly"></td>
                                    </tr>
                                    
                            </table>
                        </div>
                    </div>
                </div>
            
                </div>
                
            </div>



{!!Form::close()!!}     
@endsection