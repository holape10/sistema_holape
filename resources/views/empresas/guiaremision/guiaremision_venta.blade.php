@extends('layouts.empresas')
@section('contenido')
<style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    input[readonly]
{
    background-color:#eee;
}



input[type=number] { -moz-appearance:textfield; }

#formfact label.error {
        color:red;
    }
.btn-default.btn-on.active{background-color: #5BB75B;color: white;}
.btn-default.btn-off.active{background-color: #DA4F49;color: white;}
</style>


<?php if(Auth::user()->IdEmpresa=='3'){?>
<script>

   $(document).ready(function()
  {

 
     if($('#modalidad').val()=='02'){


          
            $('#div_dat_transp').hide();
            $('#div_dat_cond').show();

             $('#transportistanum').val('');
              $('#transportistanom').val('');

        }

        if($('#modalidad').val()=='01'){

            $('#div_dat_transp').show();
            $('#div_dat_cond').hide();

              $('#conductornum').val('');
              $('#conductornom').val('');
               $('#placa').val('');

            
        }



         if($('#motivo').val()=='04'){


          
            $('#div_cod_local_part').show();
            $('#div_cod_local_des').show();


        }else{

            $('#div_cod_local_part').hide();
            $('#div_cod_local_des').hide();

        }



        $('#modalidad').change(function(){


        if($('#modalidad').val()=='02'){

          
            $('#div_dat_transp').hide();
            $('#div_dat_cond').show();
            
             $('#transportistanum').val('');
              $('#transportistanom').val('');

        }

        if($('#modalidad').val()=='01'){

            $('#div_dat_transp').show();
            $('#div_dat_cond').hide();

             $('#conductornum').val('');
              $('#conductornom').val('');
               $('#placa').val('');
            
        }


    });



    $('#motivo').change(function(){

        if($('#motivo').val()=='04'){
            $('#div_cod_local_part').show();
            $('#div_cod_local_des').show();
        }else{
            $('#div_cod_local_part').hide();
            $('#div_cod_local_des').hide();
        }

    });



    $("#menor").keydown(function () {

                  var valor = $(this).val();
                  var cont = 0, cantidad=0,total=0;
                   $.ajax({
                        type: 'get',
                        url: '/consultarprod',
                        dataType: 'json',
                        data: {'value' : $(this).val() },
                        success : function(data) {
                         
                         $("#menor").val('');

                          if ($('#detFact >tbody >tr').length > 0){

                              $("#detFact tbody tr").each(function(){
                                 var codigo = $(this).find("td:eq(3) > input").val();
                                 if( valor == codigo){
                                    cont = cont+1;
                                    cantidad = parseFloat($(this).find("td:eq(0) > input").val())+1;
                                    totalitem = parseFloat($(this).find("td:eq(7) > input").val())*cantidad;
                                    subtotalitem = totalitem/1.1055;
                                    igvitem = totalitem-subtotalitem;
                                    presigv = subtotalitem/cantidad;
                                 }
                                if(cont >0){
                                      $(this).find("td:eq(0) > input").val(cantidad);
                                       $(this).find("td:eq(6) > input").val(presigv.toFixed(2));
                                      $(this).find("td:eq(8) > input").val(igvitem.toFixed(2));
                                      $(this).find("td:eq(9) > input").val(subtotalitem.toFixed(2));
                                      $(this).find("td:eq(10) > input").val(totalitem.toFixed(2));
                                      calculartotal();
                                      $("#menor").focus();
                                      return false;
                                  }
                              })

                                  if(cont == 0){
                                      var igvitem = data[0].propun -data[0].provun;
                                       $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td hidden="hidden"><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><select style="width:100px" name="presentacion[]"  class="form-control input-sm"><option  value=""></option></select></td><td><input type="text"  name="codpro[]" id="codpro" value="'+data[0].value+'" style="background-color:#ABEBC6"   OnBlur="Calcular(this);"  OnClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" value="'+data[0].pronom+'" size="100" onfocus="Calcular(this)"; ></td><td hidden="hidden"><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden" ><input type="number" step=".00001" id="vunit" value="'+data[0].provun+'" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input type="text" readonly="readonly" class="form-control input-sm preuni" value="'+data[0].propun+'" size="20px" id="preuni"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="'+igvitem+'" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly  value="'+data[0].provun+'"  style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="'+data[0].propun+'" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
                                     }

                                }else{

                                    var igvitem = data[0].propun -data[0].provun;
                                     $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td hidden="hidden"><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><select style="width:100px" name="presentacion[]"  class="form-control input-sm"><option  value=""></option></select></td><td><input type="text"  name="codpro[]" id="codpro" value="'+data[0].value+'" style="background-color:#ABEBC6"   OnBlur="Calcular(this);"  OnClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" value="'+data[0].pronom+'" size="100" onfocus="Calcular(this)"; ></td><td hidden="hidden"><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden" ><input type="number" step=".00001" id="vunit" value="'+data[0].provun+'" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input type="text" readonly="readonly" class="form-control input-sm preuni" value="'+data[0].propun+'" size="20px" id="preuni"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="'+igvitem+'" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly  value="'+data[0].provun+'"  style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="'+data[0].propun+'" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
                                }

                        

                            if ($('#detFact >tbody >tr').length > 0){
                                 calculartotal();
                                 $("#menor").val('');
                                 $("#menor").focus();
                            }

                        }

                    })
                  
                });
                
                 });

           

</script>

<?php }else{ ?>
  <script>

   $(document).ready(function()
    {




         if($('#motivo').val()=='04'){


          
            $('#div_cod_local_part').show();
            $('#div_cod_local_des').show();


        }else{

            $('#div_cod_local_part').hide();
            $('#div_cod_local_des').hide();

        }

            if($('#modalidad').val()=='02'){


          
            $('#div_dat_transp').hide();
            $('#div_dat_cond').show();

             $('#transportistanum').val('');
              $('#transportistanom').val('');

        }

        if($('#modalidad').val()=='01'){

            $('#div_dat_transp').show();
            $('#div_dat_cond').hide();

              $('#conductornum').val('');
              $('#conductornom').val('');
               $('#placa').val('');

            
        }




        $('#modalidad').change(function(){


        if($('#modalidad').val()=='02'){

          
            $('#div_dat_transp').hide();
            $('#div_dat_cond').show();
            
             $('#transportistanum').val('');
              $('#transportistanom').val('');

        }

        if($('#modalidad').val()=='01'){

            $('#div_dat_transp').show();
            $('#div_dat_cond').hide();

             $('#conductornum').val('');
              $('#conductornom').val('');
               $('#placa').val('');
            
        }


    });


            $('#motivo').change(function(){

        if($('#motivo').val()=='04'){
            $('#div_cod_local_part').show();
            $('#div_cod_local_des').show();
        }else{
            $('#div_cod_local_part').hide();
            $('#div_cod_local_des').hide();
        }

    });



    $("#btnregistrar").on("click", function() {

      
  
      
      if ($('#detFact >tbody >tr').length == 0){
        $('#alertitem').show();
        event.preventDefault(); 
      }

      var formulario = $("#formfact").serializeArray();
      $("#imgloadguia").show();
      $("#divcargar").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/venta/generarguia',
        data: formulario,
      }).done(function(respuesta){

           if(respuesta.estado =='error'){
            alert(respuesta.mensaje);
            
            $("#imgloadguia").hide();
            $("#divcargar").show();
        }else{
            window.location.href = "/guiasremision";
            //$("#imgload").hide();
 
        }

      });

    });



    $("#menor").keyup(function () {

                  var valor = $(this).val();
                  var cont = 0, cantidad=0,total=0;
                   $.ajax({
                        type: 'get',
                        url: '/consultarprod',
                        dataType: 'json',
                        data: {'value' : $(this).val() },
                        success : function(data) {
                         
                         $("#menor").val('');

                          if ($('#detFact >tbody >tr').length > 0){

                              $("#detFact tbody tr").each(function(){
                                 var codigo = $(this).find("td:eq(3) > input").val();
                                 if( valor == codigo){
                                    cont = cont+1;
                                    cantidad = parseFloat($(this).find("td:eq(0) > input").val())+1;
                                    totalitem = parseFloat($(this).find("td:eq(7) > input").val())*cantidad;
                                    subtotalitem = totalitem/1.1055;
                                    igvitem = totalitem-subtotalitem;
                                    presigv = subtotalitem/cantidad;
                                 }
                                if(cont >0){
                                      $(this).find("td:eq(0) > input").val(cantidad);
                                       $(this).find("td:eq(6) > input").val(presigv.toFixed(2));
                                      $(this).find("td:eq(8) > input").val(igvitem.toFixed(2));
                                      $(this).find("td:eq(9) > input").val(subtotalitem.toFixed(2));
                                      $(this).find("td:eq(10) > input").val(totalitem.toFixed(2));
                                      calculartotal();
                                      $("#menor").focus();
                                      return false;
                                  }
                              })

                                  if(cont == 0){
                                      var igvitem = data[0].propun -data[0].provun;
                                       $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td hidden="hidden"><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><select style="width:100px" name="presentacion[]"  class="form-control input-sm"><option  value=""></option></select></td><td><input type="text"  name="codpro[]" id="codpro" value="'+data[0].value+'" style="background-color:#ABEBC6"   OnBlur="Calcular(this);"  OnClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" value="'+data[0].pronom+'" size="100" onfocus="Calcular(this)"; ></td><td hidden="hidden"><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden" ><input type="number" step=".00001" id="vunit" value="'+data[0].provun+'" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input type="text" readonly="readonly" class="form-control input-sm preuni" value="'+data[0].propun+'" size="20px" id="preuni"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="'+igvitem+'" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly  value="'+data[0].provun+'"  style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="'+data[0].propun+'" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
                                     }

                                }else{

                                    var igvitem = data[0].propun -data[0].provun;
                                     $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td hidden="hidden"><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><select style="width:100px" name="presentacion[]"  class="form-control input-sm"><option  value=""></option></select></td><td><input type="text"  name="codpro[]" id="codpro" value="'+data[0].value+'" style="background-color:#ABEBC6"   OnBlur="Calcular(this);"  OnClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" value="'+data[0].pronom+'" size="100" onfocus="Calcular(this)"; ></td><td hidden="hidden"><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden" ><input type="number" step=".00001" id="vunit" value="'+data[0].provun+'" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input type="text" readonly="readonly" class="form-control input-sm preuni" value="'+data[0].propun+'" size="20px" id="preuni"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="'+igvitem+'" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly  value="'+data[0].provun+'"  style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="'+data[0].propun+'" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
                                }

                        

                            if ($('#detFact >tbody >tr').length > 0){
                                 calculartotal();
                                 $("#menor").val('');
                                 $("#menor").focus();
                            }

                        }

                    })
                  
                });
                
                 });

           

</script>
   
<?php } ?>

<script>
    $.validator.setDefaults({
    ignore: [],
    // any other default options and/or rules
    });

    $(document).ready(function()
    {
       $("#formfact").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        })


        var $svalor=0;
        var iCnt = 0;
        var comprobante = $("#comprobante").val();
        var documento = $("#documento").val();
        $("#btnPrint").printPage({

          url: "/imprimir/"+comprobante+"/"+documento,
          attr: "href",
          messageBox:false

        })

        $('#menor').focus();

        $('.detalle').keypress(function(e) {

            if(e.keyCode == 43) {
                agregarlinea();
                return false;
            }

        })








                  $('.detpro').on('change',function(){
                      validartabla();

                })

                 $('.cant').on('change',function () {
                     validartabla();
                })

                 $("#desubigeopartida").autocomplete({
                  source: '{!!URL::route('buscarubigeo')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  select: function(event,ui) {
                     $('#ubigeopartida').val(ui.item.codubigeo);
                   
                  }
                })


                 $("#desubigeollegada").autocomplete({
                  source: '{!!URL::route('buscarubigeo')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  select: function(event,ui) {
                     $('#ubigeollegada').val(ui.item.codubigeo);
                    
                  }
                })


                $("#transportistanum").autocomplete({
                  source: '{!!URL::route('autocomplete')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  select: function(event,ui) {
                     $('#transportistanom').val(ui.item.nom);
                     $("#tdicod").val(ui.item.tdicod).attr('selected', 'selected');
                 


                  }
                })


                $("#conductornum").autocomplete({
                  source: '{!!URL::route('autocomplete')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  select: function(event,ui) {
                     $('#conductornom').val(ui.item.nom);
                     $("#tdicod").val(ui.item.tdicod).attr('selected', 'selected');
                   
                  }
                })



                
                $("#mayor").keyup(function () {

                  var valor = $(this).val();
                  var cont = 0, cantidad=0,total=0;
                   $.ajax({
                        type: 'get',
                        url: '/consultarprodmay',
                        dataType: 'json',
                        data: {'value' : $(this).val() },
                        success : function(data) {
                         
                         $("#mayor").val('');

                          if ($('#detFact >tbody >tr').length > 0){

                              $("#detFact tbody tr").each(function(){

                                 var codigo = $(this).find("td:eq(3) > input").val();
                                 
                                 if( valor == codigo){
                                   
                                    cont = cont+1;
                                    cantidad = parseFloat($(this).find("td:eq(0) > input").val())+1;
                              
                                    totalitem = parseFloat($(this).find("td:eq(7) > input").val())*cantidad;
                                    subtotalitem = totalitem/1.1055;
                                    igvitem = totalitem-subtotalitem;
                                    presigv = subtotalitem/cantidad;
                                 }
                                  
                                  if(cont >0){
                                      $(this).find("td:eq(0) > input").val(cantidad);
                                       $(this).find("td:eq(6) > input").val(presigv.toFixed(2));
                                      $(this).find("td:eq(8) > input").val(igvitem.toFixed(2));
                                      $(this).find("td:eq(9) > input").val(subtotalitem.toFixed(2));
                                      $(this).find("td:eq(10) > input").val(totalitem.toFixed(2));
                                      calculartotal();
                                     
                                      $("#mayor").focus();
                                      return false;
                                  }

                                })

                               // alert(cont);
                                 if(cont == 0){
                                   
                                      var igvitem = data[0].propun -data[0].provun;

                                       $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td hidden="hidden"><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><select style="width:100px" name="presentacion[]"  class="form-control input-sm"><option  value=""></option></select></td><td><input type="text"  name="codpro[]" id="codpro" value="'+data[0].value+'" style="background-color:#ABEBC6"   OnBlur="Calcular(this);"  OnClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" value="'+data[0].pronom+'" size="100" onfocus="Calcular(this)"; ></td><td hidden="hidden"><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden" ><input type="number" step=".00001" id="vunit" value="'+data[0].provun+'" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input readonly="readonly" type="text" class="form-control input-sm preuni" value="'+data[0].propun+'" size="20px" id="preuni"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="'+igvitem+'" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly  value="'+data[0].provun+'"  style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="'+data[0].propun+'" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
                                         
                                     }
                                }else{

                                    var igvitem = data[0].propun -data[0].provun;

                                     $('#detFact').append('<tr><td><input  type="number"  step=".00001" id="cant" size="10" value="1" name="cant[]"   OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />@if ($errors->has("cant"))<span class="help-block"><strong><font color="red">{{ $errors->first("cant") }}</font></strong></span>@endif</td><td hidden="hidden"><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "UNI") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td><td><select style="width:100px" name="presentacion[]"  class="form-control input-sm"><option  value=""></option></select></td><td><input type="text"  name="codpro[]" id="codpro" value="'+data[0].value+'" style="background-color:#ABEBC6"   OnBlur="Calcular(this);"  OnClick="Calcular(this);" onChange="Calcular(this);" OnKeyUp="Calcular(this)"; onKeypress="if(event.keyCode == 45) deleteRow(this);"  placeholder=""  class="codpro form-control input-sm"></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" value="'+data[0].pronom+'" size="100" onfocus="Calcular(this)"; ></td><td hidden="hidden"><select onChange="Calcular(this);" id="tigv" name="tigv[]" class="form-control input-sm">@foreach ($igv as $tigv)<option value="{{$tigv->tigcod}}">{{$tigv->tigdes}}</option>@endforeach</select></td><td hidden="hidden" ><input type="number" step=".00001" id="vunit" value="'+data[0].provun+'" name="vunit[]" style="text-align:right;" value="0" min="0"  readonly="readonly" class="vunit form-control input-sm" />@if ($errors->has("vunit"))<span class="help-block"><strong><font color="red">{{ $errors->first("vunit") }}</font></strong></span>@endif</td><td ><input readonly="readonly" type="text" class="form-control input-sm preuni" value="'+data[0].propun+'" size="20px" id="preuni"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text"  class="form-control input-sm" size="20px" id="vigv" readonly value="'+igvitem+'" style="text-align:right;" name="vigv[]"/>@if ($errors->has("vigv"))<span class="help-block"><strong><font color="red">{{ $errors->first("vigv") }}</font></strong></span>@endif</td><td hidden="hidden" ><input type="text" class="form-control input-sm" size="20px" id="vsub" readonly  value="'+data[0].provun+'"  style="text-align:right;" name="vsub[]"/>@if ($errors->has("vsub"))<span class="help-block"><strong><font color="red">{{ $errors->first("vsub") }}</font></strong></span>@endif</td><td><input type="text" readonly value="'+data[0].propun+'" id="vtot" style="text-align:right;" name="vtot[]" class="form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

                                }

                        
                          

                            if ($('#detFact >tbody >tr').length > 0){
                                 calculartotal();
                                 $("#mayor").val('');
                                 $("#mayor").focus();
                            }

                        }

                    })



                  
                });


            
            
             $(".codpro").autocomplete({
                  source: '{!!URL::route('consultarproducto')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  select: function(event,ui) {
                     $(this).closest('tr').find("td:eq(4) > input").val(ui.item.pronom);
                     $(this).closest('tr').find("td:eq(6) > input").val(ui.item.provun);
                     $(this).closest('tr').find("td:eq(7) > input").val(ui.item.propun);
                     $(this).closest('tr').find("td:eq(1) > select").val(ui.item.umecod).attr('selected', 'selected');
                     $(this).closest('tr').find("td:eq(3) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(4) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(5) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(6) > input").prop("readonly",true);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);

                  }
                })



                $(".codpro").on('dblclick', function (){
                    $(this).closest('tr').find("td:eq(3) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(4) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(6) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(0) > input").prop("readonly",false);
                     $(this).closest('tr').find("td:eq(0) > input").val(1);
                    $(this).closest('tr').find("td:eq(3) > input").val('');
                    $(this).closest('tr').find("td:eq(4) > input").val('');
                    $(this).closest('tr').find("td:eq(6) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(7) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(8) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(9) > input").val(0.00);
                    $(this).closest('tr').find("td:eq(10) > input").val(0.00);
                    calculartotal();
                    validartabla();

                })


        $("#numdoc").on('change',function(){
            var numdoc = parseInt($('#numdoc').val(),10);
            $("#numdoc").val(numdoc);
        })

        $('#codunique').val($('#txt_IdEmpresa').val()+''+$('#txt_tdocod').val()+''+$('#serdoc').val() + '' +parseInt($('#numdoc').val(),10));


        $('#serdoc').on('change', function() {
              $('#codunique').val($('#txt_IdEmpresa').val()+''+$('#txt_tdocod').val()+''+$('#serdoc').val() + '' +parseInt($('#numdoc').val(),10));
        })

        $('#numdoc').on('change', function() {
              $('#codunique').val($('#txt_IdEmpresa').val()+''+$('#txt_tdocod').val()+''+$('#serdoc').val() + '' +parseInt($('#numdoc').val(),10));
        })

        $('#mondoc').on('change',function (){
            var fecemi = $("#fecEmi").val();
            if($('#mondoc').val()!='PEN'){
               $.ajax({
                 type: "get",
                 url:"/consultartipcambio",
                 data:{fecemi:fecemi},
                  success:function(res) {
                    $("#camdoc").val(res);
                }
             })
            }else {
                $('#camdoc').val(0);
            }

        })


        jQuery.validator.addMethod("alphanumeric", function(value, element) {
            return this.optional(element) || /^[\w.]+$/i.test(value);
        }, "Letters, numbers, and underscores only please"),


        $('#formfact').validate({

            rules: {

                   tdicod:{
                    required:true
                   },


                serdoc:{
                    required:true,
                    alphanumeric:true,
                    maxlength: 4
                },
                fecEmi:{
                    required: true,
                    date: true
                },
                fecEnv:{
                    date: true
                },
                numdoc:{
                    required:true,
                    digits:true,
                    maxlength:8
                },

                Descuentos:{
                    number:true,
                    min:0
                },
                otros:{
                    number:true,
                    min:0
                },
                otrosc:{
                    number:true,
                    min:0
                },
                camdoc:{
                    required : function () {
                                return $('#formfact select[name="mondoc"]').val() != 'PEN';
                               },
                    min: {
                        param: 1,
                        depends:  function () {
                                return $('#formfact select[name="mondoc"]').val() != 'PEN';
                        }
                    }
                },

                fecEmi:"required",
                fecVen:"required",
                clinum:{
                    required:true,
                    digits:true,
                    maxlength:11
                },
                clinom:"required",
                clidir:"required",
                clicor: {
                    email:true
                },
                obser:{
                    maxlength: 250
                }

               },


            messages: {

                tdicod:{
                    required:"Elegir el tipo de documento"
                },
                serdoc:{
                    required:"Ingresar la serie del documento",
                    alphanumeric:"Ingresar un serie válida",
                    maxlength:"El número de serie es de 4 digitos"
                },
                camdoc:{
                    required:"Ingresar el tipo de cambio",
                    min:"Tipo de cambio debe ser mayor a 0"
                },
                numdoc:{
                    required:"Ingresar número del comprobante",
                    digits:"Ingresar un N° de comprobante válido",
                    maxlength:"El N° de comprobante tiene como máximo 8 dígitos"
                } ,
                fecEmi:{
                    required:"Ingresar la fecha de emisión",
                    date:"Ingresar una fecha válida"
                },
                fecEnv:{
                    date:"Ingresar una fecha válida"
                },
                descuentos:{
                    number:"Ingresar un monto válido",
                    min:"Ingresar un monto igual o mayor a 0"
                },
                otrosc:{
                    number:"Ingresar un monto válido",
                    min:"Ingresar un monto igual o mayor a 0"
                },
                otros:{
                    number:"Ingresar un monto válido",
                    min:"Ingresar un monto igual o mayor a 0"
                },
                clinum:{
                    required:"Ingresar N° Documento de Identidad",
                    digits:"Ingresar un N° de documento válido",
                    maxlength:"El N° documento de identidad es como máximo de 11 dígitos"

                },
                clinom:"Ingresar el nombre del cliente",
                clidir:"Ingresar la dirección del cliente",
                clicor:{
                    email:"Ingresar un email válido"
                },
                obser:{
                    maxlength:"El número máximo de caracteres es de 250"
                }
            }

        })



        if ($("#mondoc").val()!='PEN'){
            $("#camdoc").prop('readonly',false);
        }else {
            $("#camdoc").prop('readonly',true);
            $("#camdoc").val(0);
        }

        //Agregar al modal filas con los elementos para registrar las guías de remisión
        $('#addgr').click(function() {


            // Añadir caja de texto.
            $('#detFact').append("<tr><td><select class='tdr form-control' name='tdr[]' id='tdr'>@foreach($doccomprobante as $docc) @if($docc->tdocod=='09' || $docc->tdocod=='31') <option value='{{$docc->tdocod}}'>{{$docc->tdodes}}</option> @endif @endforeach</select></td><td><input value='{{old('tdrser[]')}}' type='text' class='tdrser form-control' name='tdrser[]' id='tdrser' placeholder='Serie...'></td><td><input type='number' name='tdrnum[]' id='tdrnum' value='{{old('tdrnum[]')}}' class='tdrnum form-control' placeholder='Número...'></td><td><button type='button' name='btdelgr[]' id='btdelgr'  class='btdelgr btn btn-danger'>Eliminar</button></td></tr>");

                $('.tdr').on('change',function () {
                     validarexistente();
                })

                $('.tdrnum').on('change',function () {
                     validarnumero();
                     validarexistente();
                })

                $('.tdrser').on('change',function () {
                     validaralfanumerico();
                     validarexistente();
                })

        })

        $("#detFact").on('click','.btdelgr', function () {

            validaralfanumerico();
            validarnumero();
            validarexistente();
            $(this).closest('tr').remove();

        })


        $('#add').click(function() {

            agregarlinea();
        })

         $('#addm').click(function() {
 
            agregarlineamayor();
        })

         

         $("#cambia").click(function(){
            $("#texto").toggle(1000);
         })

        $("#addgr").click(function(){
            $('#gremi').show();
        })

//INICIO SUBMIT-----------------------------------------------------------------

        $( "#formfact" ).submit(function( event ) {


            var efectivo1=0,visa=0,mastercar=0,totventa=0,sumarTot=0,resta=0;
             efectivo1 = $("#efectivo1").val();
             visa = $("#visa").val();
             mastercard = $("#mastercard").val();
             totventa = $("#total").val();
             sumarTot =  parseFloat(efectivo1) +  parseFloat(visa) +  parseFloat(mastercard);
             resta = totventa - sumarTot;
            
            if(sumarTot < totventa){
                alert('Falta Pagar '+resta);
                event.preventDefault();
            }

            if ($('#detFact >tbody >tr').length == 0){
                $('#alertitem').show();
                event.preventDefault();
            }
            var condet = 0,conpro=0,concant=0;
            $('#detFact >tbody >tr').each(function(){
                var det = $(this).find("td:eq(4) > input").val();
                var pro = $(this).find("td:eq(3) > input").val();
                var cant = $(this).find("td:eq(2) > input").val();
                if(pro==''){
                    conpro++;
                }else if(det==''){
                    condet++
                }else if(cant<1){
                    concant++
                }
            })

            if(conpro>0){
                $('.alertpro').show();
                event.preventDefault();
            }else{
                $('#alertpro').hide();
            }

            if(condet>0) {
                $('.alertdet').show();
                event.preventDefault();
            }else{
                $('#alertdet').hide();
            }

            if(concant>0){
                $('.alertcant').show();
                var cantidad = event.preventDefault();
            }else{
                $('#alertcant').hide();
            }

            validaralfanumerico();
            validarnumero();
            validarexistente();
            /*if($('#mondoc').val()!='1' && $('#camdoc').val()<=0 ){
              $('#error-camdoc').show();

              event.preventDefault();
            }*/



        })

//FIN ---------------------------------------------------------------------------------

        $("#mondoc").on('change',function(){
            var mondoc = $("#mondoc").val();

            if(mondoc == 'PEN'){
                $('#camdoc').val(0);
                 $('#error-camdoc').hide();
                $("#camdoc").prop('readonly',true);
            } else {
                $("#camdoc").prop('readonly',false);
            }

        })


         $('#fecEmi').on('change', function() {
              $('#camdoc').val(0);
        })


      /*  $('#clinum').on('change', function() {
            var clinum = $('#clinum').val();
            var clinom = $('#clinom').val();
            var clidir = $('#clidir').val();
            var clicor = $('#clicor').val();

            if (clinum!="" && clinom!="" && clidir!=""){
                $('#clinom').prop("readonly",true);
                $('#clidir').prop("readonly",true);
                $('#clicor').prop("readonly",true);

                var posi = clinum.indexOf("|",0);
                var clinum1 = clinum.substring(0,posi);
                $('#clinum').val(clinum1);
            }
        })
*/
        $('#clinum').on('dblclick', function() {
            $('#clinum').prop("readonly",false);
            $('#clinom').prop("readonly",false);
            $('#clidir').prop("readonly",false);
            $('#clicor').prop("readonly",false);
            $('#clinum').val("");
            $('#clinom').val("");
            $('#clidir').val("--");
            $('#clicor').val("");
        })


        $('#isc').on('change', function() {
            if($('#isc').val()==0 || $('#isc').val()==""){
                $('#isc').val($svalor.toFixed(2))
            }
        })

        $('#inaf').on('change', function() {
            if($('#inaf').val()==0 || $('#inaf').val()==""){
                $('#inaf').val($svalor.toFixed(2))
            }
        })

        $('#clinum').on('change', function() {
            if($('#grat').val()==0 || $('#grat').val()==""){
                $('#grat').val($svalor.toFixed(2))
            }
        })

        $('#grav').on('change', function() {
            if($('#grav').val()==0 || $('#grav').val()==""){
                $('#grav').val($svalor.toFixed(2))
            }
        })
        $('#exon').on('change', function() {
            if($('#exon').val()==0 || $('#exon').val()==""){
                $('#exon').val($svalor.toFixed(2))
            }
        })
        $('#igv').on('change', function() {
            if($('#igv').val()==0 || $('#igv').val()==""){
                $('#igv').val($svalor.toFixed(2))
            }
        })

        $('#desc').on('change', function() {
            if($('#desc').val()==0 || $('#desc').val()==""){
                $('#desc').val($svalor.toFixed(2))
            }
        })

        $('#otros').on('change', function() {
            if($('#otros').val()==0 || $('#otros').val()==""){
                $('#otros').val($svalor.toFixed(2))
            }
        })

        $('#otrosc').on('change', function() {
            if($('#otrosc').val()==0 || $('#otrosc').val()==""){
                $('#otrosc').val($svalor.toFixed(2))
            }
        })


    });
</script>


<script  type="text/javascript">


function deleteRow(btn) {

  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
  calculartotal();
  calcularvuel();

  if ($('#detFact >tbody >tr').length == 0){
    $('.alertitem').show();
  }
  validartabla();
};


function agregarlinea(){
            var iCnt = 0;
            iCnt = iCnt + 1;
            $('.alertitem').hide();

            $('#detFact').append('<tr>'+
                '<td><input type="text"  name="codpro[]" id="codpro" style="background-color:#ABEBC6" value="0" readonly="readonly"  placeholder=""  class="codpro form-control input-sm"></td>'+
                '<td><input  class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" ></td>'+
                '<td><input  type="number"  step="any" id="cant" size="10" value="1" name="cant[]" class="cant form-control input-sm" /></td>'+
                '<td><select style="width:100px" name="unid[]"  class="form-control input-sm"> @foreach($unidades as $und) @if($und->umecod == "NIU") <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> @else <option  value="{{$und->umecod}}">{{$und->umenom}}</option> @endif @endforeach </select></td>'+
                '<td hidden="hidden"><input type="text"  name="IdProducto[]" id="IdProducto" style="background-color:#ABEBC6"  value="0"  class="codpro form-control input-sm"></td>'+
                '<td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');



         


}

function Calcular(ele) {


  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {
      var totgrat=0, totgrav=0, totinef=0,totexon=0,totigvi=0;
      var totitemgrat=0, totitemgrav=0, totiteminef=0, totitemexon=0,totitemivap=0;
      var calculo, valuni, totitem,presigv,subtotal,total,igvitem;
      var tigv = $(this).find("td:eq(5) > select").val();
      var precigv = $(this).find("td:eq(7) > input").val();
      var cantidad =$(this).find("td:eq(0) > input").val();

       if(tigv=='10'){

        totitemgrav = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();
        igv = totitemgrav * 0.18;

        presigv = (precigv/1.1055);
        subtotal = presigv*cantidad;
        total = subtotal*1.18;
        igvitem = subtotal*0.18;

        $(this).find("td:eq(6) > input").val(presigv.toFixed(2));
        $(this).find("td:eq(9) > input").val(subtotal.toFixed(2));
        $(this).find("td:eq(8) > input").val(igvitem.toFixed(2));
        $(this).find("td:eq(10) > input").val(total.toFixed(2));


       }

       if (tigv=='8')
       {

        totitemexon = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();

        presigv = (precigv/1);
            subtotal = presigv*cantidad;
            total = subtotal*1;
            igvitem = 0;


        $(this).find("td:eq(6) > input").val(presigv.toFixed(2));

        $(this).find("td:eq(9) > input").val(subtotal.toFixed(2));
        $(this).find("td:eq(8) > input").val(igvitem.toFixed(2));
        $(this).find("td:eq(10) > input").val(total.toFixed(2));



       }

        if ( tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='2' || tigv=='3' || tigv=='4' || tigv=='5' || tigv=='6' || tigv=='7')
       {
            totitemgrat = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();
            presigv = (precigv/1);
            subtotal = presigv*cantidad;
            total = subtotal*1;
            igvitem = 0;

            $(this).find("td:eq(6) > input").val(presigv.toFixed(2));
            $(this).find("td:eq(9) > input").val(subtotal.toFixed(2));
            $(this).find("td:eq(8) > input").val(igvitem.toFixed(2));
            $(this).find("td:eq(10) > input").val(total.toFixed(2));
       }

       if (tigv=='9' || tigv=='16')
       {
          totiteminaf = $(this).find("td:eq(0) > input").val() * $(this).find("td:eq(6) > input").val();
          presigv = (precigv/1);
            subtotal = presigv*cantidad;
            total = subtotal*1;
            igvitem = 0;


        $(this).find("td:eq(6) > input").val(presigv);

            $(this).find("td:eq(9) > input").val(subtotal.toFixed(2));
            $(this).find("td:eq(8) > input").val(igvitem.toFixed(2));
            $(this).find("td:eq(10) > input").val(total.toFixed(2));
       }



   });
  calculartotal();
  calcularvuelto();

};

function validartabla(){
     var condet = 0,conpro=0,concant=0;
     $('#detFact >tbody >tr').each(function(){
        var det = $(this).find("td:eq(4) > input").val();
        var pro = $(this).find("td:eq(3) > input").val();
        var cant = $(this).find("td:eq(0) > input").val();
        if(pro==''){
            conpro++;
        }else if(det==''){
            condet++
        }else if(cant<1){
            concant++               }
        })
        if(conpro>0){
            $('.alertpro').show();
        }else{
            $('#alertpro').hide();
        }

        if(condet>0) {
            $('.alertdet').show();
        }else{
            $('#alertdet').hide();
        }

        if(concant>0){
            $('.alertcant').show();
        }else{
            $('#alertcant').hide();
        }
        if(conpro>0 || condet >0 || concant>0){
            event.preventDefault();
        }
};


function  buscartransportista(){


  var formulario = $("#transportistanum").val();


  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#transportistanom').val(respuesta[0].nom);
   $("#transportistatdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

  // $("#imgloadcliente").hide();
   // $(".botones").show();
          
  });

  

}

function  buscarcliente(){


  var formulario = $("#clinum").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#clinom').val(respuesta[0].nom);
   $('#clidir').val(respuesta[0].dir);
   $('#clitel').val(respuesta[0].telefono);
   $('#clicor').val(respuesta[0].cor);
   $('#clicod').val(respuesta[0].clicod);
   $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');


  });

  

}


function  buscarconductor(){


  var formulario = $("#conductornum").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#conductornom').val(respuesta[0].nom);

   $("#conductortdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

          
  });

  

}

function validaralfanumerico(){

    var condr=0;
    var alfn = /^[\w]+$/;
    var serdr;

    if ($('#detFact >tbody >tr').length == 0){
     $('#detFact >tbody >tr').each(function(){

        serdr = $(this).find("td:eq(1) > input").val();

        if(serdr.trim()!=""){
            if(!alfn.test(serdr.toString())){
                condr++;
            }
            if(condr>0){
                 $('.alertgr').show();
            }else{
                 $('.alertgr').hide();
            }

            if(condr>0){
                event.preventDefault();
            }
        }

    })
    }
};

function validarexistente(){

    var cont=0;
    var tempser,tempnum,tempdocr;

     if ($('#detFact >tbody >tr').length == 0){
     $('#detFact >tbody >tr').each(function(){
        var docr = $(this).find("td:eq(0) > select").val();
        var ser = $(this).find("td:eq(1) > input").val();
        var num =$(this).find("td:eq(3) > input").val();

        if(ser.trim()!="" && num.trim()!=""){
            if(tempser==ser && tempnum==num && tempdocr==docr){
                cont++;
            }
            if(cont>0){
                 $('.alertexist').show();
            }else{
                 $('.alertexist').hide();
            }

            if(cont>0){
                event.preventDefault();
            }
        }

        tempser = ser;
        tempnum = num;
        tempdocr = docr;
    })
    }
};


function validarnumero(){
     if ($('#detFact >tbody >tr').length == 0){
    var connum=0;
    var valnum =  /^\d*$/;

     $('#detFact >tbody >tr').each(function(){
        var num = $(this).find("td:eq(2) > input").val();

        if(num.trim()!=""){
              if(!valnum.test(num.toString())){
                connum++;
            }
            if(connum>0){
                 $('.alertnum').show();
            }else{
                 $('.alertnum').hide();
            }

            if(connum>0){
                event.preventDefault();
            }
        }

    })
    }
};


function calcularvuelto(){

    var dinero, totalv;
    dinero = $('#dinero').val();
    totalv = $('#total').val();

    if(totalv > dinero){
         vuelto = '0.00';
        $('#vuelto').val(vuelto);
    }else{
        vuelto = dinero-totalv;
        $('#vuelto').val(vuelto.toFixed(2));
    }
   
}

function calculartotal(){

   var totgrav = 0,totinaf=0,totexon=0,totgrat=0,totivap=0,totigv=0,total;

   var totalinaf=0,totalexp=0,totalgrav=0,totalexon=0,inaf=0,exon=0,grav=0,dscto=0,dsctgrav=0,dsctinaf=0,dsctexon=0,caligv=0,caltotal=0;
   var $svalor=0;
    $("#detFact tbody tr").each(function(){
    //sum= sum + parseFloat($(this).find("td:eq(5) > input").val()) ;


       var tigv = $(this).find("td:eq(5) > select").val();

       //Calculo por tipos de IGV
       if(tigv=='10'){

        totgrav = totgrav + parseFloat($(this).find("td:eq(9)  > input").val());
       }

       if (tigv=='8')
       {
            totexon = totexon + parseFloat($(this).find("td:eq(9) > input").val());
       }

       if ( tigv=='11' || tigv=='12' || tigv=='13' || tigv=='14' || tigv=='15' || tigv=='2' || tigv=='3' || tigv=='4' || tigv=='5' || tigv=='6' || tigv=='7')
       {
            totgrat = totgrat + parseFloat($(this).find("td:eq(9) > input").val());

       }

       if (tigv=='9' || tigv=='16')
       {
            totinaf = totinaf + parseFloat($(this).find("td:eq(9) > input").val());

       }


        totigv = totigv + parseFloat($(this).find("td:eq(8) > input").val());

       var otrosc = $('#otrosc').val();
       var otros = $('#otros').val();
       var desc = $('#desc').val();



       if(otrosc ==""){
         $('#otrosc').val(0);
       }

       if(otros ==""){
         $('#otros').val(0);
       }


       if(desc ==""){
         $('#desc').val(0);
       }


       $('#inaf').val(totinaf.toFixed(2));
       $('#grat').val(totgrat.toFixed(2));
       $('#exon').val(totexon.toFixed(2));
       $('#igv').val((totigv).toFixed(2));
       $('#grav').val((totgrav).toFixed(2));


       var total = parseFloat($('#otrosc').val()) + parseFloat($('#otros').val()) +parseFloat(totgrav) + parseFloat(totigv) + parseFloat($('#exon').val())+ parseFloat($('#inaf').val());
       $('#total').val(total.toFixed(2));

         $('#efectivo1').val(total.toFixed(2));
   });

    if ($('#detFact >tbody >tr').length == 0){

     $('#inaf').val($svalor.toFixed(2));
       $('#grat').val($svalor.toFixed(2));
       $('#grav').val($svalor.toFixed(2));
       $('#exon').val($svalor.toFixed(2));
       $('#igv').val($svalor.toFixed(2));
       $('#desc').val($svalor.toFixed(2));
       $('#otros').val($svalor.toFixed(2));
       $('#otrosc').val($svalor.toFixed(2));
       $('#total').val($svalor.toFixed(2))
         $('#efectivo1').val($svalor.toFixed(2));
    };

    if( $('#desc').val() == 0){
        $('#totdesc').val($svalor.toFixed(2));
    }

    if( $('#desc').val()>0){
          inaf = totinaf;
          exon = totexon;
          grav = totgrav;
          dscto = $('#desc').val()/100;

           if(inaf>0){
            dsctinaf = dscto*inaf;
            totalinaf= inaf-dsctinaf;
          }

          if(exon>0){
            dsctexon = dscto*exon;
            totalexon= exon-dsctexon;
          }

          if(grav>0){
            dsctgrav = dscto*grav;
            totalgrav= grav-dsctgrav;
          }


          totdesc = dsctinaf+dsctexon+dsctgrav;
          caligv = totalgrav*0.18;
          caltotal = parseFloat($('#otrosc').val()) + parseFloat($('#otros').val())+totalinaf+totalexon+totalgrav+caligv;
          $('#inaf').val(totalinaf.toFixed(2));
          $('#exon').val(totalexon.toFixed(2));
          $('#grav').val(totalgrav.toFixed(2));
          $('#igv').val(caligv.toFixed(2));
          $('#totdesc').val(totdesc.toFixed(2));
          $('#total').val(caltotal.toFixed(2));
           $('#efectivo1').val(caltotal.toFixed(2));


    }

   // calculardescuento();
}
function formatearcliente(){
    var clinum = $('#clinum').val();
    var clinom = $('#clinom').val();
    var clidir = $('#clidir').val();
    var clicor = $('#clicor').val();

    if (clinum!="" && clinom!="" && clidir!=""){
        $('#clinom').prop("readonly",true);
        $('#clidir').prop("readonly",true);
        $('#clicor').prop("readonly",true);

        var posi = clinum.indexOf("|",0);
        var clinum1 = clinum.substring(0,posi);
        $('#clinum').val(clinum1);
    }
}

function calculardescuento(){
  var totalinaf=0,totalexp=0,totalgrav=0,totalexon=0,inaf=0,exon=0,grav=0,exp=0,dscto=0,dsctgrav=0,dsctinaf=0,dsctexon=0,caligv=0,dsctexp=0,caltotal=0;
  inaf = $('#inaf').val();
  exon = $('#exon').val();
  grav = $('#grav').val();
  exp = $('#exp').val();
  dscto = $('#desc').val()/100;

  if(inaf>0){
    dsctinaf = dscto*inaf;
    totalinaf= inaf-dsctinaf;
  }

  if(exon>0){
    dsctexon = dscto*exon;
    totalexon= exon-dsctexon;
  }

  if(grav>0){
    dsctgrav = dscto*grav;
    totalgrav= grav-dsctgrav;
  }
  if(exp>0){
    dsctexp = dscto*exp;
    totalexp= exp-dsctexp;
  }

  caligv = totalgrav*0.18;
  totdesc = dsctinaf+dsctexon+dsctgrav+dsctexp;
  caltotal = parseFloat($('#otrosc').val()) + parseFloat($('#otros').val()) +totalinaf+totalexon+totalgrav+totalexp+caligv;
  $('#inaf').val(totalinaf.toFixed(2));
  $('#exon').val(totalexon.toFixed(2));
  $('#grav').val(totalgrav.toFixed(2));
  $('#exp').val(totalexp.toFixed(2));
  $('#igv').val(caligv.toFixed(2));
  $('#total').val(caltotal.toFixed(2));
  $('#totdesc').val(totdesc.toFixed(2));



}
</script>

     <br><div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                @if(session()->has('info'))
                   <div class="alert alert-danger">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Alerta!</strong> {{ session('info') }}
                    </div>
                @endif


                @if(session()->has('success'))
                    <div class="alert alert-success">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Información!</strong> {{ session('success') }}
                      
                    </div>
                    
                
                @endif
             <a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
                  
                  
            </div>
        </div>
    </div>
    <div hidden="hidden" class="container-fluid">
        <div class="col-lg-12">
            <div class="btn-toolbar" role="toolbar" aria-label="...">
                <div class="btn-group" >
            
                        <a href="/guiasremision"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-search"></span> Consultar Comprobantes</button></a>
                </div>
                <div class="btn-group" >
                       <a href="/guiasremision/create"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nueva Gu&iacute;a</button></a>
            
                </div>
        
              
            </div>
        </div>
    </div>
    
 
    {!!Form::open(array('url'=>'/guiasremision','autocomplete'=>'off','method'=>'POST','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}

        @if(!empty($cabecera))
         <input type="hidden" readonly="readolnly" name="id_doc_ref" value="{{$cabecera->IdCpe_cabecera}}">
        @else
         <input type="hidden" readonly="readolnly" name="id_doc_ref" value="0">
        @endif
        <div class="container-fluid">
            <div class="row" >
                <div class="col-lg-12">
                <div class="box">
                    <div class="box-header" style="background:#337ab7;">
                        <font color="white" size="2"><center><strong>EMITIR GUIA DE REMISIÓN ELECTRÓNICA</strong></center></font>
                    </div>
                    
                    <div class="box-body">
                     
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
                        <label class="error" for="codunique" generated="true"></label>

                           <div class="col-lg-2" >
                         <div class="form-group form-group-sm">
            <LABEL>Sucursales</LABEL>
            <select name="id_empresa_negocio" id="id_empresa_negocio" class="form-control">
              @foreach($sucursal as $suc)
                <option value="{{$suc->id_empresa_negocio}}">{{$suc->tipo_negocio}}</option>
              @endforeach
            </select>
          </div>
        </div>

      <div class="col-lg-2" id="div_almacen">
        <div class="form-group form-group-sm">
          <LABEL>Almacenes</LABEL>
          <select name="id_almacen" id="id_almacen" class="form-control">
            @foreach($almacen as $alm)
            <option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
            @endforeach
          </select>
        </div>
      </div>    

         <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>AFECTARÁ EL STOCK</label>
                                <select name="afec_stock" class="form-control">
                                      <option value="0">NO</option>
                                    <option value="1">SI</option>
                                  
                                </select>
                                
                            </div>
                        </div>


                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Serie</label>
                                <input name="serdoc"  value="{{$senudoc->serieguia}}" id="serdoc" class="form-control" readonly="readonly">
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>N° Doc.</label>
                              
                                <input type="number"  id="numdoc" name="numdoc" value="{{$senudoc->numeroguia+1}}" class="form-control" readonly="readonly">

                            </div>
                        </div>
                       
                        <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Motivo</label>
                                <select name="motivo" id="motivo" class="form-control">
                                    @foreach ($motivos as $mot)

                                      @if($mot->IdMotivo=='01')
                                              <option selected="selected" value='{{$mot->IdMotivo}}'>{{$mot->motivo}}</option>
                                      @else
                                              <option value='{{$mot->IdMotivo}}'>{{$mot->motivo}}</option>
                                      @endif
                                  
                                    @endforeach
                                </select>
                                

                            </div>
                        </div>
                        <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Modalidad</label>
                                <select name="modalidad" id="modalidad" class="form-control">
                                    @foreach ($modalidades as $mod)
                                        <option value='{{$mod->IdModalidad}}'>{{$mod->modalidad}}</option>
                                    @endforeach
                                </select>
                              
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Bultos</label>
                                <input type="number" id="bultos" name="bultos"  min="1" value="1" class="form-control">
                                @if ($errors->has('bultos'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('bultos') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                        
                          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>peso</label>
                                <input type="number" id="peso" name="peso"   value="1" class="form-control">
                            
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Emision</label>
                                <input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                                @if ($errors->has('fecEmi'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('fecEmi') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Traslado</label>
                                <input type="date" id="fechatraslado" name="fechatraslado" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                                @if ($errors->has('fechatraslado'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('fechatraslado') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                         <div hidden="hidden"  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>DESCONTAR STOCK</label>
                                <select name="estadostock" class="form-control">
                                        <option selected="" value='0'>No</option>
                                        <option value='1'>Si</option>
                                </select>
                              
                            </div>
                        </div>
                        
                 
                        <input type="hidden" name="tdocod" id="tdocod" value="09" class="form-control">
                    </div>
                </div>  
            </div>
     
                <div class="col-lg-12">
                <div class="box">
                    <div class="box-header" style="background:#337ab7;">
                        <font color="white"><strong>Datos del Cliente</strong></font>
                    </div>
                    <div class="box-body">
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
                        <div class="row">
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Documento</label>
                                <select name="tdicod" id="tdicod" class="form-control">
                                    @foreach($docidentidad as $doc)
                                      @if(!empty($cabecera))
                                        @if($doc->tdicod == $cabecera->tdicod)
                                        <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @else
                                        <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @endif
                                      @else
                                         @if($doc->tdicod == '1')
                                        <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @else
                                        <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @endif
                                      @endif
                                       
                                    @endforeach
                                </select>
                            
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">N° Documento</label>
                                @if(!empty($cabecera))
                                <input type="text"  name="clinum" id="clinum" value="{{$cabecera->ccandi}}" onKeypress="if(event.keyCode == 13) buscarcliente();"   placeholder="" class="form-control">
                                @else
                                <input type="text"  name="clinum" id="clinum" value="00000000" onKeypress="if(event.keyCode == 13) buscarcliente();"   placeholder="" class="form-control">
                                @endif
                                
                          
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre ó Razón Social</label>
                                @if(!empty($cabecera))
                                      <input type="text" name="clinom" id="clinom" value="{{$cabecera->ccanom}}" class="form-control">
                                @else
                                      <input type="text" name="clinom" id="clinom" value="Varios" class="form-control">
                                @endif
                          
                           
                            </div>
                        </div>
                 
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Dirección</label>
                                @if(!empty($cabecera))
                                   <input name="clidir" id="clidir" value="--" class="form-control">
                                @else
                                   <input name="clidir" id="clidir" value="--" class="form-control">
                                @endif
                             
                              
                            </div>
                        </div>
                        <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electrónico</label>
                                <input name="correo" id="correo" value="{{old('correo')}}" class="form-control">
                              
                            </div>
                        </div>
                    </div>
                    </div>
                </div>  
           
        </div>
       

         <div class="col-lg-6">
                <div class="box">
                    <div class="box-header" style="background:#337ab7;">
                        <font color="white"><strong>Direcci&oacute;n de Partida</strong></font>
                    </div>
                    <div class="box-body">
                      
                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="ubigeopartida">Ubigeo</label>
                                 <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="ubigeopartida" id="ubigeopartida">
                                     @foreach($ubigeos as $ubi)
                                      <option value="{{$ubi->ubi_cod}}">{{$ubi->ubi_des}}</option>
                                     @endforeach
                                  </select>
                               
                               
                              </div>
                          </div>
                        
                         

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Direcci&oacute;n</label>
                                <input type="text" name="direccionpartida" id="direccionpartida"  class="form-control">
                            
                            </div>
                        </div>
                      
                         <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" id='div_cod_local_part'>
                            <div class="form-group form-group-sm">
                                <label>Codigo Fiscal</label>
                                <input type="text" name="cod_local_part" id="cod_local_part"  class="form-control">
                            
                            </div>
                        </div>

                        <input type="hidden" name="codubigeopartida" >
                    </div>
                    </div>
                </div>  
        </div>

         <div class="col-lg-6">
                <div class="box">
                    <div class="box-header" style="background:#337ab7;"> 
                        <font color="white"><strong>Direcci&oacute;n de Llegada</strong></font>
                    </div>
                    <div class="box-body">
                      
                        <div class="row">
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="ubigeollegada">Ubigeo</label>
                                    <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="ubigeollegada" id="ubigeollegada">
                                     @foreach($ubigeos as $ubi)
                                      <option value="{{$ubi->ubi_cod}}">{{$ubi->ubi_des}}</option>
                                     @endforeach
                                  </select>
                                
                              </div>
                          </div>
                       
                      
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Direcci&oacute;n</label>
                                <input type="text" name="direccionllegada" id="direccionllegada"  class="form-control">
                                
                            </div>
                        </div>
                    
                           <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" id='div_cod_local_des'>
                            <div class="form-group form-group-sm">
                                <label>Codigo Fiscal</label>
                                <input type="text" name="cod_local_part" id="cod_local_des"  class="form-control">
                            
                            </div>
                        </div>

                        <input type="hidden" name="codubigeollegada" >
                    </div>
                    </div>
                </div>  
        </div>
</div>
</div>
         <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6" id="div_dat_transp">
                <div class="box">
                    <div class="box-header" style="background:#337ab7;"> 
                        <font color="white"><strong>Datros de Transportista</strong></font>
                    </div>
                    
                     <div class="box-body">
                        <div class="row">
                          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Tipo Documento</label>
                                  <select name="transportistatdicod" id="transportistatdicod" class="form-control">
                                      @foreach($docidentidad as $doc)
                                          @if($doc->tdicod == '1')
                                          <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @else
                                          <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @endif
                                      @endforeach
                                  </select>
                               
                              </div>
                          </div>

                          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="transportistanum">N° Documento</label>
                                  <input type="text"  name="transportistanum" id="transportistanum" onKeypress="if(event.keyCode == 13) buscartransportista();"     placeholder="" class="form-control">
                                
                              </div>
                          </div>
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Nombre ó Razón Social</label>
                                  <input type="text" name="transportistanom" id="transportistanom"  class="form-control">
                                 
                              </div>
                          </div>
                    
                      <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Licencia</label>
                                  <input name="licencia" id="licencia" class="form-control">
                                 
                              </div>
                          </div>
                          
                        </div>
                    </div>
                </div>  
            </div>
     
                <div class="col-lg-6" id="div_dat_cond">
                  <div class="box">
                    <div class="box-header" style="background:#337ab7;"> 
                        <font color="white"><strong>Datos de Conductor</strong></font>
                    </div>
                    <div class="box-body">
                        <div class="row">
                          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Tipo Documento</label>
                                  <select name="conductortdicod" id="conductortdicod" class="form-control">
                                      @foreach($docidentidad as $doc)
                                          @if($doc->tdicod == '1')
                                          <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @else
                                          <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @endif
                                      @endforeach
                                  </select>
                               
                              </div>
                          </div>
                          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="conductornum">N° Documento</label>
                                  <input type="text"  name="conductornum" id="conductornum" onKeypress="if(event.keyCode == 13) buscarconductor();"   placeholder="" class="form-control">
                                 
                              </div>
                          </div>
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Nombre ó Razón Social</label>
                                  <input type="text" name="conductornom" id="conductornom"  class="form-control">
                                  
                              </div>
                          </div>
                 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Placa</label>
                                  <input name="placa" id="placa" class="form-control">
                                 
                              </div>
                          </div>
                         
                        </div>
                    </div>
                </div>  
              </div>
            </div>
          </div>
        <div class="container-fluid detalle">
            <div class="row">
                <div class="col-lg-12">
                <div class="box">
                    <div class="box-header" style="background:#337ab7;"> 
                        <font color="white"><center><strong>Detalle</strong></center></font>
                    </div>
                     
                    <div  class="box-body">
                     
                                  <div class="row">
                         
        <div  class="col-lg-12">
          <div class="form-group form-group-sm" id="divactpro">
            <label>BUSCAR PRODUCTO</label>
            <select data-tags='true' style=" font-weight: bold;" autocomplete="false" class="form-control" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio();" onchange="ingresar_cantidad_precio();"  name="producto" id="producto">
             
            </select>
          </div>
          
        </div>
                      </div>

                          <div class="row">
                       <div class="col-lg-12">
                        <table id="detFact" class="table">
                            <thead>
                                <th>C&oacute;digo</th>
                                <th width="70%">Descripci&oacute;n</th>
                                <th>Cantidad</th>
                                <th>Unidad</th>
                                <th><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                            </thead>
                            <tbody id="">
                              @if(!empty($items))
                               @foreach($items as $item)
                                  <tr>
                                    <td><input type="text"  name="codpro[]" id="codpro" style="background-color:#ABEBC6"  value="{{$item->procod}}"  class="codpro form-control input-sm"></td>
                                    <td> <input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" onChange="Calcular(this);" value="{{$item->cdedes}}" OnKeyUp="Calcular(this)"; onfocus="Calcular(this)"; ></td>
                                    <td><input  type="number"  step=".00001" id="cant" size="10" name="cant[]" value="{{$item->cdecan}}"  OnKeyUp="Calcular(this);" onKeypress="if(event.keyCode == 45) deleteRow(this);" class="cant form-control input-sm" />
                                    </td>
                                    <td>
                                      <select style="width:100px" name="unid[]"  class="form-control input-sm"> 
                                        @foreach($unidades as $und) 
                                          @if($und->umecod == $item->umecod) 
                                          <option  selected="selected" value="{{$und->umecod}}">{{$und->umenom}}</option> 
                                          @else 
                                          <option  value="{{$und->umecod}}">{{$und->umenom}}</option> 
                                          @endif 
                                        @endforeach 
                                      </select>
                                    </td>
                                      <td hidden="hidden"><input type="text"  name="IdProducto[]" id="IdProducto" style="background-color:#ABEBC6"  value="{{$item->IdProducto}}"  class="codpro form-control input-sm"></td>
                                    <td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>
                                @endforeach
                              @endif
                            </tbody>
                        </table>

                          </div>
                  </div>
                    <div class="row">
                       <div class="col-lg-12"> 
                            <div class="form-group form-group-sm">
                               <label>OBSERVACIÓN:</label>
                               <textarea class="form-control" rows="3" name="observacion"></textarea>
                            </div>
                       </div>
                    </div>

                    </div>
               
                 <div class="row" style="display:none;"  id="imgloadguia">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <center><img width="300px" height="300px" src="/img/load.gif"><br>
                    REGISTRANDO GUIA.....</center>
                </div>
            </div>
          </div>
              <div class="box-body" id="divcargar">
                  
                      <div class="col-lg-8">
               
                    <button type="button"  name="btnregistrar" id="btnregistrar"  class="btn btn-primary"><strong>REGISTRAR GUIA</strong></button>
                    <a href="{{config('global.ruta')}}/SisFact"><button type="button" class="btn btn-danger"><strong>CANCELAR</strong></button></a>
                </div>

              </div>
               </div>
        </div>
        
           
                </div>
                
            </div>

        {!!Form::close()!!}  

        <script>
  $(document).ready(function()
  {

   $('#modal-cantidad-precio').on('shown.bs.modal', function() { $("#can_producto").focus(); })
   $('#modal-presentaciones').on('shown.bs.modal', function() { $("#table-presentaciones .btn:first").focus(); })

   $("#modal-cantidad-precio").on('hidden.bs.modal', function () {
     actualizarpro();
   });

   $("#producto").focus();

   $("#modal-cantidad-precio").on('hidden.bs.modal', function () {
     actualizarpro();
   });

   var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

   

              $("#producto").select2( {

    
    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
      url: "{{route('Productos.consultarproductos')}}",
      dataType: 'json',
      type: "POST",
      quietMillis: 50,
      data: function (params) {

       var id_almacen = $("#id_almacen").val();
       
       return {
        _token : CSRF_TOKEN,
        search: params.term,
        almacen: id_almacen,
      };
    },
    processResults: function (response) {
     

      return {
        results: $.map(response, function(response){

          
          return {
            "text": response.text,
            "id": response.id,
            "pro_rel": response.pro_rel,
            "presentacion": response.contar,
            "propun": response.propun,
            "unidad": response.unidad,
            "producto": response.producto,
            "id_almacen_pro":response.id_almacen,
            "pro_cod":response.codigo,
            "icbper":response.icbper,
            "mon_icbper":response.mon_icbper
          }
          

        })

        
      };

      

    },
    cache:false
  }
  
});

        });
</script>   
@endsection

