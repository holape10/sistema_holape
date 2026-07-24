@extends('layouts.empresas')

@section('contenido')
<div class="container text-dark">
    <h2 class="mt-4">Editar Brazalete/Casillero</h2>
    <hr>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form action="{{ route('sauna.brazaletes.update', $brazalete->id) }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="numero_casillero"><strong>Nombre o Número del Casillero:</strong></label>
                    <input type="text" name="numero_casillero" class="form-control" value="{{ $brazalete->numero_casillero }}" required>
                </div>

                <div class="form-group mb-3 text-white bg-secondary p-3 rounded">
                    <label for="codigo_rfid"><strong>Código RFID (Pase la nueva pulsera si va a cambiarla):</strong></label>
                    <input type="text" name="codigo_rfid" class="form-control form-control-lg bg-white text-dark" value="{{ $brazalete->codigo_rfid }}" autocomplete="off" required>
                    <small class="text-light">Si solo vas a cambiar el número del casillero, no toques este campo. Si vas a reponer la pulsera por una nueva, borra el código actual y pasa el nuevo chip por el lector.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="estado"><strong>Estado del Casillero:</strong></label>
                    <select name="estado" class="form-control" required>
                        <option value="disponible" {{ $brazalete->estado == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="en_uso" {{ $brazalete->estado == 'en_uso' ? 'selected' : '' }}>En Uso (Manualmente)</option>
                        <option value="mantenimiento" {{ $brazalete->estado == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento / Bloqueado</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success btn-block">Guardar Cambios</button>
                <a href="{{ route('sauna.brazaletes.index') }}" class="btn btn-secondary btn-block">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection