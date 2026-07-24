@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class='glyphicon glyphicon-user'></i><strong> EDITAR TIPO DOCUMENTO</strong></h4>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::model($documento,['method'=>'PATCH','route'=>['tiposdocumentos.update',$documento->doc_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="doc_id">Código</label>
                <input type="text" name="doc_id" value="{{$documento->doc_id}}" class="form-control" >
                
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="doc_nom">Banco</label>
                <input type="text" name="doc_nom" value="{{$documento->doc_nom}}" class="form-control">
               
           </div>
        </div>

			

    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/tiposdocumentos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
