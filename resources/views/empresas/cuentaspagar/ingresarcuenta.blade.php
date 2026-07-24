@extends ('layouts.empresas')
@section ('contenido')

<script type="text/javascript">

  $(document).ready(function(){

    $("#formfact").keypress(function(e) {
      if (e.which == 13) {
        return false;
      }
    })



    $("#btnvale").on("click", function() {
      var formulario = $("#formfact").serializeArray();
      $("#botones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/cuentascobrar/registrarcuenta',
        data: formulario,
      }).done(function(respuesta){


       window.location.href = "/cuentascobrar";

       $("#imgload").hide();



     });
    });



  });
  

  function  buscarcliente(){


    var formulario = $("#clinum").val();
    $("#imgload").show();
    $(".botones").hide();
    $.ajax({
      type: "get",
      dataType: 'json',
      url: '/autocomplete/'+formulario,

    }).done(function(respuesta){



     $('#clinom').val(respuesta[0].nom);
     $('#clidir').val(respuesta[0].dir);
     $('#clicor').val(respuesta[0].cor);
                    // $('#clicod').val(respuesta[0].clicod);
                    $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

                    $("#imgload").hide();
          //$(".botones").show();
          
        });



  }


</script>
<section class="content">


  <div class="row">

   {!!Form::open(array('url'=>'/cuentascobrar/registrarcuenta','method'=>'POST','autocomplete'=>'off','files'=>'true','name'=>'formfact','id'=>'formfact'))!!}
   {{Form::token()}}
   <div class="col-xs-12">
    <div class="box">
      <div class="box-header" style="background:blue;">
        <font size="3" color="white"><center><strong>REGISTRAR CUENTA POR COBRAR</strong></center></font>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label for="fecreg">FECHA REGISTRO</label>
              <input type="date" name="fecreg" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
            </div>
          </div>
          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label for="fecven">Fecha Vencimiento</label>
              <input type="date" name="fecven" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
            </div>
          </div>
       
      </div>
      <div class="row">
          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label for="tdicod">Documento</label>
              <select class="form-control" id="tdicod" name="tdicod">
               <option value="6">RUC</option>
               <option value="1">DNI</option>
             </select>
           </div>
         </div>
         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
          <div class="form-group form-group-sm">
            <label for="clinum">RUC/DNI</label>
            <input type="text" name="clinum" id="clinum" onKeypress="if(event.keyCode == 13) buscarcliente();" value="" class="form-control">
          </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
          <div class="form-group form-group-sm">
            <label for="clinom">Razón Social</label>
            <input type="text" name="clinom" id="clinom" value="" class="form-control">
          </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
          <div class="form-group form-group-sm">
            <label for="clidir">Dirección</label>
            <input type="text" name="clidir" id="clidir" value="" class="form-control">
          </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
          <div class="form-group form-group-sm">
           <label>CONCEPTO</label>
            <select class="form-control" name="concepto">
                @foreach($productos as $pro)
                  <option value="{{$pro->IdProducto}}">{{$pro->pronom}}</option>
                @endforeach
            </select>
         
            
          </div>
        </div>
        <!--<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
          <div class="form-group form-group-sm">
            <label for="Concepto">Concepto</label>
            <input type="text" name="concepto" id="concepto" value="" class="form-control">
          </div>
        </div>-->
        <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
          <div class="form-group form-group-sm">
            <label for="deuda">Saldo</label>
            <input type="number" step="any" name="deuda" value="" class="form-control" >
          </div>
        </div>
      </div>




      <div class="row">
       <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" id="botones">
         <div class="form-group form-group-sm">
           <button class="btn btn-primary" type="button" name="btnvale" id="btnvale">Registrar</button>

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