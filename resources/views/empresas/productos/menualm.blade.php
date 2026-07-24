  <script type="text/javascript">
  $(document).ready(function(){
    $("#btnCategorias").on("click", function(){
    $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
    $.ajax({
      type: "GET",
      dataType: 'json',
      url: "/consultarcategorias",

    }).done(function(respuesta){
      $("#detmenu").html(respuesta.vista);
    });
  })

  });

  function deleteRow(btn) {
    var row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
    calculartotal();
  };

  function mostrarobservacion(ele){

     alert( $(this).find("input.name").val());

  }

  function Calcular(ele) {

  var totigv = 0,totgrav=0 ,subtotal=0;
  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {

      var  totitemgrav=0;

        totitemgrav = $(this).find("td:eq(1) > input").val() * $(this).find("td:eq(4) > input").val();
        $(this).find("td:eq(5) > input").val(totitemgrav.toFixed(2));

   });
  calculartotal();

};


  function agregaritem(button){
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
    // var provun = $('#'+id+'vun').val();
     var imagen = $('#'+id+'imagen').val();

  $('#grdet').append("<tr><td width='1200px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
   
     $("#modal-presentaciones").modal("hide");
      calculartotal();

      $(function(){
         $('.keyboard').keyboard();
       });
  }

</script>

  <div class="row">
  	<div class="col-lg-12 col-xs-12">
    		
  	</div>
  </div><br><br>

  <div class="row">
    <?php $i=0; ?>
    @foreach($productos as $producto)
      <?php $i=$i+1; ?>

      @if($producto->cont_pre > 0)
      <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6">
          <button type="button" id='pro<?php echo $producto->IdProducto; ?>'  onclick="presentaciones({{$producto->IdProducto}})"  value='{{$producto->propun}}' style="background:{{$producto->color}}  ;width: 180px; height: 120px; border-radius:10px">
            <input type="hidden" name="pro_nom" id='pro<?php echo $producto->IdProducto;?>nom' value='{{$producto->pronom}}'>
            <input type="hidden" name="provun" id='pro<?php echo $producto->IdProducto;?>id' value='{{$producto->IdProducto}}'>
          
            <input type="hidden" name="imagen" id='pro<?php echo $producto->IdProducto;?>imagen' value='{{$producto->imagenproducto}}'>
            <p><font color="white">{{$producto->pronom}}<br> S/. {{$producto->propun}}<br>Stock: {{$producto->stock}}</font></p>
          </button><br><br>
      </div>
      @else
         <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6">
          <button type="button" id='pro<?php echo $producto->IdProducto; ?>'  onclick="agregaritem(this)"  value='{{$producto->propun}}' style="background:{{$producto->color}}  ;width: 180px; height: 120px; border-radius:10px">
            <input type="hidden" name="pro_nom" id='pro<?php echo $producto->IdProducto;?>nom' value='{{$producto->pronom}}'>
            <input type="hidden" name="provun" id='pro<?php echo $producto->IdProducto;?>id' value='{{$producto->IdProducto}}'>
          
            <input type="hidden" name="imagen" id='pro<?php echo $producto->IdProducto;?>imagen' value='{{$producto->imagenproducto}}'>
            <p><font color="white">{{$producto->pronom}}<br> S/. {{$producto->propun}}<br>Stock: {{$producto->stock}}</font></p>
          </button><br><br>
      </div>

      @endif
    @endforeach
  </div>
