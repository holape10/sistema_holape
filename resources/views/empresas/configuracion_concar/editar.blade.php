@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
     
        <div class="row">


			 {!!Form::open(array('url'=>'/regconfconcar','autocomplete'=>'off','method'=>'POST','name'=>'formConcar','id'=>'formConcar','role'=>'form','files'=>'true'))!!}
  		{{Form::token()}}

  		<div class="col-lg-12">
  				<div class="box">
					<div class="box-header" style="background:blue;">
						<font color="white"><center><strong>REGISTRO Y ASIENTO DE VENTAS A CONCAR</strong></center></font>
						 <div class="box-tools pull-right">
	                     	<button type="submit" class="btn btn-primary btn-sm">REGISTRAR</button>
							<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">CERRAR</button>
	                    </div>
					</div>		
				</div>		
  		</div>
		


					<div class="col-lg-6">
						 <div class="box">
						 	  <div class="box-header" style="background:blue;">
								
								<font color="white"><center><strong>PROVISIONES</strong></center></font>
							</div>
							<div class="box-body">
								<div class="row">
										
								
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" >Provision </label>
									<input type="text" name="txt_provision" id="txt_provision" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->provision}}@endif">
							
								</div>
							</div>
				
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">Correlativo</label>
									<input type="text" name="txt_prov_corre" id="txt_prov_corre" class="form-control"  value="@if(!empty($configuracion_concar)){{$configuracion_concar->prov_corre}}@endif">
							
								</div>
							</div>
					


							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">D_Ventas FT</label>
									<input type="text" name="txt_cod_d_prov_fac" id="txt_cod_d_prov_fac" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_d_prov_fac}}@endif">
							
								</div>
							</div>
							

							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">H_VENTAS FT</label>
									<input type="text" name="txt_cod_h_prov_fac" id="txt_cod_h_prov_fac" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_h_prov_fac}}@endif">
							
								</div>
							</div>
					

							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">D_VENTAS BV</label>
									<input type="text" name="txt_cod_d_prov_bol" id="txt_cod_d_prov_bol" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_d_prov_bol}}@endif">
							
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">H_VENTAS BV</label>
									<input type="text" name="txt_cod_h_prov_bol" id="txt_cod_h_prov_bol" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_h_prov_bol}}@endif">
							
								</div>
							</div>	
								</div>
							</div>
						 </div>
					</div>


					<div class="col-lg-6">
						 <div class="box">
						 	  <div class="box-header" style="background:blue;">
								
								<font color="white"><center><strong>CANCELACIONES</strong></center></font>
							</div>
							<div class="box-body">
								<div class="row">
										<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" >Cancelación </label>
									<input type="text" name="txt_cancelacion" id="txt_cancelacion" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cancelacion}}@endif">
							
								</div>
							</div>
				
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">Correlativo</label>
									<input type="text" name="txt_canc_corre" id="txt_canc_corre" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->canc_corre}}@endif">
							
								</div>
							</div>

									<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">D_CANCELACION </label>
									<input type="text" name="txt_cod_d_can" id="txt_cod_d_can" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_d_can}}@endif">
							
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">H_CANCELAC FT</label>
									<input type="text" name="txt_cod_h_can_fac" id="txt_cod_h_can_fac" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_h_can_fac}}@endif">
							
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">H_CANCELAC BV</label>
									<input type="text" name="txt_cod_h_can_bol" id="txt_cod_h_can_bol" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_h_can_bol}}@endif">
							
								</div>
							</div>

							


								</div>
							</div>
						 </div>
					</div>


					<div class="col-lg-6">
						 <div class="box">
						 	  <div class="box-header" style="background:blue;">
								
								<font color="white"><center><strong>ANEXOS</strong></center></font>
							</div>
							<div class="box-body">
								<div class="row">
									
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">Anexo Contado</label>
									<input type="text" name="txt_cod_ven_con" id="txt_cod_ven_con" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_ven_con}}@endif">
							
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">Anexo Anulado</label>
									<input type="text" name="txt_cod_ven_anu" id="txt_cod_ven_anu" class="form-control" value="@if(!empty($configuracion_concar)){{$configuracion_concar->cod_ven_anu}}@endif">
							
								</div>
							</div>



								</div>
							</div>
						 </div>
					</div>


			{!!Form::close()!!}

</div>
</section>
@endsection
