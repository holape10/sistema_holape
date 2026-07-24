@php
    $sucursales = DB::table('empresa_negocios')->get();  
    $empresa = DB::table('empresa')->first();
    $ocultar_sin_stock = false;
@endphp

@if($productos->isEmpty())
    <p>No se encontraron productos para esta categoría o búsqueda.</p>
@else
    @foreach($productos as $p)
        @php
            $stockReal = 0;
            if ($p->promocion == '2') {
                $stockReal = ($p->stock_preparados ?? 0);
            } elseif ($p->promocion == '0') {
                // AQUÍ: cambiamos ->value('cantidad') por ->value('stock')
                $stockReal = DB::table('producto_stock')->where('IdProducto', $p->IdProducto)->value('stock') ?? 0;
            }
            
            $esAgotado = ($negocio->ven_sin_sto == '0' && $stockReal <= 0);

            if ($ocultar_sin_stock && $stockReal <= 0) {
                continue;
            }
        @endphp

        <div class="product-item-kiosko {{ $esAgotado ? 'agotado' : '' }}"
             data-id="{{ $p->IdProducto }}"
             data-name="{{ $p->pronom }}"
             data-price="{{ $p->precio }}"
             data-icbper="{{ $p->icbper }}"
             data-stock="{{ $stockReal }}"
             data-acompa="{{ $p->acom }}"
             data-tiene-entrada="{{ $p->tiene_entrada ?? 0 }}"
             data-promocion="{{ $p->promocion ?? 0 }}"> 
      
            <div class="product-details-kiosko">
                <div class="product-name-kiosko">{{ $p->pronom }}</div>
                
                <div class="product-price-kiosko">S/. {{ number_format($p->precio, 2) }}</div>
                
                <div class="product-stock-kiosko">
                    Stock: {{ $esAgotado ? 'AGOTADO' : ($stockReal > 0 ? floatval($stockReal) : '0') }}
                </div>
            </div>
        </div>
    @endforeach
@endif