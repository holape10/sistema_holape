@extends ('layouts.empresas')
@section ('contenido')
<style>
    

#formuni label.error {
        color:red;
    }

</style>

<script>
$(document).ready(function()
{  
        

  

        $('#formuni').validate({

            rules: {

                   txtCodUniMed:{
                    required:true,
                    max:3
                   },

                   txtUniMed:{
                    required:true
                   },


                
              
               },


            messages: {

                txtCodUniMed:{
                    required:"Campo Obligatorio",
                    max:"Máximo tres caracteres"
                },
                 txtUniMed:{
                    required:"Campo Obligatorio"
                    
                }
            }

        })


     

       
});
</script>

       

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> NUEVA UNIDAD</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::open(array('url'=>'unidades','method'=>'POST','name'=>'formuni','id'=>'formuni','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="txtCodUniMed">CODIGO</label>
                <input type="text" name="txtCodUniMed" value="{{old('txtCodUniMed')}}" class="form-control" placeholder="">
                  @if ($errors->has('txtCodUniMed'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txtCodUniMed') }}</font></strong></span>
                @endif
           </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="txtUniMed">Unidad Medida</label>
                <input type="text" name="txtUniMed" value="{{old('txtUniMed')}}" class="form-control" placeholder="">
                  @if ($errors->has('txtUniMed'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txtUniMeded') }}</font></strong></span>
                @endif
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group">
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