@extends('layouts.empresas')
@section('contenido')
@include('empresas.puntosventas.modalproductos')
@include('empresas.clientes.modalcrearcliente')
<style>
#b1
{
 /*sirve para los caracteres cuando es una palabra grande se salte a la otra linea */
 white-space: normal;
}
#scroll
{
  height: 650px;
  width: 800px;
  overflow: scroll;
}
</style>

<body>


  <script>

   $(document).ready(function()
   {

     
       var metodo = $('#estadopago').find(':selected').attr('data-medio');
       var dias = $('#estadopago').find(':selected').attr('data-dias');
        
        if(metodo=='CREDITO'){
            $("#divmediopago").hide('true');
            $(".mediopago").val('0');
             $("#divfecVen").hide('true');

             $("#fecVen").val(nuevafecha);

         }

         if(metodo =='CONTADO'){

            $("#divmediopago").show('true');
             $("#divfecVen").hide('true');
             $("#fecVen").val($("#fecEmi").val());

         }

        if(metodo =='PERSONALIZADO'){

             $("#divmediopago").hide('true');
            $(".mediopago").val('0');
            $("#divfecVen").show('true');
         }


      $("#estadopago").on("change", function() {

         var metodo = $(this).find(':selected').attr('data-medio');
         var dias = $(this).find(':selected').attr('data-dias');

          if(metodo=='CREDITO'){
            $("#divmediopago").hide('true');
            $(".mediopago").val('0');
             $("#divfecVen").hide('true');

             $("#fecVen").val(nuevafecha);

         }

         if(metodo =='CONTADO'){

            $("#divmediopago").show('true');
             $("#divfecVen").hide('true');
               $("#fecVen").val($("#fecEmi").val());

         }

        if(metodo =='PERSONALIZADO'){

             $("#divmediopago").hide('true');
            $(".mediopago").val('0');
            $("#divfecVen").show('true');
         }


      });


    $("#buscarproducto").focus();

    $('#clinum').val('00000000');
    $('#clinom').val('Varios');

    $("#clinom").autocomplete({
      source: '{!!URL::route('autocompletenom')!!}',
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
    
    $("#btnRegComp").on("click", function() {


      if ($('#grdet >tbody >tr').length == 0){
        $('#alertitem').show();
        event.preventDefault(); 
      }

      var formulario = $("#formfact").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/actualizarorden',
        data: formulario,
      }).done(function(respuesta){


        window.location.href = "/ordenes";


        $("#imgload").hide();

      });



    });


    $("#btnRegCompReg").on("click", function() {

      $("#opcion").val('1');
      
      if ($('#grdet >tbody >tr').length == 0){
        $('#alertitem').show();
        event.preventDefault(); 
      }

      var formulario = $("#formfact").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/restaurantpunto',
        data: formulario,
      }).done(function(respuesta){


        window.location.href = "/pos";


        $("#imgload").hide();

      });



    });



    $("#buscardescripcion").keyup(function() {
      var val = $(this).val();
      var contarcarateres = $(this).val().length;

      if(contarcarateres >0){
        $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/busquedaproducto/"+val,

        }).done(function(respuesta){
          $("#detmenu").html(respuesta.vista);

        });
      }


    });

      function mostrarobservacion(ele){

    
     alert($(this).closest("td").siblings().find("input[name=pronomobs[]]").val());


  }



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

           if(data[0].contar =='1'){


             $("#buscarproducto").val('');

             if ($('#grdet >tbody >tr').length > 0){

              $("#grdet tbody tr").each(function(){
               var codigo = $(this).find("td:eq(3) > input").val();
               if( valor == codigo){
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

                $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'><br><input  name='pronomobs[]' value='' class='form-control'  maxlength='52'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]'  value='"+data[0].propun+"'  style='width:130px' ></td><td><input type='text' class='form-control' name='itemtotal[]' onkeyup='CalcularItem(this);'  value='"+data[0].propun+"'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");


              }

            }else{

              var igvitem = data[0].propun -data[0].provun;
              $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'><br><input  name='pronomobs[]' value='' class='form-control'  maxlength='52'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]'  value='"+data[0].propun+"'  style='width:130px' ></td><td><input type='text' class='form-control' name='itemtotal[]' onkeyup='CalcularItem(this);'  value='"+data[0].propun+"'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
            }



            if ($('#grdet >tbody >tr').length > 0){
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



</script>

<script>

 $(document).ready(function()
 {

   $("#formfact").keypress(function(e) {
    if (e.which == 13) {
      return false;
    }
  })



   $( "#formfact" ).submit(function( event ) {
    var efectivo1=0,visa=0,mastercard=0,totventa=0,sumarTot=0,resta=0;
    efectivo1 = $("#efectivo1").val();
    visa = $("#visa").val();
    mastercard = $("#mastercard").val();
    totventa = $("#total").val();
    sumarTot =  parseFloat(efectivo1) +  parseFloat(visa) +  parseFloat(mastercard);
    resta = totventa - sumarTot;

    if(sumarTot < totventa){
      alert('Falta Pagar '+resta);
      event.preventDefault();
    }


    if ($('#grdet >tbody >tr').length == 0){
      $('#alertitem').show();
      event.preventDefault(); 
    }

    if ($('#grdet >tbody >tr').length > 0){
      $('#alertitem').hide();
    }

    var condet = 0,conpro=0,concant=0;
    $('#grdet >tbody >tr').each(function(){
      var det = $(this).find("td:eq(0) > input").val();
            
               var cant = $(this).find("td:eq(1) > input").val();
               if(det==''){
                    condet++
                  }else if(cant<1){
                    concant++
                  }
                })

       
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



          })




   var comprobante = $("#comprobante").val();
   var documento = $("#documento").val();
   $("#btnPrint").printPage({

    url: "/imprimir/"+comprobante+"/"+documento,
    attr: "href",
    messageBox:false

  })



            });

function mostrar(comp){
  var id = comp.id;
  var val = comp.value;
  $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/consultarmenu/"+val,

  }).done(function(respuesta){
    $("#detmenu").html(respuesta.vista);
  });

}

$(function(){
  $('#key').keyboard();
});


function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
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

function Calcular(ele) {

  var totigv = 0,totgrav=0 ,subtotal=0;
  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {

    var  totitemgrav=0;

    totitemgrav = $(this).find("td:eq(1) > input").val() * $(this).find("td:eq(4) > input").val();
    $(this).find("td:eq(5) > input").val(totitemgrav.toFixed(2));

  });
  calculartotal();

};

function  buscarcliente(){


  var formulario = $("#clinumn").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#clinomn').val(respuesta[0].nom);
   $('#clidirn').val(respuesta[0].dir);
   $('#cliteln').val(respuesta[0].telefono);
   $('#clicorn').val(respuesta[0].cor);
   $('#clicodn').val(respuesta[0].clicod);
   $("#tdicodn").val(respuesta[0].tdicod).attr('selected', 'selected');

   $("#imgloadcliente").hide();
    $(".botones").show();
          
        });

  

}

    $("#btnRegCliente").on("click", function() {


      var formulario = $("#frmcliente").serializeArray();
      $("#imgloadcliente").show();
      $(".botonescliente").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrarcliente',
        data: formulario,
      }).done(function(respuesta){

        $("#divcliente").html(respuesta.vista);
        $("#imgloadcliente").hide();
     
         $(".botonescliente").show();
          $('#modal-cliente').modal('toggle');
      });



    });


function seleccionarcliente(){

   $('#clinom').val($('#clicod').find(':selected').attr('data-clinom'));
   $('#clidir').val($('#clicod').find(':selected').attr('data-direccion'));
   $('#clicor').val($('#clicod').find(':selected').attr('data-correo'));
   $('#clinum').val($('#clicod').find(':selected').attr('data-clinum'));
   $("#tdicod").val($('#clicod').find(':selected').attr('data-documento'));
    $("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));
 

}


function calculartotal(){

 var totigv = 0,totgrav=0 ,subtotal=0;

 $("#grdet tbody tr").each(function(){

  totgrav = totgrav + ($(this).find("td:eq(1) > input").val() *parseFloat($(this).find("td:eq(4)  > input").val()));

  subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

  totigv = totgrav - subtotal;

  $('#total').val(totgrav.toFixed(2));
  $('#efectivo1').val(totgrav.toFixed(2));
  $('#igv').val(totigv.toFixed(2));
  $('#subtotal').val(subtotal.toFixed(2));
})


 if ($('#grdet >tbody >tr').length == 0){
  $('#total').val('0.00');
  $('#efectivo1').val('0.00');
  $('#igv').val('0.00');
  $('#subtotal').val('0.00');
  $('#vuelto').val('0.00');
  $('#total').val('0.00');
}

var pago =  $('#pagar').val();
var vuelto = pago - totgrav;
if(pago=='0.00' || pago=='0' || pago==''){
 $('#vuelto').val(0.00);
}else{
 $('#vuelto').val(vuelto.toFixed(2));
}

};



</script>


</br>
 

<div class="container-fluid">
  


   {!!Form::open(array('url'=>'/actualizarorden','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
            {{Form::token()}}
    <input type="hidden" name="opcion" id="opcion" value="0">
    <input type="hidden" name="id" value="{{$cabecera->IdCpe_cabecera}}">
    <div class="row">
      <div class="col-lg-12">
         <div class="box">
           <div class="box-header with-border  form-group form-group-sm" style="background-color:blue">
            <div  class="col-lg-2">
                <a href="" data-target="#modalproductos" data-toggle="modal"><button class="btn btn-sm btn-warning"><strong>AGREGAR PRODUCTOS</strong></button></a>
            </div>
            <div  class="col-lg-6">
                <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
                
            </div>
           

             
                   
           </div>
      </div>
    </div>
    </div>
    <div class="row">
    
    </div>
    <div class="row">
      <div class="col-lg-5">
      	<div class="row">
      	  		<div class="col-lg-12">
      	  			 <div class="box">
      	  			 	<div class="box-header" style="background-color:blue;">
      	  			 		<font color="white"><CENTER><strong>DETALLE</strong></CENTER></font>
      	  			 	</div>
                 <div class="box-body">
                   <table class="table table-hover" id="grdet">
                        <thead>

                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th hidden="hidden">Unidad</th>
                      <th hidden="hidden">VU</th>
                      <th>PU</th>
                      <th>Total</th>

                    </thead>

                    <tbody>
                    	    @foreach($detalle as $det)
                <tr>
                  <td>
                    <input type='text' class='form-control' name='pronom[]' value='{{$det->pronom}}' readonly='readonly'><br><input  name='pronomobs[]' class='form-control' value='{{$det->pronomobs}}'  maxlength='52'>
                  </td>
                  <td> 
                    <input type='text' value='1' name='cant[]' onkeyup='Calcular(this);' onChange='Calcular(this);' value="{{$det->cdecan}}" class='form-control input-sm ' id='font-size' style='width:60px'> 
                  </td>
                  <td hidden='hidden'>
                    <select style='width:100px' name='unid[]'  class='form-control input-sm'> 
                      @foreach($unidades as $und) 
                        @if($und->umecod == $det->umecod) 
                        <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> 
                        @else 
                        <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> 
                        @endif 
                      @endforeach 
                    </select>
                  </td>
                  <td hidden='hidden'>
                    <input type='text' class='form-control' name='provun[]'  value='{{$det->provun}}' readonly='readonly' style='width:130px' >
                  </td>
                  <td>
                    <input type='text' class='form-control' name='propun[]'  value='{{$det->propun}}'  style='width:130px' >
                  </td>
                  <td>
                    <input type='text' class='form-control' name='itemtotal[]' onkeyup='CalcularItem(this);'  value='{{$det->cdevve}}'  style='width:130px' >
                  </td>
                  <td hidden='hidden'>
                    <input type='text' class='form-control' name='proid[]'  value='{{$det->IdProducto}}' readonly='readonly' style='width:130px' >
                  </td>
                  <td hidden='hidden'>
                    <input type='text' class='form-control' name='IdCpe_detalle[]'  value='{{$det->IdCpe_detalle}}' readonly='readonly' style='width:130px' >
                  </td>
                  <td>
                    <button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span>
                    </button>
                  </td>
                </tr>

                @endforeach

                    </tbody>
                  </table>
                  
                </div>
            </div>
          </div>
      	  </div>

          <div class="row">
              <div class="col-lg-12">
                <div class="box">
                  <div class="box-header" style="background-color:blue;">
                    <font color="white"><center><strong>TECNICO</strong></center></font>
                  </div>
                  <div class="box-body">
                    <div class="col-lg-6">
                        <div class="form-group-sm">
                            <label>TECNICO</label>
                            <select name="tecnico" class="form-control">
                              @foreach($tecnicos as $tecnico)
                                <option value="{{$tecnico->IdUsuario}}">{{$tecnico->name}} {{$tecnico->apeusu}}</option>
                              @endforeach
                            </select>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

      	  <div class="row">
         	<div class="col-lg-12">
         			<div class="box">
    			<div class="box-header" style="background-color:blue;">
    				<font color="white"><center><strong>Datos del Equipo</strong></center></font>
    			</div>
    			<div class="box-body">
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>IMEI</label>
	    					<input type="text" class="form-control input-sm" value="{{$cabecera->imei}}" name="imei">
	    				</div>	
    				</div>
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>MARCA</label>
	    					<input type="text" class="form-control input-sm" value="{{$cabecera->marca}}" name="marca">
	    				</div>	
    				</div>
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>MODELO</label>
	    					<input type="text" class="form-control input-sm" value="{{$cabecera->modelo}}" name="modelo">
	    				</div>	
    				</div>
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>TIPO DE EQUIPO</label>
	    					<SELECT class="form-control" name="cmbtipo">
	    						@foreach($equipos as $equipo)
	    						@if($equipo->tip_equ_id == $cabecera->tip_equ_id)
	    							<option value="{{$equipo->tip_equ_id}}" selected="selected">{{$equipo->tip_equ_nom}}</option>
	    						@else
	    							<option value="{{$equipo->tip_equ_id}}">{{$equipo->tip_equ_nom}}</option>
	    						@endif
	    						
	    						@endforeach
	    					</SELECT>
	    				</div>	
    				</div>
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>ENCIENDE</label>
	    					<SELECT class="form-control" name="enciende">
	    						<option></option>
	    						@if($cabecera->enciende =='SI')
	    							<option selected="selected" value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@elseif($cabecera->enciende =='NO')
	    							<option value="SI">SI</option>
	    							<option selected="selected" value="NO">NO</option>
	    						@else
	    							<option value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@endif
	    						
	    					</SELECT>
	    				</div>	
    				</div>
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>GOLPES</label>
	    					<SELECT class="form-control" name="golpes">
	    							<option></option>
	    						@if($cabecera->golpes =='SI')
	    							<option selected="selected" value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@elseif($cabecera->golpes =='NO')
	    							<option value="SI">SI</option>
	    							<option selected="selected" value="NO">NO</option>
	    						@else
	    							<option value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@endif
	    						
	    					</SELECT>
	    				</div>	
    				</div>
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>BATERIA</label>
	    					<SELECT class="form-control" name="bateria">
	    							<option></option>
	    						@if($cabecera->bateria =='SI')
	    							<option selected="selected" value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@elseif($cabecera->bateria =='NO')
	    							<option value="SI">SI</option>
	    							<option selected="selected" value="NO">NO</option>
	    						@else
	    							<option value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@endif
	    						
	    					</SELECT>
	    				</div>	
    				</div>
    				<div class="col-lg-6">
    					<div class="form-group-sm">
	    					<label>BANDEJA SIM</label>
	    					<SELECT class="form-control" name="bandeja">
	    							<option></option>
	    						@if($cabecera->bandeja =='SI')
	    							<option selected="selected" value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@elseif($cabecera->bandeja =='NO')
	    							<option value="SI">SI</option>
	    							<option selected="selected" value="NO">NO</option>
	    						@else
	    							<option value="SI">SI</option>
	    							<option value="NO">NO</option>
	    						@endif
	    						
	    					</SELECT>
	    				</div>	
    				</div>
    				<div class="col-lg-12">
    					<div class="form-group-sm">
    						<LABEL><font color="red"><strong>En caso de que el equipo posea seguridad por patrón exiga al cliente desactivarlo temporalmente o especificar una contraseña</strong></font></LABEL>
    						<input type="text" class="form-control input-sm" name="patron" value="{{$cabecera->patron}}" placeholder="Patron ó contraseña">
    					</div>
    				</div>
    				<div class="col-xs-12">
    					<div class="form-group-sm">
    						<label>Especifique la falla de equipo</label>
    						<textarea class="form-control" rows="6" maxlength="1000" name="fallas" placeholder="Describa fallas del equipo">{{$cabecera->fallas}}</textarea>
    					</div>
    				</div>
    				<div class="col-xs-12">
    					<div class="form-group-sm">
    						<label>Observaciones</label>
    						<textarea class="form-control" rows="6" maxlength="1000" name="observaciones" placeholder="Detalle de Reparación">{{$cabecera->ccaobs}}</textarea>
    					</div>
    				</div>


    			</div>
    		</div>
         	</div>
    	
   
          </div>
      	  	
             
        
      </div>



               <div class="col-lg-7">
        <div class="box">
    
         <div class="box-header" style="background-color:blue;">
              <font color="white"><center><strong>DATOS DEL COMPROBANTE</strong></center></font>
         </div>
         <div class="box-body">
              <div class="row">

            <div hidden="hidden" class="col-lg-2">
                <div class="form-group form-group-sm">
                    <LABEL>Estado de Pago</LABEL>
                    <select name="estadopago" id="estadopago" class="form-control">
                      @foreach($creditos as $cre)
                        <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
                      @endforeach
                    </select>
                </div>
            </div>
               <div class="col-lg-2">
                <div class="form-group form-group-sm">
                   <label>Fecha</label>
                     <input  type="date" id="fecEmi" name="fecEmi" value="{{$cabecera->ccafem}}" class="form-control">
                </div>
                   
               </div>

               <div class="col-lg-2"  id="divfecVen">
                   <div class="form-group form-group-sm">
                    <label>F. Vencim.</label>
                      <input type="date" name="fecVen" id="fecVen" value="{{$cabecera->ccafve}}"  class="form-control">
                  </div>
               </div>
            </div>

              <a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
              @if(isset($cpe))
              <input type="hidden" name="comprobante" id="comprobante" value="{{$cpe}}">
              @endif

              @if(isset($tdocod))
              <input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
              @endif
             
                   <div class="row">
              <div class="col-lg-3" hidden="hidden">
                <div class="form-group form-group-sm">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
               
                    <label >
                    <input type="radio" name="tdocod" id="tdocod" value="80" checked="checked"  > ORDEN DE SERVICIO
                  </label>
                </div>
                </div>
               
              </div>

              <div  class="col-lg-3">
                <div class="form-group form-group-sm">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        @if($cabecera->moncod =='PEN')
                    <label >
                    <input type="radio" name="moncod" value="PEN" checked="checked"> SOLES
                    </label>
                     <label >
                    <input type="radio" name="moncod" value="USD" > DOLARES
                    </label>
                  @elseif($cabecera->moncod =='USD')
                  <label >
                    <input type="radio" name="moncod" value="PEN" > SOLES
                    </label>
                     <label >
                    <input type="radio" name="moncod" value="USD" checked="checked" > DOLARES
                    </label>
                  @endif
                </div>

                </div>
              

              </div>
          </div>
         </div>
         <div class="box-header" style="background-color:blue;">
            <font color="white"><center><strong>DATOS DEL CLIENTE</strong></center></font>
            <div class="box-tools pull-right">
             <a  data-target="#modal-cliente" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">NUEVO CLIENTE</button></a>
          </div>
         </div>
         <div class="box-body" id="divcliente">
             <div class="row form-group form-group-sm">
              <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Tipo Documento</label>
                  <select name="tdicod" id="tdicod" class="form-control">
                    @foreach($tipodocumento as $doc)
                     @if($doc->tdicod == $cabecera->tdicod)
                    <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                    @else
                    <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                    @endif
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-lg-2">
                <div class="form-group form-group-sm">
                  <label for="clinum">Num. Doc</label>
                  <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}"  placeholder="" class="form-control" >

                </div>
              </div>

              
             <div class="col-lg-3" >
              <div class="form-group">
                <label class="control-label">Cliente</label>
                <select class="form-control input-sm" name="clicod" id="clicod" onchange="seleccionarcliente();">
                  <option></option>
                  @foreach($clientes as $cliente)
                    @if($cliente->clicod == $cabecera->clicod)
                       <option  selected="selected" value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}"  data-clinom="{{$cliente->clinom}}" data-direccion="{{$cliente->clidir}}" data-correo="{{$cliente->clicor}}" data-telefono="{{$cliente->telefono}}">{{$cliente->clinom}}</option>
                    @else
                       <option value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}"  data-clinom="{{$cliente->clinom}}" data-direccion="{{$cliente->clidir}}" data-correo="{{$cliente->clicor}}" data-telefono="{{$cliente->telefono}}">{{$cliente->clinom}}</option>
                    @endif
                   
                  @endforeach
                </select>
                <input type="hidden" readonly="readonly" name="clinom" id="clinom" value="{{$cabecera->ccanom}}">
              </div>
            </div>

              <div class="col-lg-4">
                <div class="form-group form-group-sm">
                  <label>Direcci&oacute;n</label>
                  <input name="clidir" id="clidir" value="{{$cabecera->direccion}}" class="form-control">
                </div>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Correo Electr&oacute;nico</label>
                  <input name="clicor" id="clicor" value="{{$cabecera->clicor}}" class="form-control">
                </div>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Tel&eacute;fono</label>
                  <input name="clitel" id="clitel" value="{{$cabecera->telefono}}" class="form-control">
                </div>
              </div>


         </div>
          </div>

        <div class="box-header" style="background-color:blue;">
            <font color="white"><center><strong>MONTO A PAGAR</strong></center></font>
         </div>
          <div class="box-body">
          
                  <div class="row">
            <div class="col-lg-3">
              <div class="form-group-sm">
                <label>Total</label>
                <input type="text" class="form-control"  id="total" name="total" value='{{$cabecera->ccaitv}}' readonly="readonly">
              </div>
            </div>
              <div hidden="hidden" class="col-lg-3">
              <div class="form-group-sm">
                <label>Paga con:</label>
                <input type="number"  step="any" class="form-control"  id="pagar" name="pagar" value="{{$cabecera->paga}}" onkeyup="calculartotal();">
              </div>
            </div>
            <div hidden="hidden" class="col-lg-3">
              <div class="form-group-sm">
                <label>Vuelto</label>
                <input type="text" class="form-control"  id="vuelto" name="vuelto" value="{{$cabecera->vuelto}}" readonly="readonly">
              </div>
            </div>
               <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
            </div>
        


         <br>
          <div class="row" id="divmediopago">
            @foreach($mediospagos as $mp)
              <div class="col-lg-3">
                <div class="form-group form-group-sm">
                  <label>{{$mp->nom_med_pag}}</label>
                  @if(count($pagos)>'0')
                    @foreach($pagos as $pag)
                      @if($pag->id_med_pag == $mp->id_med_pag)
                        <input class="mediopago form-control" name="monto[]" value="{{$pag->monto}}" type="number" step="any">
                      @else
                        <input class="mediopago form-control" name="monto[]" type="number" step="any">
                      @endif
                    @endforeach
                  @else
                        <input class="mediopago form-control" name="monto[]" type="number" step="any">
                  @endif
                  
                  <input class="form-control" name="medio[]" type="hidden" value="{{$mp->id_med_pag}}">
                </div>
              </div>
            @endforeach
          </div>
      
          <div class="row">
            <div class="col-lg-6">

              <button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-lg botones">COBRAR</button><br>

            </div>
             <div class="col-lg-6">
              <button type="button" id="btnRegCompReg" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button><br>
            </div>
            
          </div>
              <div class="row">
   

             <div class="col-lg-12">
              <a href="/listallevar"><button type="button" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a><br>
            </div>
          </div>
        </div>


   
     </div>
</div>

</div>



{!!Form::close()!!}
</div>
</div>

@endsection
