@extends ('layouts.empresas')
@section ('contenido')

<style>
    /* Clase personalizada para lograr 5 columnas exactas */
    .col-custom-5 {
        width: 20%;
        float: left;
        position: relative;
        min-height: 1px;
        padding-right: 10px;
        padding-left: 10px;
    }
    @media (max-width: 992px) {
        .col-custom-5 { width: 50%; } /* En tablets se ven 2 por línea */
    }
    @media (max-width: 768px) {
        .col-custom-5 { width: 100%; } /* En móviles 1 por línea */
    }
    .box-series {
        background: #fdfdfd;
        border: 1px solid #e1e1e1;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
    }
</style>

<section class="content">
    {!!Form::model($negocios,['method'=>'PATCH','route'=>['negocios.update',$negocios->id_empresa_negocio],'files'=>'true'])!!}
    {{Form::token()}}
    
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary shadow">
                <div class="box-header with-border" style="background: #2c3e50; color: white;">
                    <h3 class="box-title"><i class="fa fa-edit"></i> <strong>EDITAR SUCURSAL</strong></h3>
                </div>
                
                <div class="box-body">
                    <!-- DATOS GENERALES -->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>CÓDIGO</label>
                                <input type="text" name="cod_suc" value="{{$negocios->cod_suc}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>SUCURSAL</label>
                                <input type="text" name="txt_tipo_negocio" value="{{$negocios->tipo_negocio}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group form-group-sm">
                                <label>NOMBRE COMERCIAL</label>
                                <input type="text" name="txt_nombre_comercial" value="{{$negocios->nombre_comercial}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label class="text-primary">CÓD. FISCAL SUNAT</label>
                                <input type="text" name="cod_fis_sun" value="{{$negocios->codigofiscal}}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group form-group-sm">
                                <label>DIRECCIÓN</label>
                                <input type="text" name="txt_direccion" value="{{$negocios->direccion}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>TELÉFONO</label>
                                <input type="text" name="txt_telefono" value="{{$negocios->telefono}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group form-group-sm">
                                <label>CORREO</label>
                                <input type="text" name="txt_correo" value="{{$negocios->correo}}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-group-sm">
                                <label>WEB</label>
                                <input type="text" name="txt_web" value="{{$negocios->web}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label>Texto cabecera Comprobante:</label>
                                <textarea class="form-control" name="descripcion1" rows="2">{{$negocios->descripcion1}}</textarea> 
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label>Texto cabecera Adicional Comprobante:</label>
                                <textarea class="form-control" name="descripcion2" rows="2">{{$negocios->descripcion2}}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN SERIES (5 POR LÍNEA) -->
                    <div class="box-header with-border" style="background: #34495e; color: white; margin: 20px -10px 15px -10px;">
                        <h3 class="box-title"><strong>SERIES DE COMPROBANTES</strong></h3>
                    </div>

                    <div class="row">
                        <!-- LINEA 1: Factura, Boleta, Nota Venta, Proforma, N. Crédito Fact -->
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-blue">FACTURA</label>
                                <input type="text" name="FseEmpresa" value="@if($negocios->FseEmpresa==''){{old('FseEmpresa')}}@elseif(old('FseEmpresa')!=''){{old('FseEmpresa')}}@else{{$negocios->FseEmpresa}}@endif" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="FnuEmpresa" value="@if(old('FnuEmpresa')!=''){{old('FnuEmpresa')}}@else{{$negocios->FnuEmpresa}}@endif" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-blue">BOLETA</label>
                                <input type="text" name="BseEmpresa" value="@if($negocios->BseEmpresa==''){{old('BseEmpresa')}}@elseif(old('BseEmpresa')!=''){{old('BseEmpresa')}}@else{{$negocios->BseEmpresa}}@endif" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="BnuEmpresa" value="@if(old('BnuEmpresa')!=''){{old('BnuEmpresa')}}@else{{$negocios->BnuEmpresa}}@endif" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-blue">NOTA VENTA</label>
                                <input type="text" name="sernota" value="@if($negocios->SerNota==''){{old('SerNota')}}@elseif(old('SerNota')!=''){{old('SerNota')}}@else{{$negocios->SerNota}}@endif" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="numnota" value="{{$negocios->NumNota}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-blue">PROFORMA</label>
                                <input type="text" name="ProSer" value="{{$negocios->ProSer}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="ProNum" value="{{$negocios->ProNum}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-red">N. CRÉDITO FACT.</label>
                                <input type="text" name="FcseEmpresa" value="@if($negocios->FcseEmpresa==''){{old('FcseEmpresa')}}@elseif(old('FcseEmpresa')!=''){{old('FcseEmpresa')}}@else{{$negocios->FcseEmpresa}}@endif" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="FcnuEmpresa" value="@if(old('fcnuempresa')!=''){{old('fcnuempresa')}}@else{{$negocios->FcnuEmpresa}}@endif" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- LINEA 2: N. Débito Fact, N. Crédito Bol, N. Débito Bol, Pedido, Guía -->
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-red">N. DÉBITO FACT.</label>
                                <input type="text" name="FdseEmpresa" value="@if($negocios->FdseEmpresa==''){{old('FdseEmpresa')}}@elseif(old('FdseEmpresa')!=''){{old('FdseEmpresa')}}@else{{$negocios->FdseEmpresa}}@endif" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="FdnuEmpresa" value="@if(old('FdnuEmpresa')!=''){{old('FdnuEmpresa')}}@else{{$negocios->FdnuEmpresa}}@endif" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-red">N. CRÉDITO BOL.</label>
                                <input type="text" name="BcseEmpresa" value="@if($negocios->BcseEmpresa==''){{old('BcseEmpresa')}}@elseif(old('BcseEmpresa')!=''){{old('BcseEmpresa')}}@else{{$negocios->BcseEmpresa}}@endif" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="BcnuEmpresa" value="@if(old('BcnuEmpresa')!=''){{old('BcnuEmpresa')}}@else{{$negocios->BcnuEmpresa}}@endif" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-red">N. DÉBITO BOL.</label>
                                <input type="text" name="BdseEmpresa" value="@if($negocios->BdseEmpresa==''){{old('BdseEmpresa')}}@elseif(old('BdseEmpresa')!=''){{old('BdseEmpresa')}}@else{{$negocios->BdseEmpresa}}@endif" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="BdnuEmpresa" value="@if(old('BdnuEmpresa')!=''){{old('BdnuEmpresa')}}@else{{$negocios->BdnuEmpresa}}@endif" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-green">PEDIDO</label>
                                <input type="text" name="SerPed" value="{{$negocios->serieNP}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="NumPed" value="{{$negocios->numNP}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-custom-5">
                            <div class="box-series text-center">
                                <label class="text-green">GUÍA</label>
                                <input type="text" name="serieguia" value="{{$negocios->serieguia}}" class="form-control input-sm mb-1" placeholder="Serie">
                                <input type="text" name="numeroguia" value="{{$negocios->numeroguia}}" class="form-control input-sm" placeholder="Número">
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN CONFIGURACIÓN -->
                    <div class="box-header with-border" style="background: #7f8c8d; color: white; margin: 20px -10px 15px -10px;">
                        <h3 class="box-title"><strong>CONFIGURACIÓN</strong></h3>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>TIPO IGV - DEFECTO</label>
                                <select class="form-control" name="tipo_igv">
                                    @foreach($tipo_igv as $igv)
                                        <option value="{{$igv->tigcod}}" {{ $igv->tigcod == $negocios->tip_igv_pred ? 'selected' : '' }}>{{$igv->tigdes}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>COMISIÓN VENDEDOR (%)</label>
                                <input type="number" name="comision" value="{{$negocios->comision}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>VENDER SIN STOCK</label>
                                <select class="form-control" name="ven_sin_sto">
                                    <option value="0" {{ $negocios->ven_sin_sto == '0' ? 'selected' : '' }}>NO</option>
                                    <option value="1" {{ $negocios->ven_sin_sto == '1' ? 'selected' : '' }}>SI</option>
                                </select>
                            </div>
                        </div> 
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>FORMATO COMPROBANTE</label>
                                <select class="form-control" name="cod_for_com">
                                    @foreach($formatos as $for)
                                        <option value="{{$for->cod_for_com}}" {{ $for->cod_for_com == $negocios->cod_for_com ? 'selected' : '' }}>{{$for->descripcion}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                    </div>

                    <div class="row">
                        <div class="col-md-1">
                            <label>SELVA</label>
                            <select class="form-control" name="serv_selv">
                                <option value="0" {{ $negocios->serv_selv == '0' ? 'selected' : '' }}>NO</option>
                                <option value="1" {{ $negocios->serv_selv == '1' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div> 
                        <div class="col-md-3">
                            <label>LOGO</label>
                            <input type="file" name="logosuc" class="form-control input-sm">
                        </div>
                        <div class="col-md-2">
                            <label>FORMATO PAPELES</label>
                            <select name="formato" class="form-control">
                                <option value="TICKET" {{ $negocios->formato == 'TICKET' ? 'selected' : '' }}>TICKET</option>
                                <option value="A4" {{ $negocios->formato == 'A4' ? 'selected' : '' }}>A4</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>PANTALLA</label>
                            <select name="ticket_pantalla" class="form-control">
                                <option value="1" {{ $negocios->ticket_pantalla == '1' ? 'selected' : '' }}>SI</option>
                                <option value="0" {{ $negocios->ticket_pantalla == '0' ? 'selected' : '' }}>NO</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>IMPRESORA</label>
                            <select name="tip_conex_imp" class="form-control">
                                <option value="COMPARTIDO" {{ $negocios->tip_conex_imp == 'COMPARTIDO' ? 'selected' : '' }}>COMPARTIDO</option>
                                <option value="RED" {{ $negocios->tip_conex_imp == 'RED' ? 'selected' : '' }}>RED</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>PREDETERMINADO</label>
                            <select name="tdocod_pred" class="form-control">
                                @foreach($documentos as $doc)
                                    <option value="{{$doc->tdocod}}" {{ $doc->tdocod == $negocios->tdocod_pred ? 'selected' : '' }}>{{$doc->tdodes}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-2">
                            <label>PEDIR PRECUENTA</label>
                            <select class="form-control" name="boton_precuenta">
                                <option value="0" {{ $negocios->boton_precuenta == '0' ? 'selected' : '' }}>NO</option>
                                <option value="1" {{ $negocios->boton_precuenta == '1' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>COBRAR EN COMANDA</label>
                            <select class="form-control" name="boton_cobrar">
                                <option value="0" {{ $negocios->boton_cobrar == '0' ? 'selected' : '' }}>NO</option>
                                <option value="1" {{ $negocios->boton_cobrar == '1' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>DESCUENTO 50%</label>
                            <select class="form-control" name="boton_descuento">
                                <option value="0" {{ $negocios->boton_descuento == '0' ? 'selected' : '' }}>NO</option>
                                <option value="1" {{ $negocios->boton_descuento == '1' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>RECARGO POR CONSUMO</label>
                            <select class="form-control" name="boton_recargo">
                                <option value="0" {{ $negocios->boton_recargo == '0' ? 'selected' : '' }}>NO</option>
                                <option value="1" {{ $negocios->boton_recargo == '1' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>IMAGEN EN PRODUCTO</label>
                            <select class="form-control" name="boton_imagenes">
                                <option value="0" {{ $negocios->boton_imagenes == '0' ? 'selected' : '' }}>NO</option>
                                <option value="1" {{ $negocios->boton_imagenes == '1' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <div class="pull-right">
                        <a href="/negocios" class="btn btn-default">Cancelar</a>
                        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!!Form::close()!!}
</section>

@endsection