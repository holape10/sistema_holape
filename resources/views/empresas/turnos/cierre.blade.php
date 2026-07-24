<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-cerrar">
    {!!Form::open(array('url'=>'/cerrarturno','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'formcerrarturno','name'=>'formcerrarturno', 'target'=>'_blank', 'onsubmit'=>'setTimeout(function(){ window.location.href="/consolacaja"; }, 1000);'))!!}
    {{Form::token()}}
    
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <!-- HEADER MEJORADO -->
            <div class="modal-header bg-primary text-white" style="border-radius: 5px 5px 0 0;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-lock"></i> Cerrar Turno Caja
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- BODY MEJORADO -->
            <div class="modal-body">
                
                <!-- INFO BOX -->
                <!--<div class="alert alert-info" style="border-left: 5px solid #17a2b8;">
                    <i class="fa fa-info-circle fa-lg mr-2"></i>
                    <strong>Ingrese los montos de su cuadre físico</strong>
                    <br><small>Complete las cantidades en soles (S/) correspondientes a cada denominación.</small>
                </div>-->

                <!-- PAYMENT ITEMS CONTAINER (Lo mantengo oculto si lo usas por JS interno) -->
                <div class="payment-items-container" hidden="hidden">
                    @forelse($medios as $medio)
                        <div class="payment-item">
                            <label><i class="fa fa-credit-card"></i> {{ $medio->nom_med_pag }}</label>
                            <input type="hidden" name="txtMedPago[]" value="{{ $medio->id_med_pag }}">
                            <input type="number" name="txtMonto[]" step="0.01" class="form-control" value="0" min="0">
                        </div>
                    @empty
                        <div class="text-center p-3 text-muted">
                            <i class="fa fa-inbox"></i> No hay medios de pago configurados
                        </div>
                    @endforelse
                </div>

                <!-- INICIO DEL DESGLOSE DE EFECTIVO -->
                <div class="arqueo-efectivo-container mt-3" style="padding: 10px;">
                    <h4 class="text-center font-weight-bold mb-4" style="color: #2c3e50; margin-bottom: 25px;">
                        <i class="fa fa-money"></i> Desglose de Efectivo Físico
                    </h4>
                    
                    <div class="row">
                        <!-- Columna Monedas -->
                        <div class="col-md-6">
                            <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; background-color: #fcfcfc; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                <h5 style="border-bottom: 2px solid #6c757d; padding-bottom: 10px; margin-bottom: 20px; color: #495057; font-weight: bold;">
                                    <i class="fa fa-circle"></i> Monedas
                                </h5>
                                
                                @php
                                    $monedas = [
                                        'cant_m_10_centimos' => '0.10', 'cant_m_20_centimos' => '0.20', 
                                        'cant_m_50_centimos' => '0.50', 'cant_m_1_sol' => '1.00', 
                                        'cant_m_2_soles' => '2.00', 'cant_m_5_soles' => '5.00'
                                    ];
                                @endphp

                                @foreach($monedas as $name => $valor)
                                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                    <label style="flex: 1; margin: 0; font-weight: 600; color: #555; font-size: 14px;">Monedas de S/ {{ $valor }}</label>
                                    <div style="flex: 1; display: flex;">
                                        <span style="background: #e9ecef; border: 1px solid #ced4da; border-right: none; padding: 6px 12px; border-radius: 4px 0 0 4px; color: #495057;">Cant.</span>
                                        <input type="number" name="{{ $name }}" step="1" class="form-control" value="0" min="0" style="border-radius: 0 4px 4px 0; text-align: right; font-weight: bold; color: #333;">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Columna Billetes -->
                        <div class="col-md-6">
                            <div style="border: 1px solid #c3e6cb; border-radius: 8px; padding: 15px; background-color: #f8fff9; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                <h5 style="border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 20px; color: #28a745; font-weight: bold;">
                                    <i class="fa fa-money"></i> Billetes
                                </h5>

                                @php
                                    $billetes = [
                                        'cant_c_10_soles' => '10.00', 'cant_c_20_soles' => '20.00', 
                                        'cant_c_50_soles' => '50.00', 'cant_c_100_soles' => '100.00', 
                                        'cant_c_200_soles' => '200.00'
                                    ];
                                @endphp

                                @foreach($billetes as $name => $valor)
                                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                                    <label style="flex: 1; margin: 0; font-weight: 600; color: #155724; font-size: 14px;">Billetes de S/ {{ $valor }}</label>
                                    <div style="flex: 1; display: flex;">
                                        <span style="background: #d4edda; border: 1px solid #c3e6cb; border-right: none; padding: 6px 12px; border-radius: 4px 0 0 4px; color: #155724; font-weight: bold;">Cant.</span>
                                        <input type="number" name="{{ $name }}" step="1" class="form-control" value="0" min="0" style="border-radius: 0 4px 4px 0; text-align: right; border-color: #c3e6cb; font-weight: bold; color: #155724;">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- FIN DEL DESGLOSE DE EFECTIVO -->

            </div>

            <!-- FOOTER MEJORADO -->
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check-circle"></i> Confirmar
                </button>
            </div>

        </div>
    </div>
    
    {{Form::Close()}}
</div>