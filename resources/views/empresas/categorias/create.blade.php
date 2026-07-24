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
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>REGISTRAR FAMILIA</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::open(array('url'=>'categorias','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">

        <div class="col-lg-2 col-md-3 col-sm-6 col-xs-6">
        <div class="form-group form-group-sm">
            <label>Linea</label>
            <select name="tip_pro_id" class="form-control" required>
                <option value="">Seleccione un Tipo...</option>
                @foreach($tipos as $tp)
                    <option value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                @endforeach
            </select>
        </div>
    </div>

        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtCatNom">Familia</label>
                <input type="text" name="txtCatNom" value="{{old('txtCatNom')}}" class="form-control" placeholder="">

           </div>
        </div>
      <div class="col-lg-1 col-md-1 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="color">Color</label>
                <input type="color" name="color" value="#3f4aee" class="form-control" placeholder="">

           </div>
        </div>
       <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="impresoras">IMPRESORA 1</label>
                <select name="impresoras" class="form-control">
                    <option value="">Ninguna</option>
                    @foreach($impresoras as $impresora)
                    <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                    @endforeach
                </select>
           </div>
        </div>

        {{-- INICIO: CAMPOS AGREGADOS --}}
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="impresora2">IMPRESORA 2</label>
                <select name="impresora2" class="form-control">
                    <option value="">Ninguna</option>
                    @foreach($impresoras as $impresora)
                    <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                    @endforeach
                </select>
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="impresora3">IMPRESORA 3</label>
                <select name="impresora3" class="form-control">
                    <option value="">Ninguna</option>
                    @foreach($impresoras as $impresora)
                    <option value="{{$impresora->Id}}">{{$impresora->descripcion}}</option>
                    @endforeach
                </select>
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6" hidden="hidden">
            <div class="form-group form-group-sm">
                <label for="txt_catnom">ACOMPAÑAMIENTO</label>
                <select name="cat_acom" class="form-control">
                      <option value="0">NO</option>
                    <option value="1">SI</option>
                </select>
           </div>
        </div>
          <div class="col-lg-1 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txt_catnom">VISIBLE</label>
                <select name="visible" class="form-control">
                    <option value="1">SI</option>
                    <option value="0">NO</option>
                </select>
           </div>
        </div>
        {{-- FIN: CAMPOS AGREGADOS --}}

        {{-- El layout original tiene 5 columnas en la primera fila, así que ajusto para que queden 6 y evitar problemas de diseño --}}
    </div>
    <div class="row">
        
        </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/categorias"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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