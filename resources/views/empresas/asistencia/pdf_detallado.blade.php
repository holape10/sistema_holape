<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Detallado de Jornadas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #000; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; color: #555; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 11px; }
        td { font-size: 11px; }
        
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .tardanza { color: #d32f2f; }
        .conforme { color: #388e3c; }
        .motivo-caja { margin-top: 4px; padding: 4px; border: 1px dashed #777; background-color: #fafafa; font-size: 10px; text-align: left;}
        
        /* Ocultar botones al imprimir */
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #d9534f; color: white; border: none; font-weight: bold; cursor: pointer;">
            🖨️ Imprimir / Guardar como PDF
        </button>
    </div>

    <div class="header">
        <h2>Reporte Detallado de Tiempos y Jornadas Laborales</h2>
        <p><strong>Periodo:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>FECHA</th>
                <th>DNI</th>
                <th class="text-left">COLABORADOR</th>
                <th>TURNO</th>
                <th>TIEMPO LABORADO</th>
                <th>TARDANZA ACUMULADA</th>
                <th style="width: 25%;">JUSTIFICACIÓN</th>
                <th>ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asistencias as $asistencia)
                <tr>
                    <td class="bold">{{ \Carbon\Carbon::parse($asistencia->date)->format('d/m/Y') }}</td>
                    <td>{{ $asistencia->emp_num_doc }}</td>
                    <td class="text-left bold">{{ $asistencia->emp_nom }} {{ $asistencia->emp_ape_pat }}</td>
                    <td>{{ $asistencia->codigo ?? 'N/A' }}</td>
                    <td class="bold conforme">{{ $asistencia->tiempo_laborado }}</td>
                    
                    <td class="bold {{ $asistencia->tardanza_texto != '0 min' ? 'tardanza' : '' }}">
                        {{ $asistencia->tardanza_texto }}
                    </td>
                    
                    <td class="text-left">
                        @if(!empty($asistencia->autorizado_por))
                            <div class="motivo-caja">
                                <strong>Auth:</strong> {{ $asistencia->autorizado_por }}<br>
                                <strong>Motivo:</strong> {{ $asistencia->motivo_tardanza }}
                            </div>
                        @else
                            --
                        @endif
                    </td>
                    
                    <td class="bold">
                        {{ $asistencia->estado_jornada }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="padding: 20px;">No se encontraron registros para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        // Dispara la ventana de impresión automáticamente al abrir
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>