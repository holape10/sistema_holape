<style>
    /* ========== ESTILOS PARA LISTAR MESAS ========== */
    .mesas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px;
        padding: 15px 0;
        width: 100%;
    }

    .mesa-button {
        padding: 0 !important;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px !important;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        font-size: 14px;
    }

    .mesa-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transition: left 0.3s ease;
    }

    .mesa-button:hover::before {
        left: 100%;
    }

    .mesa-button:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .mesa-button:active {
        transform: scale(0.98);
    }

    .mesa-button.ocupado {
        background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        color: white;
    }

    .mesa-button.libre {
        background: linear-gradient(135deg, #52BE80 0%, #27AE60 100%);
        color: white;
    }

    .mesa-button.reservado {
        background: linear-gradient(135deg, #F4D03F 0%, #F39C12 100%);
        color: white;
    }

    .mesa-nombre {
        font-size: 15px;
        font-weight: 700;
        display: block;
        position: relative;
        z-index: 1;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1200px) {
        .mesas-grid {
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .mesas-grid {
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 8px;
        }

        .mesa-button {
            min-height: 90px;
            font-size: 13px;
        }
    }

    @media (max-width: 576px) {
        .mesas-grid {
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        }

        .mesa-button {
            min-height: 80px;
            font-size: 12px;
        }
    }
</style>

<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(refrescar, 5000);
    });

    function refrescar(){
        var piso = $("#piso").val();
        var tipo = $("#tipo").val();

        if(tipo=='1'){
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/buscarmesas/"+piso,
            }).done(function(respuesta){
                $("#listar_mesas").html(respuesta.vista);
            });
        }
    }
</script>

<div class="mesas-grid">
    @if(!empty($mesas))
        @foreach($mesas as $mesas)
            @if($mesas->mes_est=='Ocupado')

            @php
				$pedido = DB::TABLE('pedidos')->where('mes_id',$mesas->mes_id)->where('ped_est','Aperturado')->first();
			@endphp

                @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin') || Auth::User()->hasRole('caja'))
                <a type="button" class="mesa-button ocupado" href="/cobrarmesa/{{$pedido->ped_id}}">
                    <button type="button" class="mesa-button ocupado" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
                        <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                        <span class="mesa-total">S/. {{$pedido->ped_tot}}</span>
                    </button>
                    </a>
                @else
                    <button type="button" class="mesa-button ocupado" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
                        <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                    </button>
                @endif


                <!--@if(isset($pedido->ped_id))
                                <a href="/cobrarmesa/{{$pedido->ped_id}}">
                                    <button type="button" class="mesa-button ocupado">
                                        <div class="mesa-info">
                                            <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                                            <span class="mesa-total">S/. {{$pedido->ped_tot}}</span>
                                        </div>
                                    </button>
                                </a>
                @else
                                <button type="button" class="mesa-button ocupado" disabled>
                                    <div class="mesa-info">
                                        <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                                    </div>
                                </button>
                @endif-->

            @elseif($mesas->mes_est=='Libre')
                @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin') || Auth::User()->hasRole('caja'))
                    <button type="button" class="mesa-button libre" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
                        <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                    </button>
                @else
                    <button type="button" class="mesa-button libre" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
                        <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                    </button>
                @endif

            @else
                @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin') || Auth::User()->hasRole('caja'))
                    <button type="button" class="mesa-button reservado" disabled>
                        <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                    </button>
                @else
                    <button type="button" class="mesa-button reservado" disabled>
                        <span class="mesa-nombre">{{$mesas->mes_nom}}</span>
                    </button>
                @endif
            @endif
        @endforeach
    @endif
</div>