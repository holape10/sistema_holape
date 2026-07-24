<div class="products-grid">
    @forelse ($productos as $producto)
        <div class="product-card 
            @if($negocio->ven_sin_sto == '0' && $producto->stock_disponible <= 0)
                out-of-stock
            @endif
            " 
             data-id="{{ $producto->IdProducto }}"
             data-name="{{ $producto->pronom }}"
             data-price="{{ $producto->precio }}"
             data-icbper="{{ $producto->icbper }}"
             data-stock="{{ $producto->stock_disponible ?? 0 }}"
             data-acompa="{{ $producto->acom ?? '0' }}">
            
            <div class="img-container">
                {{-- Verifica que $producto->imagenproducto no sea nulo y que el archivo exista --}}
                @if($producto->imagenproducto && file_exists(public_path('imagenes/productos/' . $producto->imagenproducto)))
                    <img src="{{ asset('imagenes/productos/' . $producto->imagenproducto) }}" alt="{{ $producto->pronom }}">
                @else
                    {{-- Si no hay imagen o no existe, muestra el texto "Sin Imagen" --}}
                    <span>Sin Imagen</span>
                @endif
            </div>

            <div class="product-card-body">
                <div class="product-name">{{ $producto->pronom }}</div>
                <div class="product-price">S/ {{ number_format($producto->precio, 2) }}</div>
                {{-- Modificamos la visualización del stock --}}
                <div class="stock-label">
                    @if($negocio->ven_sin_sto == '1')
                        Stock: {{ number_format($producto->stock_disponible ?? 0, 0) }}
                    @else
                        Stock: {{ $producto->stock_disponible <= 0 ? 'AGOTADO' : number_format($producto->stock_disponible, 0) }}
                    @endif
                </div>
            </div>
        </div>
    @empty
        <p class="text-center col-xs-12">No se encontraron productos.</p>
    @endforelse
</div>