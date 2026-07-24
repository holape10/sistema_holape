<script type="text/javascript">
    // Esta función se encarga de buscar productos por categoría y actualizar la vista
    function buscar_producto_categoria_img(id){
        var producto=0; // Puede ser un valor de búsqueda o 0 para listar todos por categoría
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarcartaimg/"+producto+"/"+id, // Asegúrate de que esta ruta esté definida y apunte a tu ProductosController@buscarcarta
        }).done(function(respuesta){
            $("#items_productos").html(respuesta.vista);
        });
    }

    // Función para añadir un producto a la lista de pedidos en el frontend
    function agregar_item(){
        var id_ = $('#_id').val();
        var producto_ = $('#_producto').val();
        var icbper_ = $('#_icbper').val();
        var precio_ = $('#_precio').val();
        var acompa_ = $('#_acompa').val();
        var stock_ = $('#_stock').val(); // Obtener el stock aquí

        var validar = checkId(id_); // Función para verificar si el ítem ya está en la tabla

        if (validar==true){
            $("#tbl_detalle > tbody > tr").each(function(){
                if($(this).find("td:eq(0) > input").val() == id_){
                    var current_qty_input = $(this).find("td:eq(2) > input");
                    var current_quantity = parseFloat(current_qty_input.val());
                    var new_quantity = current_quantity + 1;

                    // Validación de stock al incrementar cantidad
                    var permitir_venta_sin_stock = {{ $negocio->ven_sin_sto ?? 0 }}; // Asumiendo que $negocio está disponible

                    if (parseFloat(stock_) !== null && parseFloat(stock_) !== '' && parseFloat(stock_) >= 0 && new_quantity > parseFloat(stock_)) {
                        alert('No hay suficiente stock para este producto. Stock disponible: ' + stock_);
                        current_qty_input.val(stock_); // Establecer la cantidad al stock máximo
                        return false; // Detener la adición
                    }
                    current_qty_input.val(new_quantity);
                }
            });
        }else{
            var permitir_venta_sin_stock = {{ $negocio->ven_sin_sto ?? 0 }}; // Asumiendo que $negocio está disponible
            /*if(parseFloat(stock_) <= 0 && permitir_venta_sin_stock == 0){
                alert('No hay stock disponible para este producto y la venta sin stock no está permitida.');
                return false;
            }*/

            // Añadir nueva fila a la tabla de pedidos
            $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text"  class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
            '<td><input type="hidden" class="form-control" name="descripcion[]" value="'+producto_+' - '+acompa_+'">'+producto_+' - '+acompa_+'</td>'+
            '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1" ' + (stock_ !== null && stock_ !== '' && parseFloat(stock_) >= 0 ? 'max="'+stock_+'"' : '') + '></td>'+
            '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
            '<td style="text-align:right;" ><input  class="form-control" type="number" step="0.01" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
            '<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
            '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
            '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_stock[]" value="'+stock_+'"></td>'+ // Guardar el stock en un campo oculto
            '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        }

        // Limpiar campos ocultos de selección de producto
        $('#_id').val('');
        $('#_producto').val('');
        $('#_precio').val('');
        $('#_acompa').val('');
        $('#_icbper').val('');
        $('#_stock').val(''); 

        $(".cantidad").focus();
        $(".cantidad").select();
    
        calcular_total();
    }

    // Función para elegir un acompañamiento o producto
    function elegir_acompanamiento(id,producto,precio,cat_sig,acom,icbper,stock){ // Recibe 'stock' como parámetro

        var cat_acom = $('#cat_acom').val();

        if(cat_acom=='1'){ // Si es un acompañamiento
            $('#_acompa').val(producto);
            $('#_stock').val(stock); // Pasa el stock al campo oculto
            agregar_item(); // Agrega el ítem
            // Limpia los campos ocultos después de agregar
            $('#_id').val('');
            $('#_producto').val('');
            $('#_precio').val('');
            $('#_icbper').val('');
            $('#_acompa').val('');
            $('#_stock').val('');
        }else{ // Si es un producto normal
            $('#_id').val(id);
            $('#_producto').val(producto);
            $('#_precio').val(precio);
            $('#_icbper').val(icbper);
            $('#_stock').val(stock); // Pasa el stock al campo oculto
            $("#btnAgregarItem").hide();    // Oculta el botón de agregar si lo tenías
        }
        
        if(cat_sig !=''){ // Si hay una categoría siguiente para buscar acompañamientos
            buscar_producto_categoria_img(cat_sig); // Busca productos de la siguiente categoría
        }else{ // Si no hay más categorías siguientes, agrega el ítem directamente
            var id_ = $('#_id').val();
            var producto_ = $('#_producto').val();
            var precio_ = $('#_precio').val();
            var icbper_ = $('#_icbper').val();
            var stock_ = $('#_stock').val(); // Obtener el stock aquí

            var validar = checkId(id_); // Verifica si el ítem ya está en la tabla

            if (validar==true){
                $("#tbl_detalle > tbody > tr").each(function(){
                    if($(this).find("td:eq(0) > input").val() == id_){
                        var current_qty_input = $(this).find("td:eq(2) > input");
                        var current_quantity = parseFloat(current_qty_input.val());
                        var new_quantity = current_quantity + 1;

                        // Validación de stock al incrementar cantidad
                        if (parseFloat(stock_) !== null && parseFloat(stock_) !== '' && parseFloat(stock_) >= 0 && new_quantity > parseFloat(stock_)) {
                            alert('No hay suficiente stock para este producto. Stock disponible: ' + stock_);
                            current_qty_input.val(stock_); // Establecer la cantidad al stock máximo
                            return false; // Detener la adición
                        }
                        current_qty_input.val(new_quantity);
                    }
                });
            }else{
                var permitir_venta_sin_stock = {{ $negocio->ven_sin_sto ?? 0 }}; // Asumiendo que $negocio está disponible
                /*if(parseFloat(stock_) <= 0 && permitir_venta_sin_stock == 0){
                    alert('No hay stock disponible para este producto y la venta sin stock no está permitida.');
                    return false;
                }*/
                // Añadir nueva fila a la tabla de pedidos
                $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text"  class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
                '<td style="font-weight:bold;"><input type="hidden" class="form-control" name="descripcion[]"   value="'+producto_+'">'+producto_+'</td>'+
                '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1" ' + (stock_ !== null && stock_ !== '' && parseFloat(stock_) >= 0 ? 'max="'+stock_+'"' : '') + '></td>'+
                '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
                '<td style="text-align:right;" ><input class="form-control" type="number" step="0.01" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
                '<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
                '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
                '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_stock[]" value="'+stock_+'"></td>'+ // Guardar stock
                '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
            }

            // Limpiar campos ocultos de selección de producto después de agregar
            $('#_id').val('');
            $('#_producto').val('');
            $('#_icbper').val('');
            $('#_precio').val('');
            $('#_acompa').val('');
            $('#_stock').val('');
        }
        calcular_total();
    }
</script>

{{-- Sección de productos --}}
@foreach($productos as $p)
    <div class="col-xs-4 product-item-col"> {{-- col-xs-4 para 3 productos por fila en móvil --}}
        <div class="product-item-custom" onclick="elegir_acompanamiento('{{$p->IdProducto}}','{{$p->pronom}}','{{$p->precio}}','{{$p->cat_sig}}','{{$p->acom}}','{{$p->icbper}}','{{$p->stock_disponible}}')">
            <div class="product-image-container">
                <span class="info-box-icon bg-aqua" style="background-image:url('/imagenes/productos/{{$p->imagenproducto}}');background-repeat: no-repeat;background-size: contain; background-position: center;"></span>
            </div>
            <div class="product-details-container">
                <span class="product-name-custom">{{$p->pronom}}</span>
                <span class="product-price-custom">S/. {{number_format($p->precio,'2','.','')}}</span>
                <span class="product-stock-custom">STOCK: {{ number_format($p->stock_disponible, 2) }}</span>
            </div>
        </div>
    </div>
@endforeach