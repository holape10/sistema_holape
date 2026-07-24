

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modalproductos">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-12">
						  <div class="box">
						    <div class="box-header with-border form-group-sm">
						      <input class="form-control" name="buscardescripcion" id="buscardescripcion" placeholder="Código Barras">
						    </div>
						    <div class="box-body" id="detmenu"  style="min-height:770px;min-width:500px  ">
						      <?php $i=0; ?>
						      @foreach($categorias as $categoria)
						      <?php $i=$i+1; ?>
						      <div class="col-sm-3 col-xs-3">
						        <button id='cat<?php echo $i; ?>' type="button" value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:{{$categoria->color}};width: 120px; height: 120px; border-radius:10px">
						          <p><font color="white">{{$categoria->cat_nom}}</font></p>
						        </button><br><br>
						      </div>
						      @endforeach
						    </div>
						  </div>
						</div>
					</div>
				
				</div>
			</div>
		</div>
</div>
