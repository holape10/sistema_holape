@extends ('layouts.empresas')
@section ('contenido')
<script>
    $(document).ready(function()
    {       

       $("form").keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });


       $("#btnRegComp").on("click", function() {



          var formulario = $("#frmProducto").serializeArray();
          $("#imgload").show();
          $(".botones").hide();
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/servicios',
            data: formulario,
        }).done(function(respuesta){


            if(respuesta.estado =='error'){

                alert(respuesta.mensaje);

                $("#imgload").hide();
                $(".botones").show();

            }else{

               alert(respuesta.mensaje);
               window.location.href = "/servicios";


           }



       });

    });


         /*var producto = $("#promocion").val();
                $("#catinsu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscarcategorias/"+producto,

                }).done(function(respuesta){
                $("#catinsu").html(respuesta.vista);
               
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
        

            });*/

            var cat_id = $("#cmbCatId").val();
            $("#subcat_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
            $.ajax({
              type: "GET",
              dataType: 'json',
              url: "/buscarsubcategorias/"+cat_id,

          }).done(function(respuesta){
            $("#subcat_id").html(respuesta.vista);

        });

          $("#cmbCatId").change(function() {

            var cat_id = $("#cmbCatId").val();
            $("#subcat_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
            $.ajax({
              type: "GET",
              dataType: 'json',
              url: "/buscarsubcategorias/"+cat_id,

          }).done(function(respuesta){
            $("#subcat_id").html(respuesta.vista);

        });

          var subcat_id = $("#subcat_id").val();
          $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
              type: "GET",
              dataType: 'json',
              url: "/buscartipos/"+subcat_id,

          }).done(function(respuesta){
            $("#tip_pro_id").html(respuesta.vista);

        });



      });


          $("#subcat_id").change(function() {

            var subcat_id = $("#subcat_id").val();
            $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
            $.ajax({
              type: "GET",
              dataType: 'json',
              url: "/buscartipos/"+subcat_id,

          }).done(function(respuesta){
            $("#tip_pro_id").html(respuesta.vista);

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
            $('#detfact').append('<tr><td><input type="text" class="form-control input-sm" name="codigobarra[]"></td><td width="10%"><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

        })

          $('#addpre').click(function() {

            // Añadir caja de texto.
            $('#detpre').append('<tr><td width="15%"><select name="presentacion[]" class="form-control input-sm">@foreach($unidades as $unidad) <option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> @endforeach</select></td><td><input name="descripcion[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="factor[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="precio[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="precio2[]" class="form-control input-sm"></td><td width="10%"><input type="number" step="any" value="0" class="form-control input-sm" name="costo[]"></td><td><button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

        })



      });

function deleteRow(btn) {

  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);

  if ($('#detpre >tbody >tr').length == 0){
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

function calcular_costo_total(){
  var costo = $('#txt_costo').val();
  var peso = $('#txt_peso').val();
  var flete = $('#txt_flete').val();
  var costo_total = 0;

  costo_total = parseFloat(costo)+(parseFloat(peso*flete));

  $("#costo_total").val(costo_total.toFixed(2));

}

</script>

<section class="content">
    {!!Form::open(array('url'=>'productos','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmProducto'))!!}
    {{Form::token()}}   
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>REGISTRAR SERVICIO</strong></font></center>
                </div>
                <div class="box-body">


                    <div class="row">
                        <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="promocion">Tipo Producto</label>
                                <select class="form-control"  name="promocion" id="promocion">
                                    <option value="6">Servicio</option>
                                </select>

                            </div>
                        </div>
                        <div hidden="hidden" class="col-lg-2">
                         <div class="form-group form-group-sm">
                             <label>Programas</label>
                             <select class="form-control" name="prog_id">
                                <option></option>
                                @foreach($programas as $prog)
                                <option value="{{$prog->prog_id}}">{{$prog->prog_nom}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div hidden="hidden" class="col-lg-2">
                     <div class="form-group form-group-sm">
                         <label>Servicio</label>
                         <select class="form-control" name="tip_pre">
                            <option></option>
                            @foreach($servicios as $ser)
                            <option value="{{$ser->ser_cod}}">{{$ser->ser_nom}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="txt_procod">Código Interno</label>
                        <input type="text" name="txt_procod" value="{{old('txt_procod')}}" class="form-control" placeholder="">

                    </div>
                </div>
                  <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="tipo_codigo">Tipo Codigo</label>
                                <select class="form-control"  name="tipo_codigo" id="tipo_codigo">
                                    <option value="0"></option>
                                    <option value="1">EAN13</option>
                                  
                                </select>

                            </div>
                        </div>
                 <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="codigo_barra">Código de Barra</label>
                        <input type="text" name="codigo_barra" value="{{old('codigo_barra')}}" class="form-control" placeholder="">

                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="txt_pronom">Nombre de Servicio</label>
                        <input type="text" name="txt_pronom" value="{{old('txt_pronom')}}" class="form-control" placeholder="">

                    </div>
                </div>
            
                   <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_umecod">Unidad de Medida</label>
                    <select class="form-control"  name="txt_umecod" id="txt_umecod">
                        <option></option>
                        @foreach($unidades as $uni)
                        @if($uni->umecod =='NIU')
                        <option value="{{$uni->umecod}}" selected="selected">{{$uni->umenom}}</option>
                        @else
                        <option value="{{$uni->umecod}}">{{$uni->umenom}}</option>
                        @endif
                        
                        @endforeach
                    </select>
                    
                </div>
            </div>
            <div hidden="hidden"  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="factor_pro">Factor</label>
                    <input type="text" name="factor_pro" id="factor_pro" value="1" min='1'  class="form-control" placeholder="">
                    
                </div>
            </div>
                <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
                    <div class="form-group form-group-sm">
                        <label for="lote">Lote</label>
                        <input type="text" name="lote" value="" class="form-control" placeholder="">

                    </div>
                </div>
                <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                    <div class="form-group form-group-sm">
                        <label for="fecha">Fecha Vencimiento</label>
                        <input type="date" name="fechavencimiento" value="" class="form-control" placeholder="">

                    </div>
                </div>
                <div  hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="stock_min">Stock Mínimo</label>
                        <input type="number" name="stock_min" id="stock_min" value="0"  class="form-control" placeholder="">

                    </div>
                </div>

                
          


            <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="comision">Comisión (%)</label>
                    <input type="number" name="comision" id="comision" value="0"  class="form-control" placeholder="">

                </div>
            </div>

            <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="dias_garantia">D&iacute;as Garant&iacute;a</label>
                    <input type="number" step="any" name="dias_garantia" id="dias_garantia" value="0"  class="form-control" placeholder="">

                </div>
            </div>

            </div>

            <div hidden="hidden" class="row">
                   <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="form-group form-group-sm">
                    <label for="imagen">Imagen</label>
                    <input type="file" name="imagen" class="form-control">
                </div>
            </div>
            </div>



        </div>


        <div class="box-header with-border" style="background-color:blue;display:none;">
    <center><font color="white"><strong>Clasificación</strong></font></center>
</div>

<div class="box-body">

   <div hidden="hidden" class="row">
   
  <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
        <label for="lab_id">Laboratorio</label>
        <select class="form-control" name="lab_id">
          <option></option>
          @foreach($laboratorios as $lab)
          <option value="{{$lab->lab_id}}">{{$lab->lab_nom}}</option>                  
          @endforeach
      </select>

  </div>
</div>

<div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
        <label for="">Ubicación Físico</label>
        <input type="text" name="ubicacion" class="form-control">

    </div>
</div>
</div>

<div hidden="hidden" class="row">
         <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="txt_marca">Marca</label>
                        <select class="form-control" name="marca">
                          <option></option>
                          @foreach($marcas as $mar)
                          <option value="{{$mar->mar_id}}">{{$mar->mar_nom}}</option>                  
                          @endforeach
                      </select>

                  </div>
              </div>
                 <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="txt_marca">Modelo</label>
                        <select class="form-control" name="modelo">
                          <option></option>
                          @foreach($modelos as $mod)
                          <option value="{{$mod->mod_id}}">{{$mod->mod_nom}}</option>                  
                          @endforeach
                      </select>

                  </div>
              </div>


                 <div  class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
                        <div class="form-group form-group-sm">
                            <label for="talla">Talla</label>
                            <input type="text" name="talla" value="" class="form-control" placeholder="">

                        </div>
                </div>


   
</div>

<div hidden="hidden" class="row">
    <div id="catinsu" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
        <label for="cmbCatId">Familia</label>
        <select class="form-control"  name="cmbCatId" id="cmbCatId">
            @foreach($categorias as $cat)
            <option value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
            @endforeach
        </select>

    </div>
</div>
<div id="subcat" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
        <label for="subcat_id">Subamilia</label>
        <select class="form-control"  name="subcat_id" id="subcat_id">
            @foreach($subcategorias as $subcat)
            <option value="{{$subcat->subcat_id}}">{{$subcat->subcat_nom}}</option>
            @endforeach
        </select>

    </div>
</div>
<div id="subcat" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
        <label for="tip_pro_id">Tipo Producto</label>
        <select class="form-control"  name="tip_pro_id" id="tip_pro_id">
            @foreach($tipos as $tp)
            <option value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
            @endforeach
        </select>

    </div>
</div>
</div>

<div hidden="hidden" class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
    <div class="form-group form-group-sm">
        <label for="color">Color</label>
        <input type="color" name="color" value="#3f4aee" class="form-control" placeholder="">

    </div>
</div>



<div class="row">


    <div hidden="hidden"  class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
        <div class="form-group form-group-sm">
            <label for="txt_moncod">Moneda</label>
            <select class="form-control"  name="txt_moncod" id="txt_moncod">
                <option></option>
                @foreach($monedas as $mon)
                @if($mon->moncod =='PEN')
                <option value="{{$mon->moncod}}" selected="selected">{{$mon->monnom}}</option>
                @else
                <option value="{{$mon->moncod}}">{{$mon->monnom}}</option>
                @endif
                @endforeach
            </select>

        </div>
    </div>

</div>



</div>


        <div class="box-header with-border" style="background-color:blue;">
            <center><font color="white"><strong>Precios de Venta</strong></font></center>
        </div>

        <div class="box-body">


            <div class="row"> 
             <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_propun">P.Unitario</label>
                    <input type="text" name="txt_propun" id="txt_propun" value="0"  class="form-control" placeholder="">

                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_propun">Precio Mayorista</label>
                    <input type="text" name="txt_propun2" id="txt_propun2" value="0"  class="form-control" placeholder="">

                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_propun">Precio Especial</label>
                    <input type="text" name="txt_propun3" id="txt_propun3" value="0"  class="form-control" placeholder="">

                </div>
            </div>
        </div>

      

    </div>

    <div class="box-header with-border" style="background-color:blue;display:none;">
        <center><font color="white"><strong>Costos</strong></font></center>
    </div>

    <div hidden="hidden" class="box-body">

      <div class="row">
       <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="txt_costo">Costo</label>
            <input type="text" name="txt_costo" id="txt_costo" value="0" onkeyup="calcular_costo_total();"  class="form-control" placeholder="">

        </div>
    </div>
    <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
        <div class="form-group form-group-sm">
            <label for="txt_costofijo">Costo Fijo</label>
            <input type="text" name="txt_costofijo" id="txt_costofijo" value="0"   class="form-control" placeholder="">
        </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="txt_peso">Peso (kg)</label>
            <input type="text" name="txt_peso" id="txt_peso" value="0" onkeyup="calcular_costo_total();"   class="form-control" placeholder="">

        </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="txt_flete">Flete</label>
            <input type="text" name="txt_flete" id="txt_flete" value="0" onkeyup="calcular_costo_total();"   class="form-control" placeholder="">

        </div>
    </div>
     <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="costo_total">Costo Total</label>
            <input type="text" name="costo_total" readonly="readonly" id="costo_total" value="0"  class="form-control" placeholder="">

        </div>
    </div>
</div>


</div>


<div class="box-header with-border" style="background-color:blue;display:none;">
    <center><font color="white"><strong>SUNAT</strong></font></center>
</div>

<div class="box-body"  hidden="hidden">

    <div class="row">
      <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="tigcod">IGV</label>
            <select class="form-control"  name="tigcod" id="tigcod">

                @foreach($tipoigv as $igv)

                <option value="{{$igv->tigcod}}">{{$igv->tigdes}}</option>


                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="tigcod">ICBPER</label>
            <select class="form-control" name=icbper>
                <option selected="selected" value="0">NO</option>
                <option  value="1">SI</option>
            </select>
        </div>
    </div>

</div>

</div>




  <div class="box-header with-border" style="background-color:blue;display:none;">
                <center><font color="white"><strong>Códigos de Barra</strong></font></center>
            </div>
            <div class="box-body"  hidden="hidden">
                <div class="row">
                    <div class="col-lg-12">
                        <table id="detfact" class="table">
                            <thead>
                                <th><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button> Código de Barras </th>
                            </thead>
                            <tbody id="">
                                <tr>
                                    <td>
                                        <input type="text" name="codigobarra[]" class="form-control input-sm" >
                                    </td>
                                    <td width="10%">
                                        <button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



              <div class="box-header with-border" style="background-color:blue;display:none;">
                <center><font color="white"><strong>Presentaciones</strong></font></center>
            </div>
            <div class="box-body" hidden="hidden">
                <div class="row">
                    <div class="col-lg-12">
                        <table id="detpre" class="table">
                            <thead>

                                <tr>

                                    <th><button type="button" onClick="" name="addpre" id="addpre" class="btn btn-success btn-sm addpre"><span class="glyphicon glyphicon-plus"></span></button> PRESENTACION</th>
                                    <th>DESCRIPCION</th>
                                    <th width="10%">FACTOR</th>
                                    <th width="10%">PRECIO 1</th>
                                    <th width="10%">PRECIO 2</th>
                                    <th width="10%">COSTO</th>

                                </tr>
                            </thead>

                            <tbody id="">
                                <tr><td width="15%"><select name="presentacion[]" class="form-control input-sm">@foreach($unidades as $unidad) <option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> @endforeach</select></td><td><input name="descripcion[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="factor[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="precio[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="precio2[]" class="form-control input-sm"></td><td width="10%"><input type="number" step="any" value="0" class="form-control input-sm" name="costo[]"></td><td><button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>

                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

             <div class="box-body">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
                   <div class="form-group form-group-sm">
                       <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
                       <button id="btnRegComp" class="btn btn-primary botones" type="button">Guardar</button>
                       <a href="/productos"><button class="btn btn-danger btn-close botones" type="button">Cancelar</button></a>
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