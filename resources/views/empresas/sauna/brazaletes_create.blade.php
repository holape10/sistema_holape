@extends('layouts.empresas')

@section('contenido')
<div class="container text-dark">
    <h2 class="mt-4">Registrar Nuevo Brazalete/Casillero</h2>
    <hr>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">
            <form action="{{ route('sauna.brazaletes.store') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="numero_casillero"><strong>Nombre o Número del Casillero:</strong></label>
                    <input type="text" name="numero_casillero" class="form-control" placeholder="Ej: Casillero 05, Casillero VIP A" required>
                </div>

                <div class="form-group mb-3 text-white bg-secondary p-3 rounded">
                    <label for="codigo_rfid"><strong>Código RFID (Pase la pulsera por el lector):</strong></label>
                    <input type="text" name="codigo_rfid" class="form-control form-control-lg bg-white text-dark" autofocus autocomplete="off" placeholder="Esperando lectura de chip..." required>
                    <small class="text-light">Ponga el cursor aquí y acerque la pulsera al lector USB.</small>
                </div>

                <button type="submit" class="btn btn-success btn-block">Guardar Casillero en el Sistema</button>
                <a href="{{ route('sauna.brazaletes.index') }}" class="btn btn-secondary btn-block">Volver al Listado</a>
            </form>
        </div>
    </div>
</div>
@endsection