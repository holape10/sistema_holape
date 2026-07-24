<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia Hola P</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="container text-center">
        <div class="card shadow p-4 mx-auto" style="max-width: 500px;">
            <h3 class="text-{{ $tipo }} mb-3">
                @if($tipo == 'success') <i class="fa fa-check-circle"></i> ¡Bienvenido! @endif
                @if($tipo == 'info') <i class="fa fa-sign-out"></i> ¡Hasta luego! @endif
                @if($tipo == 'warning') <i class="fa fa-exclamation-triangle"></i> Atención @endif
                @if($tipo == 'danger') <i class="fa fa-times-circle"></i> Error de Acceso @endif
            </h3>
            <h4>{{ $nombre_completo }}</h4>
            <div class="alert alert-{{ $tipo == 'info' ? 'primary' : ($tipo == 'danger' ? 'danger' : $tipo) }} mt-3 text-center" style="font-size: 1.1rem;">
                {{ $mensaje }}
            </div>
            <p class="text-muted mt-4">Ya puedes cerrar esta ventana de forma segura.</p>
        </div>
    </div>
</body>
</html>