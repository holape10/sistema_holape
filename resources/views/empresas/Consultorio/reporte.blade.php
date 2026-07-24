<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Médico - {{ $consulta->paciente->clinom ?? 'Paciente' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #fff; color: #000; font-size: 14px; }
        .ticket-header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-img { max-height: 80px; width: auto; object-fit: contain; }
        .section-title { font-weight: bold; background-color: #f2f2f2; padding: 5px; margin-top: 15px; border-left: 4px solid #0056b3; }
        .medico-firma { margin-top: 80px; border-top: 1px solid #ccc; text-align: center; padding-top: 5px; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .container { max-width: 100% !important; width: 100% !important; }
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Botones de acción -->
    <div class="row no-print mb-4">
        <div class="col-12 d-flex justify-content-between">
            <button onclick="window.close();" class="btn btn-secondary">
                <i class="fa fa-times"></i> Cerrar Ventana
            </button>
            <button onclick="window.print();" class="btn btn-success btn-lg">
                <i class="fa fa-print"></i> Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- Encabezado dinámico con Logo de tu Base de Datos -->
    <div class="row ticket-header align-items-center">
        <div class="col-3 text-center">
            @if($negocio && $negocio->logosuc && file_exists(public_path($negocio->logosuc)))
                <!-- Si logosuc tiene valor y existe el archivo en la carpeta public -->
                <img src="{{ asset($negocio->logosuc) }}" class="logo-img" alt="Logo">
            @else
                <!-- Si no hay imagen, un marcador genérico de salud o texto vacío -->
                <div class="p-2 border rounded font-weight-bold text-muted">HOLA P</div>
            @endif
        </div>
        <div class="col-9 text-right">
            <!-- Jalamos el Nombre Comercial o Razón Social según tus datos de BD -->
            <h3 class="font-weight-bold m-0" style="color: #0056b3;">
                {{ $negocio->nombre_comercial ?? ($empresa->NomEmpresa ?? 'CONSULTORIO MÉDICO') }}
            </h3>
            <p class="mb-0 text-muted">RUC: {{ $empresa->IdEmpresa ?? '-' }}</p>
            <p class="mb-0 small"><strong>Dir:</strong> {{ $negocio->direccion ?? ($empresa->DirEmpresa ?? '-') }}</p>
            <p class="mb-0 small"><strong>Ubicación:</strong> {{ $negocio->distrito ?? 'Iquitos' }} - {{ $negocio->departamento ?? 'Loreto' }}</p>
        </div>
    </div>

    <!-- Datos de la Atención -->
    <div class="row mb-4">
        <div class="col-6">
            <p class="mb-1"><strong>Paciente:</strong> {{ $consulta->paciente->clinom ?? 'No registrado' }}</p>
            <p class="mb-1"><strong>DNI/RUC:</strong> {{ $consulta->paciente->clinum ?? '-' }}</p>
            <p class="mb-1"><strong>Dirección:</strong> {{ $consulta->paciente->clidir ?? '--' }}</p>
        </div>
        <div class="col-6 text-right">
            <p class="mb-1"><strong>Fecha de Consulta:</strong> {{ \Carbon\Carbon::parse($consulta->fecha_consulta)->format('d/m/Y') }}</p>
            <p class="mb-1"><strong>Hora:</strong> {{ \Carbon\Carbon::parse($consulta->fecha_consulta)->format('h:i A') }}</p>
            <p class="mb-1"><strong>N° Registro:</strong> #{{ $consulta->id }}</p>
        </div>
    </div>

    <!-- Detalles Clínicos -->
    <div class="row">
        <div class="col-12">
            <div class="section-title">Motivo de la Consulta</div>
            <p class="p-2 bg-light rounded" style="white-space: pre-line;">{{ $consulta->motivo_consulta }}</p>

            @if($consulta->exploracion_fisica)
                <div class="section-title">Exploración Física / Evaluación</div>
                <p class="p-2 bg-light rounded" style="white-space: pre-line;">{{ $consulta->exploracion_fisica }}</p>
            @endif

            @if($consulta->diagnostico)
                <div class="section-title">Diagnóstico Médico</div>
                <p class="p-2 bg-light rounded font-weight-bold text-danger" style="white-space: pre-line;">{{ $consulta->diagnostico }}</p>
            @endif

            <div class="section-title" style="background-color: #d4edda; border-left: 4px solid #28a745;">Tratamiento e Indicaciones (Receta)</div>
            <p class="p-3 bg-white border rounded font-weight-bold" style="white-space: pre-line; min-height: 120px; font-size: 16px;">{{ $consulta->tratamiento ?? 'No registra indicaciones médicas.' }}</p>
        </div>
    </div>

    <!-- Sección de Firma -->
    <div class="row justify-content-end">
        <div class="col-4 medico-firma">
            <p class="mb-0 small">Firma y Sello del Médico</p>
        </div>
    </div>
</div>

</body>
</html>