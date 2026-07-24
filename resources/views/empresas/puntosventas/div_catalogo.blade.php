@foreach($catalogo as $cata)
<tr>
	<td style="font-weight:bold;">{{$cata->pronom}}</td>
	<td style="font-weight:bold;">{{$cata->precio}}</td>
	<td style="font-weight:bold;">{{$cata->stock}}</td>
	<td style="font-weight:bold;"><img src="/imagenes/productos/{{$cata->imagenproducto}}" width="200px" height="200px"  onmouseover="this.width=700;this.height=700;" onmouseout="this.width=200;this.height=200;" ></td>
</tr>
@endforeach