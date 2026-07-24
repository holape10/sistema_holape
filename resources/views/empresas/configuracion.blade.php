 @extends('layouts.admin')
 @section('contenido')
 	<div class="container">
      	<div class="row">
      		<form method="post" id="perfil">
        		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 toppad" >
 
   
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><i class='glyphicon glyphicon-cog'></i> Configuración</h3>
            </div>
            <div class="panel-body">
              <div class="row">
			  
                <div class="col-md-4 col-lg-4 " > 
					<div id="load_img">
						<img class="img-responsive" src="" alt="Logo">
					</div>
					<br>				
					<div class="row">
	  					<div class="col-md-12">
							<div class="form-group">
								<input class='filestyle' data-buttonText="Logo" type="file" name="imagefile" id="imagefile" onchange="upload_image();">
							</div>
						</div>
					</div>
				</div>
                <div class=" col-md-8 col-lg-8 "> 
                  <table class="table table-condensed">
                    <tbody>
                      <tr>
                        <td class='col-md-3'>Nombre de la empresa:</td>
                        <td><input type="text" class="form-control input-sm" name="nombre_empresa" value="" required></td>
                      </tr>
                      <tr>
                        <td>Teléfono:</td>
                        <td><input type="text" class="form-control input-sm" name="telefono" value="<?php /*echo $row['telefono'] */?>" required></td>
                      </tr>
                      <tr>
                        <td>Correo electrónico:</td>
                        <td><input type="email" class="form-control input-sm" name="email" value="<?php /* echo $row['email'] */?>" ></td>
                      </tr>
					  <tr>
                        <td>IVA (%):</td>
                        <td><input type="text" class="form-control input-sm" required name="impuesto" value="<?php /* echo $row['impuesto'] */?>"></td>
                      </tr>
					  <tr>
                        <td>Simbolo de moneda:</td>
                        <td>
							<select class='form-control input-sm' name="moneda" required>
										<?php /*
											$sql="select name, symbol from  currencies group by symbol order by name ";
											$query=mysqli_query($con,$sql);
											while($rw=mysqli_fetch_array($query)){
												$simbolo=$rw['symbol'];
												$moneda=$rw['name'];
												if ($row['moneda']==$simbolo){
													$selected="selected";
												} else {
													$selected="";
												}
												?>
												<option value="<?php echo $simbolo;?>" <?php echo $selected;?>><?php echo ($simbolo);?></option>
												<?php
											}*/
										?>
							</select>
						</td>
                      </tr>
					  <tr>
                        <td>Dirección:</td>
                        <td><input type="text" class="form-control input-sm" name="direccion" value="<?php /* echo $row["direccion"]; */?>" required></td>
                      </tr>
					  <tr>
                        <td>Ciudad:</td>
                        <td><input type="text" class="form-control input-sm" name="ciudad" value="<?php /*echo $row["ciudad"];*/?>" required></td>
                      </tr>
					  <tr>
                        <td>Región/Provincia:</td>
                        <td><input type="text" class="form-control input-sm" name="estado" value="<?php /* echo $row["estado"];*/?>"></td>
                      </tr>
					  <tr>
                        <td>Código postal:</td>
                        <td><input type="text" class="form-control input-sm" name="codigo_postal" value="<?php /* echo $row["codigo_postal"];*/?>"></td>
                      </tr>
                   
                        
                     
                    </tbody>
                  </table>
                  
                  
                </div>
				<div class='col-md-12' id="resultados_ajax"></div><!-- Carga los datos ajax -->
              </div>
            </div>
                 <div class="panel-footer text-center">
                    
                     
                            <button type="submit" class="btn btn-sm btn-success"><i class="glyphicon glyphicon-refresh"></i> Actualizar datos</button>
                       
                       
                    </div>
            
          </div>
        </div>
		</form>
      </div>

 @stop