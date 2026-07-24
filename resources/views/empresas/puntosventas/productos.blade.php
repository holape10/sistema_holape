
@foreach($productos as $pro)
<option value="{{$pro->IdProducto}}">{{$pro->pronom}}</option>
@endforeach