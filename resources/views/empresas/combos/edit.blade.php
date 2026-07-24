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

     /* $("#promocion").change(function() {
         
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
            $('#detFact').append('<tr><td><input type="hidden" name="id[]" value=""><input type="text" class="form-control input-sm" name="codigobarra[]"></td><td width="10%"><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

        })

            $('#addpre').click(function() {

            // Añadir caja de texto.
            $('#detpre').append('<tr><td hidden="hidden"><input type="hidden" value="0" name="idprod[]"></td>   <td > <div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-barcode"></span></button></span> <input type="text" class="form-control input-sm"  name="codigo_barra_pre[]" value=""></div></td><td width="15%"><select name="presentacion[]" class="form-control input-sm">@foreach($unidades as $unidad) <option value="{{$unidad->umecod}}">{{$unidad->umenom}}</option> @endforeach</select></td><td><input name="descripcion[]" class="form-control input-sm"></td><td width="10%"><input type="number" min="0" value="0" step="any" name="factor[]" class="form-control input-sm"></td><td width="10%"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" min="0" value="0" step="any" name="precio[]" class="form-control input-sm"></div></td><td width="10%"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" step="any" value="0" class="form-control input-sm" name="costo[]"></div></td><td><button type="button" onClick="deletepresentacion(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

        })




        });


function agregar_producto(){

    var costo = $('#IdProducto_sel').find(':selected').attr('data-costo');
    var IdProducto = $('#IdProducto_sel').find(':selected').attr('data-IdProducto');
    var pronom = $('#IdProducto_sel').find(':selected').attr('data-pronom');
    var precio = $('#IdProducto_sel').find(':selected').attr('data-precio');

    $('#detCombo').append('<tr>'+
        '<td hidden="hidden"><div class="form-group form-group-sm"> <input type="text" name="IdProducto_rel[]" value="'+IdProducto+'" class="form-control"></div></td>'+
        '<td width="60%"><div class="form-group form-group-sm"><input type="text" name="prod_nom_comb[]" readonly="readonly" value="'+pronom+'"  class="form-control"></div></td>'+
        '<td><div class="form-group form-group-sm"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" name="prod_comb_cant[]" class="form-control input-sm"></div></div></td>'+
        '<td><div class="form-group form-group-sm"><div class="input-group input-group-sm"> <span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" name="prod_comb_prec[]" value="'+precio+'"  class="form-control input-sm"></div></div></td>'+
        '<td><div class="form-group form-group-sm"><div class="input-group input-group-sm"> <span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" name="prod_comb_cost[]" value="'+costo+'"  class="form-control input-sm"></div></div></td>'+
        '<td><button type="button" onClick="eliminar_producto(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>'+
        '</td>'+'</tr>');
}



function eliminar_producto(btn) {

  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);

  if ($('#detcombo >tbody >tr').length == 0){
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

  $("#costo_total").val(costo_total.toFixed(4));

}

</script>
   {!!Form::open(array('url'=>'/combos/actualizar','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmProducto'))!!}
    {{Form::token()}}   

<section class="content">

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>EDITAR PRODUCTO</strong></font></center>
                </div>
                <div class="box-body">
                    <input type="hidden" readonly="readonly" name="idprod" value="{{$productos->IdProducto}}">
                    <div class="row">
                      <input hidden="hidden"  name="sucursal" value="{{$sucursal}}">
                      <div   class="col-lg-1 col-md-2 col-sm-12 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="promocion">Tipo</label>
                            <select class="form-control"  name="promocion" id="promocion">
                                <option value="3">Combo</option>

                            </select>

                        </div>
                    </div>


                    <div class="col-lg-1 col-md-2 col-sm-3 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="txt_procod">Código</label>
                            <input type="text" name="txt_procod" value="{{$productos->procod}}" class="form-control" placeholder="Código del producto...">

                        </div>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden='hidden'>
                            <div class="form-group form-group-sm">
                                <label for="tipo_codigo">Tipo Codigo Barra</label>
                                <select class="form-control"  name="tipo_codigo" id="tipo_codigo">
                                    @if($productos->tipo_codigo=='1')
                                       <option  value="0"></option>
                                        <option selected="selected" value="1">EAN13</option>
                                    @else
                                       <option selected="selected" value="0"></option>
                                        <option value="1">EAN13</option>
                                    @endif
                                 
                                  
                                </select>

                            </div>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="codigo_barra">Código Barra</label>
                              <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-barcode"></span></button>
                        </span>
                      
                            <input type="text" name="codigo_barra" value="{{$productos->codigo_barra}}" class="form-control" placeholder="Código de Barra...">
                        
                    </div>



                        </div>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="txt_pronom">Nombre Producto</label>
                            <input type="text" name="txt_pronom" value="{{$productos->pronom}}" class="form-control" placeholder="">

                        </div>
                    </div>
                   
                   

                    <div class="col-lg-1 col-md-2 col-sm-2 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="txt_umecod">U.M.</label>
                            <select class="form-control"  name="txt_umecod" id="txt_umecod">
                                <option></option>
                                @foreach($unidades as $uni)
                                @if($uni->umecod==$productos->umecod)
                                <option value="{{$uni->umecod}}" selected>{{$uni->umenom}}</option>
                                @else
                                <option value="{{$uni->umecod}}">{{$uni->umenom}}</option>
                                @endif
                                @endforeach
                            </select>

                        </div>
                    </div>

                    <div  class="col-lg-1 col-md-2 col-sm-12 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="factor_pro">Factor</label>
                            <input type="text" name="factor_pro" id="factor_pro" value="{{$productos->factor}}" min='1'  class="form-control" placeholder="">

                        </div>
                    </div>
                    <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="lote">Lote</label>
                            <input type="text" name="lote" value="{{$productos->lote}}" class="form-control" placeholder="">

                        </div>
                    </div>
                    <div hidden="hidden"  class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="fecha">Fecha Vencimiento</label>
                            <input type="date" name="fechavencimiento" value="{{$productos->vencimiento}}" class="form-control" placeholder="">

                        </div>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden='hidden'>
                        <div class="form-group form-group-sm">
                            <label for="stock_min">Stock Mínimo</label>
                                <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-cart-arrow-down"></span></button>
                        </span>
                      <input type="number" name="stock_min" id="stock_min" value="{{$productos->stock_min}}"  class="form-control" placeholder="">
                    </div>
                        </div>
                    </div>

                    <div id="catinsu" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="cmbCatId">Familia</label>
                                <select class="form-control"  name="cmbCatId" id="cmbCatId">
                                      <option></option>
                                    @foreach($categorias as $cat)
                                    @if($cat->cat_id == $productos->cat_id)
                                    <option  selected="selected"value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                                    @else
                                    <option value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                                    @endif

                                    @endforeach
                                </select>

                            </div>
                        </div>

            <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <label for="txt_propun">Precio de venta</label>
                <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                     <input type="text" name="txt_propun" id="txt_propun" value="{{$productos->precio}}"  class="form-control" placeholder="">
                </div>           
            </div>
                    
                     <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="imagen">Imagen</label>
                                <input type="file" name="imagen" class="form-control">
                            </div>
                        </div>

            <div hidden="hidden"  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="dias_garantia">D&iacute;as Garant&iacute;a</label>
                    <input type="number" step="any" name="dias_garantia" id="dias_garantia" value="{{$productos->dias_garantia}}"  class="form-control" placeholder="">

                </div>
            </div>






                </div>


                <div class="row">



                </div>

                <div class="row">
                    <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="txt_moncod">Moneda</label>
                            <select class="form-control"  name="txt_moncod" id="txt_moncod">
                                <option></option>
                                @foreach($monedas as $mon)
                                @if($mon->moncod==$productos->moncod)
                                <option value="{{$mon->moncod}}" selected>{{$mon->monnom}}</option>
                                @else
                                <option value="{{$mon->moncod}}">{{$mon->monnom}}</option>
                                @endif

                                @endforeach
                            </select>

                        </div>
                    </div>

                </div>

            </div>



     <!--<div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>Clasificación</strong></font></center>
    </div>-->

 <!--   <div class="box-body">
        <div hidden="hidden" class="row"> 


            <div   class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
             <div class="form-group form-group-sm">
                <label for="lab_id">Laboratorios</label>
                <select class="form-control" name="lab_id">
                    <option></option>
                    @foreach($laboratorios as $lab)
                    @if($lab->lab_id == $productos->lab_id)
                    <option selected="selected" value="{{$lab->lab_id}}">{{$lab->lab_nom}}</option> 
                    @else

                    <option value="{{$lab->lab_id}}">{{$lab->lab_nom}}</option> 
                    @endif

                    @endforeach
                </select>

            </div>
        </div>
        <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">Ubicación</label>
                <input type="text" name="ubicacion" value="{{$productos->ubicacion}}" class="form-control">

            </div>
        </div>
    </div>
    <div class="row">
         <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="txt_marca">Marca</label>
                            <select class="form-control" name="marca">
                                <option></option>
                                @foreach($marcas as $mar)
                                @if($mar->mar_id == $productos->marca)
                                <option selected="selected" value="{{$mar->mar_id}}">{{$mar->mar_nom}}</option> 
                                @else

                                <option value="{{$mar->mar_id}}">{{$mar->mar_nom}}</option> 
                                @endif

                                @endforeach
                            </select>

                        </div>
                    </div>

                      <div hidden="hidden"   class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="txt_marca">Modelo</label>
                        <select class="form-control" name="modelo">
                          <option></option>
                          @foreach($modelos as $mod)

                           @if($mar->mar_id == $productos->modelo)
                               <option selected="selected" value="{{$mod->mod_id}}">{{$mod->mod_nom}}</option>  
                                @else

                               <option value="{{$mod->mod_id}}">{{$mod->mod_nom}}</option>  
                                @endif

                                         
                          @endforeach
                      </select>

                  </div>
              </div>

               <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="talla">Talla</label>
                            <input type="text" name="talla" value="{{$productos->talla}}" class="form-control" placeholder="">

                        </div>
                    </div>

    
     <div id="catinsu" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="cmbCatId">Familia</label>
            <select class="form-control"  name="cmbCatId" id="cmbCatId">
                  <option></option>
                @foreach($categorias as $cat)
                @if($cat->cat_id == $productos->cat_id)
                <option  selected="selected"value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                @else
                <option value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                @endif

                @endforeach
            </select>

        </div>
    </div>
    <div id="subcat" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="subcat_id">Subamilia</label>
            <select class="form-control"  name="subcat_id" id="subcat_id">
                  <option></option>
                @foreach($subcategorias as $subcat)
                @if($subcat->subcat_id == $productos->subcat_id)
                <option selected="selected" value="{{$subcat->subcat_id}}">{{$subcat->subcat_nom}}</option>
                @else
                <option value="{{$subcat->subcat_id}}">{{$subcat->subcat_nom}}</option>
                @endif
                @endforeach
            </select>

        </div>
    </div>
    <div hidden="hidden" id="subcat" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="tip_pro_id">Tipo Producto</label>
            <select class="form-control"  name="tip_pro_id" id="tip_pro_id">
                @foreach($tipos as $tp)
                @if($tp->tip_pro_id == $productos->tip_pro_id)
                <option selected="selected" value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                @else
                <option value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                @endif

                @endforeach
            </select>

        </div>
    </div>
</div>
</div>-->




            <!--<div class="box-header with-border" style="background-color:blue;">
                <center><font color="white"><strong>Precios de Venta</strong></font></center>
            </div>-->

      <!--      <div class="box-body">
                <div class="row"> 

            <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <label for="txt_propun">P.Unitario</label>
                <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                     <input type="text" name="txt_propun" id="txt_propun" value="{{$productos->precio}}"  class="form-control" placeholder="">
                </div>           
            </div>


            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_propun">Precio Mayorista</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                        <input type="text" name="txt_propun2" id="txt_propun" value="{{$productos->precio2}}"  class="form-control" placeholder="">
                        
                    </div>
                   

                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txt_propun">Precio Especial</label>
                     <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                         <input type="text" name="txt_propun3" id="txt_propun" value="{{$productos->precio3}}"  class="form-control" placeholder="">

                        
                    </div>
                   
                  
                </div>
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="comision">Comisión (%)</label>
                     <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-percent"></span></button>
                        </span>
                        <input type="number" name="comision" id="comision" value="{{$productos->comision}}"  class="form-control" placeholder="">
                        
                    </div>
                   
                   

                </div>
            </div>
                    <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="txt_provun">V.Unitario</label>
                            <input type="text" name="txt_provun" id="txt_prov" value="{{$productos->provun}}" class="form-control" placeholder="Valor Unitario...">
                        </div>
                    </div>
                
                    

                       
                    </div>
                </div>-->
          

            <div style="display:none;" class="box-header with-border" style="background-color:blue;">
                <center><font color="white"><strong>Costos</strong></font></center>
            </div>

            <div class="box-body" hidden='hidden'>

                 <div class="row">
        <!--<div  class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <div class="form-group form-group-sm">
                <label for="txt_costofijo">Costo Fijo</label>
                  <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                         <input type="text" name="txt_costofijo" id="txt_costofijo" value="{{$productos->costofijo}}"  class="form-control" placeholder="">                        
                    </div>               
            </div>
        </div>-->
    </div>

      <div class="row">

        <div  class="col-lg-3 col-md-3 col-sm-2 col-xs-2">
            <div class="form-group form-group-sm">
                <label for="txt_costofijo">Costo Fijo</label>
                  <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                         <input type="text" name="txt_costofijo" id="txt_costofijo" value="{{$productos->costofijo}}"  class="form-control" placeholder="">                        
                    </div>               
            </div>
        </div>

       <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="txt_costo">Costo</label>
              <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                           <input type="text" name="txt_costo" id="txt_costo" onkeyup="calcular_costo_total();" value="{{$productos->costo}}"  class="form-control" placeholder="">
                        
                    </div>
                   
                  
          

        </div>
    </div>
    
    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="txt_peso">Peso (kg)</label>
             <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-weight"></span></button>
                        </span>
              <input type="text" name="txt_peso" id="txt_peso" value="{{$productos->peso}}" onkeyup="calcular_costo_total();"   class="form-control" placeholder="">
        </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="txt_flete">Flete</label>
             <input type="text" name="txt_flete" id="txt_flete" value="{{$productos->flete}}" onkeyup="calcular_costo_total();"  class="form-control" placeholder="">

        </div>
    </div>
     <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="costo_total">Costo Total</label>
             <div class="input-group input-group-sm">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"><span class="fa fa-money"></span></button>
                        </span>
                            <input type="text" name="costo_total" readonly="readonly" id="costo_total" value="{{$productos->costo_total}}"  class="form-control" placeholder="">
                        
                    </div>
          

        </div>
    </div>
</div>


               
        </div>



        <div style="display:none;" class="box-header with-border" style="background-color:blue;">
            <center><font color="white"><strong>SUNAT</strong></font></center>
        </div>

        <div class="box-body" hidden='hidden'>
            <div class="row"> 

                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="tigcod">IGV</label>
                        <select class="form-control"  name="tigcod" id="tigcod">

                            @foreach($tipoigv as $igv)
                                @if($igv->tigcod == $productos->tigcod)
                                    <option selected="selected" value="{{$igv->tigcod}}">{{$igv->tigdes}}</option>
                                @else
                                 <option value="{{$igv->tigcod}}">{{$igv->tigdes}}</option>
                                @endif

                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="tigcod">ICBPER</label>
                        <select class="form-control" name=icbper>

                         @if($productos->icbper == '0')
                         <option value="0">NO</option>
                         <option  value="1">SI</option>
                         @elseif($productos->icbper =='1')
                         <option  value="0">NO</option>
                         <option  selected="selected"  value="1">SI</option>
                         @else
                         <option  value="0">NO</option>
                         <option  value="1">SI</option>

                         @endif

                     </select>
                 </div>
             </div>
         </div>
     </div>





<div style="display:none;" class="box-header with-border" style="background-color:blue;">
    <center><font color="white"><strong>Códigos de Barra</strong></font></center>
</div>

<div style="display:none;" class="box-body">
    <div class="row"> 
      <div class="col-lg-12">
        <table id="detFact" class="table">
            <thead>
                <th><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button> Código de Barras </th>
            </thead>
            <tbody id="">
             @if(!empty($codigos))
                 @foreach($codigos as $cod)
                 <tr><td><input type="hidden" name="id[]" value="{{$cod->pro_cod_id}}"><input type="text" class="form-control input-sm" name="codigobarra[]" value="{{$cod->cod_bar}}"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>
                 @endforeach
             @else
             <tr><td><input type="hidden" name="id[]" value=""><input type="text" class="form-control input-sm" name="codigobarra[]"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>
             @endif
         </tbody>
     </table>
 </div>
</div>
</div>



<div class="box-header with-border" style="background-color:blue;">
    <center><font color="white"><strong>AGREGAR PRODUCTOS</strong></font></center>
</div>
<div class="box-body">
    <div class="row">
        <div class="col-lg-12">
            <table id="detCombo" class="table">
                <thead>
                    <tr>
                        <td colspan="5">
                            <div  class="form-group form-group-sm" style="max-width:400px;">
                                <label>Elegir Producto</label>
                                <select id="IdProducto_sel" class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" onchange="agregar_producto()">
                                    <option></option>
                                    @foreach($list_prod as $lp)
                                    <option value="{{$lp->IdProducto}}" data-costo="{{$lp->costo_total}}"  data-precio="{{$lp->propun}}" data-IdProducto="{{$lp->IdProducto}}" data-pronom="{{$lp->pronom}}">{{$lp->pronom}}</option>
                                    @endforeach 
                                </select>
                            </div>              
                        </td>
                    </tr>
                    <tr>
                        <th hidden="hidden">IDPRODUCTO</th>
                        <th style="width:15%;">PRODUCTO</th>
                        <th>CANTIDAD</th>
                        <th>PRECIO</th>
                        <th>COSTO</th>
                        <th>ELIMINAR</th>
                    </tr>
                </thead>
                <tbody id="">
                @foreach($list_prod_comb as $lpc)
                   <tr>
                        <td hidden="hidden"><div class="form-group form-group-sm"> <input type="text" name="IdProducto_rel[]" value="{{$lpc->IdProducto}}" class="form-control"></div></td>
                        <td width="60%"><div class="form-group form-group-sm"><input type="text" name="prod_nom_comb[]" readonly="readonly" value="{{$lpc->pronom}}"  class="form-control"></div></td>
                        <td><div class="form-group form-group-sm"><div class="input-group input-group-sm"><span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" name="prod_comb_cant[]" value="{{$lpc->prod_comb_cant}}" class="form-control input-sm"></div></div></td>
                        <td><div class="form-group form-group-sm"><div class="input-group input-group-sm"> <span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" name="prod_comb_prec[]" value="{{$lpc->prod_comb_prec}}"  class="form-control input-sm"></div></div></td>
                        <td><div class="form-group form-group-sm"><div class="input-group input-group-sm"> <span class="input-group-btn"><button type="button" class="btn btn-default"><span class="fa fa-money"></span></button></span><input type="number" name="prod_comb_cost[]" value="{{$lpc->prod_comb_cost}}"  class="form-control input-sm"></div></div></td>
                        <td><button type="button" onClick="eliminar_producto(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>



<div class="box-body">
  <div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
       <div class="form-group form-group-sm">
        <button class="btn btn-primary" type="submit">Actualizar</button>
        <a href="/combos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
    </div>
</div>
</div>


</div>





</div>


</div>
</div>
</section>


{!!Form::close()!!}
@endsection
