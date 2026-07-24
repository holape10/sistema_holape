{{-- Este parcial solo debe contener las opciones para el select de mesas para UNIR MESA (MOVER PEDIDO) --}}
@if($mesas_para_unir->count() > 0)
    @foreach($mesas_para_unir as $mesa)
        <option value="{{ $mesa->mes_id }}">{{ $mesa->mes_nom }}</option>
    @endforeach
@else
    <option value="">No hay mesas libres para mover el pedido</option>
@endif