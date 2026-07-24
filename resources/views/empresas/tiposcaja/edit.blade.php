@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class='glyphicon glyphicon-user'></i><strong> EDITAR TIPO CAJA</strong></h4>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::model($cajas,['method'=>'PATCH','route'=>['tiposcaja.update',$cajas->tip_caj_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tip_caj_id">C&oacute;dio</label>
                <input type="text" name="tip_caj_id" value="{{$cajas->tip_caj_id}}" class="form-control">
                 
           </div>
        </div>


      

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tip_caj_nom">Tipo Caja</label>
                <input type="text" name="tip_caj_nom" value="{{$cajas->tip_caj_nom}}" class="form-control">
             
           </div>
        </div>
            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="movimiento">Movimiento</label>
                <select name="movimiento" class="form-control">
                @if($cajas->tipo =='ENTRADA')
                    <option selected="selected" value="ENTRADA">ENTRADA</option>
                      <option value="SALIDA">SALIDA</option>
                @else
                    <option value="ENTRADA">ENTRADA</option>
                      <option selected="selected" value="SALIDA">SALIDA</option>
                @endif
                    
                </select>
            
           </div>
        </div>
        
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/tiposcaja"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
