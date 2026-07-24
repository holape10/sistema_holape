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
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> NUEVO APLICATIVO</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::open(array('url'=>'aplicativos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txtApliNom">Aplicativo</label>
                <input type="text" name="txtApliNom" value="{{old('txtApliNom')}}" class="form-control" placeholder="">
                  @if ($errors->has('txtApliNom'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txtApliNom') }}</font></strong></span>
                @endif
           </div>
        </div>
    
   
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