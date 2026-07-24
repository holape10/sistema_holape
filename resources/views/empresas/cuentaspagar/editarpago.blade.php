@extends ('layouts.empresas')
@section ('contenido')

<script type="text/javascript">
    
    $(document).ready(function(){

      

         $("#abonoimporte").on("keyup", function() {
     
             $('#predeterminado_1').val($('#abonoimporte').val());
       });

      $("#btnvale").on("click", function() {
        var formulario = $("#formfact").serializeArray();
        $("#imgload").show();
        $(".botones").hide();
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: '/actualizarpago',
          data: formulario,
        }).done(function(respuesta){

           if(respuesta.estado=='CORRECTO'){
             $("#imgload").hide();
             window.location.href = "/cuentaspagar";
           }else{

             alert(respuesta.mensaje);

              $("#imgload").hide();
              $(".botones").show();
           }
          

        })

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

   {!!Form::open(array('url'=>'/cuentascobrar/registrar','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
            {{Form::token()}}
	<section class="content">
      
      	<input type="hidden" name="cue_pag_det_id" value="{{$cuentas->cue_pag_det_id}}">

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                	<div class="box-header" style="background:blue;">
                		<font size="3" color="white"><center><strong>CUENTAS POR PAGAR - EDITAR PAGO</strong></center></font>
                	</div>
                    <div class="box-body">

	
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="fec_reg">FECHA REGISTRO</label>
                <input type="date" name="fec_reg" value="{{$cuentas->fec_reg}}" class="form-control">
           </div>
        </div>

     

         
         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">RUC/DNI</label>
                <input type="text" readonly="readonly" name="clinum"  value="{{$proveedor->prov_ruc}}" class="form-control">
           </div>
        </div>
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">RAZON SOCIAL</label>
                <input type="text" name="clinom" readonly="readonly"  value="{{$proveedor->prov_raz}}" class="form-control">
           </div>
        </div>
       


       
          <div hidden="hidden" class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="">DIRECCION</label>
                <input type="text" name="clidir"   value="{{$proveedor->prov_dir}}" class="form-control">
           </div>
        </div>

      </div>

  <hr>
    <div class="row">
         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="fec_dep">FECHA PAGO</label>
                <input type="date" name="fec_dep" value="{{$cuentas->fec_dep}}" class="form-control">
           </div>
        </div>

    
 

        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="abonoimporte">Abono</label>
                <input type="number" step="any"  name="abonoimporte" id="abonoimporte" value="{{$cuentas->abono}}" class="form-control" >
               
            </div>
        </div>  

    </div>
  
  <hr>
          <div class="row" id="divmediopago">
            @foreach($medios_cobros as $mp)
            <div class="col-lg-2">
                <div class="form-group form-group-sm">
                  <label>{{$mp->nom_med_pag}}</label>
              		<input class="mediopago form-control"  style="font-size:10pt;font-weight:bold;"  id="predeterminado_{{$mp->predeterminado}}" data-predeterminado="{{$mp->predeterminado}}" name="monto[]" type="number" step="any" value="{{$mp->monto}}">
                  	<input class="form-control" style="font-size:16pt;font-weight:bold;" name="medio[]" type="hidden" value="{{$mp->id_med_pag}}">
               </div>
              </div> 
            @endforeach
          </div>
    <hr>
    <div class="row">
    	 <div class="col-lg-12 col-md-12 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="detalle">Comentario</label>
                <textarea class="form-control" name="detalle"  maxlength="250">{{$cuentas->comentario}}</textarea>
           </div>
        </div>
    </div>


    <input type="hidden" name="opcion" value="1">

    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary botones" type="button" name="btnvale" id="btnvale">Actualizar Abono</button>
        
            	<a href="/cuentascobrar"><button class="btn btn-danger btn-close botones" type="button">Cancelar</button></a>
            </div>
    	</div>
           <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
    </div>
</div>
</div>
</div>
</div>
</section>
	{!!Form::close()!!}		
@endsection