@extends('layouts.empresas')
@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12">
            
            @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> ¡Sincronización Exitosa!</h4>
                {{ session('success') }}
            </div>
            @endif

            @if(session()->has('info'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> ¡Error!</h4>
                {{ session('info') }}
            </div>
            @endif

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cogs"></i> Herramienta: Reconstrucción de Kardex</h3>
                </div>
                
                <div class="box-body">
                    <div class="callout callout-warning" style="background-color: #fcf8e3 !important; color: #8a6d3b !important; border-color: #faebcc;">
                        <h4>¿Para qué sirve esta herramienta?</h4>
                        <p>
                            Si notas que el saldo de un producto en tu inventario no coincide con los ingresos y salidas reportados, esta opción forzará un recálculo matemático desde cero.
                        </p>
                        <ul>
                            <li>Sumará todos los <b>Ingresos (I)</b> del Kardex.</li>
                            <li>Restará todos los <b>Egresos (E)</b> del Kardex.</li>
                            <li>Actualizará tu tabla de stock con el saldo exacto resultante (tanto en unidad principal como equivalente).</li>
                        </ul>
                    </div>

                    <p class="text-muted text-center" style="margin-top: 20px;">
                        Dependiendo de la cantidad de movimientos en su empresa, este proceso puede tomar unos segundos. Por favor, haga clic una sola vez y espere.
                    </p>

                    <div class="text-center" style="margin-top: 30px; margin-bottom: 20px;">
                        {!! Form::open(['url' => '/mantenimiento/sincronizar-stock', 'method' => 'POST']) !!}
                        {{ Form::token() }}
                        
                        <button type="submit" class="btn btn-lg btn-success" onclick="return confirm('¿Está seguro de querer reconstruir todos los saldos de stock según el Kardex?');" style="font-size: 1.2em; padding: 10px 30px; box-shadow: 0px 4px 6px rgba(0,0,0,0.2);">
                            <i class="fa fa-refresh fa-spin-hover"></i> Sincronizar y Cuadrar Todo el Stock
                        </button>

                        {!! Form::close() !!}
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .fa-spin-hover:hover {
        animation: fa-spin 2s infinite linear;
    }
</style>
@endsection