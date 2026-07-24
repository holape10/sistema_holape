  <script>
$(document).ready(function()
   {


    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

     $("#producto").select2({
    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
        url: "{{route('Productos.consultarproductoscompra')}}",
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
                     "costo": response.costo,
                    "producto": response.producto,
                    "laboratorio": response.lab_id,
                    "codigo":response.codigo
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