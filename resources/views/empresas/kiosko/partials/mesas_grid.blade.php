{{-- Este es el contenido de empresas/kiosko/partials/mesas_grid.blade.php --}}

@foreach ($mesas as $mesa)
    @php
        $claseSolicitud = (isset($mesa->ped_sol_cs) && $mesa->ped_sol_cs == 1) ? 'mesa-solicitud-cs' : '';
        
        // SOLUCIÓN: Buscamos en la BD el usuario que creó este pedido exacto
        $idDueñoPedido = '';
        if ($mesa->mes_est !== 'Libre' && $mesa->pedido_asociado_id) {
            $pedidoData = \DB::table('pedidos')->where('ped_id', $mesa->pedido_asociado_id)->first();
            if ($pedidoData) {
                // Reemplaza IdUsuario por el nombre real de tu columna si es diferente
                $idDueñoPedido = $pedidoData->IdUsuario ?? $pedidoData->usu_id ?? $pedidoData->user_id ?? ''; 
            }
        }
    @endphp

    <button type="button"
        style="position: relative;"
        class="btn btn-mesa-kiosko {{ $mesa->mes_est === 'Libre' ? 'libre' : ($mesa->mes_est === 'Ocupado' || $mesa->mes_est === 'Unida' ? 'ocupado' : 'cuenta') }} {{ $claseSolicitud }}"
        data-mesa-id="{{ $mesa->mes_id }}"
        data-mesa-nombre="{{ $mesa->mes_nom }}"
        data-mesa-estado="{{ $mesa->mes_est }}"
        data-pedido-id="{{ $mesa->pedido_asociado_id }}"
        data-pedido-fecha-hora="{{ $mesa->pedido_fecha_hora ? \Carbon\Carbon::parse($mesa->pedido_fecha_hora)->timestamp : '' }}"
        data-ped-tot="{{ $mesa->ped_tot }}"
        
        {{-- AHORA SÍ SE IMPRIME EL ID REAL DEL MOZO --}}
        data-usuario-id="{{ $idDueñoPedido }}">
        
        <!--<strong>{{ $mesa->mes_nom }}</strong>-->
        <strong>{!! str_replace('-', '<br>', $mesa->mes_nom) !!}</strong>
        
        @if ($mesa->mes_est !== 'Libre')
            <span class="timer" data-start-time="{{ $mesa->pedido_fecha_hora ? \Carbon\Carbon::parse($mesa->pedido_fecha_hora)->timestamp : '' }}">
                00:00:00
            </span>

            @if(isset($mesa->ped_sol_cs) && $mesa->ped_sol_cs == 1)
                <div style="position: absolute; top: 5px; right: 5px; font-size: 0.55em; background: #ffffff; color: #6f42c1; border-radius: 4px; padding: 2px 4px; font-weight: bold; border: 1px solid #6f42c1; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <i class="fa fa-hand-paper"></i> PEDIR CS
                </div>
            @endif
        @else
            <span>LIBRE</span>
        @endif
    </button>
@endforeach

<script>
    // Se asegura de que el script se ejecute después de que se cargue el contenido AJAX
    $(document).ready(function() {
        // Función para actualizar los temporizadores
        function updateTimers() {
            $('.timer').each(function() {
                const startTimeUnix = $(this).data('start-time');
                if (startTimeUnix) {
                    const currentTime = Math.floor(Date.now() / 1000); // Tiempo actual en segundos
                    const elapsedSeconds = currentTime - startTimeUnix;

                    const hours = Math.floor(elapsedSeconds / 3600);
                    const minutes = Math.floor((elapsedSeconds % 3600) / 60);
                    const seconds = elapsedSeconds % 60;

                    const formattedTime = [
                        hours.toString().padStart(2, '0'),
                        minutes.toString().padStart(2, '0'),
                        seconds.toString().padStart(2, '0')
                    ].join(':');

                    $(this).text(formattedTime);
                } else {
                    $(this).text('00:00:00'); // O algún otro indicador si no hay fecha de inicio
                }
            });
        }

        // Ejecutar la función de actualización cada segundo
        setInterval(updateTimers, 1000);

        // Llamar a updateTimers una vez al cargar el contenido (en caso de que el intervalo no se haya activado inmediatamente)
        updateTimers();
    });
</script>