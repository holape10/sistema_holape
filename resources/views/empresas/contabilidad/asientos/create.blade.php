@extends('layouts.empresas')

@section('contenido')
<div class="container">
    <h2>Nuevo Asiento Contable (Libro Diario)</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('asientos.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-3 form-group">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3 form-group">
                <label>Tipo Asiento</label>
                <select name="tipo_asiento" class="form-control">
                    <option value="diario">Diario</option>
                    <option value="apertura">Apertura</option>
                    <option value="compras">Compras</option>
                    <option value="ventas">Ventas</option>
                    <option value="caja_bancos">Caja y Bancos</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label>Glosa / Descripción</label>
                <input type="text" name="glosa" class="form-control" required>
            </div>
        </div>

        <hr>
        <h4>Detalle del Asiento</h4>
        <table class="table table-bordered" id="tabla-detalles">
            <thead>
                <tr>
                    <th>Cuenta Contable</th>
                    <th>Debe</th>
                    <th>Haber</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="detalles[0][cuenta_id]" class="form-control select2" required>
                            <option value="">Seleccione cuenta...</option>
                            @foreach($cuentas as $cuenta)
                                <option value="{{ $cuenta->id }}">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="detalles[0][debe]" class="form-control input-debe" value="0"></td>
                    <td><input type="number" step="0.01" name="detalles[0][haber]" class="form-control input-haber" value="0"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">TOTALES:</th>
                    <th><input type="text" id="total_debe" class="form-control" readonly value="0.00"></th>
                    <th><input type="text" id="total_haber" class="form-control" readonly value="0.00"></th>
                    <th><button type="button" class="btn btn-success btn-sm" id="add-row">+ Agregar Línea</button></th>
                </tr>
            </tfoot>
        </table>

        <button type="submit" class="btn btn-primary" id="btn-guardar" disabled>Guardar Asiento</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let rowIdx = 1;

        // Agregar nueva fila
        $('#add-row').click(function() {
            let rowHtml = `<tr>
                <td>
                    <select name="detalles[${rowIdx}][cuenta_id]" class="form-control" required>
                        <option value="">Seleccione cuenta...</option>
                        @foreach($cuentas as $cuenta)
                            <option value="{{ $cuenta->id }}">{{ $cuenta->codigo }} - {{ $cuenta->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" step="0.01" name="detalles[${rowIdx}][debe]" class="form-control input-debe" value="0"></td>
                <td><input type="number" step="0.01" name="detalles[${rowIdx}][haber]" class="form-control input-haber" value="0"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>`;
            $('#tabla-detalles tbody').append(rowHtml);
            rowIdx++;
        });

        // Eliminar fila
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calcularTotales();
        });

        // Calcular totales al escribir
        $(document).on('keyup change', '.input-debe, .input-haber', function() {
            calcularTotales();
        });

        function calcularTotales() {
            let totalDebe = 0;
            let totalHaber = 0;

            $('.input-debe').each(function() {
                totalDebe += parseFloat($(this).val()) || 0;
            });
            $('.input-haber').each(function() {
                totalHaber += parseFloat($(this).val()) || 0;
            });

            $('#total_debe').val(totalDebe.toFixed(2));
            $('#total_haber').val(totalHaber.toFixed(2));

            // Habilitar botón solo si cuadra y es mayor a 0
            if (totalDebe > 0 && totalDebe === totalHaber) {
                $('#btn-guardar').prop('disabled', false);
            } else {
                $('#btn-guardar').prop('disabled', true);
            }
        }
    });
</script>
@endsection