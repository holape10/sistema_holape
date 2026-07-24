@extends ('layouts.empresas')
@section ('contenido')
<script>
$(document).ready(function()
{       

   /* $("#rol").change(function(){

        
        if($("#rol").val()=='5' ){

            $valor = $('#email').val();

         

            $("#password").prop('readonly',true);
            $("#password_confirmation").prop('readonly',true);

            $("#password").val($valor);
            $("#password_confirmation").val($valor);

         }else{

            $("#password").prop('readonly',false);
            $("#password_confirmation").prop('readonly',false);

         }

    });

    $("#email").keyup(function(){

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

         
         
    });*/

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
                        <font size="3" color="white"><center><strong>EDITAR USUARIO</strong></center></font>
                     
                    </div>
	            	<div class="box-body">

	{!!Form::model($usuario,['method'=>'PATCH','route'=>['usuarios.update',$usuario->IdUsuario],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="name">Nombres</label><font color="red">*</font>
                <input id="name" type="text" class="form-control" name="name" value="{{$usuario->name}}" required autofocus >
                   
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="apeUsuario">Apellidos</label><font color="red">*</font>
                <input type="text" name="apeUsuario" value="{{$usuario->apeusu}}" class="form-control" placeholder="Apellidos...">
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="email" >usuario</label><font color="red">*</font>
                    <input id="email" type="text" class="form-control" name="email"  id="email" value="{{$usuario->email}}" required >
                  
            </div>
        </div>
         <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        <div class="form-group form-group-sm">
                <label for="rol">Rol</label>
                 <select name="rol" id="rol" class="form-control">
                  @foreach ($roles as $rol)
                  @if($usuario->role_id ==$rol->id)
                    <option value="{{$rol->id}}" selected="selected">{{$rol->description}}</option>
                  @else
                    <option value="{{$rol->id}}">{{$rol->description}}</option>
                    @endif
                  @endforeach
              </select>
           </div>
      </div>
         <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
             <label>Empresa</label><font color="red">*</font>
              <select  name="IdEmpresa" id="IdEmpresa" class="form-control">
                  @foreach ($empresas as $emp)
                  	@if($emp->IdEmpresa==$usuario->IdEmpresa)
                    	<option value="{{$emp->IdEmpresa}}" selected>{{$emp->NomEmpresa}}</option>
                    @else
                    	<option value="{{$emp->IdEmpresa}}" >{{$emp->NomEmpresa}}</option>
                    @endif
                  @endforeach
              </select>
            </div>
        </div>
          <div id="sucursales" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
            <label>SUCURSAL</label>
              <select name="idnegocio" id="idnegocio" class="form-control">
                  @foreach ($negocios as $negocio)
                    @if($negocio->id_empresa_negocio==$usuario->id_empresa_negocio)
                        <option value="{{$negocio->id_empresa_negocio}}" selected>{{$negocio->tipo_negocio}}</option>
                    @else
                        <option value="{{$negocio->id_empresa_negocio}}" >{{$negocio->tipo_negocio}}</option>
                    @endif
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
                <input  class="form-control" type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmar Contraseña...">
            </div>
        </div>

          
        </div>
        
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <button type="submit" class="btn btn-primary">Registrar</button>
                <a href="{{config('global.ruta')}}/administrador/usuarios"><button type="button" class="btn btn-danger">Cancelar</button></a>
            </div>
        </div>
    </div>
	{!!Form::close()!!}		
</div>
	</div>
	</div>
	</div>
	</section>
 
@endsection