@extends ('layouts.empresas')
@section ('contenido')
<script>
$(document).ready(function()
{       

    $("#rol").change(function(){

        
   /*     if($("#rol").val()=='5' ){

            $valor = $('#email').val();

         

            $("#password").prop('readonly',true);
            $("#password_confirmation").prop('readonly',true);

            $("#password").val($valor);
            $("#password_confirmation").val($valor);

         }else{

            $("#password").prop('readonly',false);
            $("#password_confirmation").prop('readonly',false);

         }

    });*/

  /*  $("#email").keyup(function(){

        $valor = $(this).val();

        
        if($("#rol").val()=='5' ){

            $("#password").prop('readonly',true);
            $("#password_confirmation").prop('readonly',true);

            if($valor !=''){
              $("#password").val($valor);
              $("#password_confirmation").val($valor);
         
            }else{
              $("#password").val($valor);
              $("#password_confirmation").val($valor);
         
            }

         }else{

            $("#password").prop('readonly',false);
            $("#password_confirmation").prop('readonly',false);

         }

         
         
    });
*/
      $("#IdEmpresa").change(function() {
         
                var empresa = $("#IdEmpresa").val();
                $("#sucursales").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscarsucursales/"+empresa,

                }).done(function(respuesta){
                $("#sucursales").html(respuesta.vista);
               
                });
        

        });
    });

 
</script>

<section class="content">
	

	<div class="row">
        	<div class="col-lg-12">
          		<div class="box">
                 <div class="box-header" style="background:blue;">
                        <font size="3" color="white"><center><strong>REGISTRAR USUARIO</strong></center></font>
                     
                    </div>
	            	<div class="box-body">
	{!!Form::open(array('url'=>'administrador/usuarios','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group form-group-sm">
		       	<label for="name">Nombres</label>
		        <input type="text" name="name" value="{{old('name')}}" class="form-control" placeholder="Nombres...">
           </div>
           <input type="hidden" name="IdIngreso" id="IdIngreso">
    	</div>
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group form-group-sm">
		       	<label for="apeUsuario">Apellidos</label>
		        <input type="text" name="apeUsuario" value="{{old('apeUsuario')}}" class="form-control" placeholder="Apellidos...">
           </div>
    	</div>
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group form-group-sm">
                <label for="email">Usuario</label>
                <input type="text" id="email" name="email" id="email" value="{{old('email')}}" class="form-control" placeholder="Usuario...">
           </div>
    	</div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        <div class="form-group form-group-sm">
                <label for="rol">Rol</label>
                 <select name="rol" id="rol" class="form-control">
                  @foreach ($roles as $rol)
                    <option value="{{$rol->id}}">{{$rol->description}}</option>
                  @endforeach
              </select>
           </div>
      </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
			<label>RUC</label>
              <select name="IdEmpresa" id="IdEmpresa" class="form-control">
                  @foreach ($empresas as $emp)
                    <option value="{{$emp->IdEmpresa}}">{{$emp->NomEmpresa}}</option>
                  @endforeach
              </select>
            </div>
        </div>
          <div id="sucursales" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
              <label>SUCURSAL</label>
                <select name="idnegocio" id="idnegocio" class="form-control">
                  @foreach ($negocios as $negocio)
                    <option value="{{$negocio->id_empresa_negocio}}">{{$negocio->tipo_negocio}}</option>
                  @endforeach
                </select>
              </div>
            </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" >
            <div class="form-group form-group-sm">
                <label for="password">Contraseña</label><font color="red">*</font>
                <input id="password" type="password" class="form-control" name="password" id="password"  placeholder="Contraseña...">
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" >
            <div class="form-group form-group-sm">
                <label for="password-confirm">Confirmar contraseña</label><font color="red">*</font>
                <input  class="form-control" type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmar Contraseña..." >
            </div>
        </div>

    </div>

    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="{{config('global.ruta')}}/administrador/usuarios"><button type="button" class="btn btn-danger">Cancelar</button></a>
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