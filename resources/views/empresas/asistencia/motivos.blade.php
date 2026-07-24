@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    
    <!-- Alerta de Éxito -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Formulario de Creación -->
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Nuevo Motivo de Tardanza</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('asistencia.motivos.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Descripción del Motivo:</label>
                            <input type="text" name="descripcion" class="form-control" required placeholder="Ej: Lluvia extrema">
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-save"></i> Guardar Motivo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de Motivos -->
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Motivos Registrados</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($motivos as $m)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-left">{{ $m->descripcion }}</td>
                                <td><span class="badge badge-success">{{ $m->estado }}</span></td>
                                <td>
                                    <!-- Botón Editar -->
                                    <button type="button" class="btn btn-sm btn-warning text-dark" onclick="abrirModalEditar({{ $m->id }}, '{{ $m->descripcion }}')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Botón Eliminar -->
                                    <form action="{{ route('asistencia.motivos.destroy', $m->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro que deseas eliminar este motivo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA EDITAR MOTIVO -->
<div class="modal fade" id="modalEditarMotivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit"></i> Editar Motivo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditarMotivo" method="POST">
                @csrf
                @method('PUT') <!-- Laravel requiere esto para actualizaciones -->
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Descripción del Motivo:</label>
                        <input type="text" name="descripcion" id="input_editar_descripcion" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-sync-alt"></i> Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT PARA PASAR LOS DATOS AL MODAL -->
<script>
    function abrirModalEditar(id, descripcion) {
        // 1. Apuntamos el formulario a la ruta correcta usando el ID
        var form = document.getElementById('formEditarMotivo');
        form.action = '/asistencia/motivos/' + id;
        
        // 2. Llenamos el input con el texto que ya tenía
        document.getElementById('input_editar_descripcion').value = descripcion;
        
        // 3. Mostramos el modal
        $('#modalEditarMotivo').modal('show');
    }
</script>
@endsection