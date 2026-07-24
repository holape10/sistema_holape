<script type="text/javascript">
        
    function buscar_producto_categoria(id){
        var producto=0;

        
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarcarta/"+producto+"/"+id,
        }).done(function(respuesta){
            $("#items_productos").html(respuesta.vista);
        });
    }


    // Modifica esta función para incluir el stock
    function agregar_item(){

        var id_ = $('#_id').val();
        var producto_ = $('#_producto').val();
        var icbper_ = $('#_icbper').val();
        var precio_ = $('#_precio').val();
        var acompa_ = $('#_acompa').val();
        var stock_ = $('#_stock').val(); // <-- Obtener el stock aquí

        var validar = checkId(id_);

        if (validar==true){
            $("#tbl_detalle  > tbody  > tr").each(function(){
                if(id_==$(this).find("td:eq(0) > input").val()){ // Usar id_ para que coincida con la variable local
                    var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;
                    $(this).find("td:eq(2) > input").val(calcular_cantidad);
                }
            });

        }else{

            $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text"  class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
            '<td><input type=text" class="form-control" name="descripcion[]" value="'+producto_+' - '+acompa_+'"></td>'+
            '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1" max="'+stock_+'"></td>'+ // <-- Añadir max al input de cantidad
            '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
            '<td style="text-align:right;" ><input  class="form-control" type="number" step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
            '<td style="text-align:right;" hidden="hidden"><input  class="form-control" type="text"   name="item_obs[]"></td>'+
            '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
            '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_stock[]" value="'+stock_+'"></td>'+ // <-- Guardar el stock en un campo oculto
            '<td  style="text-align:center;">)<button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        }

        $('#_id').val('');
        $('#_producto').val('');
        $('#_precio').val('');
        $('#_acompa').val('');
        $('#_icbper').val('');
        $('#_stock').val(''); // <-- Limpiar el campo de stock

        $(".cantidad").focus();
        $(".cantidad").select();
    
        calcular_total();
    }

    // Modifica esta función para incluir el stock
    function elegir_acompanamiento(id,producto,precio,cat_sig,acom,icbper,stock){ // <-- Añadir 'stock' como parámetro
        var cat_acom = $('#cat_acom').val();

        if(cat_acom=='1'){
            $('#_acompa').val(producto);
            $('#_stock').val(stock); // <-- Pasar el stock al campo oculto
            agregar_item();

            $('#_id').val('');
            $('#_producto').val('');
            $('#_precio').val('');
            $('#_acompa').val('');
            $('#_icbper').val('');
            $('#_stock').val(''); // <-- Limpiar el campo de stock
        }else{
            $('#_id').val(id);
            $('#_producto').val(producto);
            $('#_precio').val(precio);
            $('#_icbper').val(icbper);
            $('#_stock').val(stock); // <-- Pasar el stock al campo oculto
            $("#btnAgregarItem").hide();    
        }
        
        if(cat_sig !=''){
                buscar_producto_categoria(cat_sig);
        }else{
            var id_ = $('#_id').val();
            var producto_ = $('#_producto').val();
            var precio_ = $('#_precio').val();
            var icbper_ = $('#_icbper').val();
            var stock_ = $('#_stock').val(); // <-- Obtener el stock aquí

            var validar = checkId(id_);

            if (validar==true){
                $("#tbl_detalle  > tbody  > tr").each(function(){
                    if(id_==$(this).find("td:eq(0) > input").val()){ // Usar id_
                        var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;
                        $(this).find("td:eq(2) > input").val(calcular_cantidad);
                    }
                });
            }else{
                $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text"  class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
                '<td style="font-weight:bold;"><input type="hidden" class="form-control" name="descripcion[]"   value="'+producto_+'">'+producto_+'</td>'+
                '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1" max="'+stock_+'"></td>'+ // <-- Añadir max
                '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
                '<td style="text-align:right;" ><input class="form-control" type="number" step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
                '<td style="text-align:right;" ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
                '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
                '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_stock[]" value="'+stock_+'"></td>'+ // <-- Guardar stock
                '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
            }

                $('#_id').val('');
                $('#_producto').val('');
                $('#_icbper').val('');
                $('#_precio').val('');
                $('#_acompa').val('');
                $('#_stock').val(''); // <-- Limpiar el campo de stock
        }
        calcular_total();
    }
</script>

<table class="table table-hover table-bordered table-condensed" >   
  <tr>
    <td colspan="5" style="background-color:gray;color: white;">
         <center><strong>@if(!empty($data_cat)){{$data_cat->cat_nom}}@else  @endif</strong></center>
    </td>
  </tr>             
    @foreach($productos as $pro)
    <tr>
        <td style="text-align:center;font-weight:bold;" width="10%;">
            <button type="button" onclick="elegir_acompanamiento('{{$pro->IdProducto}}','{{$pro->pronom}}','{{$pro->precio}}','{{$pro->cat_sig}}','{{$pro->acom}}','{{$pro->icbper}}','{{$pro->stock_disponible}}')"  name="add" id="add" class="btn btn-success btn-sm add">
                <span class="glyphicon glyphicon-plus"></span></button>
        </td>
        <td style="padding-left:20px;font-weight:bold;" width="50%;">{{$pro->pronom}}</td>
        <td hidden="hidden" style="padding-left:20px;font-weight:bold;" width="10%;">{{$pro->umecod}}</td>
        <td hidden="hidden" style="padding-left:20px;font-weight:bold;text-align:right;" width="10%;">{{$pro->factor}}</td>
        <td style="text-align:right;padding-right:20px;font-weight:bold;" width="20%;">STOCK: {{ number_format($pro->stock_disponible, 0) }}</td>
        <td style="text-align:right;padding-right:20px;font-weight:bold;" width="20%;">S/. {{ number_format($pro->precio, 2) }}</td>
        
    </tr>
    @endforeach
    <input type="hidden" readonly="readonly"  name="cat_acom" id="cat_acom" value="@if(!empty($data_cat)){{$data_cat->cat_acom}}@else 0 @endif">    

</table>