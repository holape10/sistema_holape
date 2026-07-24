@if(empty($cart))
    <p class="text-center text-muted">El carrito está vacío. ¡Empieza a agregar productos!</p>
@else
    @foreach($cart as $item)
        <div class="cart-item"
             data-id="{{ $item['id'] }}"
             data-is-old-item="{{ $item['is_old_item'] ? 'true' : 'false' }}"
             data-pagado="{{ !empty($item['pagado']) ? '1' : '0' }}">

            <span class="cart-item-name">
                {{ $item['nombre'] }}
                @if(!empty($item['entrada']))
                    <br>
                    <small style="color: #28a745; font-weight: bold; font-size: 0.85em;">
                        <i class="fas fa-utensils"></i> Entrada: {{ $item['entrada'] }}
                    </small>
                @endif
            </span>

            <div class="cart-item-details">
                <div class="cart-item-qty-control">
                    <button type="button" class="btn btn-qty btn-qty-minus">-</button>
                    <input type="number"
                           class="cart-item-qty"
                           value="{{ $item['cantidad'] }}"
                           min="0.01"
                            step="any"
                           data-stock="{{ $item['stock'] }}"
                           data-original-qty="{{ $item['is_old_item'] ? $item['cantidad'] : 0 }}"
                           readonly>
                    <button type="button" class="btn btn-qty btn-qty-plus">+</button>
                </div>

                <input type="number"
                       class="form-control cart-item-unit-price"
                       value="{{ number_format($item['precio'], 2, '.', '') }}"
                       min="0.01"
                       step="0.01"
                       data-icbper-applies="{{ $item['icbper'] }}"
                       {{ Auth::user()->hasRole('admin') ? '' : 'readonly' }}>

                <span class="cart-item-total-price">
                    S/. {{ number_format(
                        $item['cantidad'] * $item['precio'] + ($item['icbper'] == 1 ? $item['cantidad'] * $icbper_val : 0),
                        2
                    ) }}
                </span>
            </div>

            @php
                $show_remove_button = !$item['is_old_item'] || Auth::user()->hasRole('admin');
            @endphp

            @if($item['is_old_item'] && Auth::user()->hasRole('admin'))
                <button type="button" class="btn btn-info btn-reimprimir-item" style="margin-right: 5px; color: white; background-color: #17a2b8; border: none; padding: 5px 10px; border-radius: 5px; align-self: center;" title="Reimprimir ticket">
                    <i class="fas fa-print"></i>
                </button>
            @endif

            @if($show_remove_button)
                {{-- Sin clase disabled-old-item, ya no la necesitamos --}}
                <button type="button" class="cart-item-remove">
                    <i class="fas fa-trash-alt"></i>
                </button>
            @endif

            @if(!empty($item['observaciones']))
                <textarea class="form-control cart-item-obs" placeholder="Observaciones..." rows="1">{{ $item['observaciones'] }}</textarea>
            @else
                <textarea class="form-control cart-item-obs" placeholder="Observaciones..." rows="1"></textarea>
            @endif
        </div>
    @endforeach
@endif