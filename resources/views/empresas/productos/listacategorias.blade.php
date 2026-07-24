<?php $i=0; ?>
  @foreach($categorias as $categoria)
  <?php $i=$i+1; ?>
    <div class="col-sm-3 col-xs-3">
    <button id='cat<?php echo $i; ?>' value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:{{$categoria->color}};width: 180px; height: 120px; border-radius:10px">
    <p><font color="white">{{$categoria->cat_nom}}</font></p>
    </button></br></br>
   </div>
  @endforeach