@extends ('layouts.empresas')
@section ('contenido')
<script>
$(document).ready(function()
{

      /*    var producto = $("#promocion").val();
                $("#catinsu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscarcategorias/"+producto,

                }).done(function(respuesta){
                $("#catinsu").html(respuesta.vista);
               
                });*/
        
        
        $("form").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        });

      $("#promocion").change(function() {
         
                var producto = $("#promocion").val();
                $("#catinsu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscarcategorias/"+producto,

                }).done(function(respuesta){
                $("#catinsu").html(respuesta.vista);
               
                });
        

        });

		 $("#txt_provun").on('keyup',function(){
            var numdoc = $('#txt_provun').val();
            $("#txt_propun").val((numdoc*1.18).toFixed(3));
        })

          $("#txt_propun").on('keyup',function(){
            var numdoc = $('#txt_propun').val();
            $("#txt_provun").val((numdoc/1.1055).toFixed(3));
        })

      

   
         $('#add').click(function() {


            // Añadir caja de texto.
            $('#detfact').append('<tr><td><input type="hidden" name="id[]" value=""><input type="text" class="form-control input-sm" name="codigobarra[]"></td><td width="10%"><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

        })

          $('#addpre').click(function() {

            // Añadir caja de texto.
            $('#detpre').append('<tr><td width="15%"><input type="hidden" name="idprod[]" value=""><select name="presentacion[]" class="form-control input-sm">@foreach($unidades as $unidad) <option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> @endforeach</select></td><td><input name="descripcion[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="factor[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="precio[]" class="form-control input-sm"></td><td width="10%"><input type="number" step="any" value="0" min="0" class="form-control input-sm" name="costo[]"></td><td><button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

        })




});

function deleteRow(btn) {

  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);

  if ($('#detFact >tbody >tr').length == 0){
    $('.alertitem').show();
  }

};

function deletepresentacion(btn) {

  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);

  if ($('#detpre >tbody >tr').length == 0){
    $('.alertitem').show();
  }

};

</script>
	<section class="content">
   
    {!!Form::open(array('url'=>'actualizarpresentaciones','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}   

 
    <div class="row">
            <div class="col-xs-12">
                <div class="box" >
                  
                            <div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>PRESENTACIONES</strong></font></center>
                        <input type="hidden" readonly="readonly" name="id" value="{{$id}}">
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <table id="detpre" class="table">
                                    <thead>
                                  
                                    <tr>
                              
                                        <th><button type="button" onClick="" name="addpre" id="addpre" class="btn btn-success btn-sm addpre"><span class="glyphicon glyphicon-plus"></span></button> PRESENTACION</th>
                                        <th>DESCRIPCION</th>
                                        <th width="10%">FACTOR</th>
                                        <th width="10%">PRECIO</th>
                                        <th width="10%">COSTO</th>
                                     
                                    </tr>
                                    </thead>

                                    <tbody id="">
                                        @foreach($presentaciones as $pre)
                                          <tr>
                                            <td width="15%">
                                              <input type="hidden" readonly="readonly" name="idprod[]" value="{{$pre->IdProducto}}">
                                              <select name="presentacion[]" class="form-control input-sm">
                                                @foreach($unidades as $unidad) 
                                                  @if($unidad->umecod == $pre->umecod)
                                                    <option selected="selected" value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> 
                                                  @else
                                                    <option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> 
                                                  @endif
                                                  
                                                @endforeach
                                              </select>
                                            </td>
                                            <td>
                                              <input name="descripcion[]" value="{{$pre->pronom}}" class="form-control input-sm">
                                            </td>
                                            <td width="10%">
                                              <input type="number" min="0" value="{{$pre->factor}}" step="any" name="factor[]" class="form-control input-sm">
                                            </td>
                                            <td width="10%">
                                              <input type="number" min="0" value="{{$pre->propun}}" step="any" name="precio[]" class="form-control input-sm">
                                            </td>
                                            <td width="10%">
                                              <input type="text" class="form-control input-sm"  value="{{$pre->costo}}" name="costo[]">
                                            </td>
                                            <td>
                                              <button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                                            </td>
                                          </tr>
                                        @endforeach
                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
             
                <div class="box-body">
                      <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
                             <div class="form-group form-group-sm">
                                <button class="btn btn-primary" type="submit">Actualizar</button>
                                <a href="/productos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
    </div>
  


</div>
  {!!Form::close()!!}
</section>



@endsection
