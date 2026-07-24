@extends ('layouts.empresas')
@section ('contenido')

<style>
    /* Clase para lograr 5 columnas exactas en las series */
    .col-custom-5 {
        width: 20%;
        float: left;
        position: relative;
        min-height: 1px;
        padding-right: 10px;
        padding-left: 10px;
    }
    @media (max-width: 992px) { .col-custom-5 { width: 50%; } }
    @media (max-width: 768px) { .col-custom-5 { width: 100%; } }
    
    .box-series-new {
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 12px;
        margin-bottom: 10px;
        border-radius: 5px;
    }
</style>

<section class="content">
    {!!Form::open(array('url'=>'/negocios','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}

    <div class="row">
        <div class="col-md-12">
            <!-- CAJA PRINCIPAL: DATOS GENERALES -->
            <div class="box box-success shadow">
                <div class="box-header with-border" style="background: #27ae60; color: white;">
                    <h3 class="box-title"><i class="fa fa-plus-circle"></i> <strong>NUEVA SUCURSAL</strong></h3>
                </div>
                
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>CÓDIGO</label>
                                <input type="text" name="cod_suc" value="{{old('cod_suc')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>SUCURSAL</label>
                                <input type="text" name="txt_tipo_negocio" value="{{old('txt_tipo_negocio')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group form-group-sm">
                                <label>NOMBRE COMERCIAL</label>
                                <input type="text" name="txt_nombre_comercial" value="{{old('txt_nombre_comercial')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm text-green">
                                <label>CÓD. FISCAL SUNAT</label>
                                <input type="text" name="cod_fis_sun" value="{{old('cod_fis_sun')}}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group form-group-sm">
                                <label>DIRECCIÓN</label>
                                <input type="text" name="txt_direccion" value="{{old('txt_direccion')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>TELÉFONO</label>
                                <input type="text" name="txt_telefono" value="{{old('txt_telefono')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group form-group-sm">
                                <label>CORREO</label>
                                <input type="email" name="txt_correo" value="{{old('txt_correo')}}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-group-sm">
                                <label>WEB</label>
                                <input type="text" name="txt_web" value="{{old('txt_web')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label>Texto cabecera Comprobante:</label>
                                <textarea class="form-control" name="descripcion1" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label>Texto cabecera Adicional Comprobante:</label>
                                <textarea class="form-control" name="descripcion2" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: SERIES (5 POR LÍNEA) -->
                <div class="box-header with-border" style="background: #34495e; color: white;">
                    <h3 class="box-title"><strong>SERIES DE COMPROBANTES</strong></h3>
                </div>
                <div class="box-body bg-gray-light">
                    <!-- Primera Línea de Series -->
                    <div class="row">
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-blue">FACTURA</label>
                                <input type="text" name="FseEmpresa" value="{{old('FseEmpresa')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="FnuEmpresa" value="{{old('FnuEmpresa')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-blue">BOLETA</label>
                                <input type="text" name="BseEmpresa" value="{{old('BseEmpresa')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="BnuEmpresa" value="{{old('BnuEmpresa')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-blue">NOTA VENTA</label>
                                <input type="text" name="sernota" value="{{old('sernota')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="numnota" value="{{old('numnota')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-blue">PROFORMA</label>
                                <input type="text" name="ProSer" value="" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="ProNum" value="" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-red">N. CRÉDITO FACT.</label>
                                <input type="text" name="FcseEmpresa" value="{{old('FseEmpresa')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="FcnuEmpresa" value="{{old('FcnuEmpresa')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                    </div>
                    <!-- Segunda Línea de Series -->
                    <div class="row">
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-red">N. DÉBITO FACT.</label>
                                <input type="text" name="FdseEmpresa" value="{{old('FdseEmpresa')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="FdnuEmpresa" value="{{old('FdnuEmpresa')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-red">N. CRÉDITO BOL.</label>
                                <input type="text" name="BcseEmpresa" value="{{old('BcseEmpresa')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="BcnuEmpresa" value="{{old('BcnuEmpresa')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-red">N. DÉBITO BOL.</label>
                                <input type="text" name="BdseEmpresa" value="{{old('BdseEmpresa')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="BdnuEmpresa" value="{{old('BdnuEmpresa')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-green">PEDIDO</label>
                                <input type="text" name="SerPed" value="{{old('SerPed')}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="NumPed" value="{{old('NumPed')}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series-new">
                                <label class="text-green">GUÍA</label>
                                <input type="text" name="serieguia" value="" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="numeroguia" value="" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN: CONFIGURACIÓN -->
                <div class="box-header with-border" style="background: #7f8c8d; color: white;">
                    <h3 class="box-title"><strong>CONFIGURACIÓN ADICIONAL</strong></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>TIPO IGV - DEFECTO</label>
                                <select class="form-control" name="tipo_igv">
                                    @foreach($tipo_igv as $igv)
                                        <option value="{{$igv->tigcod}}">{{$igv->tigdes}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>COMISIÓN VENDEDOR (%)</label>
                                <input type="number" name="comision" value="0" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>VENDER SIN STOCK</label>
                                <select class="form-control" name="ven_sin_sto">
                                    <option value="0">NO</option>
                                    <option value="1">SI</option>
                                </select>
                            </div>
                        </div> 
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>FORMATO COMPROBANTE</label>
                                <select class="form-control" name="cod_for_com">
                                    @foreach($formatos as $for)
                                        <option value="{{$for->cod_for_com}}">{{$for->descripcion}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label>LOGO SUCURSAL</label>
                            <input type="file" name="logosuc" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>FORMATO PAPEL</label>
                            <select name="formato" class="form-control">
                                <option value="TICKET">TICKET</option>
                                <option value="A4">A4</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>TICKET-PANTALLA</label>
                            <select name="ticket_pantalla" class="form-control">
                                <option value="1">SI</option>
                                <option value="0">NO</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>CONEXIÓN IMPRESORA</label>
                            <select name="tip_conex_imp" class="form-control">
                                <option value="COMPARTIDO">COMPARTIDO</option>
                                <option value="RED">RED</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>DOC. PREDETERMINADO</label>
                            <select name="tdocod_pred" class="form-control">
                                @foreach($documentos as $doc)
                                    <option value="{{$doc->tdocod}}">{{$doc->tdodes}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- LINEAS AGREGADAS: OPCIONES DE COMANDA/CUENTA -->
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-3">
                            <label>PEDIR PRECUENTA</label>
                            <select class="form-control" name="boton_precuenta">
                                <option value="0">NO</option>
                                <option value="1">SI</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>COBRAR EN COMANDA</label>
                            <select class="form-control" name="boton_cobrar">
                                <option value="0">NO</option>
                                <option value="1">SI</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>DESCUENTO 50%</label>
                            <select class="form-control" name="boton_descuento">
                                <option value="0">NO</option>
                                <option value="1">SI</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>RECARGO POR CONSUMO</label>
                            <select class="form-control" name="boton_recargo">
                                <option value="0">NO</option>
                                <option value="1">SI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <div class="pull-right">
                        <a href="/negocios" class="btn btn-default">Cancelar</a>
                        <button class="btn btn-primary shadow" type="submit"><i class="fa fa-save"></i> Guardar Nueva Sucursal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!!Form::close()!!}      
</section>

@endsection