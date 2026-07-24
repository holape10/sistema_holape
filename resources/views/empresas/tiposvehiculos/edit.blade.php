@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
      {!!Form::model($vehiculos,['method'=>'PATCH','route'=>['tiposvehiculos.update',$vehiculos->id_tipo_vehiculo],'files'=>'true'])!!}
    {{Form::token()}}
  <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border" style="background:gray">
                    <font size="2" color="white"><strong><center>EDITAR FICHA DE VEHICULO</center></strong></font>
                  </div>
                    <div class="box-body">

  
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="placa">Placa</label>
                <input type="text" name="placa" value="{{$vehiculos->placa}}" class="form-control" placeholder="">
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="marca">Marca</label>
                <select name="marca" class="form-control">
                    <OPTION></OPTION>
                     @foreach($marcas as $marca)
                     @if($marca->mar_id == $vehiculos->mar_id)
                       <option selected="selected" value="{{$marca->mar_id}}">{{$marca->mar_nom}}</option>
                     @else
                       <option value="{{$marca->mar_id}}">{{$marca->mar_nom}}</option>
                     @endif
                     
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
                      @if($modelo->mod_id == $vehiculos->mod_id)
                       <option selected="selected" value="{{$modelo->mod_id}}">{{$modelo->mod_nom}}</option>
                     @else
                       <option value="{{$modelo->mod_id}}">{{$modelo->mod_nom}}</option>
                     @endif
                     
                    @endforeach 
                </select>
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="color">Color</label>
                <input type="text" name="color" value="{{$vehiculos->color}}" class="form-control" placeholder="">
                  
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="ano">Año</label>
                <input type="text" name="ano" value="{{$vehiculos->ano}}" class="form-control" placeholder="">
                  
           </div>
        </div>
          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="kilometros">Kilometros</label>
                <input type="number"  step="any" name="kilometros" value="{{$vehiculos->kilometros}}" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cilindrada">Cilindrada</label>
                <input type="text" name="cilindrada" value="{{$vehiculos->cilindrada}}" class="form-control" placeholder="">
                  
           </div>
        </div>

         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="chasis">N° Chasis</label>
                <input type="text" name="chasis" value="{{$vehiculos->bastidor}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="combustible">Combustible</label>
                 <select name="combustible" class="form-control">
                     @foreach($combustible as $comb)
                      @if($comb->comb_id == $vehiculos->comb_id)
                       <option selected="selected" value="{{$comb->comb_id}}">{{$comb->comb_nom}}</option>
                     @else
                       <option value="{{$comb->comb_id}}">{{$comb->comb_nom}}</option>
                     @endif
                     
                    @endforeach 
                </select>
                  
           </div>
        </div>
       
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="Observaciones">Observaciones</label>
                <input type="text" name="observaciones" value="{{$vehiculos->observaciones}}" class="form-control" placeholder="">
                  
           </div>
        </div>
 
    </div>
    <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecinspeccion">Inspecci&oacute;n T&eacute;cnica Vigente hasta:</label>
                <input type="date" name="fecinspeccion" value="{{$vehiculos->fecinspeccion}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecsoat">SOAT Vigente hasta:</label>
                <input type="date" name="fecsoat" value="{{$vehiculos->fecsoat}}" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecrevision">Pr&oacute;xima Revisi&oacute;n en taller:</label>
                <input type="date" name="fecrevision" value="{{$vehiculos->fecrevision}}" class="form-control" placeholder="">
                  
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
                <input type="text" name="clicod" readonly="readonly" value="{{$vehiculos->clicod}}" class="form-control" placeholder="">
                 
           </div>
        </div>
               <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tdicod">Tipo Documneto</label>
                <select name="tdicod" class="form-control">
                      @foreach($documentos as $documento)
                      @if($documento->tdicod == $vehiculos->tdicod)
                         <option selected="selected" value="{{$documento->tdicod}}">{{$documento->tdides}}</option>
                      @else
                         <option value="{{$documento->tdicod}}">{{$documento->tdides}}</option>

                      @endif
                     
                    @endforeach
                </select>
                 
           </div>
        </div>


           <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clinum">N° Documento</label>
                <input type="text" name="clinum" value="{{$vehiculos->clinum}}" class="form-control" placeholder="">
                 
           </div>
        </div>


          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clinom">Razón Social</label>
                <input type="text" name="clinom" value="{{$vehiculos->clinom}}" class="form-control" placeholder="">
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Direcci&oacute;n</label>
                <input type="text" name="clidir" value="{{$vehiculos->clidir}}" class="form-control" placeholder="">
                  
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clitel">Tel&eacute;fono</label>
                <input type="text" name="clitel" value="{{$vehiculos->clitel}}" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Email</label>
                <input type="text" name="clicor" value="{{$vehiculos->clicor}}" class="form-control" placeholder="">
                  
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
          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicon">Contacto</label>
                 <input type="text" name="clicon" value="{{$vehiculos->clicon}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicontel">Tel&eacute;fono</label>
                <input type="text" name="clicontel" value="{{$vehiculos->clicontel}}" class="form-control" placeholder="">
                  
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
