@extends('layouts.empresas') {{-- Reemplaza 'layouts.app' por tu layout principal --}}

@section('contenido')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Crear Nueva Cuenta Contable</h4>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('plan-contable.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="codigo">Código Cuenta</label>
                        <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ej: 10411" maxlength="10" required>
                    </div>

                    <div class="col-md-8 form-group">
                        <label for="nombre">Nombre / Descripción de la Cuenta</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Banco de Crédito del Perú" maxlength="150" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="tipo">Tipo de Elemento</label>
                        <select name="tipo" id="tipo" class="form-control" required>
                            <option value="activo">Activo</option>
                            <option value="pasivo">Pasivo</option>
                            <option value="patrimonio">Patrimonio</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="gasto">Gasto</option>
                            <option value="costo">Costo</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="nivel">Nivel de Cuenta</label>
                        <select name="nivel" id="nivel" class="form-control" required>
                            <option value="1">1 (Elemento / Dígito principal)</option>
                            <option value="2">2 (Cuenta / 2 dígitos)</option>
                            <option value="3">3 (Subcuenta / 3 dígitos)</option>
                            <option value="4">4 (Registro / Detalle)</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="acepta_movimiento">¿Acepta Movimientos?</label>
                        <select name="acepta_movimiento" id="acepta_movimiento" class="form-control" required>
                            <option value="0">No (Es cuenta acumuladora / Cabecera)</option>
                            <option value="1" selected>Sí (Se usará en los asientos contables)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Guardar Cuenta</button>
                    <a href="{{ route('plan-contable.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection