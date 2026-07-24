@extends('layouts.empresas')
@section('contenido')

<style>
/* Estilos modernos para el dashboard */
.modern-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    color: white;
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.modern-card.card-1 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.modern-card.card-2 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.modern-card.card-3 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.modern-card.card-4 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
.modern-card.card-5 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

.modern-title {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
    opacity: 0.9;
}

.modern-value {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
}

.chart-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 20px;
    min-height: 450px;
}

.chart-title {
    font-size: 18px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 15px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
}

.table-modern {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    overflow: hidden;
}

.table-modern .table {
    margin-bottom: 0;
}

.table-modern thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-modern tbody tr:hover {
    background-color: #f7fafc;
}

.alert-modern {
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
}

.filter-panel {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 25px;
}

.btn-modern {
    border-radius: 25px;
    padding: 8px 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.icon-large {
    font-size: 2.5rem;
    opacity: 0.7;
}

@media (max-width: 768px) {
    .modern-value { font-size: 20px; }
    .modern-title { font-size: 10px; }
}
</style>

<section class="content" style="background-color: #f8f9fa; min-height: 100vh; padding: 20px 0;">

    {!! Form::open(array('url'=>'/dashboard','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}

    <div class="row">
        <div class="col-lg-12">
            <div class="filter-panel">
                <h4 style="color: #2d3748; margin-bottom: 20px; font-weight: 600;">
                    <i class="fa fa-filter"></i> Filtrar Datos Históricos
                </h4>
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #4a5568;">Establecimiento</label>
                            <select class="form-control" style="border-radius: 10px;">
                                @foreach($negocios as $neg)
                                    <option value="{{$neg->id_empresa_negocio}}">{{$neg->tipo_negocio}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #4a5568;">Fecha Del</label>
                            <input class="form-control" type="date" name="fec_ini" value="{{$fec_ini}}" style="border-radius: 10px;">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #4a5568;">Fecha Al</label>
                            <input class="form-control" type="date" name="fec_fin" value="{{$fec_fin}}" style="border-radius: 10px;">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label style="color: transparent;">.</label><br>
                            <button type="submit" class="btn btn-success btn-modern">
                                <i class="fa fa-search"></i> Consultar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{Form::close()}}

    <!-- Cards de métricas principales -->
    <div class="row">
        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-4">
        <a href="{{ route('dashboard.documentos.lista', [
            'tdocod_filter' => '13',
            'fec_ini' => $fec_ini ?? '', // Si $fec_ini no existe, usa ''
            'fec_fin' => $fec_fin ?? ''  // Si $fec_fin no existe, usa ''
        ]) }}">
            <div class="modern-card card-1">
                <div class="panel-body text-center" style="padding: 20px; position: relative;">
                    <div class="modern-title">Notas de Venta</div>
                    <div class="modern-value">S/. {{number_format($tot_not_vent,'2','.','')}}</div>
                    <i class="fa fa-file-text icon-large" style="position: absolute; right: 15px; bottom: 15px;"></i>
                </div>
            </div>
        </a>
    </div>

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-4">
        <a href="{{ route('dashboard.documentos.lista', [
            'tdocod_filter' => '01',
            'fec_ini' => $fec_ini ?? '',
            'fec_fin' => $fec_fin ?? ''
        ]) }}">
            <div class="modern-card card-2">
                <div class="panel-body text-center" style="padding: 20px; position: relative;">
                    <div class="modern-title">Facturas</div>
                    <div class="modern-value">S/. {{number_format($tot_fac_vent,'2','.','')}}</div>
                    <i class="fa fa-file-invoice icon-large" style="position: absolute; right: 15px; bottom: 15px;"></i>
                </div>
            </div>
        </a>
    </div>

        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-4">
        <a href="{{ route('dashboard.documentos.lista', [
            'tdocod_filter' => '03',
            'fec_ini' => $fec_ini ?? '',
            'fec_fin' => $fec_fin ?? ''
        ]) }}">
            <div class="modern-card card-3">
                <div class="panel-body text-center" style="padding: 20px; position: relative;">
                    <div class="modern-title">Boletas</div>
                    <div class="modern-value">S/. {{number_format($tot_bol_vent,'2','.','')}}</div>
                    <i class="fa fa-receipt icon-large" style="position: absolute; right: 15px; bottom: 15px;"></i>
                </div>
            </div>
        </a>
    </div>

        

        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
        <a href="{{ route('dashboard.documentos.lista', [
            'tdocod_filter' => 'ALL',
            'fec_ini' => $fec_ini ?? '',
            'fec_fin' => $fec_fin ?? ''
        ]) }}">
            <div class="modern-card card-4">
                <div class="panel-body text-center" style="padding: 20px; position: relative;">
                    <div class="modern-title">Total Ventas</div>
                    <div class="modern-value">S/. {{number_format($tot_not_vent+$tot_fac_vent+$tot_bol_vent,'2','.','')}}</div>
                    <i class="fa fa-shopping-cart icon-large" style="position: absolute; right: 15px; bottom: 15px;"></i>
                </div>
            </div>
        </a>
    </div>


        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="modern-card card-5">
                <div class="panel-body text-center" style="padding: 20px; position: relative;">
                    <div class="modern-title">Utilidad</div>
                    <div class="modern-value">S/. {{number_format(($tot_not_vent+$tot_fac_vent+$tot_bol_vent)-$tot_costo,'2','.','')}}</div>
                    <i class="fa fa-chart-line icon-large" style="position: absolute; right: 15px; bottom: 15px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos principales -->
    <div class="row">
        <!-- Gráfico de ventas por día -->
        <div class="col-lg-8">
            <div class="chart-container">
                <div class="chart-title">
                    <i class="fa fa-chart-bar" style="color: #667eea;"></i> Ventas por Día
                </div>
                <div style="position: relative; height: 350px;">
                    <canvas id="ventasDiariasChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Productos por agotarse -->
        <div class="col-lg-4">
            <div class="table-modern">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px;">
                    <h5 style="margin: 0; font-weight: 600;">
                        <i class="fa fa-exclamation-triangle"></i> Productos por Agotarse
                    </h5>
                </div>
                <div style="max-height: 400px; overflow-y: auto; padding: 15px;">
                    @foreach($prod_stock as $ps)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #e2e8f0;">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #2d3748; font-size: 13px;">{{$ps->pronom}}</div>
                            <div style="color: #718096; font-size: 12px;">Stock: {{$ps->stock}}</div>
                        </div>
                        <div style="text-align: center;">
                            @if($ps->stock <= 0)
                                <span class="badge" style="background: #e53e3e; color: white; padding: 5px 10px; border-radius: 15px; font-size: 10px;">AGOTADO</span>
                            @else
                                <span class="badge" style="background: #ed8936; color: white; padding: 5px 10px; border-radius: 15px; font-size: 10px;">POCAS ({{$ps->stock}})</span>
                            @endif
                        </div>
                        <div style="margin-left: 10px;">
                            <a href="/compra/crear" style="text-decoration: none;">
                                <i class="fa fa-plus-circle" style="color: #48bb78; font-size: 20px;"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de productos por vencer -->
    @if(count($expiringProducts) > 0)
    <div class="row">
        <div class="col-lg-12">
            <div class="table-modern" style="margin-bottom: 25px;">
                <div style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; padding: 20px;">
                    <h4 style="margin: 0; font-weight: 600;">
                        <i class="fa fa-exclamation-triangle"></i> Alertas de Productos por Vencer 
                        <span style="background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 14px; margin-left: 10px;">
                            {{ count($expiringProducts) }} productos
                        </span>
                    </h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" style="margin-bottom: 0;">
                        <thead style="background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%); color: white;">
                            <tr>
                                <th style="padding: 15px; font-weight: 600; border: none;">Producto</th>
                                <th style="padding: 15px; font-weight: 600; border: none; text-align: center;">Lote</th>
                                <th style="padding: 15px; font-weight: 600; border: none; text-align: center;">Vencimiento</th>
                                <th style="padding: 15px; font-weight: 600; border: none; text-align: center;">Días Restantes</th>
                                <th style="padding: 15px; font-weight: 600; border: none; text-align: center;">Cantidad</th>
                                <th style="padding: 15px; font-weight: 600; border: none; text-align: center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringProducts as $product)
                                @php
                                    $daysLeft = Carbon\Carbon::now()->diffInDays($product->vencimiento, false);
                                    if ($daysLeft <= 0) {
                                        $rowClass = 'style="background-color: #fed7d7; border-left: 4px solid #e53e3e;"';
                                        $badgeClass = 'background: #e53e3e; color: white;';
                                    } elseif ($daysLeft <= 15) {
                                        $rowClass = 'style="background-color: #fef5e7; border-left: 4px solid #ed8936;"';
                                        $badgeClass = 'background: #ed8936; color: white;';
                                    } elseif ($daysLeft <= 30) {
                                        $rowClass = 'style="background-color: #e6fffa; border-left: 4px solid #38b2ac;"';
                                        $badgeClass = 'background: #38b2ac; color: white;';
                                    } else {
                                        $rowClass = 'style="background-color: #f0fff4; border-left: 4px solid #48bb78;"';
                                        $badgeClass = 'background: #48bb78; color: white;';
                                    }
                                @endphp
                                <tr {!! $rowClass !!}>
                                    <td style="padding: 15px; font-weight: 600; color: #2d3748; font-size: 14px; border: none;">
                                        {{ $product->pronom }}
                                    </td>
                                    <td style="padding: 15px; text-align: center; color: #4a5568; font-weight: 500; border: none;">
                                        {{ $product->lote }}
                                    </td>
                                    <td style="padding: 15px; text-align: center; color: #4a5568; font-weight: 500; border: none;">
                                        {{ \Carbon\Carbon::parse($product->vencimiento)->format('d/m/Y') }}
                                    </td>
                                    <td style="padding: 15px; text-align: center; color: #2d3748; font-weight: 600; border: none;">
                                        @if($daysLeft <= 0)
                                            <span style="color: #e53e3e; font-weight: 700;">VENCIDO</span>
                                        @else
                                            {{ $daysLeft }} días
                                        @endif
                                    </td>
                                    <td style="padding: 15px; text-align: center; color: #4a5568; font-weight: 500; border: none;">
                                        {{ $product->cantidad }}
                                    </td>
                                    <td style="padding: 15px; text-align: center; border: none;">
                                        <span style="{{ $badgeClass }} padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                            {{ $product->expiration_status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top clientes y productos -->
    <div class="row">
        <div class="col-lg-6">
            <div class="table-modern">
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 15px;">
                    <h5 style="margin: 0; font-weight: 600;">
                        <i class="fa fa-users"></i> Top Clientes
                    </h5>
                </div>
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Transacciones</th>
                                <th>Total (S/)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($top_cli as $tc)
                            <tr>
                                <td style="font-weight: 600;">{{$tc->ccanom}}</td>
                                <td style="text-align: center;">{{$tc->transacciones}}</td>
                                <td style="text-align: right; font-weight: 600; color: #48bb78;">{{$tc->total}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="table-modern">
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 15px;">
                    <h5 style="margin: 0; font-weight: 600;">
                        <i class="fa fa-star"></i> Top Productos
                    </h5>
                </div>
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Movimientos</th>
                                <th>Total (S/)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($top_pro as $tp)
                            <tr>
                                <td>{{$tp->codigo_barra}}</td>
                                <td style="font-weight: 600;">{{$tp->pronom}}</td>
                                <td style="text-align: center;">{{$tp->movimientos}}</td>
                                <td style="text-align: right; font-weight: 600; color: #48bb78;">{{$tp->total}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Obtener los datos de ventas diarias
    let ventasDiarias = @json($vent_dia ?? []);
    
    console.log('Datos recibidos:', ventasDiarias);
    
    const ctx = document.getElementById('ventasDiariasChart').getContext('2d');
    
    // Si no hay datos, mostrar un gráfico vacío con mensaje
    if (!ventasDiarias || ventasDiarias.length === 0) {
        console.log('No hay datos de ventas');
        
        // Crear gráfico vacío
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sin datos'],
                datasets: [{
                    label: 'Ventas Diarias (S/)',
                    data: [0],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        
        // Mostrar mensaje sobre el canvas
        ctx.save();
        ctx.fillStyle = '#666';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('No hay datos de ventas para mostrar', ctx.canvas.width / 2, ctx.canvas.height / 2);
        ctx.restore();
        
        return;
    }
    
    // Procesar los datos CON LA CORRECCIÓN DE FECHAS
    const labels = [];
    const datos = [];
    
    ventasDiarias.forEach(function(venta) {
        // CORRECCIÓN: Parseamos la fecha manualmente para evitar problemas de zona horaria
        const fechaParts = venta.dia.split('-'); // Dividir 'YYYY-MM-DD'
        const año = parseInt(fechaParts[0]);
        const mes = parseInt(fechaParts[1]) - 1; // JavaScript cuenta meses desde 0
        const dia = parseInt(fechaParts[2]);
        
        // Crear fecha usando el constructor con parámetros específicos
        const fecha = new Date(año, mes, dia);
        
        // Formatear fecha para mostrar
        const opciones = { 
            weekday: 'short', 
            day: '2-digit', 
            month: '2-digit' 
        };
        const fechaFormateada = fecha.toLocaleDateString('es-ES', opciones);
        
        labels.push(fechaFormateada);
        datos.push(parseFloat(venta.total) || 0);
    });
    
    console.log('Labels corregidos:', labels);
    console.log('Datos:', datos);
    
    // Crear el gráfico
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas Diarias (S/)',
                data: datos,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: '#667eea',
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return 'S/ ' + context.parsed.y.toLocaleString('es-ES', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderColor: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderColor: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        callback: function(value) {
                            return 'S/ ' + value.toLocaleString('es-ES');
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    console.log('Gráfico creado exitosamente con fechas corregidas');
});
</script>

@endsection