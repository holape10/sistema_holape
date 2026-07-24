<?php $i=0; ?>
  @foreach($categorias as $categoria)
  <?php $i=$i+1; ?>
   <div class="col-lg-3 col-md-3 col-sm-2 col-xs-4">
    <button id='cat<?php echo $i; ?>' value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:{{$categoria->color}};width: 120px; height: 120px; border-radius:10px">
    <p><strong><font color="white">{{$categoria->cat_nom}}</font></strong></p>
    </button></br></br>
   </div>
   <!--#5499C7-->
  @endforeach