@extends ('layouts.empresas')
@section ('contenido')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>EDITAR SUBFAMILIA</strong></font></center>
                    </div>
                    <div class="box-body">

                    {!!Form::model($subcategorias,['method'=>'PATCH','route'=>['subcategorias.update',$subcategorias->subcat_id],'files'=>'true'])!!}
                    {{Form::token()}}
                    
                    <div class="row">
                        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <div class="form-group form-group-sm">
                                <label for="subcat_id">Código de Subcategoria</label>
                                <input type="text" name="subcat_id" value="{{$subcategorias->subcat_id}}" class="form-control">
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                            <div class="form-group form-group-sm">
                                <label for="cat_id">Familia</label>
                                <select class="form-control" name="cat_id" required>
                                    @foreach($categorias as $cat)
                                        @if($cat->cat_id == $subcategorias->cat_id)
                                            <option selected="selected" value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                                        @else
                                            <option value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                            <div class="form-group form-group-sm">
                                <label for="subcat_nom">Subfamilia</label>
                                <input type="text" name="subcat_nom" value="{{$subcategorias->subcat_nom}}" class="form-control" required>
                            </div>
                        </div>

                        <div hidden="hidden" class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                            <div class="form-group form-group-sm">
                                <label for="color">Color</label>
                                <input type="color" name="color" value="{{$subcategorias->color}}" class="form-control">
                            </div>
                        </div>
                        
                        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                            <div class="form-group form-group-sm">
                                <label for="impresoras">IMPRESORAS</label>
                                <select name="impresoras" class="form-control">
                                    <option></option>
                                    @foreach($impresoras as $impresora)
                                        @if($impresora->Id == $subcategorias->impresora)
                                            <option selected="selected" value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                                        @else
                                            <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <div class="form-group form-group-sm">
                                <button class="btn btn-primary" type="submit">Guardar</button>
                                <a href="/subcategorias"><button type="button" class="btn btn-danger">Cancelar</button></a>
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