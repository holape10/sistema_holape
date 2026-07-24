@extends('layouts.empresas')

@section('contenido')
<div class="container text-dark">
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h2>Control de Brazaletes / Casilleros</h2>
        <a href="{{ route('sauna.brazaletes.create') }}" class="btn btn-primary">+ Registrar Nuevo Brazalete</a>
    </div>
    <hr>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Número de Casillero</th>
                        <th>Código RFID (Chip)</th>
                        <th>Estado Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brazaletes as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td><strong>{{ $b->numero_casillero }}</strong></td>
                        <td><code>{{ $b->codigo_rfid }}</code></td>
                        <td>
                            @if($b->estado == 'disponible')
                                <span class="badge badge-success">Disponible</span>
                            @elseif($b->estado == 'en_uso')
                                <span class="badge badge-danger">En Uso</span>
                            @else
                                <span class="badge badge-warning">Mantenimiento</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('sauna.brazaletes.edit', $b->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection