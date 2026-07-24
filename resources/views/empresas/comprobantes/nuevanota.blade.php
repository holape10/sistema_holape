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

#formfact label.error {
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
		
		$("#btnPrint").printPage({
		
		  
		  url: "/imprimir/"+comprobante+"/"+documento,
		  attr: "href",
		  messageBox:false
		})
		
        $("#serdocmod").on('change',function(){

            var serie = $('#serdocmod').val();
            var posi = serie.indexOf("-",0);
            var seriemod = serie.substring(0,posi);
            $('#serdocmod').val(seriemod);
            $('#serdocmod').prop("readonly",true);

        })

          $("#serdocmod").on('dblclick', function (){
            
            $('#serdocmod').prop("readonly",false);
            $('#serdocmod').val('');
            $('#numdocmod').val('');
            $('#topdes').val('');
            $('#tdomod').val('');
            $('#tdo_cod').val('');
            $('#mondoc').val('');
            $('#tipmon').val('');
            $('#camdoc').val('');
            $('#tdides').val('');
            $('#tdicod').val('');
            $('#clinum').val('');
            $('#clinom').val('');
            $('#clidir').val('');
            $('#clicor').val('');
            
        })

 

        jQuery.validator.addMethod("alphanumeric", function(value, element) {
            return this.optional(element) || /^[\w.]+$/i.test(value);
        }, "Letters, numbers, and underscores only please"),

      
        $('#formfact').validate({

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
    


        $('#add').click(function() {
            iCnt = iCnt + 1;
            $('.alertitem').hide();
        
         
                $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><input type="text"  name="codpro[]" id="codpro"  OnKeyUp="Calcular(this)";  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario();" class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" onfocus="Calcular(this)"; ></td><td><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td><input type="number" step=".00001" id="vunit" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input type="text" class="form-control input-sm preuni" size="20px" id="preuni"  readonly="readonly" OnKeyUp="Calcular(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="0" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly value="0" style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="0" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm" />@if ($errors->has("vtot"))<span class="help-block"><strong><font style="text-align: right;" color="red">{{ $errors->first("vtot") }}</font></strong></span>@endif</td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
         
		
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
        $( "#formfact" ).submit(function( event ) {
            
            if ($('#detFact >tbody >tr').length == 0){
                $('#alertitem').show();
                $("#btn").prop('readonly',false);
                event.preventDefault(); 
            }
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
                    concant++
                }
            })

            if(conpro>0){
                $('.alertpro').show(); 
                event.preventDefault();  
            }else{
                $('#alertpro').hide();
            }   

            if(condet>0) {
                $('.alertdet').show();
                event.preventDefault();   
            }else{
                $('#alertdet').hide(); 
            }

            if(concant>0){
                $('.alertcant').show();
                event.preventDefault(); 
            }else{
                $('#alertcant').hide(); 
            }

      
            /*if($('#mondoc').val()!='PEN' && $('#camdoc').val()<=0 ){
              $('#error-camdoc').show();
        
              event.preventDefault(); 
            }*/
        })

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
      var totgrat=0, totgrav=0, totinef=0,totexon=0,totigvi=0,topexp;
      var totitemgrat=0, totitemgrav=0, totiteminef=0, totitemexon=0,totitemivap=0,totitemexp=0;
      var calculo, valuni, totitem,presigv,subtotal,total,igvitem;
      var tigv = $(this).find("td:eq(4) > select").val();
	  var precigv = $(this).find("td:eq(6) > input").val();
	  var cantidad =$(this).find("td:eq(0) > input").val();
       
       
       if(tigv=='10'){

        totitemgrav = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
        igv = totitemgrav * 0.18;
	
		presigv = (precigv/1.1055);
	    subtotal = presigv*cantidad;
		total = subtotal*1.18;
		igvitem = subtotal*0.18;

		$(this).find("td:eq(5) > input").val(presigv.toFixed(2));		
		$(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
		$(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
		$(this).find("td:eq(9) > input").val(total.toFixed(2));
	
    
       } 
       if(tigv=='17') {

        totitemivap = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(4) > input").val();
        ivap = totitemivap * 0.04;
      
		presigv = (precigv/1.04);
		subtotal = presigv*cantidad;
		total = subtotal*1.04;
		igvitem = subtotal*0.04;

		$(this).find("td:eq(5) > input").val(presigv.toFixed(2));
		
		$(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
		$(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
		$(this).find("td:eq(9) > input").val(total.toFixed(2));
			
       }  

       if (tigv=='20' || tigv=='21')
       {

		totitemexon = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
     
		presigv = precigv;
		subtotal = presigv*cantidad;
		total = subtotal;
		igvitem = 0;

		$(this).find("td:eq(5) > input").val(presigv.toFixed(2));
		
		$(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
		$(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
		$(this).find("td:eq(9) > input").val(total.toFixed(2));
		
		

       }
        if (tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='16' || tigv=='21' || tigv=='31' || tigv=='32' || tigv=='33' || tigv=='34' || tigv=='35' || tigv=='36' )
       {
          totitemgrat = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
		  presigv = precigv;
		  subtotal = presigv*cantidad;
		  total = subtotal;
		  igvitem = 0;

			$(this).find("td:eq(5) > input").val(presigv.toFixed(2));
			
			$(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
			$(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
			$(this).find("td:eq(9) > input").val(total.toFixed(2));
       } 
       if (tigv=='30')
       {
          totiteminaf = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
          presigv = precigv;
		  subtotal = presigv*cantidad;
		  total = subtotal;
		  igvitem = 0;

		$(this).find("td:eq(5) > input").val((presigv).toFixed(2));
		
			$(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
			$(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
			$(this).find("td:eq(9) > input").val(total.toFixed(2));
       }

       if (tigv=='40')
       {
            totitemexp = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
            presigv = precigv;
			subtotal = presigv*cantidad;
			total = subtotal;
			igvitem = 0;

			$(this).find("td:eq(5) > input").val(presigv);
			
			$(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
			$(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
			$(this).find("td:eq(9) > input").val(total.toFixed(2));
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
    
   var totgrav = 0,totinaf=0,totexon=0,totgrat=0,topivap=0,total,gravdesc=0;
    $("#detFact tbody tr").each(function(){
    //sum= sum + parseFloat($(this).find("td:eq(5) > input").val()) ;


       var tigv = $(this).find("td:eq(4) > select").val();
       //Calculo por tipos de IGV
       if(tigv=='10'){

        totgrav = totgrav + parseFloat($(this).find("td:eq(6) > input").val());
        
     

       }  
       if(tigv=='17') {

        totivap = totivap + parseFloat($(this).find("td:eq(6) > input").val());
        // $(this).find("td:eq(6) > input").val(totitem.toFixed(2));
        // total =  total + parseFloat($('#total').val())+totitem; 
        // $('#total').val(total);

       }  

       if (tigv=='20' || tigv=='21')
       {

          totexon = totexon + parseFloat($(this).find("td:eq(6) > input").val());
            

       }
        if (tigv=='31' || tigv=='32' || tigv=='33' || tigv=='34' || tigv=='35' || tigv=='36' || tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='16' )
       {
            totgrat = totgrat + parseFloat($(this).find("td:eq(6) > input").val());
           
       } 
       if (tigv=='30' || tigv=='40')
       {
            totinaf = totinaf + parseFloat($(this).find("td:eq(6) > input").val());
          
       }

     
       var otrosc = $('#otrosc').val();
       var otros = $('#otros').val();
       var isc = $('#isc').val();
       var desc = $('#desc').val();

      

       if(otrosc ==""){
         $('#otrosc').val(0);
       }

       if(otros ==""){
         $('#otros').val(0);
       }

       if(isc ==""){
         $('#isc').val(0);
       }

       if(desc ==""){
         $('#desc').val(0);
       }

       gravdesc = (totgrav-desc);
       totigv = (gravdesc*0.18);
       $('#inaf').val(totinaf.toFixed(2));
       $('#grat').val(totgrat.toFixed(2));
       //$('#grav').val(totgrav.toFixed(2));
       $('#exon').val(totexon.toFixed(2));
       $('#igv').val((totigv).toFixed(2));
       $('#grav').val((gravdesc).toFixed(2));
    

       var total = parseFloat($('#otrosc').val()) + parseFloat($('#otros').val()) +parseFloat(gravdesc) + parseFloat(totigv) + parseFloat($('#exon').val())+ parseFloat($('#isc').val())+ parseFloat($('#inaf').val());
       $('#total').val(total.toFixed(2));
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



    
    <div class="row">
            <div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="alertcomp">
                <div class="modal-dialog">
          
                <div  class="alert alert-danger">
                    <strong>Atención!</strong> El comprobante ya fue emitido.
                </div>
            
        </div>
            </div>
			 <a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
			<input type="hidden" name="comprobante" id="comprobante" value="{{$cpe}}">
			<input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
					
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  @if($ncdcod=='07')
                <center><h3>EMITIR NOTA DE CRÉDITO</h3></center>
              @elseif($ncdcod=='08')
                <center><h3>EMITIR NOTA DE DÉBITO</h3></center>
              @endif
            </div>

    </div>
    {!!Form::open(array('url'=>'/emitirnota','autocomplete'=>'off','method'=>'get','id'=>'formnota','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                      @if($ncdcod=='07')
                        <strong>Datos Nota de Crédito</strong>
                       @elseif($ncdcod=='08')
                         <strong>Datos Nota de Débito</strong>
                       @endif
                    </div>
                    
                    <div class="panel-body">
                        <input type='hidden' name='txt_tdocod' id="txt_tdocod" value='{{$ncdcod}}'>

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                          <div class="form-group form-group-sm">
                             @if($ncdcod=='07')
                                <label>Tipo Nota de Crédito</label>
                               @elseif($ncdcod=='08')
                                <label>Tipo Nota de Débito</label>
                               @endif
                            
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
                          
                              <div class="input-group-btn">
                                <button type="submit" id="btn"   class="btn btn-primary input-sm"><strong>SIGUIENTE</strong></button>
                              </div>
                            </div>
                          </div>
                        </div>
                          
                        <input type="hidden" name="tdocod" id="tdocod" value='' class="form-control">

                    </div>
                </div>  
            </div>
        </div>
        </div>
        <div class="container-fluid">
 <div class="row">
                <div class="col-lg-12">
    <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>DOCUMENTO A MODIFICAR</strong>
                    </div>
                    <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Serie-Número</label>
                                <input type="text"  name="serdocmod" id="serdocmod" value=""  placeholder="" class="form-control" >
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">Número</label>
                                <input type="text"  name="numdocmod" id="numdocmod" value=""  placeholder="" class="form-control" readonly="readonly">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Operación</label>
                                <input name="topdes" id="topdes" class="form-control" value="" readonly="readonly"></input>
                                
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Documento</label>
                                <input name="tdomod" id="tdomod" class="form-control" value="" readonly="readonly"></input>
                                <input type="hidden" name="tdo_cod" id="tdo_cod" class="form-control" value="" readonly="readonly"></input>
                            </div>
                        </div>

                        
                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Moneda</label>
                                <input type="text"  name="mondoc" id="mondoc" value=""  placeholder="" class="form-control" readonly="readonly">
                                <input type="hidden"  name="tipmon" id="tipmon"  value=""  placeholder="" class="form-control" readonly="readonly">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo de Cambio</label>
                                <input  type='text' name="camdoc"  value=""  id="camdoc" class="form-control" readonly="readonly">
                            
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
                    <div class="panel-heading">
                        <strong>Datos del Cliente</strong>
                    </div>
                    <div class="panel-body">
                        
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value=''>
                        <input type="hidden"  name="idcabecera" id="idcabecera" value=""  class="form-control" readonly="readonly">
                        <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Documento</label>
                                <input type="text"  name="tdides" id="tdides" value=""  placeholder="" class="form-control" readonly="readonly">
                                <input type="hidden"  name="tdicod" id="tdicod" value=""  placeholder="" class="form-control" readonly="readonly">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">N° Documento</label>
                                <input type="text"  name="clinum" id="clinum" value=""  placeholder="" class="form-control" readonly="readonly">
                                
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre ó Razón Social</label>
                                <input type="text" name="clinom" id="clinom" value="" class="form-control" readonly="readonly">
                               
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Dirección</label>
                                <input name="clidir" id="clidir" value="" class="form-control" readonly="readonly">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electrónico</label>
                                <input name="clicor" id="clicor" value="" class="form-control" readonly="readonly">
                            </div>
                        </div>
                    </div>
                    </div>
                </div>  
            </div>
            </div>
        </div>
       
        
        {!!Form::close()!!}     
@endsection