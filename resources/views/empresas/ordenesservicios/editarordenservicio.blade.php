@extends('layouts.empresas')
@section('contenido')
@include('empresas.puntosventas.modalpresentaciones')
@include('empresas.compras.modalcosteo')
<style>

 .ui-autocomplete {
     z-index: 215000000 !important;
}


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

   $(document).ready(function()
    {


        $("#buscardescripcion").keyup(function() {
      var val = $(this).val();
      var contarcarateres = $(this).val().length;

      if(contarcarateres >0){
        $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/busquedaproductocompra/"+val,

        }).done(function(respuesta){
          $("#detmenu").html(respuesta.vista);

        });
      }


    });


             var sucursal = $("#sucursal").val();
                $("#almacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#almacen").html(respuesta.vista);
               
                });
        

     $("#sucursal").change(function() {
         
                var sucursal = $("#sucursal").val();
                $("#almacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#almacen").html(respuesta.vista);
               
                });

      });


    $("#clinom").autocomplete({
      source: '{!!URL::route('autocompletenomprov')!!}',
      dataType: "json",
      minLength: 2,
      autoFocus:true,
      select: function(event,ui) {   
        
        $('#clinum').val(ui.item.clinum);
        $('#clidir').val(ui.item.dir);
        $('#clicor').val(ui.item.cor);
        $('#clicod').val(ui.item.clicod);
        $('#telefono').val(ui.item.telefono);
        $("#tdicod").val(ui.item.tdicod).attr('selected', 'selected');
        
      }
    })
    

   $("#buscarproducto").keypress(function(e) {

      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){



        var valor = $(this).val();
        var cont = 0, cantidad=0,total=0;
        $.ajax({
          type: 'get',
          url: '/consultarprod',
          dataType: 'json',
          data: {'value' : $(this).val() },
          success : function(data) {

            var valornuevo = data[0].proid;

     

           if(data[0].contar =='1'){


             $("#buscarproducto").val('');

             if ($('#detFact >tbody >tr').length > 0){

              $("#detFact tbody tr").each(function(){
               var codigo = $(this).find("td:eq(6) > input").val();

         

               if( valornuevo == codigo){
                cont = cont+1;
                cantidad = parseFloat($(this).find("td:eq(1) > input").val())+1;
                totalitem = parseFloat($(this).find("td:eq(4) > input").val())*cantidad;
                subtotalitem = totalitem/1.1055;
                igvitem = totalitem-subtotalitem;
                presigv = subtotalitem/cantidad;

              }
              if(cont >0){
                $(this).find("td:eq(1) > input").val(cantidad);
                $(this).find("td:eq(3) > input").val(presigv.toFixed(2));

                $(this).find("td:eq(5) > input").val(totalitem.toFixed(2));
                calculartotal();
                $("#buscarproducto").focus();
                return false;
              }
            })

              if(cont == 0){
                var igvitem = data[0].propun -data[0].provun;

                $('#detFact').append("<tr><td width='900px'><input type='text' class='form-control' name='detpro[]' value='"+data[0].pronom+"' readonly='readonly'><br></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].costo/1.1055+"' readonly='readonly' style='width:130px' ></td><td><input type='text' Onkeyup='Calcular(this)' class='form-control' name='preuni[]'  value='"+data[0].costo+"'  style='width:130px' ></td><td><input type='text' class='form-control' name='vtot[]' onkeyup='CalcularItem(this);'  value='"+data[0].costo+"'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='pro_id[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");


              }

            }else{

              var igvitem = data[0].propun -data[0].provun;
              $('#detFact').append("<tr><td width='900px'><input type='text' class='form-control' name='detpro[]' value='"+data[0].pronom+"' readonly='readonly'><br></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden' ><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].costo/1.1055+"' readonly='readonly' style='width:130px' ></td><td><input type='text' Onkeyup='Calcular(this)' class='form-control' name='preuni[]'  value='"+data[0].costo+"'  style='width:130px' ></td><td><input type='text' class='form-control' name='vtot[]' onkeyup='CalcularItem(this);'  value='"+data[0].costo+"'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='pro_id[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
            }



            if ($('#detFact >tbody >tr').length > 0){
             calculartotal();
             $("#buscarproducto").val('');
             $("#buscarproducto").focus();
           }

         }

       }

     })



}
});
            

              });


           

function mostrar(comp){
  var id = comp.id;
  var val = comp.value;
  $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/consultarmenucompra/"+val,

  }).done(function(respuesta){
    $("#detmenu").html(respuesta.vista);
  });

}

 function presentaciones(id){
     var id = id;
     var suc = $('#sucursal').val();

 
       $("#modal-presentaciones").modal("show");

       $("#presentaciones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/presentacionesproductocompra/"+id+"/"+suc,

        }).done(function(respuesta){
          $("#presentaciones").html(respuesta.vista);
        });



  }

</script>
   

<script>
    $.validator.setDefaults({ 
    ignore: [],
    // any other default options and/or rules
    });

    $(document).ready(function()
    {   


        var metodo = $('#estadopago').find(':selected').attr('data-medio');
        var dias = $('#estadopago').find(':selected').attr('data-dias');
        
        var $svalor=0;
        var iCnt = 0;
        var comprobante = $("#comprobante").val();
        var documento = $("#documento").val();

        if(metodo=='CREDITO'){
            
            $("#divfecVen").hide('true');
            $("#fecVen").val(nuevafecha);

         }

         if(metodo =='CONTADO'){

            $("#divfecVen").hide('true');
            $("#fecVen").val($("#fecEmi").val());
            
         }

        if(metodo =='PERSONALIZADO'){

            $("#fecVen").val($("#fecEmi").val());
            $("#divfecVen").show('true');
         }

         $("#formfact").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        })


        $("#estadopago").on("change", function() {

         var metodo = $(this).find(':selected').attr('data-medio');
         var dias = $(this).find(':selected').attr('data-dias');

          if(metodo=='CREDITO'){
           
             $("#divfecVen").hide('true');
             $("#fecVen").val(nuevafecha);

         }

         if(metodo =='CONTADO'){

            
            $("#divfecVen").hide('true');
            $("#fecVen").val($("#fecEmi").val());
              

         }

        if(metodo =='PERSONALIZADO'){

               $("#fecVen").val($("#fecEmi").val());  
            $("#divfecVen").show('true');
         }

        });


        

        $("#btnPrint").printPage({

          url: "/imprimir/"+comprobante+"/"+documento,
          attr: "href",
          messageBox:false
          
        })
        
          $('#buscarproducto').focus();

        $('.detalle').keypress(function(e) {
                    
            if(e.keyCode == 43) {
                agregarlinea();
                return false;
            }
            
        })
        
    
        

    

   
         

        
              

                $(".detpro").autocomplete({
                  source: '{!!URL::route('consultarproductonomcompra')!!}',
                  dataType: "json",
                  minLength: 1,
                  autoFocus:true,
                  select: function(event,ui) {   

                    alert(ui.item.pro_id);
                    
                    $(this).closest('tr').find("td:eq(2) > input").val(ui.item.pronom);
                    $(this).closest('tr').find("td:eq(3) > input").val(ui.item.propun);
                    $(this).closest('tr').find("td:eq(4) > input").val(ui.item.propun);
                    $(this).closest('tr').find("td:eq(1) > input").val(ui.item.pro_id);
                    $(this).closest('tr').find("td:eq(1) > input").prop("readonly",true);
                    $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                    $(this).closest('tr').find("td:eq(5) > input").val(ui.item.propun*$(this).closest('tr').find("td:eq(0) > input").val());

                    $(this).closest('tr').find("td:eq(1) > input").prop("readonly",true);
                    $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                    
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

                
             
                fecEmi:{
                    required: true,
                    date: true
                },
                fecEnv:{
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
                camdoc:{
                    required : function () {
                                return $('#formfact select[name="mondoc"]').val() != 'PEN';
                               },
                    min: {
                        param: 1,
                        depends:  function () {
                                return $('#formfact select[name="mondoc"]').val() != 'PEN';
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
               
                camdoc:{
                    required:"Ingresar el tipo de cambio",
                    min:"Tipo de cambio debe ser mayor a 0"
                },
               
                fecEmi:{
                    required:"Ingresar la fecha de emisi&oacute;n",
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
                clidir:"Ingresar la direcci&oacute;n del cliente",
                clicor:{
                    email:"Ingresar un email válido"
                },
                obser:{
                    maxlength:"El número máximo de caracteres es de 250"
                }
            }

        })

       

        if ($("#mondoc").val()!='PEN'){
            $("#camdoc").prop('readonly',false);
        }else {
            $("#camdoc").prop('readonly',true);
            $("#camdoc").val(0);
        }



        
        $('#add').click(function() {
 
            agregarlinea();
        })
    

         $("#cambia").click(function(){
            $("#texto").toggle(1000);
         })

  

//INICIO SUBMIT-----------------------------------------------------------------

        $( "#formfact" ).submit(function( event ) {


            if ($('#detFact >tbody >tr').length == 0){
                $('#alertitem').show();
                event.preventDefault(); 
            }
          
        
           
        })

//FIN ---------------------------------------------------------------------------------

        $("#mondoc").on('change',function(){
            var mondoc = $("#mondoc").val();

            if(mondoc == 'PEN'){
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
            if($('#grat').val()==0 || $('#grat').val()==""){
                $('#grat').val($svalor.toFixed(2))
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

};


function  buscarcliente(){

    
          var formulario = $("#clinum").val();
          $("#imgload").show();
          $(".botones").hide();
          $.ajax({
            type: "get",
            dataType: 'json',
            url: '/autocomplete/'+formulario,
            
          }).done(function(respuesta){



             $('#clinom').val(respuesta[0].nom);
                     $('#clidir').val(respuesta[0].dir);
                     $('#clicor').val(respuesta[0].cor);
                     $('#clicod').val(respuesta[0].clicod);
                     $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

          $("#imgload").hide();
          //$(".botones").show();
          
          });

  

}

function agregarlinea(){
            var iCnt = 0;
            iCnt = iCnt + 1;
            $('.alertitem').hide();
      
            $('#detFact').append('<tr><td><input  type="number"  step="any" min="1" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" /></td> <td hidden="hidden"><input type="text"  name="codpro[]" id="codpro"  OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" onfocus="Calcular(this)"; ></td><td ><input type="number" step="any" class="form-control input-sm preuni" min="0" size="20px" id="preuni"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/></td><td><input type="number" step="any"  value="0" id="vtot" style="text-align:right;" name="vtot[]" OnKeyUp="CalcularItem(this);" class="form-control input-sm" /></td><td hidden="hidden" ><input type="text"  name="pro_id[]" id="pro_id" placeholder=""  class="pro_id form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
         
       
        

               
              

                $(".detpro").autocomplete({
                  source: '{!!URL::route('consultarproductonomcompra')!!}',
                  dataType: "json",
                  minLength: 1,
                  autoFocus:true,
                  select: function(event,ui) {   

                    
                    $(this).closest('tr').find("td:eq(2) > input").val(ui.item.pronom);
                    $(this).closest('tr').find("td:eq(3) > input").val(ui.item.propun);
                    $(this).closest('tr').find("td:eq(4) > input").val(ui.item.propun);
                    $(this).closest('tr').find("td:eq(1) > input").val(ui.item.pro_id);
                    $(this).closest('tr').find("td:eq(1) > input").prop("readonly",true);
                    $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                    $(this).closest('tr').find("td:eq(5) > input").val(ui.item.propun*$(this).closest('tr').find("td:eq(0) > input").val());

                    $(this).closest('tr').find("td:eq(1) > input").prop("readonly",true);
                    $(this).closest('tr').find("td:eq(4) > input").prop("readonly",true);
                    
                  }
                })


              
                $(".codpro").on('dblclick', function (){
                    $(this).closest('tr').find("td:eq(1) > input").prop("readonly",false);
                    $(this).closest('tr').find("td:eq(1) > input").val('');
                    $(this).closest('tr').find("td:eq(2) > input").val('');
                    $(this).closest('tr').find("td:eq(3) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(4) > input").val(0.00);
                  
                    calculartotal();
                  

                })
}

function Calcular(ele) {


  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {
      var total=0;

        total = $(this).find("td:eq(1) > input").val() * $(this).find("td:eq(4) > input").val();
 

        $(this).find("td:eq(5) > input").val(total.toFixed(2));
    

     
   });
  calculartotal();
 
};

function CalcularItem(ele) {

  var totigv = 0,totgrav=0 ,subtotal=0;
  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {

    var  totitemgrav=0;

    totitemgrav = $(this).find("td:eq(5) > input").val() / $(this).find("td:eq(1) > input").val();

    $(this).find("td:eq(4) > input").val(totitemgrav.toFixed(2));

  });
  calculartotal();

};



function validaralfanumerico(){
    
    var condr=0;
    var alfn = /^[\w]+$/;
    var serdr;

    if ($('#detFact >tbody >tr').length == 0){
     $('#detFact >tbody >tr').each(function(){

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

     if ($('#detFact >tbody >tr').length == 0){
     $('#detFact >tbody >tr').each(function(){
        var docr = $(this).find("td:eq(0) > select").val();
        var ser = $(this).find("td:eq(1) > input").val();
        var num =$(this).find("td:eq(3) > input").val();
        
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


  function agregaritem_pre(button){
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
    // var provun = $('#'+id+'vun').val();
     var imagen = $('#'+id+'imagen').val();

  $('#detFact').append("<tr><td width='900px'><input type='text' class='form-control' name='detpro[]' value='"+producto+"' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td><input  type='number' step='any' min='0' class='form-control input-sm' name=preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td><input type='text' class='form-control' name='vtot[]'  value='"+precio+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='pro_id[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td><td><input class='form-control input-sm' type='text' name='lote[]'></td><td><input class='form-control input-sm' type='date' name='vencimiento[]'></td><td><button type='button' id='"+proid+"' onclick='costeo(this)'   class='btncosteo btn btn-primary btn-sm btn-block'>COSTEO</button></td><td><input type='hidden' name='com_det_id[]' value=''></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      calculartotal();

      $("#modal-presentaciones").modal("hide");

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }

  function agregaritem(){

 
    
    var producto = $('#producto').find(':selected').attr('data-pronom');
    var precio = $('#producto').find(':selected').attr('data-propun');
    var proid = $('#producto').find(':selected').attr('data-idproducto');
    var contar = $('#producto').find(':selected').attr('data-presentacion');


  if(contar>0){
        presentaciones(proid);

        $("#modal-presentaciones").modal("show");
  }else{
      $('#detFact').append("<tr><td width='900px'><input type='text' class='form-control' name='detpro[]' value='"+producto+"' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td><input  type='number' step='any' min='0' class='form-control input-sm' name=preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td><input type='text' class='form-control' name='vtot[]'  value='"+precio+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='pro_id[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td><td><input class='form-control input-sm' type='text' name='lote[]'></td><td><input class='form-control input-sm' type='date' name='vencimiento[]'></td><td><button type='button' id='"+proid+"' onclick='costeo(this)'   class='btncosteo btn btn-primary btn-sm btn-block'>COSTEO</button></td><td><input type='hidden' name='com_det_id[]' value=''></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      calculartotal();

      $("#modal-presentaciones").modal("hide");
  }



     

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }
  
  

function validarnumero(){
     if ($('#detFact >tbody >tr').length == 0){
    var connum=0;
    var valnum =  /^\d*$/;

     $('#detFact >tbody >tr').each(function(){
        var num = $(this).find("td:eq(3) > input").val();

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


function calculartotal(){
    
   var totgrav = 0;

   var $svalor=0;

    $("#detFact tbody tr").each(function(){
  

        totgrav = totgrav + parseFloat($(this).find("td:eq(5)  > input").val());
        

     
       $('#total').val(totgrav.toFixed(2));

        });

      if ($('#detFact >tbody >tr').length == 0){
       
         $('#total').val($svalor.toFixed(2))
      };
    

}   




</script>


     <BR>

  
    {!!Form::open(array('url'=>'/ordenescompra','autocomplete'=>'off','method'=>'POST','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
        <div class="container-fluid">
          <div class="row">
            <input type="hidden" name="tipocompra" value="Producto">
             <div class="col-lg-12">
                 <div class="box box-info">
                    <div class="box-header" style="background-color:blue;">
                        <font color="white" ><center><strong>REGISTRAR ORDEN DE COMPRA</strong></center></font>
                         <div class="box-tools pull-right">
                            <div class="btn-group" >
                              <button type="submit" id="btn" name="btn"  class="btn btn-success btn-sm"><strong>REGISTRAR</strong></button>
                            </div>
                            <div class="btn-group" >
                                <a href="/compras"><button type="button" class="btn btn-danger btn-sm"><strong>CANCELAR</strong></button></a>
                            </div>
                        </div>
                    </div>
          

                    <div  class="panel-body">
                      

                        <div class="row">
                        
                         <div class="col-lg-2">
                            <div class="form-group form-group-sm">
                              <label>Empresa</label>
                              <select class="form-control" name="sucursal" id="sucursal">
                                @foreach($negocios as $negocio)
                                   <option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                         

                 
                       
                       
                           <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                      <label>Moneda</label>
                                      <select name="mondoc" id="mondoc" class="form-control">
                                          @foreach ($monedas as $mon)
                                              <option value='{{$mon->moncod}}' @if(old('mondoc') == $mon->moncod) {{ 'selected' }} @endif >{{$mon->monnom}}</option>
                                          @endforeach
                                      </select>  
                            </div>
                        </div>

                   
                       <div hidden="" class="col-lg-2">
                          <div class="form-group form-group-sm">
                              <LABEL>Estado de Pago</LABEL>
                              <select name="estadopago" id="estadopago" class="form-control">
                                @foreach($creditos as $cre)
                                  <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
                                @endforeach
                              </select>
                          </div>
                      </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Emision</label>
                                <input type="date" id="fecEmi" name="fecEmi"  value="{{$compras->com_fec}}" class="form-control">
                            </div>
                        </div>
                           <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12" id="divfecVen">
                            <div class="form-group form-group-sm">
                              <label>Fecha Vencimiento</label>
                              <input type="date" name="fecVen" id="fecVen" value="{{$compras->com_fec_ven}}"  class="form-control"> 
                            </div>
                        </div>
                         <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                              <label>Fecha Ingreso Mercader&iacute;a</label>
                              <input type="date" name="fecIng" value="{{$compras->com_fec_ing}}"  class="form-control"> 
                            </div>
                        </div>

                     

                           
                     
                      
                    </div>

                                      
                    </div>

                     <div class="box-header" style="background-color:blue;">
                        <font color="white" ><center><strong>Datos del Proveedor</strong></center></font>
                         <div class="box-tools pull-right">
                        
                         
                        </div>
                    </div>
                    
                    <div  class="panel-body">

                         <div class="row">
                     
                          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Doc.</label>
                                <select name="tdicod" id="tdicod" class="form-control">
                                    <option></option>
                                    @foreach($docidentidad as $doc)
                                        @if($doc->tdicod =='1')
                                        <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @else
                                        <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @endif
                                    @endforeach
                                </select>
                              
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">N&deg;</label><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload">
                                <input type="text"  name="clinum" id="clinum" value="00000000"  onKeypress="if(event.keyCode == 13) buscarcliente();"  placeholder="" class="form-control">
                             
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre &oacute; Raz&oacute;n Social</label>
                                <input type="text" name="clinom" id="clinom" value="Varios"  class="form-control">
                              
                            </div>
                        </div>
                  

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Direcci&oacute;n</label>
                                <input name="clidir" id="clidir" value="--" class="form-control">
                              
                            </div>
                        </div>
                        <div  class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electr&oacute;nico</label>
                                <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
                               
                            </div>
                        </div>
                      
                      </div>

                    </div>

                     <div class="box-header" style="background-color:blue;">
                        <font color="white" ><center><strong>PRODUCTOS </strong></center></font>
                         <div class="box-tools pull-right">
                        
                         
                        </div>
                    </div>

                   
                    <div class="panel-body">
                         <div class="row">

                             <div class="col-lg-12">
                                <div  class="col-lg-3">
                                <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
                              </div>
                              <div  class="col-lg-3">
                                  <input class="form-control" name="buscardescripcion" id="buscardescripcion" placeholder="Descripción">
                              </div>
                               <div  class="col-lg-6">
                              <button id="btnCategorias" name="btnCategorias" class="btn btn-block btn-success btn-sm" style="background:#2d572c ">CATEGORÍAS</button>
                            </div>
                            </div>

                            <div class="box-body table-responsive" id="detmenu"  style="max-height:200px;min-width:500px  ">
                              <?php $i=0; ?>
                              @foreach($categorias as $categoria)
                              <?php $i=$i+1; ?>
                              <div class="col-sm-3 col-xs-3">
                                <button id='cat<?php echo $i; ?>' type="button" value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:{{$categoria->color}};width: 180px; height: 120px; border-radius:10px">
                                  <p><font color="white">{{$categoria->cat_nom}}</font></p>
                                </button><br><br>
                              </div>
                              @endforeach
                        
                        </div>
                      </div>
                    </div>

                      <div class="box-header" style="background-color:blue;">
                        <font color="white" ><center><strong>Detalle </strong></center></font>
                         <div class="box-tools pull-right">
                        
                         
                        </div>
                    </div>
                    
                    <div  class="panel-body">

                      <div class="row">
                     
                         <div class="col-lg-2 col-md-3 col-sm-3 col-xs-3 form-group form-group-sm">
                             <label>Total</label>
                              <input class="form-control input-sm" type="number" min="0"  step="any" min="1" style="text-align:right;" id="total" name="total" value='{{$cabecera->total_com}}' readonly="readonly">
                        </div>
                        
                        
                           
                      </div>

                      <div class="row">
                         <table id="detFact" class="table">
                            <thead>
                                <th>Cant.</th>
                                <th hidden="hidden" >C&oacute;digo</th>
                                <th>Detalle</th>
                                <th>P. Unitario</th>
                                <th>Total</th>
                                <th hidden="hidden">STOCK</th>
                                
                                <th><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                            </thead>
                            <tbody id="">
                                @foreach($detalle as $det)
                                <tr>
                                  <td>
                                    <input  type="number"  step="any" min="1" id="cant" size="10" value="{{$det->cantidad}}" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />
                                  </td>
                                  <td hidden="hidden">
                                    <input type="text"  name="codpro[]" id="codpro"  OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm" value="{{$det->pro_id}}">
                                  </td>
                                  <td>
                                    <input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" onfocus="Calcular(this)"; value="{{$det->pronom}}" >
                                  </td>
                               
                                  
                                </tr>

                                <tr>
                                  <td width='900px'><input type='text' class='form-control' name='detpro[]' value='{{$det->pronom}}' readonly='readonly'>
                                  </td>
                                  <td>
                                    <input type='number' step='any' min='0' value='{{$det->cantidad}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>
                                  </td>
                                  <td hidden='hidden'>
                                    <select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select>
                                  </td>
                                  <td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' >
                                  </td>
                                  <td><input  type='number' step='any' min='0' class='form-control input-sm' name=preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='{{$det->pre_uni}}' style='width:80px' ></td>
                                  <td><input type='text' class='form-control' name='vtot[]'  value='{{$det->total}}' onkeyup='CalcularItem(this);' style='width:80px' >
                                  </td>
                                  <td hidden='hidden'><input type='text' class='form-control' name='pro_id[]'  value='{{$det->pro_id}}' readonly='readonly' style='width:130px' >
                                  </td>
                                  <td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button>
                                  </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                      </div>
                         

                    </div>

                </div>
            </div>

                <div class="col-lg-12">
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
                  
                </div>


             
          </div>
         
        </div>
     
   
        {!!Form::close()!!}     
@endsection