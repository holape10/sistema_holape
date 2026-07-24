{{-- Este parcial muestra las mesas libres con su respectivo Piso/Ambiente --}}
@if(isset($mesas_desocupadas) && $mesas_desocupadas->count() > 0)
    @foreach($mesas_desocupadas as $mesa)
        <option value="{{ $mesa->mes_id }}">
            {{ $mesa->pis_nom }} - {{ $mesa->mes_nom }}
        </option>
    @endforeach
@else
    <option value="">No hay mesas libres disponibles</option>
@endif