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

  function agregarlineagasto(){
            var iCnt = 0;
            iCnt = iCnt + 1;
          
            $('#detgasto').append('<tr><td><select class="form-control input-sm" name="tip_gas[]">@foreach($gastos as $gas) <option value="{{$gas->tip_gas_id}}">{{$gas->tip_gas_nom}}</option>  @endforeach</select></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" ></td><td ><input type="number" step="any" class="form-control input-sm preuni" size="20px" id="preuni" name="preuni[]" onChange="Calculargasto();"   OnKeyUp="Calculargasto();" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

            
            
  }


   $(document).ready(function()
   {


    $("#btngastopersonal").on("click", function(){

      var formulario = $("#formgasto").serializeArray();
      $("#imgloadgasto").show();
      $("#botonesgasto").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/gastospersonal',
        data: formulario,
      }).done(function(respuesta){

           if(respuesta.estado =='error'){
            alert(respuesta.mensaje);
            
            $("#imgloadgasto").hide();
            $("#botonesgasto").show();

        }else{

            alert(respuesta.mensaje);
            window.location.href = "/gastospersonal";

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
                        <center><strong><font color="white" size="2">REGISTRAR PAGO DE PERSONAL</font></strong></center>
                   
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
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Personal</label>
                                <select name="personal" id="personal" class="form-control">
                                    <option></option>
                                    @foreach($personal as $per)
                                    
                                        <option value='{{$per->IdUsuario}}'>{{$per->name}} {{$per->apeusu}}</option>
                                       
                                    @endforeach
                                </select>
                   
                            </div>
                          </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>DOCUMENTO</label>
                                <select name="tdocod" id="tdocod" class="form-control">
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
                                <label>Fecha Registro</label>
                                <input type="date" id="fecreg" name="fecreg" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                            
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Pago</label>
                                <input type="date" name="fecpag" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
        
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

                    <div class="box-body">
                          <button type="button" id="btngastopersonal" name="btngastopersonal"  class="btn btn-sm btn-primary"><strong>REGISTRAR</strong></button>
                <a href="/gastospersonal"><button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cerrar</button></a>
                    </div>
                </div>
         

        </div>
                
                    <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadgasto" id="imgloadgasto"></center>
                <div  class="col-lg-12" id="botonesgasto">
                    <div hidden="hidden" class="box">
                        <div class="box-header" style="background:blue;">
                        <center><strong><font color="white" size="2">OBSERVACIONES</font></strong></center>
                    </div>
                        <div  class="box-body">
                            <div class="form-group">
                                <textarea class="form-control" id="obser" name="obser" rows="3"></textarea>
                             </div>
                        </div>
                    </div>
                  
                </div>

            
            
                </div>
                
            </div>
        
        {!!Form::close()!!}     
@endsection