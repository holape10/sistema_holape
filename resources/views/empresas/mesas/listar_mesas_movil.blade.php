<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(refrescar, 5000);
    });

    function refrescar(){
        var piso = $("#piso").val();
        var tipo = $("#tipo").val(); // Asegúrate de que el campo #tipo esté presente en el DOM principal

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

<?php $i=0; ?>
@if(!empty($mesas))
    @foreach($mesas as $mesa_item) {{-- Renombrado a $mesa_item para evitar conflicto con la colección $mesas --}}
        <?php $i=$i+1; ?>
        {{-- Aquí aplicamos las mismas clases de Bootstrap y los estilos definidos en consolamobil.blade.php --}}
        <div class="col-xs-3 mesa-container"> {{-- col-xs-3 para 4 mesas por fila en móviles --}}
            @if($mesa_item->mes_est=='Ocupado')
                <button type="button" class="btn btn-mesa ocupado" onclick="elegir_mesa('{{$mesa_item->mes_nom}}','{{$mesa_item->mes_id}}','{{$mesa_item->mes_nom}}')">
                    <strong>{{$mesa_item->mes_nom}}</strong>
                </button>
            @elseif($mesa_item->mes_est=='Libre')
                <button type="button" class="btn btn-mesa libre" onclick="elegir_mesa('{{$mesa_item->mes_nom}}','{{$mesa_item->mes_id}}','{{$mesa_item->mes_nom}}')">
                    <strong>{{$mesa_item->mes_nom}}</strong>
                </button>
            @else {{-- Si es 'Cuenta' o cualquier otro estado no definido explícitamente --}}
                <button type="button" class="btn btn-mesa cuenta" onclick="elegir_mesa('{{$mesa_item->mes_nom}}','{{$mesa_item->mes_id}}','{{$mesa_item->mes_nom}}')">
                    <strong>{{$mesa_item->mes_nom}}</strong>
                </button>
            @endif
        </div>
    @endforeach
@endif