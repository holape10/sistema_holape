@extends ('layouts.empresas')
@section ('contenido')
<script>
$(document).ready(function()
{  
        
         $("#txt_provun").on('change',function(){
            var numdoc = $('#txt_provun').val();
            $("#txt_propun").val((numdoc*1.18).toFixed(3));
        })

          $("#txt_propun").on('change',function(){
            var numdoc = $('#txt_propun').val();
            $("#txt_provun").val((numdoc/1.1055).toFixed(3));
        })


       
});
</script>
    <section class="content">
    
     {!!Form::open(array('url'=>'tiposvehiculos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border" style="background:gray">
                    <font size="2" color="white"><strong><center>FICHA DE VEHICULO</center></strong></font>
                  </div>
                    <div class="box-body">

  
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="placa">Placa</label>
                <input type="text" name="placa" value="{{old('placa')}}" class="form-control" placeholder="">
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="marca">Marca</label>
                <select name="marca" class="form-control">
                    <OPTION></OPTION>
                     @foreach($marcas as $marca)
                      <option value="{{$marca->mar_id}}">{{$marca->mar_nom}}</option>
                    @endforeach 
                </select>
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="modelo">Modelo</label>
                <select name="modelo" class="form-control">
                  <OPTION></OPTION>
                    @foreach($modelos as $modelo)
                      <option value="{{$modelo->mod_id}}">{{$modelo->mod_nom}}</option>
                    @endforeach 
                </select>
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="color">Color</label>
                <input type="text" name="color" value="{{old('color')}}" class="form-control" placeholder="">
                  
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="ano">Año</label>
                <input type="text" name="ano" value="{{old('ano')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cilindrada">Kilometros</label>
                <input type="text" name="kilometros" value="{{old('kilometros')}}" class="form-control" placeholder="">
                  
           </div>
        </div>

         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cilindrada">Cilindrada</label>
                <input type="text" name="cilindrada" value="{{old('cilindrada')}}" class="form-control" placeholder="">
                  
           </div>
        </div>

         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="chasis">N° Chasis</label>
                <input type="text" name="chasis" value="{{old('chasis')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="combustible">Combustible</label>
                 <select name="combustible" class="form-control">
                     @foreach($combustible as $comb)
                      <option value="{{$comb->comb_id}}">{{$comb->comb_nom}}</option>
                    @endforeach
                </select>
                  
           </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="Observaciones">Observaciones</label>
                <input type="text" name="Observaciones" value="{{old('Observaciones')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
       
 
    </div>
    <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecinspeccion">Inspecci&oacute;n T&eacute;cnica Vigente hasta:</label>
                <input type="date" name="fecinspeccion" value="{{old('fecinspeccion')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecsoat">SOAT Vigente hasta:</label>
                <input type="date" name="fecsoat" value="{{old('fecsoat')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecrevision">Pr&oacute;xima Revisi&oacute;n en taller:</label>
                <input type="date" name="fecrevision" value="{{old('fecrevision')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
    
    
      
        </div>
    
</div>
</div>
</div>
</div>
<div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border" style="background:gray">
                    <font size="2" color="white"><strong><center>PROPIETARIO</center></strong></font>
                  </div>
                    <div class="box-body">

  
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicod">COD CLIENTE</label>
                <input type="text" name="clicod" readonly="readonly" value="{{old('clicod')}}" class="form-control" placeholder="">
                 
           </div>
        </div>

                  <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tdicod">Tipo Documneto</label>
                <select name="tdicod" class="form-control">
                    @foreach($documentos as $documento)
                      <option value="{{$documento->tdicod}}">{{$documento->tdides}}</option>
                    @endforeach
                </select>
                 
           </div>
        </div>


           <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clinum">N° Documento</label>
                <input type="text" name="clinum" value="{{old('clinum')}}" class="form-control" placeholder="">
                 
           </div>
        </div>

          <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clinom">Razón Social</label>
                <input type="text" name="clinom" value="{{old('clinom')}}" class="form-control" placeholder="">
                 
           </div>
        </div>

    
          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Direcci&oacute;n</label>
                <input type="text" name="clidir" value="{{old('clidir')}}" class="form-control" placeholder="">
                  
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clitel">Tel&eacute;fono</label>
                <input type="text" name="clitel" value="{{old('clitel')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Email</label>
                <input type="text" name="clicor" value="{{old('clicor')}}" class="form-control" placeholder="">
                  
           </div>
        </div>

         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tarifa">Tarifa</label>
                 <select name="tarifa" class="form-control">
                    
                </select>
           </div>
        </div>
      
       
 
    </div>
    <div class="row">
          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicon">Contacto</label>
                 <input type="text" name="clicon" value="{{old('clicon')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicontel">Tel&eacute;fono</label>
                <input type="text" name="clicontel" value="{{old('clicontel')}}" class="form-control" placeholder="">
                  
           </div>
        </div>
    
    
      
        </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/tiposvehiculos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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