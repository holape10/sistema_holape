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
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> NUEVA TARIFA</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::open(array('url'=>'tarifas','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cmbUniTie">Tarifa por :</label>
                <select name="cmbUniTie" class="form-control">
                    @foreach($unitiempo as $unitie)
                    <option value="{{$unitie->id_uni_tie}}">{{$unitie->nom_uni_tie}}</option>
                    @endforeach
                </select>
                
           </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtdescripcion">Descripci&oacute;n</label>
                <input type="text" name="txtdescripcion" value="{{old('txtdescripcion')}}" class="form-control" placeholder="">
                 
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cmbVehiculos">Vehiculos</label>
                <select name="cmbVehiculos" class="form-control">
                    @foreach($vehiculos as $veh)
                    <option value="{{$veh->id_tipo_vehiculo}}">{{$veh->descripcion}}</option>
                    @endforeach
                </select>
                
           </div>
        </div>
       
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txttolerancia">Tolerancia</label>
                <input type="text" type="number" min="0" step="0.01" name="txttolerancia" value="{{old('txttolerancia')}}" class="form-control" placeholder="">
                
           </div>
        </div>
        </div>
    <div class="row">
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtprecio">Precio</label>
                <input type="number" min="0"  step="0.01" name="txtprecio" value="{{old('txtprecio')}}" class="form-control" placeholder="">
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <label for="txtprecio">Incluye IGV</label>
            <div class="form-group form-group-sm">

                <input class="form-check-input" type="checkbox"  name="chkIgv" checked="checked">
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/tarifas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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