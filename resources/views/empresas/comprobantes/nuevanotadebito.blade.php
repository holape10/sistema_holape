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

#formnota label.error {
        color:red;
    }

</style>

<script>
    $.validator.setDefaults({ 
    ignore: [],
    // any other default options and/or rules
    });

    $(document).ready(function()
    {   
        var $svalor=0;
        var iCnt = 0;      

        $("#serdocmod").on('change',function(){

            var serie = $('#serdocmod').val();
            var posi = serie.indexOf("-",0);
            var seriemod = serie.substring(0,posi);
            $('#serdocmod').val(seriemod);
            $('#serdocmod').prop("readonly",true);

        })

        $("#serdocmod").on('dblclick', function (){
            
            $('#serdocmod').prop("readonly",false);
            $('#serdocmod').val('');
            $('#numdocmod').val('');
            $('#topdes').val('');
            $('#tdomod').val('');
            $('#tdo_cod').val('');
            $('#mondoc').val('');
            $('#tipmon').val('');
            $('#camdoc').val('');
            $('#tdides').val('');
            $('#tdicod').val('');
            $('#clinum').val('');
            $('#clinom').val('');
            $('#clidir').val('');
            $('#clicor').val('');
            
        })


      
        $('#formnota').validate({

            rules: {
                
            
                serdocmod:{
                    required:true
                },
                numdocmod:{
                    required:true,
                    maxlength: 8
                }

               },


            messages: {
                serdocmod:{
                    required:"Ingresar Número de Serie",
                    maxlength:"Ingresar 4 Caracteres como máximo"
                },
                numdocmod:{
                    required:"Ingresar Número de documento"
                }
            }

        })
  
    }); 
</script>


    
    <div class="row">
            <div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="alertcomp">
                <div class="modal-dialog">
          
                <div  class="alert alert-danger">
                    <strong>Atención!</strong> El comprobante ya fue emitido.
                </div>
            
        </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <center><h3>EMITIR NOTA DE DÉBITO</h3></center>
            </div>

    </div>
   {!!Form::open(array('url'=>'/emitirnota','autocomplete'=>'off','method'=>'get','id'=>'formnota','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>Datos Nota de Débito</strong>
                    </div>
                    
                    <div class="panel-body">
                       
                        <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <label>Tipo Nota de Débito</label>
                                <div class="row">
                                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                        <div class="input-group">
                                            <select name="tipnot" id="tipnot" class="form-control">
                                               @if($ncdcod=='07')
                                                  @foreach ($nota as $not)
                                                    <option  value="{{$not->nccod}}">{{$not->ncdes}}</option>
                                                  @endforeach
                                              @elseif($ncdcod=='08')
                                                  @foreach ($nota as $not)
                                                    <option  value="{{$not->ndcod}}">{{$not->nddes}}</option>
                                                  @endforeach
                                              @endif
                                            </select>
                                      
                                            <div class="input-group-btn">
                                               <button type="submit" id="btn"   class="btn btn-primary"><strong>SIGUIENTE</strong></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4col-sm-12 col-xs-12">
                                      <div class="form-group">
                                        <a href="{{config('global.ruta')}}/SisFact"><button type="button" class="btn btn-danger"><strong>CANCELAR</strong></button></a>  
                                      </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                          
                        <input type="hidden" name="tdocod" id="tdocod" value={{$tdocod}} class="form-control">
                    </div>
                </div>  
            </div>
        </div>
        </div>
        <div class="container-fluid">
 <div class="row">
                <div class="col-lg-12">
    <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>DOCUMENTO A MODIFICAR</strong>
                    </div>
                    <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                 <label>Serie-Número</label>
                                <input type="text"  name="serdocmod" id="serdocmod" value="{{old('serdocmod')}}"  placeholder="Buscar: Serie-Número" class="form-control">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">Número</label>
                                <input type="text"  name="numdocmod" id="numdocmod" value="{{old('numdocmod')}}"  placeholder="" class="form-control" readonly="readonly">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Operación</label>
                                <input name="topdes" id="topdes" class="form-control" readonly="readonly"></input>
                                
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Documento</label>
                                <input name="tdomod" id="tdomod" class="form-control" readonly="readonly"></input>
                                <input type="hidden" name="tdo_cod" id="tdo_cod" class="form-control" readonly="readonly"></input>
                            </div>
                        </div>

                        
                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Moneda</label>
                                <input type="text"  name="mondoc" id="mondoc" value="{{old('mondoc')}}"  placeholder="" class="form-control" readonly="readonly">
                                <input type="hidden"  name="tipmon" id="tipmon"   placeholder="" class="form-control" readonly="readonly">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo de Cambio</label>
                                <input  type='text' name="camdoc"  value="{{old('camdoc')}}"  id="camdoc" class="form-control" readonly="readonly">
                            
                            </div>
                        </div>
                    </div>
                    </div>
                </div>  
            </div>
        </div>

            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>Datos del Cliente</strong>
                    </div>
                    <div class="panel-body">
                        <input type='hidden' name='txt_tdocod' id="txt_tdocod" value='4'>
                        <input type='hidden' name='idcabecera' id="idcabecera">
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
                        <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Documento</label>
                                <input type="text"  name="tdides" id="tdides" value="{{old('tdides')}}"  placeholder="" class="form-control" readonly="readonly">
                                <input type="hidden"  name="tdicod" id="tdicod" value="{{old('tdidoc')}}"  placeholder="" class="form-control" readonly="readonly">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">N° Documento</label>
                                <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}"  placeholder="" class="form-control" readonly="readonly">
                                
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre ó Razón Social</label>
                                <input type="text" name="clinom" id="clinom" value="{{old('clinom')}}" class="form-control" readonly="readonly">
                               
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Dirección</label>
                                <input name="clidir" id="clidir" value="{{old('clidir')}}" class="form-control" readonly="readonly">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electrónico</label>
                                <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control" readonly="readonly">
                            </div>
                        </div>
                    </div>
                    </div>
                </div>  
            </div>
            </div>
        </div>
        
        {!!Form::close()!!}     
@endsection