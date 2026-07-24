<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matriz de Turnos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h2 { text-align: center; text-transform: uppercase; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #eee; font-weight: bold; }
        .text-left { text-align: left; }
        .libre { color: red; font-style: italic; }
        .turno { font-weight: bold; }
        
        /* Oculta la url e info extra al imprimir */
        @media print {
            @page { margin: 1cm; size: landscape; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <h2>Matriz de Turnos - Semana del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</h2>

    <table>
        <thead>
            <tr>
                <th class="text-left" style="width: 25%;">COLABORADOR</th>
                @foreach($fechas as $fecha)
                    <th>{{ strtoupper($fecha['dia_nombre']) }}<br>{{ $fecha['vista'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($empleados as $emp)
                <tr>
                    <td class="text-left">{{ $emp->emp_nom }} {{ $emp->emp_ape_pat }}</td>
                    
                    @foreach($fechas as $fecha)
                        @php
                            $turnoGuardadoId = isset($matriz[$emp->emp_id][$fecha['sql']]) ? $matriz[$emp->emp_id][$fecha['sql']] : null;
                            $turnoObj = $turnos->where('id', $turnoGuardadoId)->first();
                            $codigoPintar = $turnoObj ? $turnoObj->codigo : 'LIBRE';
                            $claseCSS = $turnoObj ? 'turno' : 'libre';
                        @endphp
                        <td class="{{ $claseCSS }}">{{ $codigoPintar }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <h4>Leyenda:</h4>
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            @foreach($turnos as $turno)
                <div style="border: 1px solid #ccc; padding: 5px 10px;">
                    <strong>{{ $turno->codigo }}</strong>: 
                    {{ \Carbon\Carbon::parse($turno->hora_entrada_1)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_salida_1)->format('H:i') }}
                    @if($turno->hora_entrada_2)
                        / {{ \Carbon\Carbon::parse($turno->hora_entrada_2)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_salida_2)->format('H:i') }}
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</body>
</html>