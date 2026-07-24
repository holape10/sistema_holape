@extends('layouts.empresas')
@section('contenido')

<style>
/* Estilos para hacer la tabla responsive en móviles sin aplastar el contenido */
.col-producto {
    min-width: 250px; 
    max-width: 600px; 
    white-space: normal;
}
.col-receta {
    min-width: 55px;
    text-align: center;
}
.col-opciones {
    min-width: 90px;
    text-align: center;
}
.table>tbody>tr>td, .table>thead>tr>th {
    vertical-align: middle !important;
}
</style>

<script>
$(document).ready(function()
{       
     $("#btnExcel").click(function() {
          var accion = $(this).attr('dir');
          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
     });

     $("#cmbCatId").change(function() {
          var cat_id = $("#cmbCatId").val();
          $("#subcat_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarsubcategorias/"+cat_id,
          }).done(function(respuesta){
            $("#subcat_id").html(respuesta.vista);
          });

          var subcat_id = $("#subcat_id").val();
          $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscartipos/"+subcat_id,
          }).done(function(respuesta){
            $("#tip_pro_id").html(respuesta.vista);
          });
     });

     $("#subcat_id").change(function() {
          var subcat_id = $("#subcat_id").val();
          $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscartipos/"+subcat_id,
          }).done(function(respuesta){
            $("#tip_pro_id").html(respuesta.vista);
          });
     });

     $.ajaxSetup({
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
     });

     $('.check-individual').change(function() {
          var check = $(this);
          var idProducto = check.data('id');
          var estado = check.is(':checked') ? 1 : 0;
          var spanPrecio = check.closest('tr').find('.precio-item');
          var precioOriginal = parseFloat(spanPrecio.data('precio-original'));

          if (estado === 1) {
              spanPrecio.text((precioOriginal / 2).toFixed(2));
          } else {
              spanPrecio.text(precioOriginal.toFixed(2));
          }
          $.post("{{ route('productos.toggleDescuento') }}", { id: idProducto, estado: estado });
     });

     $('#checkMitadPrecioGlobal').change(function() {
          var estadoGlobal = $(this).is(':checked') ? 1 : 0;
          $('.check-individual').prop('checked', estadoGlobal).trigger('change');
          $.post("{{ route('productos.toggleDescuento') }}", { id: 'todos', estado: estadoGlobal });
     });
});
</script>

<section class="content">
    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header" style="background-color:blue;">
                        <font color="white"><center><strong>LISTADO DE INSUMOS</strong></center></font>
                        <div class="box-tools pull-right">
                            <a href="{{ url('productos') }}"><button class="btn btn-info btn-sm">Ver Producuctos</button></a>
                            <a href="{{ url('productos/create?origen=insumo') }}">
                                <button class="btn btn-success btn-sm">Nuevo Insumo</button>
                            </a>
                            <a href="/exportar-productos-excel"><button class="btn btn-primary btn-sm">Exportar Excel</button></a>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-importar-productos">Importar Excel</button>
                        </div>
                    </div>
                    <div class="box-body">
                     <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        @include('empresas.productos.modalproductos')
                        @include('empresas.productos.modalimportarproductos')
                        @include('empresas.productos.modalimportarpresentaciones')
                        @include('empresas.productos.search', ['es_insumos' => true])
                    </div>
                    </div>
                </div>
            </div>
    </div>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header" style="background-color:blue;">
                <font color="white"><center><strong>PRODUCTOS - {{$data_suc->IdEmpresa}} - {{$data_suc->tipo_negocio}}</strong></center></font>
            </div>
            <div class="box-body table-responsive" >
            <table class="table table-striped table-bordered table-condensed table-hover">
                <thead>
                    <th>TIPO PRODUCTO</th> 
                    <th>CODIGO</th>
                    <th>LINEA</th>                    
                    <th>FAMILIA</th>
                    <th hidden='hidden'>SUBFAMILIA</th>
                    <th class="col-producto">PRODUCTO</th>
                    <th>IMAGEN</th>
                    <th>UM</th>
                    <th hidden='hidden'>MONEDA</th>
                    @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin'))
                    <th hidden="hidden">50% Dcto.</th>
                    @endif
                    <th>PRECIO</th>
                    <th>COSTO</th>
                    <th hidden='hidden'>STOCK ACTUAL</th>
                    <th hidden="hidden">IMAGEN</th>
                    <th>RECETA</th>
                    <th>OPCIONES</th>
                </thead>
                
                @foreach ($productos as $pro)
                <tr>
                     <td>
                        @if($pro->promocion =='0') PRODUCTO 
                        @elseif($pro->promocion =='1') COMBO 
                        @elseif($pro->promocion =='2') PREPARADOS 
                        @elseif($pro->promocion =='4') INSUMO 
                        @elseif($pro->promocion =='5') ENTRADA 
                        @elseif($pro->promocion =='6') COMBO 
                        @endif
                    </td>
                    <td>{{$pro->procod}}</td>
                    <td><strong>{{$pro->tip_pro_nom}}</strong></td>                    
                    <td>{{$pro->cat_nom}}</td>
                    <td hidden='hidden'>{{$pro->subcat_nom}}</td>
                    <td class="col-producto">{{$pro->pronom}}</td>
                    
                    <td style="text-align: center; vertical-align: middle;">
                        @if($pro->imagenproducto)
                            <img src="/imagenes/productos/{{$pro->imagenproducto}}" alt="{{$pro->pronom}}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        @else
                            <span class="text-muted" style="font-size: 11px;">Sin imagen</span>
                        @endif
                    </td>

                    <td>{{$pro->umenom}}</td>
                    <td hidden='hidden'>{{$pro->monnom}}</td>
                    @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin'))
                    <td hidden="hidden">
                        <label style="cursor: pointer; color: #2980b9;">
                            <input type="checkbox" class="check-individual" data-id="{{ $pro->IdProducto }}" style="transform: scale(1.2); margin-right: 5px;" {{ $pro->mitad_precio == 1 ? 'checked' : '' }}> 
                            50%
                        </label>
                    </td>
                    @endif
                    <td>{{$pro->precio}}</td>
                    <td>{{$pro->costo}}</td>
                    <td hidden='hidden'>{{$pro->stock}}</td>

                    {!!Form::open(array('url'=>['productos/subirimagen',$pro->IdProducto],'method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmProducto'))!!}
                    {{Form::token()}}   
                        <td hidden="hidden">
                            <a href="" data-target="#modal-imagen-{{$pro->IdProducto}}" data-toggle="modal"><button type="button" class="btn btn-sm btn-primary">Ver Imagen</button></a>
                        </td>
                    {!!Form::close()!!} 

                    <td class="col-receta">
                        @if($pro->promocion =='2')
                            <a href="/asignarreceta/{{$pro->IdProducto}}"><img src="/icon/receta.png" title="RECETA" height="30px" width="40px"></a>
                        @else
                            <img src="/icon/receta.png" title="RECETA" height="30px" width="40px" style="opacity:0.5">
                        @endif
                    </td>
                    <td class="col-opciones">
                        <a href="/editarproducto/{{$pro->IdProducto}}/{{$sucursal}}"><img src="/icon/editar.png" title="EDITAR" height="30px" width="30px"></a>
                        <a href="" data-target="#modal-delete-{{$pro->IdProducto}}" data-toggle="modal"><img src="/icon/error.png" title="ELIMINAR" height="30px" width="30px"></a>
                    </td>
                </tr>
                @include('empresas.productos.modal')
                @include('empresas.productos.modalimagen')
                @endforeach
            </table>
        </div>
        {{$productos->render()}}
    </div>
</div>
</section>
@endsection