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

  <script>

      function Calculargasto(){
      var total=0;
      var $svalor=0;
      var item=0;
   
      $("#detgasto tbody tr").each(function(){
          
          item = parseFloat($(this).find("td:eq(2)  > input").val());

    
          total = total + parseFloat(item);

      }) 
       $('#total_gasto').val(total.toFixed(2));
         


  }

function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
  calculargasto();
};


  function agregarlineagasto(){
            var iCnt = 0;
            iCnt = iCnt + 1;
          
            $('#detgasto').append('<tr><td><select class="form-control input-sm" name="tip_gas[]">@foreach($gastos as $gas) <option value="{{$gas->tip_gas_id}}">{{$gas->tip_gas_nom}}</option>  @endforeach</select></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" ></td><td ><input type="number" step="any" class="form-control input-sm preuni" size="20px" id="preuni" name="preuni[]" onChange="Calculargasto();"   OnKeyUp="Calculargasto();" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

            
            
  }


   $(document).ready(function()
   {


    $("#btngasto").on("click", function(){

      var formulario = $("#formgasto").serializeArray();
      $("#imgloadgasto").show();
      $("#botonesgasto").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/ingresos',
        data: formulario,
      }).done(function(respuesta){

           if(respuesta.estado =='error'){
            alert(respuesta.mensaje);
            
            $("#imgloadgasto").hide();
            $("#botonesgasto").show();

        }else{

            alert(respuesta.mensaje);
            window.location.href = "/ingresos";

            //$("#imgloadgasto").hide();
            //$("#botonesgasto").show();

            
        }

      });

    });



   });

</script>

    
	
    
  
    {!!Form::open(array('url'=>'/gastos','autocomplete'=>'off','method'=>'POST','id'=>'formgasto','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
    <BR>
        <div class="container-fluid detalle">
                <div class="box">
                    <div class="box-header" style="background:blue;">
                        <center><strong><font color="white" size="2">REGISTRAR INGRESO </font></strong></center>
                        
                    </div>
                    
                    
                    <div class="box-body">
                        <div class="col-lg-2">
                            <div class="form-group form-group-sm">
                                <label>SUCURSAL</label>
                                <select class="form-control" name="sucursal" id="sucursal">
                                    @foreach($negocios as $neg)
                                        <option value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
                                    @endforeach
                                    
                                </select>
                            </div>
                        </div>

                        @if(Auth::user()->hasRole('admin'))

                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" hidden="hidden">
                             <div class="form-group form-group-sm">
                                <label>Personal</label>
                                <select name="colaborador" id="colaborador" class="form-control">
                                    <<option></option>}
                                    option
                                    @foreach ($colaboradores as $colaborador)
                                        <option value='{{$colaborador->IdUsuario}}'  >{{$colaborador->name}} {{$colaborador->apeusu}}</option>
                                    @endforeach
                                </select>
     
                              
                            </div>
                        </div>

                        @endif
                         
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Documento</label>
                                <select name="cmbTdo" id="cmbTdo" class="form-control">
                                    <option></option>
                                    @foreach($doccomprobante as $doc)
                                     
                                        
                                        <option value='{{$doc->tdocod}}' @if(old('tdocod') == $doc->tdocod) {{ 'selected' }} @endif >{{$doc->tdodes}}</option>
                                      
                                    @endforeach
                                </select>
                               
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Serie</label>
                            
                                <input name="serdoc"  id="serdoc" class="form-control" >
                        
                            </div>
                        </div>

                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>N° Doc.</label>
                           
                                <input type="number"  id="numdoc" name="numdoc"  class="form-control" >
                            
                            </div>
                        </div>
                  
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Emision</label>
                                <input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                            
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Vencimiento</label>
                                <input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
        
                            </div>
                        </div>
                           <div style="display:none;" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                             <div class="form-group form-group-sm">
                                <label>Tipo Movimiento</label>
                                <select name="cmbmovimiento" id="cmbmovimiento" class="form-control">
                                    
                                    <option value='INGRESO'>INGRESO</option>
                                </select>
                              
                            </div>
                        </div>
                          
                        <div style="display:none;" class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Moneda</label>
                                <select name="mondoc" id="mondoc" class="form-control">
                                    @foreach ($monedas as $mon)
                                        <option value='{{$mon->moncod}}' @if(old('mondoc') == $mon->moncod) {{ 'selected' }} @endif >{{$mon->monnom}}</option>
                                    @endforeach
                                </select>
     
                            </div>
                        </div>
                     

                </div>  
           
        </div>
        </div>
           <div style='display:none;' class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>DATOS DEL PROVEEDOR</strong>
                    </div>
                    <div class="panel-body">
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
                        <div class="row">
                        <input type="hidden" name="txtProvId" id="txtProvId" >
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Documento</label>
                                <select name="cmbTdi" id="cmbTdi" class="form-control">
                                    <option></option>
                                    @foreach($docidentidad as $doc)
                                        @if($doc->tdicod =='6')
                                        <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @else
                                        <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @endif
                                    @endforeach
                                </select>
                   
                            </div>
                          </div>

                     

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txtProvNum">N° Documento</label>
                                <input type="text" id='clinum' name="txtProvNum" id="txtProvNum" value="{{old('txtProvNum')}}"  placeholder="" class="form-control clinum">
                              
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre ó Razón Social</label>
                                <input type="text" name="txtProvRaz" id="clinom" value="{{old('txtProvRaz')}}" class="form-control">
                             
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Dirección</label>
                                <input name="txtProvDir" id="clidir" value="--" class="form-control">
                               
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electrónico</label>
                                <input name="txtProvCor" id="clicor" value="{{old('txtProvCor')}}" class="form-control">
                               
                            </div>
                        </div>
                        <div style="display:none;" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Número Contacto</label>
                                <input name="txtProvNumCon" id="txtProvNumCon" value="{{old('txtProvNumCon')}}" class="form-control">
                              
                            </div>
                        </div>
                        <div style="display:none;" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Persona de Contacto</label>
                                <input name="txtProvCon" id="txtProvCon" value="{{old('txtProvCont')}}" class="form-control">
                              
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
                    <div class="box-header" style="background:blue;">
                        <center><strong><font color="white" size="2">DETALLE</font></strong></center>
                    </div>
                    
                    <div  class="box-body">
                       <table id="detgasto" class="table table-border table-striped">
                            <thead>
                                <th>Tipo</th>
                                <th>Detalle</th>
                                <th>Monto</th>
                                <th><button type="button" onClick="agregarlineagasto();"  class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                            </thead>
                            <tbody id="">
                             
                            </tbody>
                        </table>

                              <div class="col-lg-2">
                             <div class="form-group form-group-sm">
                              <label>TOTAL</label>
                        <input class="form-control" type="number" min="0"  step="any" style="text-align:right;" id="total_gasto" name="total_gasto" value='0.00' readonly="readonly">
                        </div>
                      
                        </div>
                       
              
                    </div>
                </div>
                <!--<div class="panel panel-default">
                    <div  class="panel-body">
                                <button class="btn btn-primary btn-sm" id="addgr" type="button" name="addgr">GUÍA DE REMISIÓN</button><br>
                            <div class="row">
                                <div id="alertgr" hidden class="alertgr col-lg-8">
                                    <strong><font color="red">Ingresar una serie de guía válida</font></strong>
                                </div>
                                <div id="alertnum" hidden class="alertnum col-lg-8">
                                    <strong><font color="red">Ingresar un número de guía válido</font></strong>
                                </div>
                                <div id="alertexist" hidden class="alertexist col-lg-8">
                                    <strong><font color="red">No se permite duplicidad de documentos</font></strong>
                                </div>
                            </div>
                                <table class="table" name="grdet" id="grdet">
                                <tbody>
                                    
                                </tbody>
                                </table>
                    </div>
                </div>-->

        </div>
                
                    <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadgasto" id="imgloadgasto"></center>
                <div class="col-lg-12" id="botonesgasto">
                    <div class="box">
                        <div class="box-header" style="background:blue;">
                        <center><strong><font color="white" size="2">OBSERVACIONES</font></strong></center>
                    </div>
                        <div  class="box-body">
                            <div class="form-group">
                                <textarea class="form-control" id="obser" name="obser" rows="1"></textarea>
                             </div>
                        </div>
                    </div>
                    <button type="button" id="btngasto" name="btngasto"  class="btn btn-sm btn-primary"><strong>REGISTRAR</strong></button>
                <a href="/gastos"><button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">CANCELAR</button></a>
                </div>

            
            
                </div>
                
            </div>
        
        {!!Form::close()!!}     
@endsection