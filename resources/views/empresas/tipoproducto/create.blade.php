@extends ('layouts.empresas')
@section ('contenido')

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>REGISTRAR TIPO DE PRODUCTO</strong></font></center>
                </div>
                <div class="box-body">

                {!!Form::open(array('url'=>'tipoproducto','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
                {{Form::token()}}
                
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="tip_pro_nom">Tipo de Producto</label>
                            <input type="text" name="tip_pro_nom" value="{{old('tip_pro_nom')}}" class="form-control" placeholder="Ej. Alimentos, Bebidas...">
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="cta_contable_70">Cuenta Contable 70 (Ventas)</label>
                            <input type="text" name="cta_contable_70" value="{{old('cta_contable_70')}}" class="form-control" placeholder="Ej. 7032111">
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="cta_contable_12">Cuenta Contable 12 (Cobros)</label>
                            <input type="text" name="cta_contable_12" value="{{old('cta_contable_12')}}" class="form-control" placeholder="Ej. 121201">
                        </div>
                    </div>

                    <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div class="form-group form-group-sm">
                            <label for="color">Color</label>
                            <input type="color" name="color" value="#3f4aee" class="form-control" placeholder="">
                        </div>
                    </div>
                    
                    <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
                        <div class="form-group form-group-sm">
                            <label for="txt_catnom">IMPRESORAS</label>
                            <select name="impresoras" class="form-control">
                                <option></option>
                                @foreach($impresoras as $impresora)
                                <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="form-group form-group-sm">
                            <button class="btn btn-primary" type="submit">Guardar</button>
                            <a href="/tipoproducto"><button type="button" class="btn btn-danger">Cancelar</button></a>
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