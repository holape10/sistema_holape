@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid" style="padding-top: 20px;">
    <div class="row">
        @php
        $modulos = [
            ['nombre' => 'ESTACIONAMIENTO', 'icon' => 'fa-parking', 'url' => '/estacionamiento/ingreso', 'color' => '#6f42c1'], 
            ['nombre' => 'ASISTENCIA', 'icon' => 'fa-clock', 'url' => '/asistencia', 'color' => '#5a6268'],
            ['nombre' => 'CAJA', 'icon' => 'fa-money-bill-alt', 'url' => '/consolacaja', 'color' => '#007bff'], // Azul
            ['nombre' => 'COMANDAS', 'icon' => 'fa-utensils', 'url' => '/seleccion', 'color' => '#28a745'], // Verde
            ['nombre' => 'HISTORIA CLINICA', 'icon' => 'fa-stethoscope', 'url' => '/consultorio', 'color' => '#28a745'], // Verde
            ['nombre' => 'PUNTO VENTA', 'icon' => 'fa-cash-register', 'url' => '/ventacaja', 'color' => '#17a2b8'], // Celeste
            ['nombre' => 'DASHBOARD', 'icon' => 'fa-chart-line', 'url' => '/dashboard', 'color' => '#ffc107'], // Amarillo
            ['nombre' => 'PUNTO VENTA', 'icon' => 'fa-rocket', 'url' => '/pos', 'color' => '#dc3545'], // Rojo
            ['nombre' => 'CATEGORIAS', 'icon' => 'fa-th-list', 'url' => '/categorias', 'color' => '#6610f2'], // Morado
            ['nombre' => 'PRODUCTOS', 'icon' => 'fa-box-open', 'url' => '/productos', 'color' => '#fd7e14'], // Naranja
            ['nombre' => 'MALL', 'icon' => 'fa-store', 'url' => '/mall', 'color' => '#6c757d'], // Gris
            ['nombre' => 'PUNTO VENTA MASIVA', 'icon' => 'fa-cash-register', 'url' => '/ventas/masiva', 'color' => '#17a2b8'], // Celeste
            ['nombre' => 'SOPORTE', 'icon' => 'fa-headset', 'url' => '/soporte', 'color' => '#343a40'], // Negro
            ['nombre' => 'LISTAR CAJA', 'icon' => 'fa-history', 'url' => '/caja', 'color' => '#20c997'], // Turquesa
            ['nombre' => 'REPORTE VENTAS', 'icon' => 'fa-file-invoice-dollar', 'url' => '/reportes/1', 'color' => '#d81b60'], // Rosado oscuro
            ['nombre' => 'REPORTE PRODUCTOS', 'icon' => 'fa-clipboard-list', 'url' => '/reportes/6', 'color' => '#3d9970'], // Verde Oliva
            ['nombre' => 'CONTABILIDAD', 'icon' => 'fa-balance-scale', 'url' => '/asientos', 'color' => '#fd7e14'],            
            // --- NUEVOS MÓDULOS AGREGADOS ---
            ['nombre' => 'ENVÍO SUNAT', 'icon' => 'fa-cloud-upload-alt', 'url' => '/facturacionelectronica', 'color' => '#001f3f'], // Azul Marino (SUNAT Individual)
            ['nombre' => 'RESÚMENES', 'icon' => 'fa-file-archive', 'url' => '/listarresumenes', 'color' => '#85144b'], // Guinda (Resumen Diario)
            ['nombre' => 'FIDELIZACIÓN', 'icon' => 'fa-award', 'url' => '/configuracion/puntos', 'color' => '#e83e8c'], // Fucsia (Puntos)
            ['nombre' => 'USUARIOS', 'icon' => 'fa-users-cog', 'url' => '/empleado', 'color' => '#3c8dbc'], // Azul Acero (Usuarios)
            ['nombre' => 'SINCRONIZAR STOCK', 'icon' => 'fa-sync-alt', 'url' => '/mantenimiento/stock', 'color' => '#6f42c1'],
        ];
        @endphp

        @foreach($modulos as $modulo)
            <div class="col-lg-3 col-xs-6">
                <a href="{{ url($modulo['url']) }}" class="text-decoration-none">
                    <div class="small-box" style="background-color: {{ $modulo['color'] }} !important; color: white !important; border-radius: 12px; overflow: hidden; height: 160px; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: all 0.3s; margin-bottom: 20px;">
                        <div class="inner" style="padding: 15px;">
                            <i class="fas {{ $modulo['icon'] }}" style="font-size: 55px; margin-bottom: 12px; display: block; text-align: center; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);"></i>
                            <h4 style="font-weight: bold; text-align: center; margin: 0; text-transform: uppercase; font-size: 16px;">{{ $modulo['nombre'] }}</h4>
                        </div>
                        <div class="small-box-footer" style="position: absolute; bottom: 0; width: 100%; background: rgba(0,0,0,0.15); padding: 5px 0; text-align: center; font-size: 12px;">
                             Click para entrar <i class="fa fa-arrow-circle-right"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

<style>
    .small-box:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.3) !important;
        filter: brightness(1.1);
        z-index: 10;
    }
    .text-decoration-none, .text-decoration-none:hover {
        text-decoration: none;
    }
</style>
@endsection