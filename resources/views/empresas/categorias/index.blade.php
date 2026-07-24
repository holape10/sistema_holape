@extends('layouts.empresas')
@section('contenido')
<script type="text/javascript">
	  $(document).ready(function(){

		$(".predeterminado").on("click", function() {
	        
	          var cat = $(this).val();

	          $.ajax({
	            type: "GET",
	            dataType: 'json',
	            url: '/categoriapredeterminada/'+cat,
	            
	          }).done(function(respuesta){


	            
	     
	          });

        });
      }) 

</script>

<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>FAMILIAS</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/categorias/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.categorias.search')
						</div>
				    </div>
	            </div>
	        </div>
	</div>
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
    <th>TIPO PRODUCTO</th> <th>FAMILIAS</th>
    <th>PREDETERMINADO</th>
    <th>IMPRESORA 1</th>
    <th>IMPRESORA 2</th>
    <th>IMPRESORA 3</th>
    <th>OPCIONES</th>
</thead>

@foreach ($categorias as $cat)
<tr>
    <td><strong>{{$cat->tip_pro_nom}}</strong></td> 
    <td>{{$cat->cat_nom}}</td>
    <td>
        @if($cat->predeterminado =='1')
            <div class="form-check">
                <input class="form-check-input predeterminado" type="radio" checked="checked" name="predeterminado" id="predeterminado" value="{{$cat->cat_id}}">
            </div>
        @else
            <div class="form-check">
                <input class="form-check-input predeterminado" type="radio" name="predeterminado" id="predeterminado" value="{{$cat->cat_id}}">
            </div>
        @endif
    </td>
    <td>{{$cat->descripcion1}}</td>
    <td>{{$cat->descripcion2}}</td>
    <td>{{$cat->descripcion3}}</td>
    <td>
        <a href="{{URL::action('CategoriasController@edit',$cat->cat_id)}}"><button class="btn btn-info">Editar</button></a>
        <a href="" data-target="#modal-delete-{{$cat->cat_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
    </td>
</tr>
@include('empresas.categorias.modal')
@endforeach
			</table>
		</div>
		{{$categorias->render()}}
	</div>
</div>
</div>
</section>
@endsection
