


<script type="text/javascript">

   $(document).ready(function()
   {


      if($('#tdicod').val() =='6' ){

             if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

                  $('#factura').prop("checked",true);
       
          }

             


      }


      if($('#tdicod').val() =='1' ){

            if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

                $('#boleta').prop("checked",true);
       
          }

         
      }

       $("#tdicod").on("change", function() {

          if($('#tdicod').val() =='6' ){

              if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

              $('#factura').prop("checked",true);
       
          }


             
          }

          if($('#tdicod').val() =='1' ){

                if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

                $('#boleta').prop("checked",true);
       
              }

          
          }


       });

});

  $('.selectpicker2').selectpicker();



 function seleccionarcliente(){

  if($('#clicod').find(':selected').attr('data-clinum')==''){
      $('#clinum').val('00000000');
  }else{
      $('#clinum').val($('#clicod').find(':selected').attr('data-clinum'));
  }
  

  if($('#clicod').find(':selected').attr('data-documento')==''){
      $("#tdicod").val('1');
  }else{
      $("#tdicod").val($('#clicod').find(':selected').attr('data-documento'));
  }
  


   $('#clinom').val($('#clicod').find(':selected').attr('data-clinom'));
   $('#clidir').val($('#clicod').find(':selected').attr('data-direccion'));
   $('#clicor').val($('#clicod').find(':selected').attr('data-correo'));
   
   
    $("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));
  
     if($('#tdicod').val() =='6' ){
        
          if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

           $('#factura').prop("checked",true);
       
          }
            


             
      }

      if($('#tdicod').val() =='1' ){
           if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

         $('#boleta').prop("checked",true);
       
          }
            
        
            
      

        
      }

  /*    if($('#tdicod').val() =='6' ){
             $('#factura').prop("checked",true);
      }

      if($('#tdicod').val() =='1' ){
         $('#boleta').prop("checked",true);
      }
*/

}


    </script>

  
              <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Documento</label>
                  <select name="tdicod" id="tdicod" class="form-control">
                    @foreach($documentos as $doc)
                    @if($doc->tdicod == $clientenuevo->tdicod)
                    <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                    @else
                    <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                    @endif
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div hidden="hidden" class="col-lg-2">
                <div class="form-group form-group-sm">
                  <label for="clinum">Num. Doc</label>
                  <input type="text"  name="clinum" id="clinum" value="@if(empty($clientenuevo->clinum)) 00000000 @else {{$clientenuevo->clinum}} @endif"  placeholder="" class="form-control" >

                </div>
              </div>

              
             <div class="col-lg-3" >
              <div class="form-group">
                <label class="control-label">Cliente</label>
                <select class="form-control selectpicker2 input-sm" data-show-subtext="true" data-live-search="true" name="clicod" id="clicod" onchange="seleccionarcliente();">
                  <option></option>
                  @foreach($clientes as $cliente)
                     @if($cliente->tdicod == $clientenuevo->tdicod)
                    <option selected="selected" value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}" data-direccion="{{$cliente->clidir}}" data-clinom="{{$cliente->clinom}}" data-correo="{{$cliente->clicor}}"  data-telefono="{{$cliente->telefono}}">{{$cliente->clinum}} - {{$cliente->clinom}}</option>
                   @else
                   <option value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}" data-direccion="{{$cliente->clidir}}" data-clinom="{{$cliente->clinom}}" data-correo="{{$cliente->clicor}}" data-telefono="{{$cliente->telefono}}">{{$cliente->clinum}} - {{$cliente->clinom}}</option>
                   @endif
                  @endforeach
                </select>
                <input type="hidden" readonly="readonly" value="{{$clientenuevo->clinom}}" name="clinom" id="clinom" >
              </div>
            </div>

                 <div class="col-lg-4">

            
                   <label>Direcci&oacute;n</label>
                <div class="input-group input-group-sm">
               
                  <input name="clidir" id="clidir" value="{{$clientenuevo->clidir}}" class="form-control">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="seleccionardireccion();">...</button>
                    </span>
              </div>


              </div>


              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Correo</label>
                  <input name="clicor" id="clicor" value="{{$clientenuevo->clicor}}" class="form-control">
                </div>
              </div>
              
             <!-- <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Tel&eacute;fono</label>
                  <input name="clitel" id="clitel" value="{{$clientenuevo->telefono}}" class="form-control">
                </div>
              </div>-->
               <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clifecna">Fecha Nacimiento</label>
                <input type="date" name="clifecnac" class="form-control" value="{{old('clifecnac')}}">
                 
           </div>
        </div>
      
