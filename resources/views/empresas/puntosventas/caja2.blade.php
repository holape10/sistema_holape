@extends('layouts.empresas')
@section('contenido')

<br>
<div class="container-fluid">
	<div class="row">

		<div class="col-lg-6">
			
			<div class="box">
				<div class="box-header" style="background-color:gray;">
					 <center><font style="color:#fff;font-weight:bold;font-size:9pt;">DATOS DEL COMPROBANTE</font></center>
				</div>

				<div class="box-body">
					
					<div class="row">

						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>COMPROBANTES</label>
								<select name="tdocod" class="form-control">
									@foreach($comprobantes as $comp)
										@if($comp->tdocod=='03')
											<option selected="selected" value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
										@else
											<option value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
										@endif
									@endforeach
								</select>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			
		</div>


		<div class="col-lg-6">
			
			<div class="box">
				<div class="box-header" style="background-color:gray;">
					 <center><font style="color:#fff;font-weight:bold;font-size:9pt;">DETALLE COMPROBANTE</font></center>
				</div>

				<div class="box-body">
					
				</div>
			</div>

		</div>
		
	</div>
</div>

@endsection