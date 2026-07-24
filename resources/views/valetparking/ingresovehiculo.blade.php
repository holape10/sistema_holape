@extends('layouts.empresas')
@section('contenido')
 
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

    $(document).ready(function(){


      $('#placacobrar').focus();

        $("#nombreplaca").autocomplete({
      source: '{!!URL::route('autocompletenom')!!}',
      dataType: "json",
      minLength: 2,
      autoFocus:true,
      select: function(event,ui) {   
        
        $('#nombre').val(ui.item.clinom);
        $('#dni').val(ui.item.clinum);
        $('#placa').val(ui.item.placa1);
        $('#placa2').val(ui.item.placa2);
        $("#tdicod").val(ui.item.tdicod).attr('selected', 'selected');
        
       
      }
    })



        $("#formfact").keypress(function(e) {
            if (e.which == 13) {
                  var formulario = $("#formfact").serializeArray();
                  $("#imgload").show();
                  $(".botones").hide();
                  $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/registraringreso',
                    data: formulario,
                  }).done(function(respuesta){

                    if(respuesta.codigo =='504'){

                     alert(respuesta.mensaje);

                     $("#imgload").hide();
                     $(".botones").show();

                   }else{
                     window.location.href = "/ingresovehiculo";
                     $("#imgload").hide();
                       
                  }
              })
            }
          
        })


      var tipo = $("#tipovehiculo").children("option:selected").val();
      $.ajax({
        type: "get",
        dataType: 'json',
        url: '/buscartarifas/'+tipo,         
      }).done(function(respuesta){
        $("#cmbTarifa").html(respuesta.vista);
      });


      $("#tipovehiculo").on('change',function (){
        var tipo = $(this).children("option:selected").val();
        $.ajax({
          type: "get",
          dataType: 'json',
          url: '/buscartarifas/'+tipo,         
        }).done(function(respuesta){
          $("#cmbTarifa").html(respuesta.vista);
        });
      });

      $("#btnRegComp").on("click", function() {
        var formulario = $("#formfact").serializeArray();
        $("#imgload").show();
        $(".botones").hide();
        $.ajax({
          type: "POST",
          dataType: 'json',
          url: '/registraringreso',
          data: formulario,
        }).done(function(respuesta){

          if(respuesta.codigo =='504'){

           alert(respuesta.mensaje);

           $("#imgload").hide();
           $(".botones").show();

         }else{
           window.location.href = "/ingresovehiculo";
           $("#imgload").hide();
              //$(".botones").show();
            }



       });



      });

      
      

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

      $('#boleta').attr('checked', 'checked');;
      $('#soles').attr('checked', 'checked');;
      if($('#boleta').is(':checked')){
        $('#clinum').val('00000000');
        $('#clinom').val('Varios');
        $("#tdicod").val('1');
        $('#tdocod').val('2');
      }

      if($('#factura').is(':checked')){
        $('#clinum').val('');
        $('#clinom').val('');
        $("#tdicod").val('6');
        $('#tdocod').val('1');
      }

      if($('#soles').is(':checked')){
       $('#key').prop('disabled',true);
       $('#moncod').val('1');
     }

     if($('#dolares').is(':checked')){
      $('#key').prop('disabled',false);
      $('#moncod').val('2');
    }


    $("#soles").on('change', function (){

     if($('#soles').is(':checked')){
      $('#key').prop('disabled',true);
      $('#moncod').val('1');
    }

  })


    $("#dolares").on('change', function (){

     if($('#dolares').is(':checked')){
      $('#key').prop('disabled',false);
      $('#moncod').val('2');
    }

  })


    $("#factura").on('change', function (){

     if($('#factura').is(':checked')){
      $('#clinum').val('');
      $('#clinom').val('');
      $("#tdicod").val('6');
      $('#tdocod').val('1');
    }

  })

    $("#boleta").on('change', function (){

      if($('#boleta').is(':checked')){

        $('#clinum').val('00000000');
        $('#clinom').val('Varios');
        $("#tdicod").val('1');
        $('#tdocod').val('2');
      }
    })
  });


    function deleteRow(btn) {
      var row = btn.parentNode.parentNode;
      row.parentNode.removeChild(row);
      calculartotal();
    };

    function mostrar(comp){
      var id = comp.id;
      var val = comp.value;
      $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
      $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/consultarmenucomanda/"+val,

      }).done(function(respuesta){
        $("#detmenu").html(respuesta.vista);
      });

    }

    $(function(){
      $('#key').keyboard();
    });



    function cobrarvehiculo(){

        var placa = $('#placacobrar').val();

        if(placa ==""){
          
          $('#placa').focus();

        }else{


          $("#imgload").show();
          $(".botones").hide();
          $.ajax({
            type: "GET",
            dataType: 'json',
            url: '/buscarplaca/'+placa,
    
          }).done(function(respuesta){

            if(respuesta.codigo =='ERROR'){

             alert(respuesta.mensaje);

             $("#imgload").hide();
             $(".botones").show();

           }else{
             window.location.href = "/cobrarplaca/"+respuesta.codigo;
             $("#imgload").hide();
                
              }



         });

        }

    }

    function  buscarcliente(){

    
          var formulario = $("#dni").val();
          //$("#imgload").show();
          //$(".botones").hide();
          $.ajax({
            type: "get",
            dataType: 'json',
            url: '/autocomplete/'+formulario,
            
          }).done(function(respuesta){

             $('#nombre').val(respuesta[0].nom);
        
          //$("#imgload").hide();
          //$(".botones").show();
          
          });

    }


    function mostrar(comp){
      var id = comp.id;
      var val = comp.value;
      $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
      $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/consultarmenucomanda/"+val,

      }).done(function(respuesta){
        $("#detmenu").html(respuesta.vista);
      });

    }

    function calculartotal(){

     var totigv = 0,totgrav=0 ,subtotal=0;

     $("#grdet tbody tr").each(function(){

      totgrav = totgrav + ($(this).find("td:eq(1) > input").val() *parseFloat($(this).find("td:eq(4)  > input").val()));

      subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

      totigv = totgrav - subtotal;

      $('#total').val(totgrav.toFixed(2));
      $('#igv').val(totigv.toFixed(2));
      $('#subtotal').val(subtotal.toFixed(2));
    })



     if ($('#grdet >tbody >tr').length == 0){
      $('#total').val('0.00');
      $('#igv').val('0.00');
      $('#subtotal').val('0.00');
    }


  };

</script>


</br>
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
      @if(session()->has('info'))
      <div class="alert alert-danger">
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
    </div>
  </div> 
   <div class="row">
 
      <div class="col-lg-12">  
     
                 <div class="form-group form-group-sm">
                  <input class="form-control input-sm" type="text" name="placacobrar" id="placacobrar" placeholder="INGRESAR PLACA A COBRAR" onKeypress="if(event.keyCode == 13) cobrarvehiculo();">
               
              </div>
            </div>  
     
     

  </div>
  {!!Form::open(array('url'=>'/mesas','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
  {{Form::token()}}
  <div class="row">
   <input style="display:none;" type="date" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-dS')}}" name="fecha" id="fecha">
   <div class="col-lg-12">
    <div class="box">
      
         <div class="box-header" style="background-color:blue;">
               <center><font color="white"><strong>INGRESO DE VEHICULOS</strong></font></center>
            </div>
  
        <div class="box-body">
          <div class="row">
           
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
           
                <input class="form-control" type="text" name="nombreplaca" id="nombreplaca" placeholder="BUSCAR CLIENTE - PLACAS">
              </div>
            </div>
         
            
          </div>
          <div class="row">
            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                <label>PLACA</label>
                <input class="form-control" style="height:100px;font-size:20px;font-weight:bold;text-transform:uppercase" type="text" name="placa" id="placa" >
              </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                <label>PLACA ADICIONAL</label>
                <input class="form-control" style="height:100px;font-size:20px;font-weight:bold;text-transform:uppercase"  type="text" name="placa2" id="placa2">
              </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                <label>TIPO</label>
                <select name="tipovehiculo" id="tipovehiculo" class="form-control">
                  @foreach($tiposvehiculos as $tipovehiculo)
                  <option value="{{$tipovehiculo->id_tipo_vehiculo}}">{{$tipovehiculo->descripcion}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div id="cmbTarifa" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                <label>TARIFAS</label>
                <select name="tarifa" id="tarifa" class="form-control">
                  @foreach($tarifas as $tarifa)
                  <option value="{{$tarifa->id_tarifa}}">{{$tarifa->descripcion}} / S/. {{$tarifa->precio}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
             <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                <label>TIPO DOCUMENTO</label>
                <select class="form-control" name="tdicod" id="tdicod" >
                    @foreach($documentos as $documento)
                     <option value="{{$documento->tdicod}}">{{$documento->tdides}}</option>
                    @endforeach
                </select>
              
              </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                <label>RUC</label>
                <input class="form-control" type="text" name="dni" id="dni" onKeypress="if(event.keyCode == 13) buscarcliente();">
              </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                <label>RAZON SOCIAL</label>
                <input class="form-control" type="text" name="nombre" id="nombre">
              </div>
            </div>
          </div>
          <div class="row">
           <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>DNI CONDUCTOR</label>
              <input class="form-control" type="text" name="dniconductor">
            </div>
          </div>
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>NOMBRE CONDUCTOR</label>
              <input class="form-control" type="text" name="nombreconductor">
            </div>
          </div>
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>TELEFONO CONDUCTOR</label>
              <input class="form-control" type="text" name="telefonoconductor">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>DESCRIPCION</label>
              <input class="form-control" type="text" name="descripcion">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="btn-toolbar" role="toolbar" aria-label="...">
              <div class="btn-group">

               <button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-md botones">REGISTRAR</button><center><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload"></center>


             </div>
             <div class="btn-group" >
              <a href="/ingresovehiculo"><button type="button" class=" btn btn-block btn-primary btn-md botones" >SALIR</button></a>
            </div>
          </div>
        </div>
      </div>
    </div>
 
</div>



</div>




</div>
{!!Form::close()!!}
</div>

@endsection
