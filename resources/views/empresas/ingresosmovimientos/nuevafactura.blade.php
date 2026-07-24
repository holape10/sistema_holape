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
.btn-default.btn-on.active{background-color: #5BB75B;color: white;}
.btn-default.btn-off.active{background-color: #DA4F49;color: white;}
</style>

<script>
    $.validator.setDefaults({
    ignore: [],
    // any other default options and/or rules
    });

    $(document).ready(function()
    {
        var $svalor=0;
        var iCnt = 0;
        var comprobante = $("#comprobante").val();
        var documento = $("#documento").val();
      $("#btnPrint").printPage({

          url: "/imprimir/"+comprobante+"/"+documento,
          attr: "href",
          messageBox:false

        })

        $('.detalle').keypress(function(e) {

            if(e.keyCode == 43) {
                agregarlinea();
                return false;
            }

        })







          $('.codpro').on('change',function () {

                       validartabla();
                })


                  $('.detpro').on('change',function(){
                      validartabla();

                })

                 $('.cant').on('change',function () {
                     validartabla();
                })


                $("#clinum").autocomplete({
                  source: '{!!URL::route('autocomplete')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  select: function(event,ui) {
                     $('#clinom').val(ui.item.nom);
                     $('#clidir').val(ui.item.dir);
                     $('#clicor').val(ui.item.cor);
                     $('#clicod').val(ui.item.clicod);
                     $("#tdicod").val(ui.item.tdicod).attr('selected', 'selected');
                     $('#clinum').prop("readonly",false);
                     $('#clinom').prop("readonly",false);
                     $('#clidir').prop("readonly",false);
                     $('#clicor').prop("readonly",false);
                     $('#clicod').prop("readonly",false);
                     $('.codpro').focus();


                  }
                })

               $(".codpro").autocomplete({
                  source: '{!!URL::route('consultaringreso')!!}',
                  dataType: "json",
                  minLength: 2,
                  autoFocus:true,
                  select: function(event,ui) {
                     $(this).closest('tr').find("td:eq(3) > input").val(ui.item.pronom);
                     $(this).closest('tr').find("td:eq(5) > input").val(ui.item.provun);
                     $(this).closest('tr').find("td:eq(6) > input").val(ui.item.propun);
					 $(this).closest('tr').find("td:eq(9) > input").val(ui.item.propun);
                     $(this).closest('tr').find("td:eq(1) > select").val(ui.item.umecod).attr('selected', 'selected');
                     $(this).closest('tr').find("td:eq(2) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(3) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);

                  }
                })

				 $(".detpro").autocomplete({
                  source: '{!!URL::route('consultaringresonom')!!}',
                  dataType: "json",
                  minLength: 1,
                  autoFocus:true,
                  select: function(event,ui) {   
                     $(this).closest('tr').find("td:eq(2) > input").val(ui.item.codpro);
                     $(this).closest('tr').find("td:eq(5) > input").val(ui.item.provun);
                     $(this).closest('tr').find("td:eq(6) > input").val(ui.item.propun);
					 $(this).closest('tr').find("td:eq(9) > input").val(ui.item.propun);
                     $(this).closest('tr').find("td:eq(1) > select").val(ui.item.umecod).attr('selected', 'selected');
                     $(this).closest('tr').find("td:eq(2) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(3) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);
                  }
                })

                $(".codpro").on('dblclick', function (){
                    $(this).closest('tr').find("td:eq(2) > input").prop("readonly",false);
                     //$(this).closest('tr').find("td:eq(3) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(0) > input").val(1);
                    $(this).closest('tr').find("td:eq(2) > input").val('');
                    $(this).closest('tr').find("td:eq(3) > input").val('');
                    $(this).closest('tr').find("td:eq(5) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(6) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(7) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(8) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(9) > input").val(0.00);
                    calculartotal();
                    validartabla();

                })


        $("#numdoc").on('change',function(){
            var numdoc = parseInt($('#numdoc').val(),10);
            $("#numdoc").val(numdoc);
        })

        $('#codunique').val($('#txt_IdEmpresa').val()+''+$('#txt_tdocod').val()+''+$('#serdoc').val() + '' +parseInt($('#numdoc').val(),10));


        $('#serdoc').on('change', function() {
              $('#codunique').val($('#txt_IdEmpresa').val()+''+$('#txt_tdocod').val()+''+$('#serdoc').val() + '' +parseInt($('#numdoc').val(),10));
        })

        $('#numdoc').on('change', function() {
              $('#codunique').val($('#txt_IdEmpresa').val()+''+$('#txt_tdocod').val()+''+$('#serdoc').val() + '' +parseInt($('#numdoc').val(),10));
        })

        $('#mondoc').on('change',function (){
            var fecemi = $("#fecEmi").val();
            if($('#mondoc').val()!='1'){
               $.ajax({
                 type: "get",
                 url:"/consultartipcambio",
                 data:{fecemi:fecemi},
                  success:function(res) {
                    $("#camdoc").val(res);
                }
             })
            }else {
                $('#camdoc').val(0);
            }

        })


        jQuery.validator.addMethod("alphanumeric", function(value, element) {
            return this.optional(element) || /^[\w.]+$/i.test(value);
        }, "Letters, numbers, and underscores only please"),


        $('#formfact').validate({

            rules: {

                   tdicod:{
                    required:true
                   },


                serdoc:{
                    required:true,
                    alphanumeric:true,
                    maxlength: 4
                },
                fecEmi:{
                    required: true,
                    date: true
                },
                fecEnv:{
                    date: true
                },
                numdoc:{
                    required:true,
                    digits:true,
                    maxlength:8
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
                camdoc:{
                    required : function () {
                                return $('#formfact select[name="mondoc"]').val() != '1';
                               },
                    min: {
                        param: 1,
                        depends:  function () {
                                return $('#formfact select[name="mondoc"]').val() != '1';
                        }
                    }
                },

                fecEmi:"required",
                fecVen:"required",
                clinum:{
                    required:true,
                    digits:true,
                    maxlength:11
                },
                clinom:"required",
                clidir:"required",
                clicor: {
                    email:true
                },
                obser:{
                    maxlength: 250
                }

               },


            messages: {

                tdicod:{
                    required:"Elegir el tipo de documento"
                },
                serdoc:{
                    required:"Ingresar la serie del documento",
                    alphanumeric:"Ingresar un serie válida",
                    maxlength:"El número de serie es de 4 digitos"
                },
             
                numdoc:{
                    required:"Ingresar número del comprobante",
                    digits:"Ingresar un N° de comprobante válido",
                    maxlength:"El N° de comprobante tiene como máximo 8 dígitos"
                } ,
                fecEmi:{
                    required:"Ingresar la fecha de emisión",
                    date:"Ingresar una fecha válida"
                },
                fecEnv:{
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
                clinum:{
                    required:"Ingresar N° Documento de Identidad",
                    digits:"Ingresar un N° de documento válido",
                    maxlength:"El N° documento de identidad es como máximo de 11 dígitos"

                },
                clinom:"Ingresar el nombre del cliente",
                clidir:"Ingresar la dirección del cliente",
                clicor:{
                    email:"Ingresar un email válido"
                },
                obser:{
                    maxlength:"El número máximo de caracteres es de 250"
                }
            }

        })



        if ($("#mondoc").val()!='1'){
            $("#camdoc").prop('readonly',false);
        }else {
            $("#camdoc").prop('readonly',true);
            $("#camdoc").val(0);
        }

        //Agregar al modal filas con los elementos para registrar las guías de remisión
       
       

        $('#add').click(function() {

            agregarlinea();
        })

         $('#addm').click(function() {
 
            agregarlineamayor();
        })

         

         $("#cambia").click(function(){
            $("#texto").toggle(1000);
         })

        $("#addgr").click(function(){
            $('#gremi').show();
        })

//INICIO SUBMIT-----------------------------------------------------------------

        $( "#formfact" ).submit(function( event ) {


            if ($('#detFact >tbody >tr').length == 0){
                $('#alertitem').show();
                event.preventDefault();
            }
            var condet = 0,conpro=0,concant=0;
            $('#detFact >tbody >tr').each(function(){
                var det = $(this).find("td:eq(3) > input").val();
             //   var pro = $(this).find("td:eq(2) > input").val();
                var cant = $(this).find("td:eq(0) > input").val();
               /* if(pro==''){
                    conpro++;
                }else*/
					if(det==''){
                    condet++
                }else if(cant<1){
                    concant++
                }
            })

           /* if(conpro>0){
                $('.alertpro').show();
                event.preventDefault();
            }else{
                $('#alertpro').hide();
            }*/

            if(condet>0) {
                $('.alertdet').show();
                event.preventDefault();
            }else{
                $('#alertdet').hide();
            }

            if(concant>0){
                $('.alertcant').show();
                var cantidad = event.preventDefault();
            }else{
                $('#alertcant').hide();
            }

            validaralfanumerico();
            validarnumero();
            validarexistente();
            /*if($('#mondoc').val()!='1' && $('#camdoc').val()<=0 ){
              $('#error-camdoc').show();

              event.preventDefault();
            }*/



        })

//FIN ---------------------------------------------------------------------------------

        $("#mondoc").on('change',function(){
            var mondoc = $("#mondoc").val();

            if(mondoc == '1'){
                $('#camdoc').val(0);
                 $('#error-camdoc').hide();
                $("#camdoc").prop('readonly',true);
            } else {
                $("#camdoc").prop('readonly',false);
            }

        })


         $('#fecEmi').on('change', function() {
              $('#camdoc').val(0);
        })


        $('#clinum').on('change', function() {
            var clinum = $('#clinum').val();
            var clinom = $('#clinom').val();
            var clidir = $('#clidir').val();
            var clicor = $('#clicor').val();

          /*  if (clinum!="" && clinom!="" && clidir!=""){
                $('#clinom').prop("readonly",true);
                $('#clidir').prop("readonly",true);
                $('#clicor').prop("readonly",true);

                var posi = clinum.indexOf("|",0);
                var clinum1 = clinum.substring(0,posi);
                $('#clinum').val(clinum1);
            }*/
        })

        $('#clinum').on('dblclick', function() {
            $('#clinum').prop("readonly",false);
            $('#clinom').prop("readonly",false);
            $('#clidir').prop("readonly",false);
            $('#clicor').prop("readonly",false);
            $('#clinum').val("");
            $('#clinom').val("");
            $('#clidir').val("--");
            $('#clicor').val("");
        })


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

        $('#clinum').on('change', function() {
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
  calcularvuel();

  if ($('#detFact >tbody >tr').length == 0){
    $('.alertitem').show();
  }
  validartabla();
};



function agregarlinea(){
            var iCnt = 0;
            iCnt = iCnt + 1;
            $('.alertitem').hide();

            $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><input type="text"  name="codpro[]" id="codpro" style="background-color:#ABEBC6"  onFocus="Calcular(this);" onBlur="Calcular(this);" onClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario();" class="detpro form-control input-sm" name="detpro[]" onFocus="Calcular(this);" onBlur="Calcular(this);" onClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; value="" id="detpro" size="100" onfocus="Calcular(this)"; ></td><td hidden="hidden"><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden"><input type="number"  id="vunit" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input type="text" class="form-control input-sm preuni" size="20px" id="preuni"  onFocus="Calcular(this);" onBlur="Calcular(this);" onClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="0" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly value="0" style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="0" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm" />@if ($errors->has("vtot"))<span class="help-block"><strong><font style="text-align: right;" color="red">{{ $errors->first("vtot") }}</font></strong></span>@endif</td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');


       $('.codpro').focus();
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
                  source: '{!!URL::route('consultaringreso')!!}',
                  dataType: "json",
                  minLength: 2,
                  autoFocus:true,
                  select: function(event,ui) {
                     $(this).closest('tr').find("td:eq(3) > input").val(ui.item.pronom);
                     $(this).closest('tr').find("td:eq(5) > input").val(ui.item.provun);
                     $(this).closest('tr').find("td:eq(6) > input").val(ui.item.propun);
					 $(this).closest('tr').find("td:eq(9) > input").val(ui.item.propun);
                     $(this).closest('tr').find("td:eq(1) > select").val(ui.item.umecod).attr('selected', 'selected');
                     $(this).closest('tr').find("td:eq(2) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(3) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);
                  }
                })

				 $(".detpro").autocomplete({
                  source: '{!!URL::route('consultaringresonom')!!}',
                  dataType: "json",
                  minLength: 1,
                  autoFocus:true,
                  select: function(event,ui) {   
                     $(this).closest('tr').find("td:eq(2) > input").val(ui.item.codpro);
                     $(this).closest('tr').find("td:eq(5) > input").val(ui.item.provun);
                     $(this).closest('tr').find("td:eq(6) > input").val(ui.item.propun);
					 	 $(this).closest('tr').find("td:eq(9) > input").val(ui.item.propun);
                     $(this).closest('tr').find("td:eq(1) > select").val(ui.item.umecod).attr('selected', 'selected');
                     $(this).closest('tr').find("td:eq(2) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(3) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);
                  }
                })

                $(".codpro").on('dblclick', function (){
                    $(this).closest('tr').find("td:eq(2) > input").prop("readonly",false);
                     //$(this).closest('tr').find("td:eq(3) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(0) > input").val(1);
                    $(this).closest('tr').find("td:eq(2) > input").val('');
                    $(this).closest('tr').find("td:eq(3) > input").val('');
                    $(this).closest('tr').find("td:eq(5) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(6) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(7) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(8) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(9) > input").val(0.00);
                    calculartotal();
                    validartabla();
                })
}

function Calcular(ele) {


  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {
      var totgrat=0, totgrav=0, totinef=0,totexon=0,totigvi=0;
      var totitemgrat=0, totitemgrav=0, totiteminef=0, totitemexon=0,totitemivap=0;
      var calculo, valuni, totitem,presigv,subtotal,total,igvitem;
      var tigv = $(this).find("td:eq(4) > select").val();
      var precigv = $(this).find("td:eq(6) > input").val();
      var cantidad =$(this).find("td:eq(0) > input").val();

       if(tigv=='1'){

        //totitemgrav = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
        //igv = totitemgrav * 0.18;


        total = cantidad*precigv;
		subtotal = total/1.1055;
        igvitem = total-subtotal;
		presigv = subtotal/cantidad;
		 
        $(this).find("td:eq(5) > input").val(presigv.toFixed(10));
        $(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
        $(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
        $(this).find("td:eq(9) > input").val(total.toFixed(2));

       }

       if (tigv=='8')
       {

        totitemexon = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();

        presigv = (precigv/1);
            subtotal = presigv*cantidad;
            total = subtotal*1;
            igvitem = 0;


        $(this).find("td:eq(5) > input").val(presigv.toFixed(2));

        $(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
        $(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
        $(this).find("td:eq(9) > input").val(total.toFixed(2));



       }

        if (tigv=='10' || tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='2' || tigv=='3' || tigv=='4' || tigv=='5' || tigv=='6' || tigv=='7')
       {
            totitemgrat = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
            presigv = (precigv/1);
            subtotal = presigv*cantidad;
            total = subtotal*1;
            igvitem = 0;

            $(this).find("td:eq(5) > input").val(presigv.toFixed(2));
            $(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
            $(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
            $(this).find("td:eq(9) > input").val(total.toFixed(2));
       }

       if (tigv=='9' || tigv=='16')
       {
          totiteminaf = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(5) > input").val();
          presigv = (precigv/1);
            subtotal = presigv*cantidad;
            total = subtotal*1;
            igvitem = 0;


        $(this).find("td:eq(5) > input").val(presigv);

            $(this).find("td:eq(8) > input").val(subtotal.toFixed(2));
            $(this).find("td:eq(7) > input").val(igvitem.toFixed(2));
            $(this).find("td:eq(9) > input").val(total.toFixed(2));
       }



   });
  calculartotal();
  calcularvuelto();

};

function validartabla(){
     var condet = 0,conpro=0,concant=0;
     $('#detFact >tbody >tr').each(function(){
        var det = $(this).find("td:eq(3) > input").val();
       // var pro = $(this).find("td:eq(2) > input").val();
        var cant = $(this).find("td:eq(0) > input").val();
     /*   if(pro==''){
            conpro++;
        }else*/
		if(det==''){
            condet++
        }else if(cant<1){
            concant++               }
        })
      /*  if(conpro>0){
            $('.alertpro').show();
        }else{
            $('#alertpro').hide();
        }
*/
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

function validaralfanumerico(){

    var condr=0;
    var alfn = /^[\w]+$/;
    var serdr;

    if ($('#grdet >tbody >tr').length == 0){
     $('#grdet >tbody >tr').each(function(){

        serdr = $(this).find("td:eq(1) > input").val();

        if(serdr.trim()!=""){
            if(!alfn.test(serdr.toString())){
                condr++;
            }
            if(condr>0){
                 $('.alertgr').show();
            }else{
                 $('.alertgr').hide();
            }

            if(condr>0){
                event.preventDefault();
            }
        }

    })
    }
};

function validarexistente(){

    var cont=0;
    var tempser,tempnum,tempdocr;

     if ($('#grdet >tbody >tr').length == 0){
     $('#grdet >tbody >tr').each(function(){
        var docr = $(this).find("td:eq(0) > select").val();
        var ser = $(this).find("td:eq(1) > input").val();
        var num =$(this).find("td:eq(2) > input").val();

        if(ser.trim()!="" && num.trim()!=""){
            if(tempser==ser && tempnum==num && tempdocr==docr){
                cont++;
            }
            if(cont>0){
                 $('.alertexist').show();
            }else{
                 $('.alertexist').hide();
            }

            if(cont>0){
                event.preventDefault();
            }
        }

        tempser = ser;
        tempnum = num;
        tempdocr = docr;
    })
    }
};


function validarnumero(){
     if ($('#grdet >tbody >tr').length == 0){
    var connum=0;
    var valnum =  /^\d*$/;

     $('#grdet >tbody >tr').each(function(){
        var num = $(this).find("td:eq(2) > input").val();

        if(num.trim()!=""){
              if(!valnum.test(num.toString())){
                connum++;
            }
            if(connum>0){
                 $('.alertnum').show();
            }else{
                 $('.alertnum').hide();
            }

            if(connum>0){
                event.preventDefault();
            }
        }

    })
    }
};


function calcularvuelto(){

    var dinero, totalv;
    dinero = $('#dinero').val();
    totalv = $('#total').val();

    if(totalv > dinero){
         vuelto = '0.00';
        $('#vuelto').val(vuelto);
    }else{
        vuelto = dinero-totalv;
        $('#vuelto').val(vuelto.toFixed(2));
    }
   
}

function calculartotal(){

   var totgrav = 0,totinaf=0,totexon=0,totgrat=0,totivap=0,totigv=0,total;

   var totalinaf=0,totalexp=0,totalgrav=0,totalexon=0,inaf=0,exon=0,grav=0,dscto=0,dsctgrav=0,dsctinaf=0,dsctexon=0,caligv=0,caltotal=0;
   var $svalor=0;
    $("#detFact tbody tr").each(function(){
    //sum= sum + parseFloat($(this).find("td:eq(5) > input").val()) ;


       var tigv = $(this).find("td:eq(4) > select").val();

       //Calculo por tipos de IGV
       if(tigv=='1'){

        totgrav = totgrav + parseFloat($(this).find("td:eq(8)  > input").val());
       }

       if (tigv=='8')
       {
            totexon = totexon + parseFloat($(this).find("td:eq(8) > input").val());
       }

       if (tigv=='10' || tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='2' || tigv=='3' || tigv=='4' || tigv=='5' || tigv=='6' || tigv=='7')
       {
            totgrat = totgrat + parseFloat($(this).find("td:eq(8) > input").val());

       }

       if (tigv=='9' || tigv=='16')
       {
            totinaf = totinaf + parseFloat($(this).find("td:eq(8) > input").val());

       }


        totigv = totigv + parseFloat($(this).find("td:eq(7) > input").val());

       var otrosc = $('#otrosc').val();
       var otros = $('#otros').val();
       var desc = $('#desc').val();



       if(otrosc ==""){
         $('#otrosc').val(0);
       }

       if(otros ==""){
         $('#otros').val(0);
       }


       if(desc ==""){
         $('#desc').val(0);
       }


       $('#inaf').val(totinaf.toFixed(2));
       $('#grat').val(totgrat.toFixed(2));
       $('#exon').val(totexon.toFixed(2));
       $('#igv').val((totigv).toFixed(2));
       $('#grav').val((totgrav).toFixed(2));


       var total = parseFloat($('#otrosc').val()) + parseFloat($('#otros').val()) +parseFloat(totgrav) + parseFloat(totigv) + parseFloat($('#exon').val())+ parseFloat($('#inaf').val());
       $('#total').val(total.toFixed(2));
   });

    if ($('#detFact >tbody >tr').length == 0){

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

    if( $('#desc').val() == 0){
        $('#totdesc').val($svalor.toFixed(2));
    }

    if( $('#desc').val()>0){
          inaf = totinaf;
          exon = totexon;
          grav = totgrav;
          dscto = $('#desc').val()/100;

           if(inaf>0){
            dsctinaf = dscto*inaf;
            totalinaf= inaf-dsctinaf;
          }

          if(exon>0){
            dsctexon = dscto*exon;
            totalexon= exon-dsctexon;
          }

          if(grav>0){
            dsctgrav = dscto*grav;
            totalgrav= grav-dsctgrav;
          }


          totdesc = dsctinaf+dsctexon+dsctgrav+dsctexp;
          caligv = totalgrav*0.18;
          caltotal = parseFloat($('#otrosc').val()) + parseFloat($('#otros').val())+totalinaf+totalexon+totalgrav+caligv;
          $('#inaf').val(totalinaf.toFixed(2));
          $('#exon').val(totalexon.toFixed(2));
          $('#grav').val(totalgrav.toFixed(2));
          $('#igv').val(caligv.toFixed(2));
          $('#totdesc').val(totdesc.toFixed(2));
          $('#total').val(caltotal.toFixed(2));


    }

   // calculardescuento();
}
function formatearcliente(){
    var clinum = $('#clinum').val();
    var clinom = $('#clinom').val();
    var clidir = $('#clidir').val();
    var clicor = $('#clicor').val();

    if (clinum!="" && clinom!="" && clidir!=""){
        $('#clinom').prop("readonly",true);
        $('#clidir').prop("readonly",true);
        $('#clicor').prop("readonly",true);

        var posi = clinum.indexOf("|",0);
        var clinum1 = clinum.substring(0,posi);
        $('#clinum').val(clinum1);
    }
}

function calculardescuento(){
  var totalinaf=0,totalexp=0,totalgrav=0,totalexon=0,inaf=0,exon=0,grav=0,exp=0,dscto=0,dsctgrav=0,dsctinaf=0,dsctexon=0,caligv=0,dsctexp=0,caltotal=0;
  inaf = $('#inaf').val();
  exon = $('#exon').val();
  grav = $('#grav').val();
  exp = $('#exp').val();
  dscto = $('#desc').val()/100;

  if(inaf>0){
    dsctinaf = dscto*inaf;
    totalinaf= inaf-dsctinaf;
  }

  if(exon>0){
    dsctexon = dscto*exon;
    totalexon= exon-dsctexon;
  }

  if(grav>0){
    dsctgrav = dscto*grav;
    totalgrav= grav-dsctgrav;
  }
  if(exp>0){
    dsctexp = dscto*exp;
    totalexp= exp-dsctexp;
  }

  caligv = totalgrav*0.18;
  totdesc = dsctinaf+dsctexon+dsctgrav+dsctexp;
  caltotal = parseFloat($('#otrosc').val()) + parseFloat($('#otros').val()) +totalinaf+totalexon+totalgrav+totalexp+caligv;
  $('#inaf').val(totalinaf.toFixed(2));
  $('#exon').val(totalexon.toFixed(2));
  $('#grav').val(totalgrav.toFixed(2));
  $('#exp').val(totalexp.toFixed(2));
  $('#igv').val(caligv.toFixed(2));
  $('#total').val(caltotal.toFixed(2));
  $('#totdesc').val(totdesc.toFixed(2));



}
</script>



     <BR><div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                @if(session()->has('info'))
                    <br><br><br><br><div class="alert alert-danger">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Alerta!</strong> {{ session('info') }}
                    </div>
                @endif


                @if(session()->has('success'))
                    <div class="alert alert-success">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Información!</strong> {{ session('success') }}
                    </div>
                @endif
				 <a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
					<input hidden="hidden" name="comprobante" id="comprobante" value="">
				    <input hidden="hidden" name="documento" id="documento" value="">
            </div>
        </div>
    </div>

	  <div class="container-fluid">
        <div class="col-lg-12">
            <div class="btn-toolbar" role="toolbar" aria-label="...">
                <div class="btn-group" >
            
                        <a href="/movingresos"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-search"></span> Consultar Comprobantes</button></a>
                </div>
               	<div class="btn-group" >
	
				<a href="/movingresos/create"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nuevo Registro</button></a>
		</div>
		
		
		
                
            </div>
        </div>
    </div>



    <div class="row">

            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <center><strong><font size="4">REGISTRAR DE INGRESO</font></strong></center>
            </div>

    </div>
    {!!Form::open(array('url'=>'/movingresos','autocomplete'=>'off','method'=>'POST','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
        <div class="container-fluid detalle">
            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>Datos COMPROBANTE</strong>
                    </div>

                    <div class="panel-body">
                        
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
                        <label class="error" for="codunique" generated="true"></label>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type='hidden' name='codunique' id="codunique">
                            </div>
                        </div>
                         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>TIPO DE DOCUMENTO</label>
                                <select name="codigodoc" class="form-control">
                                    @foreach ($operaciones as $ope)
                                        <option value='{{$ope->tdocod}}'>{{$ope->tdodes}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('topcod'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('topcod') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Serie</label>

                               
                                <input name="serdoc"  value="" id="serdoc" class="form-control" >
                           
                            </div>
                        </div>

                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>N° Doc.</label>
                   
                                <input type="number"  id="numdoc" name="numdoc" value="" class="form-control">
                           
                                @if ($errors->has('numdoc'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('numdoc') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                       
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Emision</label>
                                <input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">

                                @if ($errors->has('fecEmi'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('fecEmi') }}</font></strong></span>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Vencimiento</label>
                                <input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
                                @if ($errors->has('fecVen'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('fecVen') }}</font></strong></span>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Moneda</label>
                                <select name="mondoc" id="mondoc" class="form-control">
                                    @foreach ($monedas as $mon)
                                        <option value='{{$mon->moncod}}' @if(old('mondoc') == $mon->moncod) {{ 'selected' }} @endif >{{$mon->monnom}}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('mondoc'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('mondoc') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                
                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Medio de Pago</label>
                                <select name="cmbMP" id="cmbMP" class="form-control">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Visa">Visa</option>
                                    <option value="Mastercard">Mastercard</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo de Cambio</label>
                                <input  type='text' name="camdoc"  value="{{old('camdoc')}}"  id="camdoc" class="form-control">
                                        <span id="error-camdoc" style="display:none;"><strong><font color="red">Ingresar un tipo de cambio mayor a cero</font></strong></span>

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
                    <div class="panel-heading">
                        <strong>Datos del Proveedor</strong>
                    </div>
                    <div class="panel-body">
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
                        <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Documento</label>
                                <select name="tdicod" id="tdicod" class="form-control">
                                    <option></option>
                                    @foreach($docidentidad as $doc)
                                        @if($doc->tdicod =='6')
                                        <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @else
                                        <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @if ($errors->has('tdicod'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('tdicod') }}</font></strong></span>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">N° Documento</label>
                                <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}"  placeholder="" class="form-control">
                                @if ($errors->has('clinum'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('clinum') }}</font></strong></span>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre ó Razón Social</label>
                                <input type="text" name="clinom" id="clinom" value="{{old('clinom')}}" class="form-control">
                                @if ($errors->has('clinom'))
                                     <span class="help-block"><strong><font color="red">{{ $errors->first('clinom') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Dirección</label>
                                <input name="clidir" id="clidir" value="--" class="form-control">
                                @if ($errors->has('clidir'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('clidir') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electrónico</label>
                                <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
                                @if ($errors->has('clicor'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('clicor') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="container-fluid detalle">
            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>Detalle de COMPROBANTE</strong>
                    </div>

                    <div  class="panel-body">
                        @if ($errors->has('codpro'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('codproclinom') }}</font></strong></span>
                    @endif
                    @if ($errors->has('detpro'))
                            <span class="help-block"><strong><font color="red">{{ $errors->first('detpro') }}</font></strong></span>
                    @endif
                        <div id="alertitem" hidden class="col-lg-8 alertitem">
                            <strong><font color="red"> No hay productos y/o servicios para COMPROBANTEr</font></strong>
                       </div>
                        <div id="alertcant" hidden class="col-lg-2 alertcant">
                            <strong><font color="red">Ingresar una cantidad mayor a 0</font></strong>
                       </div>
                        <div id="alertpro" hidden class="col-lg-2 alertpro">
                            <strong><font color="red">Elegir un producto o servicio</font></strong>
                       </div>
                       <div id="alertdet" hidden class="col-lg-2 alertdet">
                            <strong><font color="red">Ingresar el detalle del producto o servicio</font></strong>
                       </div>


                        <table id="detFact" class="table">
                            <thead>
                                <th>Cant.</th>
                                <th>Unidad</th>
                                <th>Codigo</th>
                                <th>Detalle</th>
                                <th hidden="hidden">Tipo IGV</th>
                                <th hidden="hidden">Valor Unitario</th>
								<th>P. Unitario</th>
                                <th hidden="hidden" >IGV</th>
                                <th>Subtotal</th>
                                <th>Total</th>
                                <th><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                            </thead>
                            <tbody id="">
                                <tr>
									 <td>
                                        <input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif
                                    </td>
                                    <td>
                                        <select style="width:100px" name="unid[]"  class="form-control input-sm">
										@foreach ($unidades as $und)
										@if($und->umecod == "UNI")
											<option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option>
										@else
											<option  value="{{$und->umecod}}">{{$und->umenom}}</option>
										@endif
										@endforeach</select>
									</td>
									<td>
                                        <input type="text"  name="codpro[]"  id="codpro" onChange="Calcular(this);" onFocus="Calcular(this);" onBlur="Calcular(this);" onClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)";  placeholder="" value="" class="codpro form-control input-sm">
                                    </td>
                                    <td>
                                        <input onkeypress="if (event.keyCode == 13) enviar_formulario();" class="detpro form-control input-sm" name="detpro[]"  onFocus="Calcular(this);" onBlur="Calcular(this);" onClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; value="" id="detpro" size="100" onfocus="Calcular(this)"; >
									</td>

									<td hidden="hidden" >
										<select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select>
                                    </td>
                                    <td hidden="hidden">
                                        <input readonly="readonly"  type="number"  id="vunit" name="vunit[]" style="text-align:right;" value="0" min="0"  class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif
                                    </td>
									 <td >
                                        <input type="text"  class="form-control input-sm preuni" size="20px" id="preuni"  onFocus="Calcular(this);" onBlur="Calcular(this);" onClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; value="0" min="0" style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif
									</td>
									 <td hidden="hidden">
                                        <input type="text" class="form-control input-sm" size="20px" id="vigv" readonly value="0" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif
                                    </td>
                                    <td>
                                        <input type="text" class="form-control input-sm" size="20px" id="vsub" readonly value="0" style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif
                                    </td>

                                    <td>
                                        <input type="text" readonly value="0" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm" />@if ($errors->has("vtot"))<span class="help-block"><strong><font style="text-align: right;" color="red">{{ $errors->first("vtot") }}</font></strong></span>@endif
                                    </td>

									<td>
										<button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                                    </td>
								</tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            

        </div>

                <div class="col-lg-9">
                    <div class="panel panel-default">
                        <div  class="panel-heading">
                            <strong>Observaciones</strong>
                        </div>
                        <div  class="panel-body">
                            <div class="form-group">
                                <textarea class="form-control" id="obser" name="obser" rows="3"></textarea>
                             </div>
                        </div>
                    </div>
                    <button type="submit" id="btn" name="btn"  class="btn btn-primary"><strong>REGISTRAR INGRESO</strong></button>
                    <a href="{{config('global.ruta')}}/SisFact"><button type="button" class="btn btn-danger"><strong>CANCELAR</strong></button></a>
                </div>

                <div class="col-lg-3" >
                    <div class="panel panel-default">
                        <div  class="panel-body">
                            <table class="table" border="0">
                                    <tr hidden="hidden">
                                        <td><strong>Descuentos %</strong></td>
                                        <td><input type="number" class="form-control input-sm" min="0.00"  step=".00001" id="desc" style="text-align:right;" value='0.00' OnChange="calculartotal()" name="desc"></td>
                                    </tr>
                                     <tr hidden="hidden">
                                        <td><strong>Exportadas</strong></td>
                                        <td><input class="form-control input-sm" type="number"  step=".00001"  style="text-align:right;" id="exp" name="exp" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr hidden="hidden">
                                        <td><strong>Exoneradas</strong></td>
                                        <td><input class="form-control input-sm" type="number"  step=".00001"  style="text-align:right;" id="exon" name="exon" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr hidden="hidden">
                                        <td><strong>Inafectas</strong></td>
                                        <td><input class="form-control input-sm" type="number"  step=".00001" style="text-align:right;" id="inaf" name="inaf" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gravadas</strong></td>
                                        <td><input class="form-control input-sm" type="text" style="text-align:right;" id="grav" name="grav" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>IGV 18%</strong></td>
                                        <td><input class="form-control input-sm" type="number"  step=".00001" style="text-align:right;" id="igv" name="igv" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr hidden="hidden">
                                        <td><strong>ISC</strong></td>
                                        <td><input class="form-control input-sm" type="number"  step=".00001" style="text-align:right;" id="isc" name="isc" OnChange="calculartotal();" value='0.00'></td>
                                    </tr>
                                    <tr hidden="hidden">
                                        <td><strong>Gratuita</strong></td>
                                        <td><input class="form-control input-sm" type="number"  step=".00001" style="text-align:right;" id="grat" name="grat" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr hidden=hidden>
                                        <td><strong>Otros Cargos</strong></td>
                                        <td><input type="number" min="0"  step=".00001" style="text-align:right;" id="otrosc" value='0.00' OnChange="calculartotal();" name="otrosc"></td>
                                    </tr>
                                    <tr hidden=hidden>
                                        <td><strong>Otros Tributos</strong></td>
                                        <td><input type="number" min="0"  step=".00001" style="text-align:right;" id="otros" value='0.00' OnChange="calculartotal();" name="otros"></td>
                                    </tr>
                                    <tr hidden="hidden">
                                        <td><strong>Descuento Total (-)</strong></td>
                                        <td><input class="form-control input-sm" type="number" min="0.00"  step=".00001" id="totdesc" style="text-align:right;" value='0.00' OnChange="calculartotal()" name="totdesc" readonly="readonly"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td><input class="form-control input-sm" type="number" min="0"  step=".00001" style="text-align:right;" id="total" name="total" value='0.00' readonly="readonly"></td>
                                    </tr>
                                    <tr hidden="hidden">
                                        <td><strong>Efectivo</strong></td>
                                        <td><input class="form-control input-sm" type="number" min="0"  step=".00001" style="text-align:right;" id="dinero" name="dinero" onchange="calcularvuelto();" onkeyup="calcularvuelto();" value='0.00'></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Vuelto</strong></td>
                                        <td><input class="form-control input-sm" type="number" min="0"  step=".00001" style="text-align:right;" id="vuelto" name="vuelto" value='0.00' readonly="readonly"></td>
                                    </tr>
                            </table>
                        </div>
                    </div>
                </div>

                </div>

            </div>

        {!!Form::close()!!}
@endsection
