@extends('layouts.empresas')

@section('contenido')
<style>
    /* Ajustes específicos para móviles en esta vista */
    @media (max-width: 768px) {
        .btn-action-container {
            flex-direction: column;
            gap: 10px;
        }
        .btn-action-container .btn {
            width: 100%;
            margin-right: 0 !important;
            margin-bottom: 5px;
        }
        .header-concar {
            flex-direction: column;
            text-align: center;
        }
        .header-concar .badge {
            margin-top: 10px;
        }
    }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12 col-xs-12">
            
            @if(session('success'))
                <div class="alert alert-success alert-elegant alert-dismissible fade in" style="border-left: 5px solid #28a745 !important;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check-circle"></i> ¡Éxito!</h4>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-elegant alert-dismissible fade in" style="border-left: 5px solid #dc3545 !important;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-exclamation-triangle"></i> Error</h4>
                    {{ session('error') }}
                </div>
            @endif

            <div class="box shadow-box">
                <div class="box-header custom-header header-concar" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
                    <div style="display: flex; align-items: center;">
                        <i class="fa fa-file-export fa-2x" style="color: rgba(255,255,255,0.7); margin-right: 15px;"></i>
                        <div>
                            <h3 class="box-title" style="color: white; font-weight: bold; margin: 0; font-size: 18px;">Integración Contable CONCAR</h3>
                            <small style="color: rgba(255,255,255,0.7); display: block;">Módulo de exportación directa a SQL Server y Excel</small>
                        </div>
                    </div>
                    <span class="label bg-white text-primary" style="font-size: 13px; color: #2c3e50 !important; font-weight: bold; padding: 5px 10px; border-radius: 4px;">Hola P</span>
                </div>

                <div class="box-body" style="padding: 20px 30px;">
                    <form method="POST">
                        @csrf
                        
                        <h4 style="color: #2c3e50; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-bottom: 15px; font-size: 16px;">
                            <i class="fa fa-calendar text-primary"></i> 1. Rango de Fechas a Exportar
                        </h4>
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-6 col-xs-12 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Fecha Inicial</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="date" name="fecha_inicio" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Fecha Final</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="date" name="fecha_fin" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <h4 style="color: #2c3e50; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-bottom: 15px; font-size: 16px;">
                            <i class="fa fa-building text-primary"></i> 2. Sucursal de Origen
                        </h4>
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-12 col-xs-12 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Seleccione la Empresa en Hola P</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-store"></i></span>
                                    <select name="id_empresa_negocio" class="form-control" required>
                                        <option value="" disabled selected>-- Despliegue para elegir la sucursal --</option>
                                        @foreach($empresas as $emp)
                                            @php 
                                                $codigoConcar = !empty($emp->cod_suc) ? str_pad($emp->cod_suc, 4, '0', STR_PAD_LEFT) : 'SIN CÓDIGO';
                                            @endphp
                                            <option value="{{ $emp->id_empresa_negocio }}">
                                                {{ $emp->ruc }} | {{ $emp->NomEmpresa }} ({{ $emp->nombre_comercial }}) - [CÓD. CONCAR: {{ $codigoConcar }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="text-muted" style="font-size: 11px; margin-top: 5px; display: block;">
                                    <i class="fa fa-info-circle text-info"></i> El sistema validará automáticamente que la empresa tenga su código `cod_suc` registrado.
                                </small>
                            </div>
                        </div>

                        <h4 style="color: #2c3e50; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-bottom: 15px; font-size: 16px;">
                            <i class="fa fa-cogs text-primary"></i> 3. Parámetros Contables
                        </h4>
                        <div class="row" style="margin-bottom: 30px;">
                            <div class="col-md-3 col-xs-6 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Año Ejercicio</label>
                                <select name="ejercicio" class="form-control" style="font-weight: bold;">
                                    <option value="2026" selected>2026</option>
                                    <option value="2025">2025</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xs-6 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Subdiario Ventas</label>
                                <input type="text" name="sub_ventas" class="form-control text-center" style="font-weight: bold; color: #2c3e50;" value="05" maxlength="2" required>
                            </div>
                            <div class="col-md-3 col-xs-6 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Subdiario Cobros</label>
                                <input type="text" name="sub_cobranzas" class="form-control text-center" style="font-weight: bold; color: #2c3e50;" value="03" maxlength="2" required>
                            </div>
                            <!-- NUEVO CAMPO: CORRELATIVO INICIAL -->
                            <div class="col-md-3 col-xs-6 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;">Correlativo Inicial</label>
                                <input type="number" name="correlativo_inicial" class="form-control text-center" style="font-weight: bold; color: #2c3e50;" value="1" min="1" required>
                                <small class="text-muted" style="font-size: 10px; margin-top: 2px; display: block;">Ej: 15 (Empezará en 050015)</small>
                            </div>
                        </div>

                        <div class="row" style="border-top: 1px solid #eee; padding-top: 20px;">
                            <div class="col-md-4 col-xs-12 hidden-xs" style="display: flex; align-items: center;">
                                <span class="text-muted" style="font-size: 12px;">
                                    <i class="fa fa-shield text-success"></i> Sistema Seguro Hola P
                                </span>
                            </div>
                            <div class="col-md-8 col-xs-12">
                                <div class="pull-right btn-action-container" style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                                    <a href="{{ url()->previous() }}" class="btn btn-default btn-elegant">
                                        <i class="fa fa-arrow-left"></i> Cancelar
                                    </a>
                                    
                                    <!-- NUEVO BOTÓN: Para descargar EXCEL de Cobranzas -->
                                    <button type="submit" formaction="{{ route('concar.cobranzas') }}" class="btn btn-warning btn-elegant" style="color: #333; font-weight: bold;">
                                        <i class="fa fa-money-bill"></i> Excel Cobranzas
                                    </button>

                                    <!-- Botón para descargar EXCEL de Ventas (El que ya tenías) -->
                                    <button type="submit" formaction="{{ route('concar.excel') }}" class="btn btn-success btn-elegant">
                                        <i class="fa fa-file-excel"></i> Excel Ventas
                                    </button>
                                    
                                    <!-- Botón para enviar a Base de Datos (SQL Server) -->
                                    <button type="submit" formaction="{{ route('concar.export') }}" class="btn btn-primary btn-elegant">
                                        <i class="fa fa-database"></i> Enviar a BD Directa
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
                
                <div class="box-footer" style="background-color: #f9f9f9; text-align: center; border-radius: 0 0 8px 8px;">
                    <p class="text-muted" style="margin: 0; font-size: 11px;">
                        <i class="fa fa-server"></i> Conexión transaccional y exportación masiva lista.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection