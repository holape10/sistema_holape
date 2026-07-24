@extends ('layouts.empresas')
@section ('contenido')
<script>
$(document).ready(function()
{

		$("#txt_provun").on('change',function(){
            var numdoc = $('#txt_provun').val();
            $("#txt_propun").val((numdoc*1.18).toFixed(3));
        })

          $("#txt_propun").on('change',function(){
            var numdoc = $('#txt_propun').val();
            $("#txt_provun").val((numdoc/1.1055).toFixed(3));
        })



});
</script>
	<section class="content">
 

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                     <div class="box-header" style="background-color:blue;">
                        <font color="white"><center><strong>EDITAR MESA</strong></center></font>
                      
                    </div>
                    <div class="box-body">

    {!!Form::model($mesa,['method'=>'PATCH','route'=>['mesa.update',$mesa->mes_id,$mesa->IdEmpresa],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="txt_mesid">Código de la Mesa</label>
                <input type="text" name="txt_mesid" value="{{$mesa->mes_id}}" class="form-control" placeholder="Código de la Mesa...">
                  @if ($errors->has('txt_mesid'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_mesid') }}</font></strong></span>
                @endif
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="txt_pronom">Nombre de la Mesa</label>
                <input type="text" name="txt_pronom" value="{{$mesa->mes_nom}}" class="form-control" placeholder="Nombre de la Mesa...">
                  @if ($errors->has('txt_pronom'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_pronom') }}</font></strong></span>
                @endif
           </div>
        </div>

		  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="pis_id">Piso</label>
                <select name="pis_id" class="form-control">
                    @foreach($pisos as $piso)
                    @if($piso->pis_id == $mesa->pis_id)
                        <option value="{{$piso->pis_id}}" selected="selected">{{$piso->pis_nom}}</option>
                    @else
                        <option value="{{$piso->pis_id}}">{{$piso->pis_nom}}</option>
                    @endif
                    @endforeach
                </select>
           </div>
        </div>

           <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="pis_id">Mozos</label>
                <select name="usuario" class="form-control">
                    @foreach($usuarios as $usuario)
                    @if($usuario->IdUsuario == $mesa->IdUsuario)
                         <option selected="selected" value="{{$usuario->IdUsuario}}">{{$usuario->name}} {{$usuario->apeusu}}</option>
                    @else
                         <option value="{{$usuario->IdUsuario}}">{{$usuario->name}} {{$usuario->apeusu}}</option>
                    @endif
                   
                    @endforeach
                </select>
           </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{config('global.ruta')}}/mesas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
