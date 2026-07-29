@extends ('layouts.empresas')
@section ('contenido')
<script>
    // PARA QUE PIDA CUENTA CONTABLE OBLIGATORIA
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

        // 1. Inicializar visibilidad de Insumo
        gestionarCamposInsumo();

        // 2. Select2 para Código SUNAT
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

        // 3. Detección Inteligente al escribir el Nombre del Producto
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
                            // Se agrega el Option SIN .trigger('change') para evitar movimientos bruscos de la vista
                            var option = new Option(sugerencia.text, sugerencia.id, true, true);
                            $('#cod_producto_sunat').append(option);
                        }
                    }
                });
            }
        });

        // 4. Cambios en Tipo de Producto (Insumo)
        $("#promocion").change(function() {
            gestionarCamposInsumo();
        });

        // Evitar submit con tecla Enter
        $("form").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        });

        // 5. Cambio en LÍNEA -> Carga FAMILIAS
        $("#tip_pro_id_form").change(function() {
            var tip_pro_id = $(this).val();
            if(tip_pro_id !== "") {
                $("#cat_id_form").html('<option value="">Cargando...</option>');
                $("#subcat_id_form").html('<option value="">Seleccione primero la Familia...</option>');

                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "/buscarfamilias/" + tip_pro_id,
                }).done(function(respuesta) {
                    $("#cat_id_form").html(respuesta.vista);

                    // Selección automática de la primera familia
                    var primerFamilia = $("#cat_id_form option:nth-child(2)").val(); 
                    if(primerFamilia) {
                        $("#cat_id_form").val(primerFamilia).trigger('change');
                    }
                });
            }
        });

        // 6. Cambio en FAMILIA -> Carga SUBFAMILIAS
        $("#cat_id_form").change(function() {
            var cat_id = $(this).val();
            if(cat_id !== "" && cat_id !== null) {
                $("#subcat_id_form").html('<option value="">Cargando...</option>');

                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "/buscarsubcategorias/" + cat_id,
                }).done(function(respuesta) {
                    $("#subcat_id_form").html(respuesta.vista);

                    // Selección automática de la primera subfamilia
                    var primeraSubfamilia = $("#subcat_id_form option:nth-child(2)").val();
                    if(primeraSubfamilia) {
                        $("#subcat_id_form").val(primeraSubfamilia);
                    }
                });
            }
        });

        // Cargar primera línea por defecto si existe
        var primerLinea = $("#tip_pro_id_form option:nth-child(2)").val();
        if(primerLinea && !$("#tip_pro_id_form").val()) {
            $("#tip_pro_id_form").val(primerLinea).trigger('change');
        }

        // 7. Cálculos de Costos y Porcentajes
        $("#aplicar_porcentaje").change(function() {
            calcular_costo_total();
        });

        $("#porcentaje_costo").on('keyup change', function() {
            calcular_costo_total();
        });

        $("#txt_provun").on('keyup', function(){
            var numdoc = $('#txt_provun').val();
            $("#txt_propun").val((numdoc*1.18).toFixed(3));
        });

        $("#txt_propun").on('keyup', function(){
            var numdoc = $('#txt_propun').val();
            $("#txt_provun").val((numdoc/1.1055).toFixed(3));
        });

        // 8. Botones dinámicos para tablas
        $('#add').click(function() {
            $('#detfact').append('<tr><td><input type="text" class="form-control input-sm" name="codigobarra[]"></td><td width="10%"><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        });

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
                +'<td width="10%"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" min="0" value="0" step="any" class="form-control input-sm" name="precio2[]"></div></td>'
                +'<td width="10%"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" step="any" value="0" class="form-control input-sm" name="costo[]"></div></td>'
                +'<td><input type="file" name="imagen_pre[]" class="form-control input-sm"></td>'
                +'<td><button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        });

        // 9. Guardar Registro mediante AJAX
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
                var unidadEquivalente = $("#umecod_cons").val();
                var factorEquivalente = $("#factor_cons").val();

                if (unidadEquivalente === "") {
                    alert("Hermano, cuando seleccionas INSUMO, la U.M Equivalente es obligatoria.");
                    $("#umecod_cons").focus();
                    return false;
                }

                if (factorEquivalente === "" || parseFloat(factorEquivalente) <= 0) {
                    alert("Hermano, cuando seleccionas INSUMO, el Factor Equivalente debe ser mayor a 0.");
                    $("#factor_cons").focus();
                    return false;
                }
            }

            if (validarContabilidad) {
                var cuentaDebe = $("input[name='debe']").val().trim();
                var cuentaHaber = $("input[name='haber']").val().trim();
                if (cuentaDebe === "" || cuentaHaber === "") {
                    alert("Las cuentas DEBE y HABER son obligatorias para este cliente.");
                    return false;
                }
            }

            var formData = new FormData($('#frmProducto')[0]); 

            $("#imgload").show();
            $(".botones").hide();

            $.ajax({
                type: "POST",
                url: '/productos', 
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
                    alert('Ocurrió un error al guardar el producto. Por favor, inténtalo de nuevo.');
                    console.log(xhr.responseText); 
                    $("#imgload").hide();
                    $(".botones").show();
                }
            });
        });

    });

    // Funciones auxiliares fuera de document.ready
    function deleteRow(btn) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
        if ($('#detpre >tbody >tr').length == 0){
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
        var costo = parseFloat($('#txt_costo').val()) || 0;
        var peso = parseFloat($('#txt_peso').val()) || 0;
        var flete = parseFloat($('#txt_flete').val()) || 0;
        
        var costo_total = costo + (peso * flete);
        $("#costo_total").val(costo_total.toFixed(4));

        var aplicarPorcentaje = $('#aplicar_porcentaje').is(':checked');
        if(aplicarPorcentaje && costo_total > 0) {
            var porcentaje = parseFloat($('#porcentaje_costo').val()) || 0;
            var precioCalculado = costo_total * (1 + porcentaje / 100);
            $('#txt_propun').val(precioCalculado.toFixed(1));
        }
    }
</script>

<section class="content">
    {!!Form::open(array('url'=>'productos','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmProducto'))!!}
    {{Form::token()}}   
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>REGISTRAR PRODUCTO</strong></font></center>
                </div>
                <div class="box-body">


                    <div class="row">
                        <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="promocion">Tipo</label>
                                <select class="form-control" name="promocion" id="promocion">
                                    <option value="0" {{ isset($origen) && $origen == 'insumo' ? '' : 'selected' }}>Producto</option>
                                    <option value="2">Preparado</option>
                                    
                                    <option value="4" {{ isset($origen) && $origen == 'insumo' ? 'selected' : '' }}>Insumo</option>
                                </select>

                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
                            <div class="form-group form-group-sm">
                                <label>¿Requiere Entrada?</label>
                                <div class="checkbox" style="margin-top: 5px;">
                                    <label>
                                        <input type="checkbox" name="tiene_entrada" value="1"> 
                                        <strong class="text-primary">SI</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div hidden="hidden" class="col-lg-2">
                           <div class="form-group form-group-sm">
                               <label>Programas</label>
                               <select class="form-control" name="prog_id">
                                <option></option>
                                @foreach($programas as $prog)
                                <option value="{{$prog->prog_id}}">{{$prog->prog_nom}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div hidden="hidden" class="col-lg-2">
                       <div class="form-group form-group-sm">
                           <label>Servicio</label>
                           <select class="form-control" name="tip_pre">
                            <option></option>
                            @foreach($servicios as $ser)
                            <option value="{{$ser->ser_cod}}">{{$ser->ser_nom}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-1 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="txt_procod">Código</label>
                        <input type="text" name="txt_procod" value="{{old('txt_procod')}}" class="form-control" placeholder="">

                    </div>
                </div>
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
                        <select class="form-control"  name="tipo_codigo" id="tipo_codigo">
                            <option value="0"></option>
                            <option value="1">EAN13</option>

                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden='hidden'>
                   <label for="tipo_codigo">Codigo Barra</label>
                   <div class="input-group input-group-sm">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default"><span class="fa fa-barcode"></span></button>
                    </span>
                    <input type="text" name="codigo_barra" value="{{old('codigo_barra')}}" class="form-control" placeholder="">
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_pronom">Nombre de Producto</label>
                    <input type="text" name="txt_pronom" value="{{old('txt_pronom')}}" class="form-control" placeholder="">

                </div>
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
                            <div class="form-group form-group-sm">
                                <label>Genera Puntos?</label>
                                <div class="checkbox" style="margin-top: 5px;">
                                    <label>
                                        <input type="checkbox" name="genera_puntos" value="1"> 
                                        <strong class="text-primary">SI</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

            <div class="col-lg-1 col-md-4 col-sm-12 col-xs-12" hidden='hidden'>
                <div class="form-group form-group-sm">
                    <label for="txt_pronom">T.M.</label>
                    <input type="text" name="tiempo_maximo" value="{{old('tiempo_maximo')}}" class="form-control" placeholder="">

                </div>
            </div>
            
            <div class="col-lg-1 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_umecod">U.M</label>
                    <select class="form-control"  name="txt_umecod" id="txt_umecod">
                        <option></option>
                        @foreach($unidades as $uni)
                        @if($uni->umecod =='NIU')
                        <option value="{{$uni->umecod}}" selected="selected">{{$uni->umenom}}</option>
                        @else
                        <option value="{{$uni->umecod}}">{{$uni->umenom}}</option>
                        @endif
                        
                        @endforeach
                    </select>
                </div>
            </div>
            <div  class="col-lg-1 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="factor_pro">Factor</label>
                    <input type="number" name="factor_pro" id="factor_pro" value="1" min='1'  class="form-control" placeholder="">
                </div>
            </div>

              <div id="seccion_equivalencia">
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="umecod_cons">U.M Equivalente</label>
                        <select class="form-control" name="umecod_cons" id="umecod_cons">
                            <option></option>
                            @foreach($unidades as $uni)
                                <option value="{{$uni->umecod}}">{{$uni->umenom}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="factor_cons">Factor Equivalente</label>
                        <input type="number" name="factor_cons" id="factor_cons" min='1' class="form-control">
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
                        <input type="number" name="stock_min" id="stock_min" value="0"  class="form-control" placeholder="">
                    </div>
                </div>
            </div>

            <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="dias_garantia">D&iacute;as Garant&iacute;a</label>
                    <input type="number" step="any" name="dias_garantia" id="dias_garantia" value="0"  class="form-control" placeholder="">
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="tip_pro_id">Línea</label>
                    <select class="form-control" name="tip_pro_id" id="tip_pro_id_form" required>
                        <option value="">Seleccione Línea...</option>
                        @foreach($tipos as $tp)
                            <option value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="cat_id">Familia</label>
                    <select class="form-control" name="cat_id" id="cat_id_form" required>
                        <option value="">Seleccione primero la Línea...</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="subcat_id">Subfamilia</label>
                    <select class="form-control" name="subcat_id" id="subcat_id_form" required>
                        <option value="">Seleccione primero la Familia...</option>
                    </select>
                </div>
            </div>
                

            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-4">
                <div class="form-group form-group-sm">
                    <label for="imagen">Imagen</label>
                    <input type="file" name="imagen" class="form-control">
                </div>
            </div>

            <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <label for="txt_propun">Precio</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                        <input type="number" name="txt_propun" id="txt_propun" value="0"  class="form-control" placeholder="">
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

            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-4" hidden='hidden'>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="requiere_lote_vencimiento" value="1" {{ old('requiere_lote_vencimiento') ? 'checked' : '' }}>
                                ¿Requiere Control por Lote/Vencimiento?
                            </label>
                            <p class="help-block">Marque esta opción si el producto debe gestionarse por lotes y fechas de vencimiento.</p>
                          
                        </div>
                    </div>

        </div>
    </div>


    <div class="row" hidden="hidden">


    <div hidden="hidden"  class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
        <div class="form-group form-group-sm">
            <label for="txt_moncod">Moneda</label>
            <select class="form-control"  name="txt_moncod" id="txt_moncod">
                <option></option>
                @foreach($monedas as $mon)
                @if($mon->moncod =='PEN')
                <option value="{{$mon->moncod}}" selected="selected">{{$mon->monnom}}</option>
                @else
                <option value="{{$mon->moncod}}">{{$mon->monnom}}</option>
                @endif
                @endforeach
            </select>

        </div>
    </div>

</div>



<div style="display:none;" class="box-header with-border" style="background-color:blue;">
    <center><font color="white"><strong>Precios de Venta</strong></font></center>
</div>

<div class="box-body">


    <div class="row"> 

       
    

                <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden='hidden'>
                    <label for="txt_propun">P.D.</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-success" id="btn_precios_dinamicos" data-toggle="modal" data-target="#modalPreciosDinamicos">
                        <span class="glyphicon glyphicon-plus"></span>
                        </button>
                        </span>
                        
                    </div>
                </div>

    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
        <div class="form-group form-group-sm">
            <label for="comision">Comisión (%)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default"><span class="fa fa-percent"></span></button>
                </span>
                <input type="number" name="comision" id="comision" value="0"  class="form-control" placeholder="">
            </div>
        </div>
    </div>

</div>

</div>

<div class="box-header with-border" style="background-color:blue;">
    <center><font color="white"><strong>Costos</strong></font></center>
</div>

<div class="box-body">

    <div class="row">

        <div  class="col-lg-3 col-md-3 col-sm-2 col-xs-2">
            <div class="form-group form-group-sm">
                <label for="txt_costofijo">Costo Fijo</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                    </span>
                    <input type="text" name="txt_costofijo" id="txt_costofijo" value="0"   class="form-control" placeholder="">
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
                <input type="text" name="txt_costo" id="txt_costo" value="0" onkeyup="calcular_costo_total();"  class="form-control" placeholder="">
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
                <input type="text" name="txt_peso" id="txt_peso" value="0" onkeyup="calcular_costo_total();"   class="form-control" placeholder="">
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="txt_flete">Flete</label>
            <input type="text" name="txt_flete" id="txt_flete" value="0" onkeyup="calcular_costo_total();"   class="form-control" placeholder="">

        </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="costo_total">Costo Total</label>
            <div class="input-group input-group-sm">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                </span>
                <input type="text" name="costo_total" readonly="readonly" id="costo_total" value="0"  class="form-control" placeholder="">
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
            <select class="form-control"  name="tigcod" id="tigcod">

                @foreach($tipoigv as $igv)
                @if($sucursal->tip_igv_pred == $igv->tigcod)
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
            <select class="form-control" name=icbper>
                <option selected="selected" value="0">NO</option>
                <option  value="1">SI</option>
            </select>
        </div>
    </div>

    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label>DEBE</label>
                            <input type="text" class="form-control" name="debe">
                        </div>
                    </div>
                     <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label>HABER</label>
                             <input type="text" class="form-control" name="haber">
                        </div>
                    </div>

</div>

</div>




<div style="display:none;" class="box-header with-border" style="background-color:blue;">
    <center><font color="white"><strong>Códigos de Barra</strong></font></center>
</div>
<div style="display:none;" class="box-body">
    <div class="row">
        <div class="col-lg-12">
            <table id="detfact" class="table">
                <thead>
                    <th><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button> Código de Barras </th>
                </thead>
                <tbody id="">
                    <tr>
                        <td>
                            <input type="text" name="codigobarra[]" class="form-control input-sm" >
                        </td>
                        <td width="10%">
                            <button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                        </td>
                    </tr>
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
                        <th style="width:10%;"><button type="button" onClick="" name="addpre" id="addpre" class="btn btn-success btn-sm addpre"><span class="glyphicon glyphicon-plus"></span></button> BARRA</th>
                        <th width="10%">PRESENTACION</th>
                        <th>DESCRIPCION</th>
                        <th width="10%">FACTOR</th>
                        <th width="10%">PRECIO 1</th>
                        <!--<th width="10%">PRECIO 2</th>-->
                        <th width="10%">COSTO</th>
                        <th style="width:15%;">IMAGEN</th> {{-- **CAMBIO: Añadido TH para la columna de imagen** --}}
                        <th></th> {{-- **CAMBIO: TH para el botón de eliminar** --}}
                    </tr>
                </thead>

                <tbody id="">
                    <tr>
                        <td hidden="hidden"><input type="hidden" value="0" name="idprod[]"></td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default"><span class="fa fa-barcode"></span></button>
                                </span> 
                                <input type="text" class="form-control input-sm" name="codigo_barra_pre[]" value="">
                            </div>
                        </td>
                        <td width="10%">
                            <select name="presentacion[]" class="form-control input-sm">
                                @foreach($unidades as $unidad) 
                                    <option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> 
                                @endforeach
                            </select>
                        </td>
                        <td><input name="descripcion[]" class="form-control input-sm"></td>
                        <td width="10%"><input type="number" min="0" value="0" step="any" name="factor[]" class="form-control input-sm"></td>
                        <td width="10%"> 
                            <div class="input-group input-group-sm">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                </span>
                                <input type="number" min="0" value="0" step="any" name="precio[]" class="form-control input-sm">
                            </div>
                        </td>
                        
                        <td width="10%">
                            <div class="input-group input-group-sm">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                                </span>
                                <input type="number" step="any" value="0" class="form-control input-sm" name="costo[]">
                            </div>
                        </td>
                        <td><input type="file" name="imagen_pre[]" class="form-control input-sm"></td> {{-- **CAMBIO: Input para la imagen de la presentación** --}}
                        <td><button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

                <div class="box-body">
                  <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
                     <div class="form-group form-group-sm">
                         <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
                         <button id="btnRegComp" class="btn btn-primary botones" type="button">Guardar</button>
                         <a href="/productos"><button class="btn btn-danger btn-close botones" type="button">Cancelar</button></a>
                     </div>
                 </div>
             </div>


         </div>




     </div>



 </div>


</div>



<div class="modal fade" id="modalPreciosDinamicos" tabindex="-1" role="dialog" aria-labelledby="modalPreciosDinamicosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> {{-- modal-lg para que sea grande --}}
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalPreciosDinamicosLabel">Gestionar Precios Dinámicos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- El contenido de este modal se cargará dinámicamente con AJAX --}}
                <div id="contenido_modal_precios_dinamicos">
                    <center><img src="/img/load.gif" width="50px" height="50px"></center>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                {{-- El botón de guardar para las reglas dinámicas estará dentro del contenido cargado dinámicamente --}}
            </div>
        </div>
    </div>
</div>



{!!Form::close()!!} 
</section>

@endsection