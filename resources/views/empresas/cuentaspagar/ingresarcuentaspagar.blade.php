@extends ('layouts.empresas')
@section ('contenido')

<script type="text/javascript">
    
    $(document).ready(function(){


      $("#btnvale").on("click", function() {
        var formulario = $("#formfact").serializeArray();
        $("#imgload").show();
        $(".botones").hide();
        $.ajax({
          type: "POST",
          dataType: 'json',
          url: '/cuentascobrar/registrar',
          data: formulario,
        }).done(function(respuesta){

    
           window.location.href = "/cuentascobrar";

           $("#imgload").hide();
            }

       });
      });

    $("#btntotal").on("click", function() {
        var formulario = $("#formfact").serializeArray();
        $("#imgload").show();
        $(".botones").hide();
        $.ajax({
          type: "POST",
          dataType: 'json',
          url: '/cuentascobrar/registrartotal',
          data: formulario,
        }).done(function(respuesta){

         
             window.location.href = "/cuentascobrar";

     
       });



      });



});
  

</script>
	<section class="content">
      
      
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                	<div class="box-header" style="background:blue;">
                		<font size="3" color="white"><center><strong>REGISTRAR PAGO</strong></center></font>
                	</div>
                    <div class="box-body">

	{!!Form::open(array('url'=>'/cuentaspagar/registrar','method'=>'POST','autocomplete'=>'off','files'=>'true','name'=>'formfact'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="fec_reg">FECHA REGISTRO</label>
                <input type="date" name="fec_reg" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
           </div>
        </div>

    

         <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="tdocod">COMPROBANTE</label>
                <select name="tdocod" class="form-control">
                	    
                    <option value="01">FACTURA</option>
                    <option value="03">BOLETA</option>
                </select>
           </div>
        </div>
            <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="tdicod">DOCUMENTO IDENTIDAD</label>
                <select name="tdicod" class="form-control">
                      
                  @foreach($tiposdocumentos as $tipdoc)
                    @if($tipdoc->tdicod == $cuentas[0]->tdicod)
                    <option selected="selected" value="{{$tipdoc->tdicod}}">{{$tipdoc->tdides}}</option>
                    @else
                    <option value="{{$tipdoc->tdicod}}">{{$tipdoc->tdides}}</option>
                    @endif
                  @endforeach
                </select>
           </div>
        </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">RUC/DNI</label>
                <input type="text" readonly="readonly" name="clinum"  value="{{$cuentas[0]->prov_raz}}" class="form-control">
           </div>
        </div>
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">RAZON SOCIAL</label>
                <input type="text" name="clinom" readonly="readonly"  value="{{$cuentas[0]->prov_ruc}}" class="form-control">
           </div>
        </div>
          <div hidden="hidden" class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">DIRECCION</label>
                <input type="text" name="clidir"   value="{{$cuentas[0]->prov_dir}}"" class="form-control">
           </div>
        </div>

      </div>
 <div class="row">

                   <div class="col-lg-3">
                    <div class="form-group form-group-sm">
                        <label class="control-label">Banco - Cuenta</label>
                        <select name="cuen_ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                            <OPTION value="efectivo">Efectivo</OPTION>
                            @foreach($cuentasbancarias as $banco)
                                <option value="{{$banco->cuen_ban_id}}">{{strtoupper($banco->ban_nom)}} - CUENTA {{strtoupper($banco->tip_cuen_nom)}} {{strtoupper($banco->monnom)}} {{strtoupper($banco->cuen_ban_num)}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
      
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="concepto_id">Concepto</label>
                <select name="concepto_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                    @foreach($conceptos as $concepto)
                    <option value="{{$concepto->concepto_id}}">{{$concepto->concepto_nom}}</option>
                    @endforeach
                </select>
           </div>
        </div>

         <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="num_oper">N° Operaci&oacute;n</label>
                <input type="text" name="num_oper" value="" class="form-control" >
            

            </div>
        </div>



  </div>
    <div class="row">
        
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="abonoimporte">Abono</label>
                <input type="number" step="any"  name="abonoimporte" value="0.00" class="form-control" >
               
            </div>
        </div>  


         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="importe">Total</label>
                <input type="number" step="any" readonly="readonly" name="importetotal" value="{{$totalcuenta}}" class="form-control" >
               
            </div>
        </div>  

   


         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="fec_dep">FECHA PAGO</label>
                <input type="date" name="fec_dep" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
           </div>
        </div>


    </div>
    <div class="row">
    	 <div class="col-lg-12 col-md-12 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="detalle">Comentario</label>
                <textarea class="form-control" name="detalle"  maxlength="250"></textarea>
           </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-12">
    	<table class="table table-responsive table-striped">
    		<thead>
    			<th hidden="hidden">item</th>
    		
    			<th>Cliente</th>
    			<th>Total</th>
          <!--<th>Abono</th>-->
    		</thead>
    		<tbody>
    			@foreach($cuentas as $cuenta)
    			<tr>
    			<td hidden=""><input type="hidden" readonly="readonly" name="items[]" value="{{$cuenta->cue_pag_id}}"></td>
    			
    			<td>{{$cuenta->prov_ruc}}</td>
    			<td>S/. {{$cuenta->saldo}}</td>
    			<td hidden="hidden"><input type="hidden" readonly="readonly" name="importe[]" value="{{$cuenta->saldo}}"></td>
    			</tr>
    			@endforeach
    		</tbody>
    	</table>
    	</div>
    </div>

    <input type="hidden" name="opcion" value="1">

    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit" name="btnvale" id="btnvale">Registrar Abono</button>
        
            	<a href="/cuentascobrar"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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