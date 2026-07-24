@extends('layouts.empresas')
@section('contenido')
<div class="container">
    <div class="box">
        <div class="box-header"><h3>Configuración de Tarifas</h3></div>
        <div class="box-body">
            <form action="/estacionamiento/tarifas/guardar" method="POST">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-3"><input name="nombre" class="form-control" placeholder="Nombre (Ej: Tarifa Lunes)"></div>
                    <div class="col-md-2"><input name="precio_primera_hora" type="number" step="any" class="form-control" placeholder="1ra Hora"></div>
                    <div class="col-md-2"><input name="precio_hora_adicional" type="number" step="any" class="form-control" placeholder="Hora Adic."></div>
                    <div class="col-md-2"><input name="descuento_progresivo" type="number" step="any" class="form-control" placeholder="% Desc."></div>
                    <div class="col-md-3"><button class="btn btn-primary">Guardar Tarifa</button></div>
                </div>
            </form>
            <table class="table mt-3">
                <thead><tr><th>Nombre</th><th>1ra Hora</th><th>Hora Adic.</th><th>Desc %</th></tr></thead>
                <tbody>
                    @foreach($tarifas as $t)
                    <tr><td>{{$t->nombre}}</td><td>{{$t->precio_primera_hora}}</td><td>{{$t->precio_hora_adicional}}</td><td>{{$t->descuento_progresivo}}%</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection