<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-pdf">
	
		<div class="modal-dialog">
			<div class="modal-content modal-lg">
				
			@if(!empty($codfact) && $datos->ticket_pantalla=='1' && $datos->formato=='A4')
				<div class="modal-body">
					  <center> <embed src="/pdf/{{$pdf->IdEmpresa}}-{{$pdf->tdocod}}-{{$pdf->serdoc}}-{{$pdf->numdoc}}.pdf" width="800px" height="400px" frameborder="0" /></center>
				</div>
				@endif
				
			</div>
		</div>

</div>
