<script type="text/javascript">

$(document).ready(function(){

		$(".selectpicker").selectpicker();
	});

</script>
<div class="form-group form-group-sm">
								<label>Mesas</label>
								<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="mesas_unir[]" id="mesas_unir" multiple>
									@if(!empty($mesas))
										@foreach($mesas as $mesa)
											<option value="{{$mesa->mes_id}}">{{$mesa->mes_nom}}</option>
										@endforeach
									@endif
									@if(!empty($mesas_unidas))
										@foreach($mesas_unidas as $mesa)
											<option selected="selected" value="{{$mesa->mes_id}}">{{$mesa->mes_nom}}</option>
										@endforeach
									@endif
								</select>
							</div>