@extends('layouts.empresas')
@section('contenido')
@include('empresas.productos.modalimportarinventario')
@include('empresas.puntosventas.modalpresentaciones')
@include('empresas.puntosventas.modalingresarcantidadprecio')
<style type="text/css">
  #modal-cantidad-precio{
  z-index: 99999 !important;
}

#table-presentaciones .btn:hover
{
    color: #fff;
    background-color:red;
    border-color: red;
}

#table-presentaciones .btn:focus
{
    color: #fff;
    background-color:red;
    border-color: red;
}


.green
{
    color: #fff;
    background-color: #398439;
    border-color: #255625;
}

</style>
<script>
$(document).ready(function()
{       

    
           var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


        $('#modal-cantidad-precio').on('shown.bs.modal', function() { $("#can_producto").focus(); })
      $('#modal-presentaciones').on('shown.bs.modal', function() { $("#table-presentaciones .btn:first").focus(); })


    $("#can_producto").keypress(function(e){
       var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        
        $("#pre_producto").focus();
        $("#pre_producto").select();
      }
     


    });


    $("#pre_producto").keypress(function(e){
       var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        
        agregaritem();
        $("#modal-cantidad-precio").modal("hide");
      }
     
    });





       $("#producto").select2({
    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
      url: "{{route('Productos.consultarproductos')}}",
      dataType: 'json',
      type: "POST",
      quietMillis: 50,
       data: function (params) {

           var id_almacen = $("#almacen").val();
           
            return {
                _token : CSRF_TOKEN,
                search: params.term,
                almacen: id_almacen,
            };
        },
      processResults: function (response) {

       /* $("#producto").html(response.vista);*/

       return {
        results: $.map(response, function(response){


          return {
            "text": response.textcompra,
            "id": response.id,
            "pro_rel": response.pro_rel,
            "presentacion": response.contar,
            "propun": response.propun,
            "producto": response.producto,
            "unidad":response.unidad
          }

        })

      };
    },
    cache:false
  }

});

  $("#producto").select2('open');



     $("#sucursal").change(function() {
         
                var sucursal = $("#sucursal").val();
                $("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacen").html(respuesta.vista);
               
                });

      });

          $("#sucursalimport").change(function() {
         
                var sucursal = $("#sucursalimport").val();
                $("#divalmacenimport").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacenimport").html(respuesta.vista);
               
                });

      });




        $("#buscardescripcion").keyup(function() {
      var val = $(this).val();
      var contarcarateres = $(this).val().length;
      var sucursal = $('#sucursal').val();
      var almacen = $('#id_almacen').val();
      if(contarcarateres >0){
        $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/busquedaproductoinventario/"+val+"/"+sucursal+"/"+almacen,

        }).done(function(respuesta){
          $("#detmenu").html(respuesta.vista);

        });
      }


    });







   /*   $("#cmbCatId").change(function() {
            
              $('#promocion').val('Todos');
          $('#buspro').val('');
              $('#formstock').submit();
        

        });*/

 });

function buscarinventario(id){

   var suc = $("#sucursal").val();
   var almacen = $("#almacen").val();
   window.location.href = "/inventarios/"+suc+"/"+almacen+"/"+id;
}

function exportarinventario(){

   var suc = $("#sucursal").val();
   var almacen = $("#id_almacen").val();
   window.location.href = "/inventariosexcel/"+suc+"/"+almacen;
}


function crearinventario(id){

   var suc = $("#sucursal").val();
   var almacen = $("#almacen").val();
   window.location.href = "/inventarios/"+suc+"/"+almacen+"/"+id;
}

function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);

};

           
function mostrar(comp){
  var id = comp.id;
  var val = comp.value;
  var sucursal = $('#sucursal').val();
   var almacen = $('#id_almacen').val();
  $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/consultarmenuinventario/"+val+"/"+sucursal+"/"+almacen,

  }).done(function(respuesta){
    $("#detmenu").html(respuesta.vista);
  });

}

 function costeo(btn){
     var id = btn.id;
     var suc = $('#sucursal').val();

 
       $("#modal-costeo").modal("show");

       $("#costeo").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/costeoproductos/"+id+"/"+suc,

        }).done(function(respuesta){
          $("#costeo").html(respuesta.vista);
        });



  }


 function presentaciones(id){
     var id = id;
     var suc = $('#sucursal').val();
     var almacen = $('#id_almacen').val();

 
       $("#modal-presentaciones").modal("show");

       $("#presentaciones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/presentacionesproducto/"+id,

        }).done(function(respuesta){
          $("#presentaciones").html(respuesta.vista);
        });



  }
 

     function ingresar_cantidad_precio_presentacion(button){
      
    
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
     var unidad= $('#'+id+'unidad').val();
     
     

        //$("#modal-cantidad-precio").modal("show");

       $('#modal-cantidad-precio').modal('show'); 
       $('#modal-cantidad-precio').on('shown', function(){ 
       $("#can_producto").focus();

        
      })



        $("#des_producto").val(producto);
        $("#id_producto").val(proid);
        $("#pre_producto").val(precio);
        $("#uni_producto").val(unidad);

        $("#can_producto").select(); 
      
        $("#modal-presentaciones").modal("hide");
           
        actualizarpro();

      
       

  }

  function ingresar_cantidad_precio(){
      
    
      var producto = $('#producto').select2('data')[0].producto;
      var precio =  $('#producto').select2('data')[0].propun;
      var costo =  $('#producto').select2('data')[0].costo;
      var pro_rel =  $('#producto').select2('data')[0].pro_rel;
      var proid =  $('#producto').select2('data')[0].id;
      var unidad =  $('#producto').select2('data')[0].unidad;
      var laboratorio =  $('#producto').select2('data')[0].laboratorio;
      var contar = $('#producto').select2('data')[0].presentacion;


      if(contar>0){

        presentaciones(proid);

        $("#modal-presentaciones").modal("show");

      }else{


       $('#modal-cantidad-precio').modal('show'); 
       $('#modal-cantidad-precio').on('shown', function(){ 
       $("#can_producto").focus();

        
      })


        $("#des_producto").val(producto);
        $("#id_producto").val(proid);
        $("#pre_producto").val(costo);
        $("#uni_producto").val(unidad);
        $("#lab_producto").val(laboratorio);

        $("#can_producto").select(); 
  
        actualizarpro();

      }
       

  }




 function actualizarpro(){

  
  
    $.ajax({
      type: "GET",
      dataType: 'json',
      url: "/actualizarpro/venta",

    }).done(function(respuesta){
  
      
        $("#divactpro").html(respuesta.vista);
    
     
    
    });

    


  }
  

function agregaritem_pre(button){
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
     var laboratorio = $('#'+id+'lab').val();
     var imagen = $('#'+id+'imagen').val();
      var cantidad = $('#can_producto').val();
         var total = cantidad*precio;

  $('#detFact').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='detpro[]' value='"+producto+"' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td ><input type='text' class='form-control input-sm' readonly='readonly' value='"+unidad+"'></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name=preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control input-sm' name='vtot[]'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='id[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      

      actualizarpro();
      $("#modal-presentaciones").modal("hide");

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }

  function agregaritem(){
     
 

   
    var producto = $('#des_producto').val();
    var precio =  $('#pre_producto').val();
    var costo =  $('#pre_producto').val();
    var proid =  $('#id_producto').val();
    var  unidad =  $('#uni_producto').val();
    var cantidad = $('#can_producto').val();
    var laboratorio = $('#lab_producto').val();
    var total = cantidad*costo;


        $('#detFact').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='detpro[]' value='"+producto+"' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td ><input type='text' class='form-control input-sm' readonly='readonly' value='"+unidad+"'></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td  hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name=preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+costo+"' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control input-sm' name='vtot[]'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='id[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

        

       
        actualizarpro();
        $("#modal-presentaciones").modal("hide");
 

  }

</script>
<section class="content">

<div class="row">
    <div class="col-xs-12">
      <div class="box">
        
        <div class="box-header" style="background-color:blue;">
              <font size="2" color="white"><strong><center>INVENTARIOS</center></strong></font>
            </div>
            <div class="box-body">
              @include('empresas.productos.searchinventario') 
            </div>

          <div class="box-body">

          @if($tipo=='2')
             <div class="row">

                             <div class="col-lg-12">
                      
                     <div class="form-group form-group-sm" id="divactpro">
                        <select style=" font-weight: bold;" class="form-control input-sm" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio();" onchange="ingresar_cantidad_precio();" onclick="ingresar_cantidad_precio();"  name="producto" id="producto">
                         
                     </select>
                    </div>
                   
                   
                  </div>
                           

                       
                      </div>

          @endif


          {!!Form::open(array('url'=>'actualizarstock','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
        {{Form::token()}}  
        <input type="hidden" name="suc_id" value="{{$sucursal}}">
        <input type="hidden" name="alm_id" id="id_almacen" value="{{$almacen}}">
        <input type="hidden" name="inv_fec" value="{{$fecha}}">
        @if($tipo=='1')
      <table style="background-color:#D5DBDB;" id="detFact"   class="table table-responsive table-striped table-bordered">
        <thead>
          <tr>
            <th colspan="5" style="background:blue;">
            <font color="white"><center>STOCK DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}} @if(!empty($datos)) - {{$datos->tipo_negocio}} @endif @if(!empty($datosalm)) - <br>{{$datosalm->descripcion}} @endif</center></font>
          </th>#

          
          </tr>
          <tr>
          <th >FECHA</th>
          <th width="500px;">USUARIO</th>
          <th>EMPRESA</th>
          <th>ALMACEN</th>
          <th>EDITAR </th>        
        </tr>
        </thead>
        <tbody >
        @if($tipo=='1')
        @if(!empty($productos))
        @foreach ($productos as $pro)

        

          <tr >
            <td >{{$pro->inv_fec}}</td>
            <td>{{$pro->name}} {{$pro->apeusu}}</td>
                      
            <td>{{$datos->IdEmpresa}} - {{$datos->tipo_negocio}}</td>
            <td>{{$datosalm->descripcion}}</td>
            <td><a href="/editarinventario/{{$pro->inv_cab_id}}"><button type="button" class="btn btn-primary">Editar</button></a></td>
            
          </tr>
        
      
        
        @endforeach
        @endif
          @endif
        </tbody>
      </table>
      @elseif($tipo=='2')
        <table style="background-color:#D5DBDB;" id="detFact"   class="table table-responsive table-striped table-bordered">
        <thead>
          <tr>
            <th colspan="5" style="background:blue;">
            <font color="white"><center>STOCK DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}} @if(!empty($datos)) - {{$datos->tipo_negocio}} @endif @if(!empty($datosalm)) - <br>{{$datosalm->descripcion}} @endif</center></font>
          </th>

          <th style="background:blue;">
            @if($tipo =='2')
            <button type="submit" class="btn btn-sm btn-block btn-success">ACTUALIZAR STOCK</button>
            @endif
          </th>
            
          </tr>
          <tr>
          <th hidden="hidden" >CODIGO</th>
          <th width="1000px;">PRODUCTO</th>
            <th>CANTIDAD</th>
          <th>UNIDAD MEDIDA</th>
        
          <th hidden="hidden">COSTO</th>        
        </tr>
        </thead>
        <tbody >
          @if(!empty($productoslista))
          @foreach($productoslista as $prod)
            <tr><td width='900px'><input type='text' class='form-control input-sm' name='detpro[]' value='{{$prod->pronom}}' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='{{$prod->inv_can}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td ><input type='text' class='form-control input-sm' readonly='readonly' value='{{$prod->umecod}}'></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td  hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control input-sm' name='vtot[]'  value='' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='id[]'  value='{{$prod->IdProducto}}' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>
          @endforeach
          @endif
        </tbody>
      </table>
      @endif
    {!!Form::close()!!} 
    </div>

  </div>
</div>
</div>
</section>
@endsection
