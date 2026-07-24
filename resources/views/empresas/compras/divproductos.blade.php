
  <div class="row">
    <?php $i=0; ?>

     <table id="table-presentaciones" class="table table-responsive table-striped">
         <thead >
            <th>AGREGAR</th>
             <th>PRODUCTO</th>
            <th>FACTOR</th>
           
            <th>PRECIO</th>
            <th>STOCK</th>
         </thead>
               <tbody>
    @foreach($productos as $producto)
      <?php $i=$i+1; ?>

    
   
          <tr>
             <td><BUTTON id='pro<?php echo $producto->IdProducto; ?>' value='{{$producto->costo}}' onclick="ingresar_cantidad_precio_presentacion(this)" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$producto->pronom}}</td>
             <td>{{$producto->factor}}</td>
             <td style="background-color:green;" ><font color="white" size='2'><strong>COSTO  {{$producto->costo}}</strong></font></td>
             <td>{{$producto->stock}}</td>

              <input type="hidden" name="pro<?php echo $producto->IdProducto;?>nom" id='pro<?php echo $producto->IdProducto;?>nom' value='{{$producto->pronom}}'>
              <input type="hidden" name="pro<?php echo $producto->IdProducto;?>id" id='pro<?php echo $producto->IdProducto;?>id' value='{{$producto->IdProducto}}'>
              <input type="hidden" name="pro<?php echo $producto->IdProducto;?>unidad" id='pro<?php echo $producto->IdProducto;?>unidad' value='{{$producto->umecod}}'>
               <input type="hidden" name="pro<?php echo $producto->IdProducto;?>lab" id='pro<?php echo $producto->IdProducto;?>lab' value='{{$producto->lab_id}}'>

          
            <input type="hidden" name="pro<?php echo $producto->IdProducto;?>imagen" id='pro<?php echo $producto->IdProducto;?>imagen' value='{{$producto->imagenproducto}}'>


          </tr>
      

    @endforeach
     </tbody>
      </table>



  </div>
