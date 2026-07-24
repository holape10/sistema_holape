@extends('layouts.empresas')

@section('contenido')
<style>
    .table thead th {
        background-color: #2c3e50 !important;
        color: white !important;
        text-align: center;
        vertical-align: middle !important;
        text-transform: uppercase;
        font-size: 11px;
    }
    .btn-action {
        transition: transform 0.2s;
        margin: 2px;
    }
    .btn-action:hover {
        transform: scale(1.15);
    }
    .label-sunat {
        font-size: 10px !important;
        padding: 5px 8px !important;
        display: block;
        width: 100%;
        text-align: center;
    }
</style>

<script>
	var href = $('#btnPrint').attr('href');
	$("#btnPrint").printPage({
		  url: href,
		  attr: "href",
		  messageBox:false,
	});
</script>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @if(session()->has('info'))
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-warning"></i> Atencion!</h4>
                    {{ session('info') }}
                </div>
            @endif

            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-check"></i> ¡Logrado!</h4>
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary shadow">
                <div class="box-header with-border text-center" style="background: #3c8dbc; color: white;">
                    <h3 class="box-title" style="font-weight: bold;">REGISTRO DE COMPRAS</h3>
                </div>
                <div class="box-body">
                    @include('empresas.compras.buscarcomprasproductos')
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-solid">
                <div class="box-body table-responsive no-padding">
                    <table id="tblCompra" class="table table-bordered table-hover table-striped" style="font-size: 11px;">
                        <thead>
                            <tr>
                                <th>Cod. Mov</th>
                                <th>Fec. Compra</th>
                                <th>Documento</th>
                                <th>Serie - N°</th>
                                <th class="text-center">PDF/EXCEL</th>
                                <th>Proveedor</th>
                                <th>Moneda</th>
                                <th>Total</th>
                                <th class="text-center" style="width: 100px;">Estado SUNAT</th>
                                <th class="text-center" style="width: 150px;">OPCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compras as $comp)
                            <tr>
                                <td class="text-center" style="vertical-align: middle;">{{$comp->cod_mov}}</td>
                                <td class="text-center" style="vertical-align: middle;">{{$comp->com_fec}}</td>
                                <td style="vertical-align: middle;">{{$comp->tdodes}}</td>
                                <td style="vertical-align: middle;"><strong>{{$comp->com_doc_ser}}-{{$comp->com_doc_num}}</strong></td>
                                <td class="text-center" style="vertical-align: middle;">
                                    <a href="/descargarcompra/{{$comp->com_cab_id}}" class="text-danger" title="Descargar PDF"><i class="fa fa-file-pdf-o fa-lg"></i></a>
                                    &nbsp;
                                    <a href="/descargarcompraexcel/{{$comp->com_cab_id}}" class="text-success" title="Descargar Excel"><i class="fa fa-file-excel-o fa-lg"></i></a>
                                </td>
                                <td style="vertical-align: middle;">
                                    <small><strong>{{$comp->prov_ruc}}</strong></small><br>
                                    {{$comp->prov_raz}}
                                </td>
                                <td class="text-center" style="vertical-align: middle;">{{$comp->monnom}}</td>
                                <td class="text-right" style="vertical-align: middle; font-weight: bold;">
                                    {{number_format($comp->total_com,'2','.',',')}}
                                </td>
                                
                                <td style="vertical-align: middle;">
                                    @php
                                        $sunat_val = strtoupper($comp->sunat_estado ?? 'PENDIENTE');
                                        if(strpos($sunat_val, 'ACEPTADO') !== false) {
                                            $clase_label = 'label-success';
                                            $icon_s = 'fa-check';
                                        } elseif(strpos($sunat_val, 'NO EXISTE') !== false) {
                                            $clase_label = 'label-warning';
                                            $icon_s = 'fa-exclamation-triangle';
                                        } else {
                                            $clase_label = 'label-danger';
                                            $icon_s = 'fa-times-circle';
                                        }
                                    @endphp
                                    <span class="label {{ $clase_label }} label-sunat">
                                        <i class="fa {{ $icon_s }}"></i> {{ $sunat_val }}
                                    </span>
                                </td>

                                <td class="text-center" style="vertical-align: middle;">
                                    <a href="/detallecompras/{{$comp->com_cab_id}}/1" class="btn-action">
                                        <img src="/icon/detalles.png" title="VER DETALLE" height="28px">
                                    </a>

                                    @if($comp->tdocod=='07')
                                        <a href="/editarnotacompra/{{$comp->com_cab_id}}" class="btn-action">
                                            <img src="/icon/editar.png" title="EDITAR NOTA" height="28px">
                                        </a>
                                    @else
                                        <a href="/editarcomp/{{$comp->com_cab_id}}" class="btn-action">
                                            <img src="/icon/editar.png" title="EDITAR COMPRA" height="28px">
                                        </a>
                                    @endif

                                    @if(strpos($sunat_val, 'ACEPTADO') === false)
                                        <a href="{{ url('revalidar-compra/'.$comp->com_cab_id) }}" class="btn-action" title="REVALIDAR EN SUNAT">
                                            <img src="/img/mallas.png" height="28px" style="filter: hue-rotate(180deg);">
                                        </a>
                                    @endif

                                    @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin')) 
                                        <a href="" data-target="#modal-delete-{{$comp->com_cab_id}}" data-toggle="modal" class="btn-action">
                                            <img src="/icon/error.png" title="ELIMINAR" height="28px">
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @include('empresas.compras.modal')
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection