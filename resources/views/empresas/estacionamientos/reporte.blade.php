@extends('layouts.empresas')
@section('contenido')
<div class="container-fluid mt-4">
    <form method="GET" action="/estacionamiento/reportes" class="mb-4">
        <div class="row">
            <div class="col-md-3"><input type="date" name="fecha_inicio" class="form-control" value="{{$fecha_inicio}}"></div>
            <div class="col-md-3"><input type="date" name="fecha_fin" class="form-control" value="{{$fecha_fin}}"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Filtrar</button></div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-green">
                <div class="inner"><h3>S/ {{ number_format($total_general, 2) }}</h3><p>Total Recaudado</p></div>
                <div class="icon"><i class="fa fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-blue">
                <div class="inner"><h3>{{ $total_tickets }}</h3><p>Tickets Procesados</p></div>
                <div class="icon"><i class="fa fa-car"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header"><h4>Recaudación por Punto</h4></div>
                <div class="box-body">
                    <table class="table table-hover">
                        <thead><tr><th>Punto / Caja</th><th>Tickets</th><th>Recaudado</th></tr></thead>
                        <tbody>
                            @foreach($reporte as $r)
                            <tr><td>Punto #{{ $r->id_punto_atencion }}</td><td>{{ $r->total_tickets }}</td><td>S/ {{ number_format($r->total_recaudado, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header"><h4>Gráfico de Ventas</h4></div>
                <div class="box-body"><canvas id="graficoVentas" height="150"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoVentas').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [ @foreach($reporte as $r) 'Punto #{{ $r->id_punto_atencion }}', @endforeach ],
            datasets: [{ label: 'Recaudación S/', data: [ @foreach($reporte as $r) {{ $r->total_recaudado }}, @endforeach ], backgroundColor: '#00a65a' }]
        }
    });
</script>
@endsection