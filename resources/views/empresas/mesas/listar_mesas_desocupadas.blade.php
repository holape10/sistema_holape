<script type="text/javascript">

$(document).ready(function(){

		$(".selectpicker").selectpicker();
	});

</script>
<div class="form-group form-group-sm">
								<label>Mesas</label>
								<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="mesas" id="mesas">
									@if(!empty($mesas))
										@foreach($mesas as $mesa)
											<option value="{{$mesa->mes_id}}">{{$mesa->mes_nom}}</option>
										@endforeach
									@endif
								</select>
							</div>