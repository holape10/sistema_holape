@extends('layouts.empresas')
@section('contenido')


<section class="content">
		
    	<div class="row">
        	<div class="col-xs-12">
        		<div class="box">
          			<div class="box-header" style="background:blue;">
          				<font size="3" color="white"><center><strong>USUARIOS</strong></center></font>
          				<div class="box-tools pull-right">
                			<a href="/usuarios/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
          			</div>
          			<div class="box-body">
						<div class="col-lg-6">
							@include('administrador.empresas.search')	
						</div>
							
						</div>
				</div>
          		<div class="box">
	            	<div class="box-body">
				<table id=""  class="table table-bordered table-hover">
						<thead style="background:gray">
								<th colspan="6"><font color="white"><center><strong>LISTA DE EMPRESAS</strong></center></font></th>
							</thead>
				<thead>
					<th>Empresa</th>
					<th>Nombres</th>
					<th>Apellidos</th>
					<th>Correo</th>
					<th>Estado</th>
					<th>Opciones</th>
				</thead>
				<tbody>
				@foreach ($usuarios as $usu)
				
				<tr>
					<td>{{$usu->NomEmpresa}}</td>
					<td>{{$usu->name}}</td>
					<td>{{$usu->apeusu}}</td>
					<td>{{$usu->email}}</td>
					<td>{{$usu->estusu}}</td>
					<td>
						<a href="{{URL::action('UsuarioController@edit',$usu->IdUsuario)}}"><button class="btn btn-info">Editar</button></a>
						<a href="/administrador/contrasena/{{$usu->IdUsuario}}"><button class="btn btn-success">Contraseña</button></a>
                        <a href="" data-target="#modal-delete-{{$usu->IdUsuario}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>


					</td>
				</tr>
				@include('administrador.usuarios.modal')
				@endforeach
				</tbody>
			</table>
		</div>	
		{{$usuarios->render()}}
		</div>	
		</div>	
	</div>	

</section>
@endsection