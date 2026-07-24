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
     var provun = $('#'+id+'vun').val();

  $('#grdet').append("<tr><td style='width:500px;'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onchange='Calcular(this);' onkeyup='Calcular(this);' class='form-control  input-sm' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control input-sm' name='provun[]'  value='"+provun+"' readonly='readonly' style='width:130px' ></td><td><input type='number' step='any' min='0' class='form-control input-sm'  name='propun[]' onChange='Calcular(this);' onkeyup='Calcular(this);'  value='"+precio+"'  style='width:130px' ></td><td ><input type='text' class='form-control input-sm' name='itemtotal[]'  value='"+precio+"' onkeyup='CalcularItem(this);'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control input-sm' name='proid[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td>   <td hidden ='hidden' style='width:200px;'><input type='text' class='form-control input-sm ' name='detalle[]' value=''></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      calculartotal();

      $(function(){
         $('.keyboard').keyboard();
       });
  }

</script>
  <div class="row">

  	<div class="col-lg-12">
    		<button id="btnCategorias" name="btnCategorias" class="btn btn-block btn-success btn-lg" style="background:#2d572c ">CATEGORÍAS</button>
  	</div>
  </div><br><br>

  <div class="row">
    <?php $i=0; ?>
    @foreach($productos as $producto)
    <?php $i=$i+1; ?>

    <div class="col-sm-3 col-xs-3">
         <button type="button" id='pro<?php echo $producto->IdProducto; ?>' onclick="agregaritem(this)" value='{{$producto->propun}}' style="background:{{$producto->color}}  ;width: 120px; height: 120px; border-radius:10px">
      <input type="hidden" name="pro_nom" id='pro<?php echo $producto->IdProducto;?>nom' value='{{$producto->pronom}}'>
      <input type="hidden" name="provun" id='pro<?php echo $producto->IdProducto;?>id' value='{{$producto->IdProducto}}'>
     
      <p><font color="white">{{$producto->pronom}}<br> S/. {{$producto->propun}}</font></p>
      </button></br></br>
    </div>
    @endforeach

</div>
