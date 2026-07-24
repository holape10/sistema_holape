@extends ('layouts.empresas')
@section ('contenido')
<style>
    .nav-pills .nav-link.active {
        background-color: #20c997 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(32, 201, 151, 0.3);
    }
    .nav-pills .nav-link {
        color: #495057;
        font-weight: 600;
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.3s ease;
    }
    .form-modern .form-control {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.2s ease;
    }
    .form-modern .form-control:focus {
        border-color: #20c997;
        box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.15);
    }
    .card-custom {
        border-radius: 12px;
        border: none;
    }
    .table-container {
        max-height: 400px; /* Limita la altura de la tabla para que no se vaya al infinito */
        overflow-y: auto;
    }
</style>

<div class="container-fluid pt-3">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa fa-exclamation-triangle mr-2"></i> Hubo un problema al procesar la solicitud.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h3 class="font-weight-bold text-dark m-0">Administración de Flota</h3>
            <p class="text-muted small m-0">Gestiona las unidades de transporte de la empresa de forma rápida.</p>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end mt-2 mt-md-0">
            <ul class="nav nav-pills bg-white p-1 rounded shadow-sm" id="fleetTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="vehiculos-tab" data-toggle="pill" href="#vehiculos-pane" role="tab">
                        <i class="fa fa-truck mr-1"></i> Vehículos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="choferes-tab" data-toggle="pill" href="#choferes-pane" role="tab">
                        <i class="fa fa-id-card mr-1"></i> Conductores
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="fleetTabContent">
        
        <div class="tab-pane fade show active" id="vehiculos-pane" role="tabpanel">
            <div class="card card-custom shadow-sm mb-3">
                <div class="card-body p-3">
                    <form action="{{ route('vehiculos.store') }}" method="POST" class="form-modern">
                        {{ csrf_field() }}
                        <div class="row align-items-end">
                            <div class="form-group col-md-2 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">PLACA *</label>
                                <input type="text" name="placa" class="form-control" placeholder="ABC-123" required style="text-transform: uppercase;">
                            </div>
                            <div class="form-group col-md-3 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">MARCA</label>
                                <input type="text" name="marca" class="form-control" placeholder="Ej: Volvo">
                            </div>
                            <div class="form-group col-md-3 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">CERTIFICADO MTC</label>
                                <input type="text" name="inscripcion_mtc" class="form-control" placeholder="Inscripción MTC">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">MODELO</label>
                                <input type="text" name="modelo" class="form-control" placeholder="Detalles">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <button type="submit" class="btn btn-dark btn-block font-weight-bold" style="border-radius: 8px; height: calc(2.25rem + 2px);">
                                    <i class="fa fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-custom shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive table-container">
                        <table class="table table-hover table-striped m-0">
                            <thead class="bg-light text-secondary small font-weight-bold">
                                <tr>
                                    <th class="p-2">PLACA</th>
                                    <th class="p-2">MARCA / MODELO</th>
                                    <th class="p-2">CERTIFICADO MTC</th>
                                    <th class="p-2 text-center">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehiculos as $v)
                                <tr>
                                    <td class="p-2 font-weight-bold text-dark">{{ $v->placa }}</td>
                                    <td class="p-2 text-muted text-sm">{{ $v->marca ?? '-' }} {{ $v->modelo ?? '' }}</td>
                                    <td class="p-2 text-muted text-sm">{{ $v->inscripcion_mtc ?? '-' }}</td>
                                    <td class="p-2 text-center">
                                        <span class="badge px-2 py-1" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745; border-radius: 30px;">{{ $v->estado }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted p-3">No hay vehículos registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="choferes-pane" role="tabpanel">
            <div class="card card-custom shadow-sm mb-3">
                <div class="card-body p-3">
                    <form action="{{ route('choferes.store') }}" method="POST" class="form-modern">
                        {{ csrf_field() }}
                        <div class="row align-items-end">
                            <div class="form-group col-md-3 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">DNI *</label>
                                <div class="input-group">
                                    <input type="text" name="dni" id="dni_chofer" class="form-control" placeholder="Número de DNI" required maxlength="8">
                                    <div class="input-group-append">
                                        <button class="btn btn-info" type="button" id="btn-buscar-dni" onclick="buscarDniApi()">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">NOMBRES Y APELLIDOS *</label>
                                <input type="text" name="nombres_apellidos" id="nombres_chofer" class="form-control" placeholder="Búsqueda SUNAT/RENIEC" required readonly>
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">NRO LICENCIA *</label>
                                <input type="text" name="licencia" class="form-control" placeholder="Licencia" required style="text-transform: uppercase;">
                            </div>
                            <div class="form-group col-md-1 mb-2">
                                <label class="text-secondary small font-weight-bold mb-1">TELÉFONO</label>
                                <input type="text" name="telefono" class="form-control" placeholder="Celular">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <button type="submit" class="btn btn-success btn-block font-weight-bold" style="border-radius: 8px; height: calc(2.25rem + 2px);">
                                    <i class="fa fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-custom shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive table-container">
                        <table class="table table-hover table-striped m-0">
                            <thead class="bg-light text-secondary small font-weight-bold">
                                <tr>
                                    <th class="p-2">DNI</th>
                                    <th class="p-2">NOMBRES Y APELLIDOS</th>
                                    <th class="p-2">LICENCIA</th>
                                    <th class="p-2 text-center">TELÉFONO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($choferes as $c)
                                <tr>
                                    <td class="p-2 text-muted text-sm">{{ $c->dni }}</td>
                                    <td class="p-2 font-weight-bold text-dark">{{ $c->nombres_apellidos }}</td>
                                    <td class="p-2 text-muted font-weight-bold text-sm">{{ $c->licencia }}</td>
                                    <td class="p-2 text-center text-muted text-sm">{{ $c->telefono ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted p-3">No hay conductores registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function buscarDniApi() {
        var documento = $('#dni_chofer').val();
        
        if (documento.length !== 8) {
            alert('El DNI debe tener exactamente 8 dígitos');
            return;
        }

        $('#btn-buscar-dni').html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("transportes.consultardoc") }}', 
            type: 'GET',
            data: { documento: documento },
            success: function(response) {
                $('#btn-buscar-dni').html('<i class="fa fa-search"></i>');

                if (response.nom) {
                    $('#nombres_chofer').val(response.nom);
                    $('input[name="licencia"]').focus(); 
                } else if (response.error) {
                    alert(response.error);
                    $('#nombres_chofer').val('');
                    $('#nombres_chofer').prop('readonly', false);
                }
            },
            error: function() {
                $('#btn-buscar-dni').html('<i class="fa fa-search"></i>');
                alert('Error al conectar con el servidor.');
                $('#nombres_chofer').prop('readonly', false);
            }
        });
    }

    $('#dni_chofer').on('keypress', function(e) {
        if(e.which === 13) { 
            e.preventDefault(); 
            buscarDniApi(); 
        }
    });
</script>
@endsection