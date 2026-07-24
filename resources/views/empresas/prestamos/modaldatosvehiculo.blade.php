

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-vehiculo">
	<div class="modal-dialog modal-lg" style="width:90%">
		<div class="modal-content">
			<div class="box-header" style="background-color:blue;">
				<font color="white"><center><strong>DATOS DEL VEHICULO</strong></center></font>
			
			</div>
		
			<div class="modal-body">


      <div class="box-body">
        
        <div class="row">
         
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Placa</label>
              <input  type="text" id="placa" name="placa" value="" class="form-control">
            </div>
          </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="marca">Marca</label>
                <select name="marca" class="form-control">
                  <option></option>
                    @foreach($marcas as $marca)
                   

                       <option value="{{$marca->mar_id}}">{{$marca->mar_nom}}</option>
                  
                     
                    @endforeach
                </select>
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="modelo">Modelo</label>
                <select name="modelo" class="form-control">
                  <option></option>
                     @foreach($modelos as $modelo)
                    
                       <option value="{{$modelo->mod_id}}">{{$modelo->mod_nom}}</option>
                   
                    @endforeach
                </select>
                 
           </div>
        </div>
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Año</label>
              <input  type="text"  id=ano" name=ano" value="" class="form-control">
            </div>
          </div>
         <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Color</label>
              <input  type="text"  id="color" name="color" value="" class="form-control">
            </div>
          </div>
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
        
              <label>Kil&oacute;metros</label>
              <input  type="number" step="any" id="kilometros" name="kilometros" value="" class="form-control">
            </div>
          </div>
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Nivel Combustible</label>
              <select name="Combustible" class="form-control">
                <option></option>
                @foreach($combustible as $comb)
             
                    <option value="{{$comb->comb_id}}">{{$comb->comb_nom}}</option>
                
                @endforeach
               
              </select>
            </div>
          </div>
         
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Cilindrada</label>
              <input  type="text" id="cilindrada" name="cilindrada" value="" class="form-control">
            </div>
          </div>
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>N° Bastidor</label>
              <input  type="text" id="bastidor" name="bastidor" value=""  class="form-control">
            </div>
          </div>
             <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Entra con grua</label>
              <select name="grua" class="form-control">

                  <option selected="selected"  value="NO">NO</option>
                <option value="SI">SI</option>
                
                
              </select>
            </div>
          </div>
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Recibido por</label>
              <select name="tecnico" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                <option></option>
                @foreach($tecnicos as $tecnico)
                
                		 <option value="{{$tecnico->tec_id}}">{{$tecnico->tecnom}}</option>

                @endforeach
                
              </select>
            </div>
          </div>
          </div>
           <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecinspeccion">Inspecci&oacute;n T&eacute;cnica Vigente hasta:</label>
                <input type="date" name="fecinspeccion" value="" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecsoat">SOAT Vigente hasta:</label>
                <input type="date" name="fecsoat" value="" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecrevision">Pr&oacute;xima Revisi&oacute;n en taller:</label>
                <input type="date" name="fecrevision" value="" class="form-control" placeholder="">
                  
           </div>
        </div>
    
    
      
        </div>
         
        <div class="row">
            <div class="col-lg-6">
            <div class="form-group form-group-sm">
              <label>Persona que trae el vehículo</label>
             <input type="text" name="encargado" value="" class="form-control">
            </div>
          </div>
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>T&eacutelefono</label>
             <input type="text" name="encargadotel" value="" class="form-control">
            </div>
          </div>

        </div>
      </div>
    


</div>
	<div class="modal-footer">
			
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cerrar</button>
		    </div>
</div>
</div>
</div>