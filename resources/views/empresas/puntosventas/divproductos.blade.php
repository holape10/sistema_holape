

  <div class="row">
    <?php $i=0; ?>
      <table id="table-presentaciones" class="table table-responsive table-striped">
         <thead >
            <th>AGREGAR</th>
             <th>PRODUCTO</th>
            <th>FACTOR</th>
            <th>U.M</th>
            <th>PRECIO 1</th>
            <th>PRECIO 2</th>
            <th>STOCK</th>
         </thead>
               <tbody>
    @foreach($productos as $producto)
      <?php $i=$i+1; ?>

    
   
          <tr>
             <td><BUTTON id='pro<?php echo $producto->IdProducto; ?>'    value='{{$producto->precio}}' onclick="ingresar_cantidad_precio_presentacion(this)" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$producto->pronom}}</td>
             <td>{{$producto->factor}}</td>
             <td>{{$producto->umenom}}</td>
             <td style="background-color:green;" ><font color="white" size='2'><strong>  {{$producto->precio}}</strong></font></td>
             <td></td>
             <td>{{$producto->stock}}</td>

                <input type="hidden" name="pro<?php echo $producto->IdProducto;?>nom" id='pro<?php echo $producto->IdProducto;?>nom' value='{{$producto->pronom}}'>
            <input type="hidden" name="pro<?php echo $producto->IdProducto;?>id" id='pro<?php echo $producto->IdProducto;?>id' value='{{$producto->IdProducto}}'>
               <input type="hidden" name="pro<?php echo $producto->IdProducto;?>unidad" id='pro<?php echo $producto->IdProducto;?>unidad' value='{{$producto->umecod}}'>
          
            <input type="hidden" name="pro<?php echo $producto->IdProducto;?>imagen" id='pro<?php echo $producto->IdProducto;?>imagen' value='{{$producto->imagenproducto}}'>


          </tr>
            
            @if($producto->precio2 > 0)
               <td><BUTTON type="button" id='pro<?php echo $producto->IdProducto; ?>' onclick="ingresar_cantidad_precio_presentacion(this)"     value='{{$producto->precio2}}' class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
               <td>{{$producto->pronom}}</td>
                 <td>{{$producto->factor}}</td>
                     <td>{{$producto->umenom}}</td>
                     <td></td>
               <td style="background-color:orange;"><font color="white" size='2'
                ><strong>  {{$producto->precio2}}</strong></font></td>
               <td>{{$producto->stock}}</td>

                <input type="hidden" name="pro<?php echo $producto->IdProducto;?>nom" id='pro<?php echo $producto->IdProducto;?>nom' value='{{$producto->pronom}}'>
                <input type="hidden" name="pro<?php echo $producto->IdProducto;?>id" id='pro<?php echo $producto->IdProducto;?>id' value='{{$producto->IdProducto}}'>
                <input type="hidden" name="pro<?php echo $producto->IdProducto;?>unidad" id='pro<?php echo $producto->IdProducto;?>unidad' value='{{$producto->umecod}}'>
                <input type="hidden" name="pro<?php echo $producto->IdProducto;?>imagen" id='pro<?php echo $producto->IdProducto;?>imagen' value='{{$producto->imagenproducto}}'>

            @endif
        
            @if($producto->precio3 > 0)
               <td><BUTTON id='pro<?php echo $producto->IdProducto; ?>' onclick="ingresar_cantidad_precio_presentacion(this)" value='{{$producto->precio3}}'     class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
               <td>{{$producto->pronom}}</td>
                 <td>{{$producto->factor}}</td>
                     <td>{{$producto->umenom}}</td>
               <td style="background-color:red;"><font color="white" size="2"><strong>P. ESPECIAL  {{$producto->precio3}}</strong></font></td>
               <td>{{$producto->stock}}</td>

                <input type="hidden" name="pro<?php echo $producto->IdProducto;?>nom" id='pro<?php echo $producto->IdProducto;?>nom' value='{{$producto->pronom}}'>
            <input type="hidden" name="pro<?php echo $producto->IdProducto;?>id" id='pro<?php echo $producto->IdProducto;?>id' value='{{$producto->IdProducto}}'>
          
            <input type="hidden" name="pro<?php echo $producto->IdProducto;?>imagen" id='pro<?php echo $producto->IdProducto;?>imagen' value='{{$producto->imagenproducto}}'>

            @endif
        

    @endforeach
     </tbody>
      </table>
  </div>
