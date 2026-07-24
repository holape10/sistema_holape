  <script>
$(document).ready(function()
   {


    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

  
  $("#servicio").select2( {


    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
      url: "{{route('Productos.consultarservicios')}}",
      dataType: 'json',
      type: "POST",
      quietMillis: 50,
      data: function (params) {

       var id_almacen = $("#id_almacen").val();

       return {
        _token : CSRF_TOKEN,
        search: params.term,
        almacen: id_almacen,
      };
    },
    processResults: function (response) {


      return {
        results: $.map(response, function(response){


          return {
            "text": response.text,
            "id": response.id,
            "pro_rel": response.pro_rel,
            "presentacion": response.contar,
            "propun": response.propun,
            "unidad": response.unidad,
            "producto": response.producto,
            "id_almacen_pro":response.id_almacen,
            "pro_cod":response.codigo
          }


        })


      };



    },
    cache:false
  }

});
      $("#servicio").select2('open');

   });

  </script>
  
  <select data-tags='true' style=" font-weight: bold;" autocomplete="false" class="form-control" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio_servicio();" onchange="ingresar_cantidad_precio_servicio();"  name="servicio" id="servicio">
                         
                     </select>