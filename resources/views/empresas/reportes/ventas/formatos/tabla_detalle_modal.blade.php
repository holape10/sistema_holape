@if(count($comprobantes) > 0)
    <div class="accordion" id="acordeonComprobantes">
        @foreach($comprobantes as $index => $comp)
            @php
                // Mapeo rápido para saber qué tipo de documento es
                $tipoDoc = 'DOC';
                if($comp->tdocod == '01') $tipoDoc = 'FACTURA';
                if($comp->tdocod == '03') $tipoDoc = 'BOLETA';
                if($comp->tdocod == '13') $tipoDoc = 'NOTA DE VENTA';
            @endphp
            
            <div class="card mb-2" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                <div class="card-header bg-light d-flex justify-content-between align-items-center" 
                     id="heading{{ $index }}" 
                     data-toggle="collapse" 
                     data-target="#collapse{{ $index }}" 
                     style="cursor:pointer; padding: 12px 15px;">
                    <div>
                        <span class="badge badge-secondary" style="font-size: 10px;">{{ $tipoDoc }}</span>
                        <strong class="d-block text-primary" style="font-size: 15px;">
                            {{ $comp->serdoc }}-{{ $comp->numdoc }}
                        </strong> 
                        <small class="text-dark d-block mt-1">
                            <strong>Cliente:</strong> {{ $comp->ccandi }} - {{ $comp->ccanom }}
                        </small>
                        <small class="text-muted d-block mt-1">
                            <i class="fa fa-user"></i> <strong>Mozo:</strong> {{ trim($comp->mozo_nombre) != '' ? $comp->mozo_nombre : 'Sin Mozo' }} | 
                            <i class="fa fa-map-marker"></i> <strong>Zona:</strong> {{ $comp->pis_nom ?? 'N/A' }}
                        </small>
                    </div>
                    <div class="text-right">
                        <span class="badge {{ empty($comp->mes_id) ? 'badge-danger' : 'badge-success' }} mb-1" style="font-size: 11px;">
                            {{ $comp->tipo_consumo }}
                        </span>
                        <h4 class="m-0 text-dark font-weight-bold" style="font-size: 16px;">
                            S/ {{ number_format($comp->total, 2) }}
                        </h4>
                    </div>
                </div>

                <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#acordeonComprobantes">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover m-0" style="font-size: 13px;">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th class="text-center" width="10%">Cant</th>
                                        <th width="55%">Producto</th>
                                        <th class="text-right" width="15%">P. Unit</th>
                                        <th class="text-right" width="20%">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comp->detalles as $det)
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ number_format($det->cdecan, 2) }}</td>
                                        <td>{{ $det->cdedes }}</td>
                                        <td class="text-right">S/ {{ number_format($det->cdepuni, 2) }}</td>
                                        <td class="text-right font-weight-bold">S/ {{ number_format($det->cdevve, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 bg-light text-right text-muted" style="font-size: 11px; border-top: 1px solid #edf2f7;">
                            <i class="fa fa-laptop"></i> <strong>Cajero:</strong> {{ trim($comp->cajero_nombre) != '' ? $comp->cajero_nombre : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-warning text-center m-0">
        <i class="fa fa-exclamation-circle"></i> No se encontraron registros detallados para esta hora.
    </div>
@endif