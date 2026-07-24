<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Asistencia</title>
    <style>
        /* Ajustes específicos para ticketeras térmicas de 80mm */
        @page { margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 13px; 
            width: 76mm; /* Margen de seguridad para 80mm */
            margin: 0 auto; 
            padding: 5mm 2mm; 
            color: #000;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 4px 0; border-bottom: 1px dashed #000; }
        .separator { margin: 10px 0; border-bottom: 1px dashed #000; }
    </style>
</head>
<body>
    <div class="text-center">
        <h2 style="margin:0;">HOLAPE</h2>
        <p style="margin: 5px 0;" class="font-bold">REPORTE DE ASISTENCIA</p>
        <p style="margin: 0;">Fecha: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
    </div>
    
    <div class="separator"></div>

    <table>
        <thead>
            <tr>
                <th class="text-left">Empleado</th>
                <th class="text-right">Ent</th>
                <th class="text-right">Sal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asistencias as $a)
            <tr>
                <td class="text-left">{{ substr($a->emp_nom, 0, 12) }}</td>
                <td class="text-right">{{ $a->check_in ? date('H:i', strtotime($a->check_in)) : '--' }}</td>
                <td class="text-right">{{ $a->check_out ? date('H:i', strtotime($a->check_out)) : '--' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator"></div>
    
    <div class="text-center" style="margin-top: 15px; font-size: 11px;">
        <p style="margin: 0;">Reporte generado automáticamente</p>
        <p style="margin: 0;">-- Fin del Reporte --</p>
    </div>

    <script>
        // Lanza la ventana de impresión nativa nada más abrirse
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>