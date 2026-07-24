@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
      
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                	<div class="box-header" style="background:blue;">
                		<font size="3" color="white"><center><strong>ACTUALIZAR MOVIMIENTO DE CAJA</strong></center></font>
                	</div>
                    <div class="box-body">

	{!!Form::open(array('url'=>'/movimientoscaja/registrar','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="mov_fecha">FECHA</label>
                <input type="date" name="mov_fecha" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
           </div>
        </div>


 
        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="tip_caj_id">Concepto</label>
               	<select name="tip_caj_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
               		@foreach($conceptos as $concepto)
               		<option value="{{$concepto->tip_caj_id}}">{{$concepto->tip_caj_nom}}</option>
               		@endforeach
               	</select>
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="cuen_ban_id">Cuenta Bancaria</label>
                <select name="cuen_ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                	<OPTION></OPTION>
                	@foreach($bancos as $banco)
                	<option value="{{$banco->cuen_ban_id}}">{{strtoupper($banco->ban_nom)}} - CUENTA {{strtoupper($banco->tip_cuen_nom)}} {{strtoupper($banco->monnom)}} {{strtoupper($banco->cuen_ban_num)}}</option>
                	@endforeach
                </select>
                
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
          <label for="concepto_id">Concepto Bancario</label>
          <select name="concepto_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
            <option></option>
           @foreach($conceptosbancarios as $conceptoban)
         
           <option value="{{$conceptoban->concepto_id}}">{{$conceptoban->concepto_nom}}</option>
        
           @endforeach
         </select>
       </div>
     </div>
         <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="mov_num_oper">N° Operaci&oacute;n</label>
                <input type="text" name="mov_num_oper" value="" class="form-control" >
               
            </div>
        </div>


         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="importe">Importe</label>
                <input type="text" name="importe" value="" class="form-control" >
               
            </div>
        </div>  

    </div>
    <div class="row">
    	 <div class="col-lg-12 col-md-12 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="mov_com">Comentario</label>
                <textarea class="form-control" name="mov_com"></textarea>
           </div>
        </div>
    </div>



    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="/movimientosbancarios"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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