@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">


        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>EDITAR FAMILIA</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::model($categorias,['method'=>'PATCH','route'=>['categorias.update',$categorias->cat_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txt_procod">Código de Categoría</label>
                <input type="text" name="txt_procod" value="{{$categorias->cat_id}}" class="form-control" placeholder="Código de Categoría...">

           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
        <div class="form-group form-group-sm">
            <label>Linea</label>
            <select name="tip_pro_id" class="form-control" required>
                @foreach($tipos as $tp)
                    @if($tp->tip_pro_id == $categorias->tip_pro_id)
                        <option value="{{$tp->tip_pro_id}}" selected>{{$tp->tip_pro_nom}}</option>
                    @else
                        <option value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>




        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txt_catnom">Familia</label>
                <input type="text" name="txt_catnom" value="{{$categorias->cat_nom}}" class="form-control" placeholder="Nombre de Categoría...">

           </div>
        </div>
        <div class="col-lg-1 col-md-1 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="color">Color</label>
                <input type="color" name="color" value="{{$categorias->color}}" class="form-control" placeholder="">

           </div>
        </div>
          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="impresoras">IMPRESORA 1</label>
                <select name="impresoras" class="form-control">
                    <option value=""></option>
                    @foreach($impresoras as $impresora)
                        @if($impresora->Id == $categorias->impresora)
                            <option selected="selected" value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                        @else
                            <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                        @endif
                    @endforeach
                </select>
           </div>
        </div>

        {{-- INICIO: CAMPOS AGREGADOS PARA IMPRESORA 2 y 3 --}}
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="impresora2">IMPRESORA 2</label>
                <select name="impresora2" class="form-control">
                    <option value=""></option> {{-- Opción para no seleccionar ninguna --}}
                    @foreach($impresoras as $impresora)
                        {{-- Compara con el valor actual de impresora2 --}}
                        @if($impresora->Id == $categorias->impresora2)
                            <option selected="selected" value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                        @else
                            <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                        @endif
                    @endforeach
                </select>
           </div>
        </div>

        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="impresora3">IMPRESORA 3</label>
                <select name="impresora3" class="form-control">
                    <option value=""></option> {{-- Opción para no seleccionar ninguna --}}
                    @foreach($impresoras as $impresora)
                        {{-- Compara con el valor actual de impresora3 --}}
                        @if($impresora->Id == $categorias->impresora3)
                            <option selected="selected" value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                        @else
                            <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                        @endif
                    @endforeach
                </select>
           </div>
        </div>
        {{-- FIN: CAMPOS AGREGADOS PARA IMPRESORA 2 y 3 --}}

        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6" hidden="hidden">
            <div class="form-group form-group-sm">
                <label for="txt_catnom">ACOMPAÑAMIENTO</label>
                <select name="cat_acom" class="form-control">
                    @if($categorias->cat_acom=='1')
                        <option selected="selected" value="1">SI</option>
                        <option value="0">NO</option>
                    @else
                        <option value="1">SI</option>
                        <option selected="selected" value="0">NO</option>
                    @endif


                </select>
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txt_catnom">VISIBLE</label>
                <select name="visible" class="form-control">
                    @if($categorias->visible=='1')
                        <option selected="selected" value="1">SI</option>
                        <option value="0">NO</option>
                    @else
                        <option value="1">SI</option>
                        <option selected="selected" value="0">NO</option>
                    @endif


                </select>
           </div>
        </div>
        </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{config('global.ruta')}}/categorias"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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