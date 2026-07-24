@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> CREAR CUENTA BANCARIA</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

	{!!Form::open(array('url'=>'cuentasbancarias','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="ban_nom">Banco</label>
                <select name="ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                    @foreach($bancos as $banco)
                    <option value="{{$banco->ban_id}}">{{$banco->ban_nom}}</option>
                    @endforeach
                </select>
                
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="tip_cuen_id">Tipo Cuenta</label>
                <select name="tip_cuen_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                    @foreach($tipocuentas as $tipocuenta)
                    <option value="{{$tipocuenta->tip_cuen_id}}">{{$tipocuenta->tip_cuen_nom}}</option>
                    @endforeach
                </select>
                
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="moncod">Moneda</label>
                <select name="moncod" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                    @foreach($monedas as $moneda)
                    <option value="{{$moneda->moncod}}">{{$moneda->monnom}}</option>
                    @endforeach
                </select>
           </div>
        </div>
    
          <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="cuen_ban_num">Nro. Cuenta</label>
                <input type="text" name="cuen_ban_num" class="form-control">
           </div>
        </div>

      
    </div>
    <div class="row">
    	<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="/cuentasbancarias"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
