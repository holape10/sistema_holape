@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="panel-title"><i class="fa fa-list"></i> Control de Stock Preparados</h3>
            <a href="{{ route('kiosko.stock_preparados.crear') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Asignar Stock del Día
            </a>
        </div>
        
        <div class="panel-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-navy">
                        <tr>
                            <th>Producto / Plato</th>
                            <th class="text-center">Stock Actual en Sistema</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preparados as $item)
                        <tr>
                            <td>{{ $item->pronom }}</td>
                            <td class="text-center"><strong>{{ number_format($item->stock_preparados, 0) }}</strong></td>
                            <td class="text-center">
                                @if($item->stock_preparados > 5)
                                    <span class="label label-success">Disponible</span>
                                @else
                                    <span class="label label-danger">Bajo Stock</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('kiosko.stock_preparados.crear') }}" class="btn btn-warning btn-xs">
                                    <i class="fa fa-edit"></i> Corregir
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay stock asignado para hoy o todo se ha agotado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection