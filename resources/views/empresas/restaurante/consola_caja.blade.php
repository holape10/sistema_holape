@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')

<style>
    /* ========== ESTILOS GENERALES ========== */
    * {
        box-sizing: border-box;
    }

    body {
        background-color: #f4f6f9;
    }

    /* ========== DASHBOARD HEADER ========== */
    .dashboard-header {
        margin-bottom: 30px;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stats-box {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .stats-box:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .stats-box.green {
        border-left: 5px solid #27AE60;
    }

    .stats-box.blue {
        border-left: 5px solid #3498DB;
    }

    .stats-box.red {
        border-left: 5px solid #E74C3C;
    }

    .stats-box-inner {
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stats-box-content h3 {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #666;
        margin: 0 0 8px 0;
        letter-spacing: 0.5px;
    }

    .stats-box-content h2 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #2c3e50;
    }

    .stats-box-icon {
        font-size: 40px;
        opacity: 0.2;
    }

    /* ========== CONTROL BUTTONS ========== */
    .control-buttons-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .control-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }

    .control-buttons .btn {
        font-weight: 600;
        font-size: 11px;
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .control-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .control-buttons .btn:active {
        transform: translateY(0);
    }

    /* ========== MESAS GRID ========== */
    .mesas-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .mesas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 12px;
        min-height: 100px;
    }

    .mesa-button {
        padding: 0 !important;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px !important;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .mesa-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transition: left 0.3s ease;
    }

    .mesa-button:hover::before {
        left: 100%;
    }

    .mesa-button:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .mesa-button:active {
        transform: scale(0.98);
    }

    .mesa-button.ocupado {
        background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        color: white;
    }

    .mesa-button.libre {
        background: linear-gradient(135deg, #52BE80 0%, #27AE60 100%);
        color: white;
    }

    .mesa-button.reservado {
        background: linear-gradient(135deg, #F4D03F 0%, #F39C12 100%);
        color: white;
    }

    .mesa-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
        position: relative;
        z-index: 1;
    }

    .mesa-nombre {
        font-size: 15px;
        font-weight: 700;
    }

    .mesa-total {
        font-size: 12px;
        opacity: 0.9;
        font-weight: 500;
    }

    /* ========== BOX STYLES ========== */
    .box {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: none;
    }

    .box-header {
        background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        border-radius: 12px 12px 0 0;
        letter-spacing: 0.5px;
    }

    .box-body {
        padding: 20px;
        background-color: #f9f9f9;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1200px) {
        .mesas-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .mesas-grid {
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
        }

        .mesa-button {
            min-height: 90px;
            font-size: 13px;
        }

        .control-buttons {
            gap: 8px;
        }

        .control-buttons .btn {
            padding: 8px 12px;
            font-size: 10px;
        }
    }

    @media (max-width: 576px) {
        .mesas-grid {
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        }

        .mesa-button {
            min-height: 90px;
        }

        .stats-box-content h2 {
            font-size: 22px;
        }
    }

    /* ========== ANIMACIONES ========== */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stats-box, .mesas-section, .control-buttons-section {
        animation: slideInUp 0.4s ease-out;
    }

    /* ========== UTILIDADES ========== */
    .text-center {
        text-align: center;
    }

    .mb-20 {
        margin-bottom: 20px;
    }

    .hidden-element {
        display: none;
    }
</style>

<script type="text/javascript">
    $(document).ready(function(){

        //setTimeout(refrescar, 10000);
        setInterval(refrescar, 30000);

        var comprobante = $("#comprobante").val();
        var documento = $("#documento").val();
        $("#btnPrint").printPage({
            url: "/voucher/"+comprobante,
            attr: "href",
            messageBox:false
        })

        limpiarpedido();

        $(".selectpicker").selectpicker();

        $("#tipo").val('1');

        $("#btnCambiar").click(function(){
            var mesa_actual = $("#mes_id").val();
            var mesa_nom_actual = $("#mes_nom").val();
            var ped_id_actual = $("#ped_id").val();

            if(mesa_actual===""){
                alert('Elegir una mesa');
            }else{
                $("#mes_id_act").val(mesa_actual);
                $("#ped_id_act").val(ped_id_actual);
                $("#mes_act").val(mesa_nom_actual);
                $("#modal-cambiar-mesa").modal("show");
            }
        });

        $("#btnComanda").click(function(){
            var formulario = $("#frmcomandas").serializeArray();
            var accion = $("#accion").val();
            var tipo_comanda = $("#tipo").val();

            $("#imgloadcliente").show();

            if(accion=='0'){
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/registrarcomanda',
                    data: formulario,
                }).done(function(respuesta){
                    if(respuesta.estado=='error'){
                        alert(respuesta.mensaje);
                    }else{
                        window.location.href = "/consola";
                    }
                });
            }else{
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/actualizarcomanda',
                    data: formulario,
                }).done(function(respuesta){
                    window.location.href = "/consola";
                });
            }
        });

        $("#btnSalon").click(function(){
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: '/panelsalon',
            }).done(function(respuesta){
                $("#tipo").val('1');
                $("#salon").html(respuesta.vista);
                limpiarpedido();
            });
        });

        $("#txt_bus_pro").keyup(function(){
            var producto = $(this).val();
            var contarcarateres = $(this).val().length;

            if(contarcarateres >0){
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "/buscarcarta/"+producto,
                }).done(function(respuesta){
                    $("#items_productos").html(respuesta.vista);
                });
            }
        });

        $("#piso").change(function(){
            $("#mes_id").val("");
            $("#pis_id").val("");
            var piso = $(this).val();

            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/buscarmesas/"+piso,
            }).done(function(respuesta){
                $("#listar_mesas").html(respuesta.vista);
            });
        });

        $(".btnPiso").click(function(){
            $("#mes_id").val("");
            $("#pis_id").val("");
            var piso = $(this).val();

            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/buscarmesascaja/"+piso,
            }).done(function(respuesta){
                $("#listar_mesas").html(respuesta.vista);
            });
        });
    });

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

    function agregar_item(id,producto,precio){
        var validar = checkId(id);

        if (validar==true){
            $("#tbl_detalle  > tbody  > tr").each(function(){
                if(id==$(this).find("td:eq(0) > input").val()){
                    var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;
                    $(this).find("td:eq(2) > input").val(calcular_cantidad);
                }
            });
        }else{
            $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id+'"></td>'+
                '<td>'+producto+'</td>'+
                '<td><input type="number" style="text-align:center;" step="any" class="form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
                '<td style="text-align:right;">'+precio+'</td>'+
                '<td style="text-align:right;" hidden="hidden"><input type="number" readonly="readonly" step="any" name="precios[]" value="'+precio+'"></td>'+
                '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        }

        calcular_total();
    }

    function checkId(id) {
        var contar=0;

        $("#tbl_detalle  > tbody  > tr").each(function(){
            if(id==$(this).find("td:eq(0) > input").val()){
                contar = contar+1;
            }
        });

        if(contar>0){
            return true;
        }else{
            return false;
        }
    }

    function eliminar_item(btn) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    function eliminar_item_registrado(btn,item) {
        eliminar_item_pedido(item);
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    function elegir_mesa(mesa,id,nombre){
        var piso =  $("#piso option:selected").text();
        var pis_id = $("#piso option:selected").val();

        $("#mes_id").val(id);
        $("#mes_nom").val(nombre);
        $("#pis_id").val(pis_id);

        $("#lbl_pis_mes").text(piso+' / '+mesa);

        consultar_mesa_pedido(id);
    }

    function eliminar_item_pedido(item){
        var pedido = $("#ped_id").val();

        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/eliminaritem/"+item+"/"+pedido,
        }).done(function(respuesta){
            alert(respuesta.mensaje);
        });
    }

    function consultar_mesa_pedido(id){
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarpedidomesa/"+id,
        }).done(function(respuesta){
            if(respuesta.estado=='1'){
                $("#listar_pedido").html(respuesta.vista);
                $("#ped_id").val(respuesta.ped_id);
                $("#accion").val("1");
            }else{
                $('#items_pedidos').empty();
                $("#accion").val("0");
                $("#ped_id").val("");
            }
            calcular_total();
        });
    }

    function consultar_pedido_llevar_delivery(id){
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarpedidollevardelivery/"+id,
        }).done(function(respuesta){
            if(respuesta.estado=='1'){
                $("#listar_pedido").html(respuesta.vista);
                $("#ped_id").val(respuesta.ped_id);
                $("#accion").val("1");
            }else{
                $('#items_pedidos').empty();
                $("#accion").val("0");
                $("#ped_id").val("");
            }
            calcular_total();
        });
    }

    function limpiarpedido(){
        $("#mes_id").val("");
        $("#pis_id").val("");
        $("#accion").val("0");

        $("#ped_num_doc").val("");
        $("#ped_cli_nom").val("");
        $("#ped_dir").val("");
        $("#ped_obs").val("");
        $("#tdicod").val("1").attr('selected', 'selected');

        $("#ped_tel").val("");
        $("#ped_ref").val("");
        $("#ped_pag_tar").prop("checked", false);
        $("#ped_pag_efe").val("");
        $("#ped_fac").prop("checked", false);

        $('#items_pedidos').empty();

        calcular_total();
    }

    function buscarcliente(){
        var ped_cli_num = $("#ped_num_doc").val();
        $("#imgloadcliente").show();

        $.ajax({
            type: "get",
            dataType: 'json',
            url: '/autocomplete/'+ped_cli_num,
        }).done(function(respuesta){
            if(respuesta.error){
                alert(respuesta.error);
                $("#imgloadcliente").hide();
            }else{
                $('#ped_cli_nom').val(respuesta[0].nom);
                $('#ped_dir').val(respuesta[0].dir);
                $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');
                $("#imgloadcliente").hide();
                $(".botones").show();
            }
        });
    }

    function calcular_total(){
        var total = 0;

        $("#tbl_detalle tbody tr").each(function(){
            total = total + parseFloat($(this).find("td:eq(2)> input").val()*$(this).find("td:eq(4)>input").val());
        })

        $('#total_venta').val(total.toFixed(2));
    }

    function buscar_pedidos_llevar_delivery(tipo){
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarpedidoscaja/"+tipo,
        }).done(function(respuesta){
            $("#listar_mesas").html(respuesta.vista);
        });
    }

    function refrescar(){
        location.reload();
    }
</script>

@if(isset($codfact) && $datos->ticket_pantalla=='1' && $datos->formato=='TICKET')
    <a class="hidden-element" href=''><button type="button" hidden="hidden" id="btnPrint" class="hidden-element" value="imprimir"></button></a>
@endif

@if(isset($codfact))
    <input type="hidden" name="comprobante" id="comprobante" value="{{$codfact}}">
@endif

@if(isset($tdocod))
    <input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
@endif

<div class="container-fluid">
    {!!Form::open(array('url'=>'/registrar','autocomplete'=>'off','method'=>'POST','name'=>'frmcomandas','id'=>'frmcomandas','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}

    <!-- DASHBOARD HEADER CON ESTADÍSTICAS -->
    <div class="dashboard-header">
        @if($es_admin)
            <div class="alert alert-info text-center" style="border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
                <i class="fa fa-eye"></i> Vista Global: Estás viendo los totales combinados de TODOS los turnos aperturados.
            </div>
        @endif
        <div class="stats-container">
            <!-- Total Ventas -->
            <div class="stats-box green">
                <div class="stats-box-inner">
                    <div class="stats-box-content">
                        <h3>Total Ventas</h3>
                        <h2>S/. {{number_format($ventas,'2','.','')}}</h2>
                    </div>
                    <div class="stats-box-icon">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
            </div>

            <!-- Métodos de Pago -->
            @if(!empty($sum_mp))
                @foreach($sum_mp as $mp)
                    <div class="stats-box blue">
                        <div class="stats-box-inner">
                            <div class="stats-box-content">
                                <h3>{{$mp->nom_med_pag}}</h3>
                                <h2>S/. {{number_format($mp->monto_total,'2','.','')}}</h2>
                            </div>
                            <div class="stats-box-icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Aperturar/Cerrar Caja -->
            @if(Auth::User()->turno =='Cerrado')
                <a href="" data-target="#modal-aperturar" data-toggle="modal" style="text-decoration: none;">
                    <div class="stats-box green">
                        <div class="stats-box-inner">
                            <div class="stats-box-content">
                                <h3>Aperturar</h3>
                                <h2>Caja</h2>
                            </div>
                            <div class="stats-box-icon">
                                <i class="fa fa-cash-register"></i>
                            </div>
                        </div>
                    </div>
                </a>
            @else
                <a href="" data-target="#modal-cerrar" data-toggle="modal" style="text-decoration: none;">
                    <div class="stats-box red">
                        <div class="stats-box-inner">
                            <div class="stats-box-content">
                                <h3>Cerrar</h3>
                                <h2>Caja</h2>
                            </div>
                            <div class="stats-box-icon">
                                <i class="fa fa-cash-register"></i>
                            </div>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Panel de Comandas -->
            <a href="/seleccion" style="text-decoration: none;">
                <div class="stats-box green">
                    <div class="stats-box-inner">
                        <div class="stats-box-content">
                            <h3>Panel</h3>
                            <h2>Comandas</h2>
                        </div>
                        <div class="stats-box-icon">
                            <i class="fa fa-bar-chart"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- BOTONES DE CONTROL -->
    <div class="control-buttons-section">
        <div class="control-buttons">
            <button type="button" onclick="buscar_pedidos_llevar_delivery(3);" class="btn btn-warning">
                <i class="fa fa-shopping-bag"></i> Llevar
            </button>
            <button type="button" onclick="buscar_pedidos_llevar_delivery(2);" class="btn btn-success">
                <i class="fa fa-truck"></i> Delivery
            </button>
            @foreach($pisos as $piso)
                <button type="button" value="{{$piso->pis_id}}" class="btnPiso btn btn-info">
                    <i class="fa fa-home"></i> {{$piso->pis_nom}}
                </button>
            @endforeach
        </div>
    </div>

    <!-- GRID DE MESAS -->
        <!-- GRID DE MESAS -->
    <div class="mesas-section">
        <div id="salon">
            <div id="listar_mesas" class="mesas-grid">
                @if(!empty($mesas))
                    @foreach($mesas as $mesa) {{-- CAMBIO: $mesa en singular --}}
                        @if($mesa->mes_est == 'Ocupado' || $mesa->mes_est == 'Cuenta')
                            
                            {{-- OPTIMIZACIÓN: Ya no consulta a la BD, usa la variable $pedidos_activos --}}
                            @php
                                $pedido = $pedidos_activos->get($mesa->mes_id);
                            @endphp

                            @if(isset($pedido->ped_id))
                                <a type="button" class="mesa-button ocupado" href="/cobrarmesa/{{$pedido->ped_id}}">
                                    <button type="button" class="mesa-button ocupado">
                                        <div class="mesa-info">
                                            <span class="mesa-nombre">{{$mesa->mes_nom}}</span>
                                            <span class="mesa-total">S/. {{number_format($pedido->ped_tot, 2)}}</span>
                                        </div>
                                    </button>
                                </a>
                            @else
                                <button type="button" class="mesa-button ocupado" disabled>
                                    <div class="mesa-info">
                                        <span class="mesa-nombre">{{$mesa->mes_nom}}</span>
                                    </div>
                                </button>
                            @endif

                        @elseif($mesa->mes_est=='Libre')
                            <button type="button" class="mesa-button libre" onclick="elegir_mesa('{{$mesa->mes_nom}}','{{$mesa->mes_id}}','{{$mesa->mes_nom}}')">
                                <div class="mesa-info">
                                    <span class="mesa-nombre">{{$mesa->mes_nom}}</span>
                                </div>
                            </button>

                        @else
                            <button type="button" class="mesa-button reservado" disabled>
                                <div class="mesa-info">
                                    <span class="mesa-nombre">{{$mesa->mes_nom}}</span>
                                </div>
                            </button>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {!!Form::close()!!}

    @include('empresas.turnos.turno')
    @include('empresas.turnos.cierre')
</div>

@endsection