@extends('layouts.empresas')

@section('contenido')
<div class="container mt-4">
    
    {{-- Mensajes de Notificación --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Plan Contable</h2>
        <div>
            <a href="{{ route('plan-contable.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nueva Cuenta
            </a>
            <a href="{{ route('plan-contable.exportar') }}" class="btn btn-success">
                <i class="fa fa-file-excel"></i> Descargar Excel (.xls)
            </a>
            <button type="button" class="btn btn-info text-white" data-toggle="modal" data-target="#modalImportar">
                <i class="fa fa-upload"></i> Importar desde Excel
            </button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 15%;">Código</th>
                        <th>Nombre / Descripción</th>
                        <th style="width: 15%;">Tipo</th>
                        <th style="width: 10%;">Nivel</th>
                        <th style="width: 15%;">Acepta Mov.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $cuenta)
                    <tr>
                        <td><strong>{{ $cuenta->codigo }}</strong></td>
                        <td>{{ $cuenta->nombre }}</td>
                        <td><span class="badge badge-info">{{ strtoupper($cuenta->tipo) }}</span></td>
                        <td>{{ $cuenta->nivel }}</td>
                        <td>
                            @if($cuenta->acepta_movimiento)
                                <span class="text-success font-weight-bold">SÍ</span>
                            @else
                                <span class="text-muted">NO</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No hay cuentas registradas en el Plan Contable.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL PARA IMPORTACIÓN DIRECTA DE EXCEL --}}
<div class="modal fade" id="modalImportar" tabindex="-1" role="dialog" aria-labelledby="modalImportarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalImportarLabel">Migrar Plan Contable desde Excel</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('plan-contable.importar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-muted" style="font-size: 0.95em;">
                        <strong>Instrucciones del Formato:</strong><br>
                        1. El archivo debe contener las siguientes columnas ordenadas:<br>
                        <span class="badge badge-secondary">codigo</span>
                        <span class="badge badge-secondary">nombre</span>
                        <span class="badge badge-secondary">tipo</span>
                        <span class="badge badge-secondary">nivel</span>
                        <span class="badge badge-secondary">acepta_movimiento</span><br><br>
                        2. Los valores permitidos en la columna <strong>tipo</strong> son: <br>
                        <em>activo, pasivo, patrimonio, ingreso, gasto, costo</em>.<br>
                        3. En <strong>acepta_movimiento</strong>: <code>1</code> (Sí) o <code>0</code> (No).
                    </p>
                    <div class="form-group mt-3">
                        <label for="archivo_excel">Seleccione su archivo de Excel</label>
                        <input type="file" name="archivo_excel" id="archivo_excel" class="form-control-file" accept=".xls, .xlsx, .csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-info text-white">Iniciar Importación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection