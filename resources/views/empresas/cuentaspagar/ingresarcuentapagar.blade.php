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

	{!!Form::open(array('url'=>'/cuentascobrar/registrar','method'=>'POST','autocomplete'=>'off','files'=>'true','name'=>'formfact'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="fec_reg">FECHA REGISTRO</label>
                <input type="date" name="fec_reg" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
           </div>
        </div>

        <input type="hidden" name="codcuenta" value="{{$cuentas->cue_cob_id}}">

         <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="tdocod">COMPROBANTE</label>
                <select name="tdocod" class="form-control">
                    
                    <option value="01">FACTURA</option>
                    <option value="03">BOLETA</option>
                </select>
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">RUC/DNI</label>
               	<input type="text" name="ruc"  value="{{$cuentas->clinum}}" class="form-control">
           </div>
        </div>
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">RAZON SOCIAL</label>
                <input type="text" name="razon"  value="{{$cuentas->clinom}}" class="form-control">
           </div>
        </div>
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">DIRECCION</label>
                <input type="text" name="direccion"   value="{{$cuentas->clidir}}" class="form-control">
           </div>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                 <label for="tip_caj_id">TIPO DOCUMENTO</label>
                <input type="text"  readonly="readonly" value="{{$cuentas->tdodes}}" class="form-control">
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                 <label for="tip_caj_id">N° DOCUMENTO</label>
                <input type="text"  readonly="readonly" value="{{$cuentas->serdoc}}-{{$cuentas->numdoc}}" class="form-control">
           </div>
        </div>

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
                <label for="importe">Importe</label>
                <input type="text" name="importe" value="{{$cuentas->saldo}}" class="form-control" >
               
            </div>
        </div>  

        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="importe">Deuda Total</label>
                <input type="text" readonly="readonly" name="deuda" value="{{$total}}" class="form-control" >
               
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
                <label for="comentario">Comentario</label>
                <textarea class="form-control" name="comentario"></textarea>
           </div>
        </div>
    </div>



    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit" name="btnvale" id="btnvale">Cobrar</button>
        
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