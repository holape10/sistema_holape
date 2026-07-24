@extends('layouts.empresas') 

@section('contenido')
<div class="container text-dark">
    <h2 class="mt-4">Punto de Recepción y Control - Sauna</h2>
    <hr>

    @if(session('error'))
        <div class="alert alert-danger shadow-sm">
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row mt-5">
        
        <div class="col-md-6 mb-4">
            <div class="card shadow border-primary h-100" style="border-radius: 10px;">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0"><i class="fa fa-wifi"></i> Opción 1: Lector RFID Activo</h5>
                </div>
                <div class="card-body text-center p-4 d-flex flex-column justify-content-center">
                    <p class="text-muted">Pase el brazalete por el lector USB. El sistema procesará el ingreso o consumo al instante.</p>
                    
                    <form action="{{ route('sauna.procesar-rfid') }}" method="POST">
                        @csrf 
                        <div class="form-group">
                            <input type="password" name="codigo_rfid" id="input_rfid" class="form-control form-control-lg text-center" autofocus autocomplete="off" placeholder="[ Esperando pulsera... ]" style="letter-spacing: 3px; font-size: 1.5rem;">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow border-secondary h-100" style="border-radius: 10px;">
                <div class="card-header bg-secondary text-white text-center">
                    <h5 class="mb-0"><i class="fa fa-search"></i> Opción 2: Búsqueda Manual</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <p class="text-muted text-center">Use esta opción si el cliente perdió la pulsera o requiere soporte por número de casillero.</p>
                    
                    <form action="{{ route('sauna.procesar-rfid') }}" method="POST">
                        @csrf 
                        <div class="form-group">
                            <label for="numero_casillero"><strong>Ingrese Número de Casillero:</strong></label>
                            <div class="input-group">
                                <input type="text" name="numero_casillero" id="numero_casillero" class="form-control form-control-lg" placeholder="Ej: 05, 12, VIP-1" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-secondary btn-lg">Buscar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Truco para que si hacen clic en cualquier lado por error, 
// el cursor regrese automáticamente al input del lector RFID
$(document).ready(function() {
    // Solo regresa el foco si el input manual no está siendo usado
    setInterval(function() {
        if (!$('#numero_casillero').is(':focus') && !$('#input_rfid').is(':focus')) {
            $('#input_rfid').focus();
        }
    }, 3000);
});
</script>
@endsection