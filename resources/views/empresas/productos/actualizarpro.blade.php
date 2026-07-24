  <script>
$(document).ready(function()
   {

	 
	 $("#num_ped").focus();
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

     $("#producto").select2({
    minimumInputLength: 2,
    tags: "true",
    allowClear: "true",
    ajax: {
        url: "{{route('Productos.consultarproductos')}}",
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

             /* $("#producto").html(response.vista);*/

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
                     "pro_cod":response.codigo,
                    "icbper":response.icbper,
                    "mon_icbper":response.mon_icbper
                  }

                })
                    
                };
        },
            cache:false
        }
    
});

      $("#producto").select2('open');


   });
  </script>
  
  <select data-tags='true' style=" font-weight: bold;" autocomplete="false" class="form-control" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio();" onchange="ingresar_cantidad_precio();"  name="producto" id="producto">
                         
  </select>