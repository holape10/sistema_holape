@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> NUEVA MEDIO PAGO</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::open(array('url'=>'/mediospagos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtNomMedPag">Medio Pago</label>
                <input type="text" name="txtNomMedPag" value="{{old('txtNomMedPag')}}" class="form-control" placeholder="">
               
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="cuen_ban_id">Cuenta Bancaria</label>
                <select name="cuen_ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                    <option></option>
                    @foreach($bancos as $banco)
                    <option value="{{$banco->cuen_ban_id}}">{{strtoupper($banco->ban_nom)}} - CUENTA {{strtoupper($banco->tip_cuen_nom)}} {{strtoupper($banco->monnom)}} {{strtoupper($banco->cuen_ban_num)}}</option>
                    @endforeach
                </select>
                
           </div>
        </div>
          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="concepto_id">Conceptos Bancarios</label>

                <select name="concepto_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                    <option></option>
                    @foreach($conceptos as $concepto)
                    <option value="{{$concepto->concepto_id}}">{{$concepto->concepto_nom}}</option>
                    @endforeach
                </select>
                
           </div>
        </div>
          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="comision">Comisi&oacute;n (%)</label>
                <input type="text" name="comision" value="" class="form-control" >
               
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="comision_mont">Comisi&oacute;n</label>
                <input type="text" name="comision_mont" value="" class="form-control" >
               
           </div>
        </div>
        </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/categorias"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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