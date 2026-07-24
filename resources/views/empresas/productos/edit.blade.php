@extends ('layouts.empresas')
@section ('contenido')
<script>
    var validarContabilidad = false;

    function gestionarCamposInsumo() {
        var tipoSeleccionado = $("#promocion").val();
        if (tipoSeleccionado == "4") { // 4 es Insumo
            $("#seccion_equivalencia").fadeIn();
        } else {
            $("#seccion_equivalencia").hide();
        }
    }

    $(document).ready(function() {

        gestionarCamposInsumo();

        // ====================================================================
        // SELECT2 PARA CÓDIGO SUNAT (EDICIÓN)
        // ====================================================================
        $('#cod_producto_sunat').select2({
            placeholder: 'Escriba código de 8 dígitos o descripción...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: '/buscar-catalogo-sunat',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        // PRESELECCIONAR EL CÓDIGO SUNAT ACTUAL SI EXISTE EN LA BASE DE DATOS
        var codSunatActual = "{{ $productos->cod_producto_sunat }}";
        if(codSunatActual !== "") {
            $.ajax({
                type: 'GET',
                url: '/buscar-catalogo-sunat',
                data: { q: codSunatActual },
                dataType: 'json'
            }).then(function (data) {
                if (data && data.length > 0) {
                    var item = data.find(function(i){ return i.id == codSunatActual; }) || data[0];
                    var option = new Option(item.text, item.id, true, true);
                    $('#cod_producto_sunat').append(option).trigger('change');
                } else {
                    var option = new Option(codSunatActual, codSunatActual, true, true);
                    $('#cod_producto_sunat').append(option).trigger('change');
                }
            });
        }

        // DETECCIÓN INTELIGENTE DE CÓDIGO SUNAT AL EDITAR NOMBRE
        $("input[name='txt_pronom']").on('blur', function() {
            var nombreProducto = $(this).val().trim();
            var codigoActual = $('#cod_producto_sunat').val();

            if (nombreProducto !== "" && !codigoActual) {
                $.ajax({
                    type: "GET",
                    url: "/buscar-catalogo-sunat",
                    data: { q: nombreProducto },
                    dataType: "json",
                    success: function(data) {
                        if (data && data.length > 0) {
                            var sugerencia = data[0];
                            var option = new Option(sugerencia.text, sugerencia.id, true, true);
                            $('#cod_producto_sunat').append(option).trigger('change');
                        }
                    }
                });
            }
        });

        $("#promocion").change(function() {
            gestionarCamposInsumo();
        });

        $("form").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        });

        // ====================================================================
        // 1. Cuando cambia Línea -> Carga las FAMILIAS
        // ====================================================================
        $("#tip_pro_id_form").change(function() {
            var tip_pro_id = $(this).val();
            $("#cat_id_form").html('<option value="">Cargando...</option>');
            $("#subcat_id_form").html('<option value="">Seleccione primero la Familia...</option>');

            if(tip_pro_id !== "") {
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "/buscarfamilias/" + tip_pro_id,
                }).done(function(respuesta) {
                    $("#cat_id_form").html(respuesta.vista);

                    // Selecciona automáticamente la primera Familia si no hay una previa seleccionada
                    var primerFamilia = $("#cat_id_form option:nth-child(2)").val(); 
                    if(primerFamilia && !$("#cat_id_form").val()) {
                        $("#cat_id_form").val(primerFamilia).trigger('change');
                    }
                });
            }
        });

        // ====================================================================
        // 2. Cuando cambia Familia -> Carga las SUBFAMILIAS
        // ====================================================================
        $("#cat_id_form").change(function() {
            var cat_id = $(this).val();
            $("#subcat_id_form").html('<option value="">Cargando...</option>');

            if(cat_id !== "" && cat_id !== null) {
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "/buscarsubcategorias/" + cat_id,
                }).done(function(respuesta) {
                    $("#subcat_id_form").html(respuesta.vista);

                    var primeraSubfamilia = $("#subcat_id_form option:nth-child(2)").val();
                    if(primeraSubfamilia && !$("#subcat_id_form").val()) {
                        $("#subcat_id_form").val(primeraSubfamilia);
                    }
                });
            }
        });

        // AUTO-SELECCIÓN SI LA LÍNEA ESTÁ VACÍA AL ENTRAR A EDITAR
        if(!$("#tip_pro_id_form").val()) {
            var primerLinea = $("#tip_pro_id_form option:nth-child(2)").val();
            if(primerLinea) {
                $("#tip_pro_id_form").val(primerLinea).trigger('change');
            }
        }

        // Listener para checkbox de aplicar porcentaje
        $("#aplicar_porcentaje").change(function() {
            calcular_costo_total();
        });

        // Listener para cambios en el porcentaje
        $("#porcentaje_costo").on('keyup change', function() {
            calcular_costo_total();
        });

        // Cálculos de Precios
        $("#txt_provun").on('keyup',function(){
            var numdoc = $('#txt_provun').val();
            $("#txt_propun").val((numdoc*1.18).toFixed(3));
        });

        $("#txt_propun").on('keyup',function(){
            var numdoc = $('#txt_propun').val();
            $("#txt_provun").val((numdoc/1.1055).toFixed(3));
        });

        // Agregar Códigos de Barra
        $('#add').click(function() {
            $('#detFact').append('<tr><td><input type="hidden" name="id[]" value=""><input type="text" class="form-control input-sm" name="codigobarra[]"></td><td width="10%"><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        });

        // Agregar Presentaciones
        $('#addpre').click(function() {
            var unidadesOptions = '';
            @foreach($unidades as $unidad) 
                unidadesOptions += '<option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option>';
            @endforeach

            $('#detpre').append('<tr>'
                +'<td hidden="hidden"><input type="hidden" value="0" name="idprod[]"></td>'
                +'<td><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-barcode"></span></button></span> <input type="text" class="form-control input-sm" name="codigo_barra_pre[]" value=""></div></td>'
                +'<td width="15%"><select name="presentacion[]" class="form-control input-sm">' + unidadesOptions + '</select></td>'
                +'<td><input name="descripcion[]" class="form-control input-sm"></td>'
                +'<td width="10%"><input type="number" min="0" value="0" step="any" name="factor[]" class="form-control input-sm"></td>'
                +'<td width="10%"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" min="0" value="0" step="any" name="precio[]" class="form-control input-sm"></div></td>'
                +'<td width="10%"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" step="any" value="0" class="form-control input-sm" name="costo[]"></div></td>'
                +'<td><input type="file" name="imagen_pre[]" class="form-control input-sm"></td>'
                +'<td><button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        });

        // Botón Actualizar (AJAX)
        $("#btnRegComp").on("click", function() {
            var familia = $("#cat_id_form").val();
            if (familia === "" || familia === null) {
                alert("El campo Familia es obligatorio. Por favor, seleccione una opción.");
                $("#cat_id_form").focus();
                return false;
            }

            var precioActual = parseFloat($("#txt_propun").val());
            if (isNaN(precioActual) || precioActual <= 0) {
                alert("Hermano, el precio es obligatorio y debe ser mayor a 0.");
                $("#txt_propun").focus();
                return false;
            }

            if ($("#promocion").val() == "4") {
                if ($("#umecod_cons").val() === "") {
                    alert("Hermano, para Insumos la U.M Equivalente es obligatoria.");
                    $("#umecod_cons").focus();
                    return false;
                }
                if ($("#factor_cons").val() === "" || parseFloat($("#factor_cons").val()) <= 0) {
                    alert("Hermano, el Factor Equivalente debe ser mayor a 0.");
                    $("#factor_cons").focus();
                    return false;
                }
            }

            if (validarContabilidad) {
                var v_debe = $("input[name='debe']").val().trim();
                var v_haber = $("input[name='haber']").val().trim();
                if (v_debe === "" || v_haber === "") {
                    alert("Las cuentas DEBE y HABER son obligatorias.");
                    return false;
                }
            }

            var formData = new FormData($('#frmProducto')[0]);
            var idProducto = $("#id_producto_hidden").val();

            $("#imgload").show();
            $(".botones").hide();

            $.ajax({
                type: "POST", 
                url: '/productos/' + idProducto, 
                data: formData,
                processData: false,
                contentType: false,
                success: function(respuesta) {
                    if(respuesta.estado == 'error'){
                        alert(respuesta.mensaje);
                        $("#imgload").hide();
                        $(".botones").show();
                    } else {
                        alert(respuesta.mensaje);
                        window.location.href = "/productos";
                    }
                },
                error: function(xhr) {
                    alert('Error al actualizar el producto.');
                    console.log(xhr.responseText);
                    $("#imgload").hide();
                    $(".botones").show();
                }
            });
        });

        // Modal Precios Dinámicos
        $('#modalPreciosDinamicos').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var productId = button.data('product-id');

            if (!productId) {
                alert('Por favor, guarda el producto primero para gestionar sus precios dinámicos.');
                $(this).modal('hide');
                return;
            }

            $.ajax({
                url: "/productos/get-precios-dinamicos-modal/" + productId,
                method: "GET",
                success: function(response) {
                    $('#contenido_modal_precios_dinamicos').html(response.html);
                },
                error: function(xhr) {
                    $('#contenido_modal_precios_dinamicos').html('<p class="text-danger">Error al cargar los precios dinámicos. Intente de nuevo.</p>');
                    console.log(xhr.responseText);
                }
            });
        });

        $('#modalPreciosDinamicos').on('hidden.bs.modal', function () {
            $('#contenido_modal_precios_dinamicos').html('<center><img src="/img/load.gif" width="50px" height="50px"></center>');
        });

    });

    function deleteRow(btn) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
        if ($('#detFact >tbody >tr').length == 0){
            $('.alertitem').show();
        }
    }

    function deletepresentacion(btn) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
        if ($('#detpre >tbody >tr').length == 0){
            $('.alertitem').show();
        }
    }

    function calcular_costo_total(){
        var costo = $('#txt_costo').val();
        var peso = $('#txt_peso').val();
        var flete = $('#txt_flete').val();
        var costo_total = 0;
        costo_total = parseFloat(costo)+(parseFloat(peso*flete));
        $("#costo_total").val(costo_total.toFixed(4));

        var aplicarPorcentaje = $('#aplicar_porcentaje').is(':checked');
        if(aplicarPorcentaje && costo_total > 0) {
            var porcentaje = parseFloat($('#porcentaje_costo').val()) || 0;
            var precioCalculado = costo_total * (1 + porcentaje / 100);
            $('#txt_propun').val(precioCalculado.toFixed(1));
        }
    }
</script>

{!! Form::model($productos, ['method'=>'PATCH', 'route'=>['productos.update', $productos->IdProducto], 'files'=>'true', 'id'=>'frmProducto']) !!}

<input type="hidden" id="id_producto_hidden" value="{{$productos->IdProducto}}">

<section class="content">

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>EDITAR PRODUCTO</strong></font></center>
                </div>
                <div class="box-body">

                    <div class="row">
                        <input hidden="hidden" name="sucursal" value="{{$sucursal}}">

                        <div class="col-lg-1 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="promocion">Tipo</label>
                                <select class="form-control" name="promocion" id="promocion">
                                    <option value="0" {{ $productos->promocion == '0' ? 'selected' : '' }}>Producto</option>
                                    <option value="2" {{ $productos->promocion == '2' ? 'selected' : '' }}>Preparado</option>
                                    <option value="4" {{ $productos->promocion == '4' ? 'selected' : '' }}>Insumo</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
                            <div class="form-group form-group-sm">
                                <label>¿Requiere Entrada?</label>
                                <div class="checkbox" style="margin-top: 5px;">
                                    <label>
                                        <input type="checkbox" name="tiene_entrada" value="1" {{ $productos->tiene_entrada == 1 ? 'checked' : '' }}> 
                                        <strong class="text-primary">SI</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-1 col-md-2 col-sm-3 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txt_procod">Código</label>
                                <input type="text" name="txt_procod" value="{{$productos->procod}}" class="form-control" placeholder="Código del producto...">
                            </div>
                        </div>

                        <!-- CÓDIGO SUNAT SELECT2 -->
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="cod_producto_sunat">Código SUNAT</label>
                                <select class="form-control" name="cod_producto_sunat" id="cod_producto_sunat" style="width: 100%;">
                                    <option value="">Buscar por código o descripción...</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden='hidden'>
                            <div class="form-group form-group-sm">
                                <label for="tipo_codigo">Tipo Codigo Barra</label>
                                <select class="form-control" name="tipo_codigo" id="tipo_codigo">
                                    @if($productos->tipo_codigo=='1')
                                        <option value="0"></option>
                                        <option selected="selected" value="1">EAN13</option>
                                    @else
                                        <option selected="selected" value="0"></option>
                                        <option value="1">EAN13</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-3 col-xs-12" hidden='hidden'>
                            <div class="form-group form-group-sm">
                                <label for="codigo_barra">Código de Barra</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><span class="fa fa-barcode"></span></button>
                                    </span>
                                    <input type="text" name="codigo_barra" value="{{$productos->codigo_barra}}" class="form-control" placeholder="Código de Barra...">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txt_pronom">Nombre Producto</label>
                                <input type="text" name="txt_pronom" value="{{$productos->pronom}}" class="form-control" placeholder="">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
                            <div class="form-group form-group-sm">
                                <label>¿Genera Puntos?</label>
                                <div class="checkbox" style="margin-top: 5px;">
                                    <label>
                                        <input type="checkbox" name="genera_puntos" value="1" {{ $productos->genera_puntos == 1 ? 'checked' : '' }}> 
                                        <strong class="text-primary">SI</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-1 col-md-4 col-sm-6 col-xs-12" hidden='hidden'>
                            <div class="form-group form-group-sm">
                                <label for="txt_pronom">T.M.</label>
                                <input type="text" name="tiempo_maximo" value="{{$productos->tiempo_maximo}}" class="form-control" placeholder="">
                            </div>
                        </div>

                        <div class="col-lg-1 col-md-2 col-sm-2 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txt_umecod">U.M.</label>
                                <select class="form-control" name="txt_umecod" id="txt_umecod">
                                    <option></option>
                                    @foreach($unidades as $uni)
                                    @if($uni->umecod==$productos->umecod)
                                    <option value="{{$uni->umecod}}" selected>{{$uni->umenom}}</option>
                                    @else
                                    <option value="{{$uni->umecod}}">{{$uni->umenom}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-1 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="factor_pro">Factor</label>
                                <input type="number" name="factor_pro" id="factor_pro" value="{{$productos->factor}}" min='1' class="form-control" placeholder="">
                            </div>
                        </div>

                        <div id="seccion_equivalencia">
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <div class="form-group form-group-sm">
                                    <label for="umecod_cons">U.M Equivalente</label>
                                    <select class="form-control" name="umecod_cons" id="umecod_cons">
                                        <option></option>
                                        @foreach($unidades as $uni)
                                            <option value="{{$uni->umecod}}" {{ $productos->umecod_cons == $uni->umecod ? 'selected' : '' }}>
                                                {{$uni->umenom}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <div class="form-group form-group-sm">
                                    <label for="factor_cons">Factor Equivalente</label>
                                    <input type="number" name="factor_cons" id="factor_cons" value="{{$productos->factor_cons}}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden='hidden'>
                            <div class="form-group form-group-sm">
                                <label for="stock_min">Stock Mínimo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><span class="fa fa-cart-arrow-down"></span></button>
                                    </span>
                                    <input type="number" name="stock_min" id="stock_min" value="{{$productos->stock_min}}" class="form-control" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="tip_pro_id">Línea</label>
                                <select class="form-control" name="tip_pro_id" id="tip_pro_id_form" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $tp)
                                        <option value="{{$tp->tip_pro_id}}" {{ (isset($productos->tip_pro_id) && $productos->tip_pro_id == $tp->tip_pro_id) ? 'selected' : '' }}>
                                            {{$tp->tip_pro_nom}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="cat_id">Familia</label>
                                <select class="form-control" name="cat_id" id="cat_id_form" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{$cat->cat_id}}" {{ (isset($productos->cat_id) && $productos->cat_id == $cat->cat_id) ? 'selected' : '' }}>
                                            {{$cat->cat_nom}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="subcat_id">Subfamilia</label>
                                <select class="form-control" name="subcat_id" id="subcat_id_form" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($subcategorias as $subcat)
                                        <option value="{{$subcat->subcat_id}}" {{ (isset($productos->subcat_id) && $productos->subcat_id == $subcat->subcat_id) ? 'selected' : '' }}>
                                            {{$subcat->subcat_nom}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="imagen">Imagen</label>
                                <input type="file" name="imagen" class="form-control">
                                @if($productos->imagenproducto)
                                    <img src="/imagenes/productos/{{$productos->imagenproducto}}" alt="Imagen actual" style="width: 50px; height: auto; margin-top: 5px;">
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <label for="txt_propun">Precio</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                </span>
                                <input type="number" name="txt_propun" id="txt_propun" value="{{$productos->precio}}" class="form-control" placeholder="">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-success" id="btn_precios_dinamicos"
                                        data-toggle="modal" data-target="#modalPreciosDinamicos"
                                        data-product-id="{{ $productos->IdProducto }}">
                                        <span class="glyphicon glyphicon-plus"></span>
                                    </button>
                                </span>
                            </div>           
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
                            <div class="form-group form-group-sm">
                                <label>
                                    <input type="checkbox" id="aplicar_porcentaje" name="aplicar_porcentaje" value="1">
                                    Aplicar % al Costo
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
                            <div class="form-group form-group-sm">
                                <label for="porcentaje_costo">Porcentaje (%)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><span class="fa fa-percent"></span></button>
                                    </span>
                                    <input type="number" id="porcentaje_costo" name="porcentaje_costo" value="20" min="0" step="0.01" class="form-control" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12" hidden='hidden'>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="requiere_lote_vencimiento" value="1" {{ old('requiere_lote_vencimiento', $productos->requiere_lote_vencimiento) ? 'checked' : '' }}>
                                    ¿Requiere Control por Lote/Vencimiento?
                                </label>
                                <p class="help-block">Marque esta opción si el producto debe gestionarse por lotes y fechas de vencimiento.</p>
                            </div>
                        </div>

                        <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="dias_garantia">D&iacute;as Garant&iacute;a</label>
                                <input type="number" step="any" name="dias_garantia" id="dias_garantia" value="{{$productos->dias_garantia}}" class="form-control" placeholder="">
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txt_moncod">Moneda</label>
                                <select class="form-control" name="txt_moncod" id="txt_moncod">
                                    <option></option>
                                    @foreach($monedas as $mon)
                                    @if($mon->moncod==$productos->moncod)
                                    <option value="{{$mon->moncod}}" selected>{{$mon->monnom}}</option>
                                    @else
                                    <option value="{{$mon->moncod}}">{{$mon->monnom}}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>Costos</strong></font></center>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-2 col-xs-2">
                            <div class="form-group form-group-sm">
                                <label for="txt_costofijo">Costo Fijo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                    </span>
                                    <input type="text" name="txt_costofijo" id="txt_costofijo" value="{{$productos->costofijo}}" class="form-control" placeholder="">                        
                                </div>                
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txt_costo">Costo</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                    </span>
                                    <input type="text" name="txt_costo" id="txt_costo" onkeyup="calcular_costo_total();" value="{{$productos->costo}}" class="form-control" placeholder="">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txt_peso">Peso (kg)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><span class="fa fa-weight"></span></button>
                                    </span>
                                    <input type="text" name="txt_peso" id="txt_peso" value="{{$productos->peso}}" onkeyup="calcular_costo_total();" class="form-control" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txt_flete">Flete</label>
                                <input type="text" name="txt_flete" id="txt_flete" value="{{$productos->flete}}" onkeyup="calcular_costo_total();" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="costo_total">Costo Total</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                    </span>
                                    <input type="text" name="costo_total" readonly="readonly" id="costo_total" value="{{$productos->costo_total}}" class="form-control" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>SUNAT</strong></font></center>
                </div>

                <div class="box-body">
                    <div class="row"> 
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="tigcod">IGV</label>
                                <select class="form-control" name="tigcod" id="tigcod">
                                    @foreach($tipoigv as $igv)
                                        @if($igv->tigcod == $productos->tigcod)
                                            <option selected="selected" value="{{$igv->tigcod}}">{{$igv->tigdes}}</option>
                                        @else
                                            <option value="{{$igv->tigcod}}">{{$igv->tigdes}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="tigcod">ICBPER</label>
                                <select class="form-control" name="icbper">
                                    @if($productos->icbper == '0')
                                        <option value="0" selected="selected">NO</option>
                                        <option value="1">SI</option>
                                    @elseif($productos->icbper =='1')
                                        <option value="0">NO</option>
                                        <option value="1" selected="selected">SI</option>
                                    @else
                                        <option value="0">NO</option>
                                        <option value="1">SI</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="tigcod">DEBE</label>
                                <input type="text" class="form-control" name="debe" value="{{$productos->debe}}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="tigcod">HABER</label>
                                <input type="text" class="form-control" name="haber" value="{{$productos->haber}}">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:none;" class="box-body">
                    <div class="row"> 
                        <div class="col-lg-12">
                            <table id="detFact" class="table">
                                <thead>
                                    <th><button type="button" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button> Código de Barras </th>
                                </thead>
                                <tbody>
                                    @if(!empty($codigos))
                                        @foreach($codigos as $cod)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="id[]" value="{{$cod->pro_cod_id}}">
                                                <input type="text" class="form-control input-sm" name="codigobarra[]" value="{{$cod->cod_bar}}">
                                            </td>
                                            <td>
                                                <button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>
                                                <input type="hidden" name="id[]" value="">
                                                <input type="text" class="form-control input-sm" name="codigobarra[]">
                                            </td>
                                            <td>
                                                <button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>Presentaciones</strong></font></center>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <table id="detpre" class="table">
                                <thead>
                                    <tr>
                                        <th style="width:10%;"><button type="button" name="addpre" id="addpre" class="btn btn-success btn-sm addpre"><span class="glyphicon glyphicon-plus"></span></button> BARRA</th>
                                        <th width="10%">PRESENTACION</th>
                                        <th>DESCRIPCION</th>
                                        <th width="10%">FACTOR</th>
                                        <th width="10%">PRECIO 1</th>
                                        <th width="10%">COSTO</th>
                                        <th style="width:15%;">IMAGEN</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($presentaciones as $pre)
                                    <tr>
                                        <td hidden="hidden"> <input type="hidden" name="idprod[]" value="{{$pre->IdProducto}}"></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default"><span class="fa fa-barcode"></span></button>
                                                </span>
                                                <input type="text" class="form-control" name="codigo_barra_pre[]" value="{{$pre->codigo_barra}}">
                                            </div>
                                        </td>
                                        <td width="10%">
                                            <select name="presentacion[]" class="form-control input-sm">
                                                @foreach($unidades as $unidad) 
                                                    @if($unidad->umecod == $pre->umecod)
                                                        <option selected="selected" value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> 
                                                    @else
                                                        <option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> 
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input name="descripcion[]" value="{{$pre->pronom}}" class="form-control input-sm">
                                        </td>
                                        <td width="10%">
                                            <input type="number" min="0" value="{{$pre->factor}}" step="any" name="factor[]" class="form-control input-sm">
                                        </td>
                                        <td width="10%">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                                </span>
                                                <input type="number" min="0" value="{{$pre->precio}}" step="any" name="precio[]" class="form-control input-sm">
                                            </div>
                                        </td>
                                        <td width="10%">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                                </span>
                                                <input type="text" class="form-control input-sm" value="{{$pre->costo}}" name="costo[]">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="file" name="imagen_pre_existente[{{$pre->IdProducto}}]" class="form-control input-sm">
                                            @if($pre->imagenproducto)
                                                <img src="/imagenes/productos/{{$pre->imagenproducto}}" alt="Imagen actual" style="width: 50px; height: auto; margin-top: 5px;">
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
                                <button id="btnRegComp" class="btn btn-primary botones" type="button">Actualizar</button>
                                <a href="/productos"><button class="btn btn-danger btn-close botones" type="button">Cancelar</button></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPreciosDinamicos" tabindex="-1" role="dialog" aria-labelledby="modalPreciosDinamicosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalPreciosDinamicosLabel">Gestionar Precios Dinámicos</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="contenido_modal_precios_dinamicos">
                        <center><img src="/img/load.gif" width="50px" height="50px"></center>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</section>

{!!Form::close()!!}
@endsection