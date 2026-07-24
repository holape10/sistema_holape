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
  //Agregar al modal filas con los elementos para registrar las guías de remisión
  $('#addgr').click(function() {

      // Añadir caja de texto.
      $('#grdet').append("<tr><td><input type='text' class='form-control' name='nombrep' readonly='readonly'></td><td> <div class='input-group'><span class='input-group-btn'><button class='btn btn-default value-control' data-action='minus' data-target='font-size'><span class='glyphicon glyphicon-minus'></span></button></span><input type='text' value='1' class='form-control' id='font-size' style='width:40px'><span class='input-group-btn'><button class='btn btn-default value-control' data-action='plus' data-target='font-size'><span class='glyphicon glyphicon-plus'></span></button></span></div> </td><td><input type='text' class='form-control' name='total' readonly='readonly'style='width:80px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
  })

  });

  function deleteRow(btn) {
    var row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
    calculartotal();
  };

  function calculartotal(){

   var totigv = 0,totgrav=0 ,subtotal=0;

    $("#grdet tbody tr").each(function(){

        totgrav = totgrav + parseFloat($(this).find("td:eq(2)  > input").val());

        subtotal = subtotal + parseFloat(($(this).find("td:eq(2) > input").val())/(1.18));

        totigv = totgrav - subtotal;

       $('#total').val(totgrav.toFixed(2));
       $('#igv').val(totigv.toFixed(2));
       $('#subtotal').val(subtotal.toFixed(2));
    })

     if ($('#grdet >tbody >tr').length == 0){
        $('#total').val('0.00');
       $('#igv').val('0.00');
       $('#subtotal').val('0.00');
     }
};

  function agregaritem(button){
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();


      $('#grdet').append("<tr><td><input type='text' class='form-control' name='nombrep' value='"+producto+"' readonly='readonly'></td><td> <div class='input-group'><span class='input-group-btn'><button class='btn btn-default value-control' data-action='minus' data-target='font-size'><span class='glyphicon glyphicon-minus'></span></button></span><input type='text' value='1' class='form-control' id='font-size' style='width:60px'><span class='input-group-btn'><button class='btn btn-default value-control' data-action='plus' data-target='font-size' style='margin-left:-66px'><span class='glyphicon glyphicon-plus'></span></button></span></div> </td><td><input type='text' class='form-control' name='total'  value='"+precio+"' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      calculartotal();
  }

  $(document).on('click','.value-control',function(){
      var action = $(this).attr('data-action')
      var target = $(this).attr('data-target')
      var value  = parseFloat($('[id="'+target+'"]').val());
      if ( action == "plus" ) {
        value++;
      }
      if ( action == "minus" ) {
        value--;
      }
      $('[id="'+target+'"]').val(value)
  })

</script>
  <div class="row">

  	<div class="col-lg-12">
    		<button id="btnCategorias" name="btnCategorias" class="btn btn-block btn-success btn-lg" style="background:#5499C7 ">CATEGORÍAS</button>
  	</div>
  </div><br><br>

  <div class="row">
    <?php $i=0; ?>
    @foreach($productos as $producto)
    <?php $i=$i+1; ?>

    <div class="col-sm-2 col-xs-4">
      <button id='pro<?php echo $i; ?>' onclick="agregaritem(this)" value='{{$producto->provun}}' style="background:#5499C7  ;width: 120px; height: 120px; border-radius:10px">
      <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
      <p>{{$producto->pronom}}</p>
      </button></br></br>
    </div>
    @endforeach

</div>
