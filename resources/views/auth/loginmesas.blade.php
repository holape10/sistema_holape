@extends('layouts.app')

@section('content')

  		<style type="text/css">
  			.table_teclado tr td
			{
				background: #828282;
				color: #FFF;
				padding: 25px 25px;
				cursor: pointer;
				border-radius: 3px;
				font-family: sans-serif;
				border: 1px solid #757575;
			    max-width: 360px;
				
			}

			.table_teclado tr td:hover
			{
				background: #525252;
			}

			.btn_delete
			{
				position: absolute;
				left: .1em;
				bottom: .1em;
				width: 19px;
				position: relative;
			}
		</style>

	 	<script type="text/javascript">
	  		$(document).ready(function(){
				$('.table_teclado tr td').click(function(){
					var number = $(this).text();
						
					if (number == '')
					{
						$('#password').val($('#password').val().substr(0, $('#password').val().length - 1)).focus();
						$('#email').val($('#email').val().substr(0, $('#email').val().length - 1)).focus();
					}
					else
					{
						$('#password').val($('#password').val() + number).focus();
						$('#email').val($('#email').val() + number).focus();
					}

					});
			});

	 	</script>
	 	<form class="form-horizontal" autocomplete="off"  method="POST" action="/login">
	 	{{ csrf_field() }}
		<div class="container" >
			<div class="row">
				 
				<div class="col-lg-4 offset-lg-4 col-md-4 offset-md-12 col-sm-12  col-xs-12 " style="text-align: center; align-items:center;" >

					
						<table >
							<tr>
								<td colspan="3"><input type="password" style="width:360px;" readonly id="password" name="password" class="teclado_text">   <input type="hidden" name="email" id="email" value="{{ old('email') }}" class="form-control" placeholder="Usuario"> <br><br></td>
							</tr>
						</table>
						<table class="table_teclado" ">
							<tr>
								<td style="width:120px;height:70px;">1</td>
								<td style="width:120px;height:70px;">2</td>
								<td style="width:120px;height:70px;">3</td>
							</tr>
							<tr>
								<td style="width:120px;height:70px;">4</td>
								<td style="width:120px;height:70px;">5</td>
								<td style="width:120px;height:70px;">6</td>
							</tr>
							<tr>
								<td style="width:120px;height:70px;">7</td>
								<td style="width:120px;height:70px;">8</td>
								<td style="width:120px;height:70px;">9</td>
							</tr>
							
							<tr>
								<td colspan="2">0</td>
								<td style="width:120px;height:70px;"><img class="btn_delete" src="/images/borrar.png"></td>
							</tr>
						
						</table>
						<table>
								<tr>
								<td colspan="3" width="360px" style="background-color:white;border:0px"><button class="btn btn-primary btn-lg btn-block">INGRESAR</button></td>
							</tr>
						</table>
				</div>
		
			</div>
			 
		</div>
	 	</form>
  
  @endsection
