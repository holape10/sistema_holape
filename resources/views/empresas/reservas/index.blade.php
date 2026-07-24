@extends('layouts.empresas')

@section('contenido')
<style>
    /* Estilos específicos si necesitas ajustes en móvil para el filtro */
    @media (max-width: 768px) {
        .filter-container {
            flex-direction: column;
            gap: 10px;
        }
        .filter-container input, .filter-container button {
            width: 100%;
            margin: 0 !important;
        }
    }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="row">
        <div class="col-xs-12">
            <div class="box shadow-box">
                <div class="box-header custom-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
                    <h3 class="box-title" style="color: white; font-weight: bold; margin: 0;">
                        <i class="fa fa-list-alt"></i> HISTORIAL DE RESERVAS
                    </h3>
                    <a href="{{ route('reservas.create') }}" class="btn btn-success btn-sm btn-elegant pull-right" style="font-weight: bold;">
                        <i class="fa fa-plus"></i> Nueva Reserva
                    </a>
                </div>

                <div class="box-body" style="padding: 20px;">
                    
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #eaeaea; margin-bottom: 20px;">
                        <form action="{{ route('reservas.index') }}" method="GET" style="margin: 0;">
                            <div class="row filter-container" style="display: flex; align-items: center;">
                                <div class="col-md-3 col-xs-12">
                                    <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-calendar"></i> Filtrar por Fecha</label>
                                    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" required>
                                </div>
                                <div class="col-md-2 col-xs-12" style="margin-top: 22px;">
                                    <button type="submit" class="btn btn-primary btn-block btn-elegant">
                                        <i class="fa fa-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-vertical-align">
                            <thead class="custom-subheader">
                                <tr>
                                    <th style="text-align: center; width: 10%;">Hora</th>
                                    <th>Cliente</th>
                                    <th style="text-align: center;">Mesa / Zona</th>
                                    <th style="text-align: center; width: 10%;">Cant.</th>
                                    <th style="text-align: right; width: 12%;">Total</th>
                                    <th style="text-align: center; width: 15%;">Estado</th>
                                    <th style="text-align: center; width: 10%;">Ticket</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reservas as $res)
                                <tr>
                                    <td style="text-align: center; font-weight: bold; font-size: 16px; color: #2c3e50;">
                                        <i class="fa fa-clock-o text-muted"></i> {{ date('H:i', strtotime($res->hora_inicio)) }}
                                    </td>
                                    <td style="font-weight: bold;">
                                        {{ $res->clinom }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="label bg-gray" style="font-size: 12px;">{{ $res->mes_nom }}</span><br>
                                        <small class="text-muted">{{ $res->pis_nom }}</small>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-weight: bold; font-size: 14px;"><i class="fa fa-users text-muted"></i> {{ $res->cantidad_personas }}</span>
                                    </td>
                                    <td style="text-align: right; font-weight: bold; color: #27ae60;">
                                        S/ {{ number_format($res->total, 2) }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($res->estado == 'Confirmada')
                                            <span class="label label-success" style="font-size: 12px; padding: 5px 10px;"><i class="fa fa-check"></i> {{ $res->estado }}</span>
                                        @elseif($res->estado == 'Pendiente')
                                            <span class="label label-warning" style="font-size: 12px; padding: 5px 10px;"><i class="fa fa-clock-o"></i> {{ $res->estado }}</span>
                                        @else
                                            <span class="label label-danger" style="font-size: 12px; padding: 5px 10px;"><i class="fa fa-times"></i> {{ $res->estado }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('reservas.ticket', $res->res_id) }}" target="_blank" class="btn btn-info btn-sm btn-elegant" title="Imprimir Ticket">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px;">
                                        <i class="fa fa-calendar-times-o fa-3x text-muted" style="margin-bottom: 10px; display: block;"></i>
                                        <span class="text-muted" style="font-size: 16px;">No hay reservas registradas para esta fecha.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection