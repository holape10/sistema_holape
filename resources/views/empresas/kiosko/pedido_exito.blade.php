<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Enviado - DEVSOFT</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #e6ffee; /* Fondo suave verde */
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .success-container {
            max-width: 800px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 3px solid #28a745; /* Borde verde de éxito */
        }
        .icon-check {
            font-size: 5em;
            color: #28a745; /* Verde de éxito */
            margin-bottom: 20px;
        }
        h1 {
            color: #28a745;
            font-size: 3em;
            margin-bottom: 20px;
        }
        p {
            color: #555;
            font-size: 1.6em;
            margin-bottom: 30px;
        }
        .order-number {
            font-size: 2.2em;
            font-weight: bold;
            color: #007bff; /* Azul para el número de pedido */
            margin-bottom: 40px;
        }
        .btn-new-order {
            background-color: #007bff; /* Azul para "Nuevo Pedido" */
            color: white;
            font-size: 2em;
            padding: 20px 40px;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.1s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: none;
            cursor: pointer;
        }
        .btn-new-order:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .icon-check {
                font-size: 4em;
            }
            h1 {
                font-size: 2.5em;
            }
            p {
                font-size: 1.4em;
            }
            .order-number {
                font-size: 1.8em;
            }
            .btn-new-order {
                font-size: 1.8em;
                padding: 15px 30px;
            }
        }
        @media (max-width: 480px) {
            .icon-check {
                font-size: 3.5em;
            }
            h1 {
                font-size: 2em;
            }
            p {
                font-size: 1.2em;
            }
            .order-number {
                font-size: 1.5em;
            }
            .btn-new-order {
                font-size: 1.5em;
                padding: 12px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <i class="fas fa-check-circle icon-check"></i>
        <h1>¡Pedido Enviado con Éxito!</h1>
        <p>Tu pedido ha sido recibido y está siendo procesado.</p>
        @if(isset($pedidoId))
            <div class="order-number">N° de Pedido: {{ $pedidoId }}</div>
        @endif
        <p>En breve, nuestro personal lo preparará.</p>
        <a href="{{ route('kiosko.bienvenida') }}" class="btn btn-new-order">
            <strong>HACER OTRO PEDIDO</strong>
        </a>
    </div>

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
</body>
</html>