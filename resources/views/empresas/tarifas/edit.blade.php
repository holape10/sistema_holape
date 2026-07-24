@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
     <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
               
                    <div class="alert alert-success">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Alerta!</strong> Tarifa por día: Tolerancia es considerada en horas. / Tarifa por hora: Tolerancia considerada en minutos.
                    </div>

            </div>
        </div>
    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class='glyphicon glyphicon-user'></i><strong> EDITAR TARIFA</strong></h4>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::model($tarifas,['method'=>'PATCH','route'=>['tarifas.update',$tarifas->id_tarifa],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">

        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txt_codtarifa">Código de Tarifa</label>
                <input type="text" name="txt_codtarifa" value="{{$tarifas->id_tarifa}}" class="form-control" placeholder="Código de Categoría...">
    
           </div>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cmbUniTie">Tarifa por :</label>
                <select name="cmbUniTie" class="form-control">
                    @foreach($unitiempo as $unitie)
                        @if($tarifas->id_uni_tie == $unitie->id_uni_tie)
                            <option selected="selected" value="{{$unitie->id_uni_tie}}">{{$unitie->nom_uni_tie}}</option>
                        @else
                            <option value="{{$unitie->id_uni_tie}}">{{$unitie->nom_uni_tie}}</option>
                        @endif
                    @endforeach
                </select>
                
           </div>
        </div>


        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtdescripcion">Tarifa</label>
                <input type="text" name="txtdescripcion" value="{{$tarifas->descripcion}}" class="form-control" placeholder="">
                 
           </div>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cmbVehiculos">Vehiculos</label>
                <select name="cmbVehiculos" class="form-control">
                    @foreach($vehiculos as $veh)
                        @if($veh->id_tipo_vehiculo == $tarifas->id_tipo_vehiculo)
                        <option selected="selected" value="{{$veh->id_tipo_vehiculo}}">{{$veh->descripcion}}</option>
                        @else
                        <option value="{{$veh->id_tipo_vehiculo}}">{{$veh->descripcion}}</option>
                        @endif
                    @endforeach
                </select>
                
           </div>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtprecio">Precio</label>
                <input type="number" step="0.01" min="0" name="txtprecio" value="{{$tarifas->precio}}" class="form-control" placeholder="">
                 
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txttolerancia">Tolerancia (minutos)</label>
                <input type="number" min="0" step="0.01" name="txttolerancia" value="{{$tarifas->tolerancia}}"  class="form-control" placeholder="">
                 
           </div>
        </div>
         
    </div>
     <div class="row">
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtprecio">Precio</label>
                <input type="number" min="0"  step="0.01" name="txtprecio" value="{{$tarifas->precio}}" class="form-control" placeholder="">
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <label for="txtprecio">Incluye IGV</label>
            <div class="form-group form-group-sm">
                @if($tarifas->incluyeigv =='0')
                    <input class="form-check-input" type="checkbox"  name="chkIgv">
                @else
                    <input class="form-check-input" type="checkbox"  name="chkIgv" checked="checked">
                @endif
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{config('global.ruta')}}/tarifas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
