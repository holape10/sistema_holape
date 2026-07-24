@extends('layouts.empresas')
@section('contenido')
    
<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }

    input[readonly]
{
    background-color:#eee;
}


input[type=number] { -moz-appearance:textfield; }

#formfact label.error {
        color:red;
    }

</style>
<script>
  
</script>
<script>
    $(document).ready(function()
    {   
        var $svalor=0;
        var iCnt = 0;      

        $("#serdocmod").on('dblclick', function (){
            
            $('#serdocmod').prop("readonly",false);
            $('#serdocmod').val('');
            $('#numdocmod').val('');
            $('#tdomod').val('');
            $('#tdo_cod').val('');
            
        })

        jQuery.validator.addMethod("alphanumeric", function(value, element) {
            return this.optional(element) || /^[\w.]+$/i.test(value);
        }, "Letters, numbers, and underscores only please"),

      
        $('#formfact').validate({

            rules: {
                
            
                serdocmod:{
                    required:true,
                    maxlength: 4
                },
                numdocmod:{
                    required:true,
                    maxlength: 8
                },

                fecEmi:{
                    required: true,
                    date: true
                },
                Descuentos:{
                    number:true,
                    min:0
                },
                otros:{
                    number:true,
                    min:0
                },
                otrosc:{
                    number:true,
                    min:0
                },
                fecEmi:"required",
                obser:{
                    required:true,
                    maxlength: 250
                }

               },


            messages: {
                serdocmod:{
                    required:"Ingresar Número de Serie",
                    maxlength:"Ingresar 4 Caracteres como máximo"
                },
                numdocmod:{
                    required:"Ingresar Número de documento",
                    maxlength:"Ingresar 8 Caracteres como máximo"
                },

                fecEmi:{
                    required:"Ingresar la fecha de emisión",
                    date:"Ingresar una fecha válida"
                },
                fecbaj:{
                    required:"Ingresar la fecha de baja",
                    date:"Ingresar una fecha válida"
                },
                obser:{
                    required:"Ingresar el motivo o sustento de la baja",
                    maxlength:"El número máximo de caracteres es de 250"
                }
            }

        })

        
  

      $(".serdocbaja").autocomplete({
          source: '{!!URL::route('buscarcomprobantebaja')!!}',
          dataType: "json",
          minLength: 3,
          autoFocus:true,
          select: function(event,ui) {   
            //$(this).closest('tr').find("td:eq(1) > input").val(ui.item.numdoc);
            $(this).closest('tr').find("td:eq(1) > input").val(ui.item.fecemi);
            $(this).closest('tr').find("td:eq(2) > input").val(ui.item.tdomod);
            $(this).closest('tr').find("td:eq(3) > input").val(ui.item.tdocod);
            $(this).closest('tr').find("td:eq(5) > input").val(ui.item.monnom);
            $(this).closest('tr').find("td:eq(6) > input").val(ui.item.ccaitv);
            $(this).closest('tr').find("td:eq(0) > input").prop('readonly',true);
                     
            validarexistente();
            validartabla();
          }
      })

      $( "#formbaja" ).submit(function( event ) {
        validaritem();  
        validartabla(); 
        validarexistente();
       
      })



    }); 
</script>
<script type="text/javascript">
  function validartabla(){
     var conser = 0,conmot=0;
     $('#detbaja >tbody >tr').each(function(){
        var ser = $(this).find("td:eq(0) > input").val();
        var mot = $(this).find("td:eq(4) > input").val();
     
        if(ser.trim()==''){
            conser++;
        }else if(mot.trim()==''){
            conmot++
        }


        if(conser>0){
            $('.alertser').show(); 
        }else{
            $('#alertser').hide();
        }   

        if(conmot>0) {
            $('.alertmot').show(); 
        }else{
            $('#alertmot').hide(); 
        }

        if(conser>0 || conmot >0){
            event.preventDefault(); 
        }

      })
  };

   function deleteRow(btn) {
 
      var row = btn.parentNode.parentNode;
      row.parentNode.removeChild(row);

      validaritem();
      validarexistente();

    };
  
    function validaritem(){
      if ($('#detbaja >tbody >tr').length == 0){
                $('#alertitem').show();
                event.preventDefault(); 
      }else{
         $('#alertitem').hide();
      }
    }

    function validarexistente(){
        var cont=0;
        var tempser,tempnum;

        $('#detbaja >tbody >tr').each(function(){
          var serdoc = $(this).find("td:eq(0) > input").val();
          var numdoc = $(this).find("td:eq(1) > input").val();
                    
          if(serdoc.trim()!="" && numdoc.trim()!=""){
            if(tempnum==numdoc &&  tempser==serdoc){
              cont++;   
            }
            
            if(cont>0){
              $('.alertexist').show(); 
            }else{
              $('.alertexist').hide(); 
            }   

            if(cont>0){
              event.preventDefault(); 
            }  
          }

          tempser = serdoc;
          tempnum = numdoc;
          
        })    
      };
</script>
    
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <center><h3>COMUNICACIÓN DE BAJA</h3></center>
  </div>

    </div>
    {!!Form::open(array('url'=>'/registrarbajacomprobante','autocomplete'=>'off','method'=>'GET','name'=>'formbaja','id'=>'formbaja','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
     
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <strong>DATOS DE BAJA</strong>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Número</label>
                  <input type="text"  name="numbaj" id="numbaj" value="{{$cor}}" class="form-control" readonly="readonly">
                </div>
              </div> 
              <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Fecha de Baja</label>
                  <input type="date"  name="fecbaj" id="fecbaj" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control" readonly="readonly">
                </div>
              </div>            
            </div>
          </div>
        </div>  
      </div>
    </div>
  </div>
   <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <strong>DOCUMENTOS DE BAJA</strong>
          </div>
          <div class="panel-body">
              <div id="alertser" hidden class="col-lg-8 alertser">
                <strong><font color="red">Ingresar la serie del documento</font></strong>
              </div>
              <div id="alertmot" hidden class="col-lg-8 alertmot">
                <strong><font color="red">Ingresar el motivo de la comunicación de baja</font></strong>
              </div>
              <div id="alertitem" hidden class="col-lg-8 alertitem">
                  <strong><font color="red"> Ingresar los comprobantes que se darán de baja</font></strong>
              </div>
              <div id="alertexist" hidden class="alertexist col-lg-8">
                <strong><font color="red">No se permite duplicidad de documentos</font></strong>
              </div>
             <table id="detbaja" class="table">
                  <thead>
                    <th>Serie</th>
                    <th>Fecha de Doc.</th>
                    <th>Tipo Documento</th>
                    <th>Motivo</th>
                    <th>Moneda</th>
                    <th>Total</th>
                    <th hidden="hidden"><button type="button" onClick="" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <input type="text"  name="serdocbaja" id="serdocbaja"  placeholder="" class="serdocbaja form-control"></td>
                    </td>

                    <td>
                      <input type="date"  name="fecemi" id="fecemi" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control" readonly="readonly">
                    </td>
                    <td>
                      <input name="tdomod" id="tdomod" value="" class="form-control" readonly="readonly">
                    </td>
                    <td hidden>
                      <input type="hidden" name="tdo_cod"  value="" id="tdo_cod" class="form-control" readonly="readonly">
                    </td>
                    <td>
                      <input class="obser form-control" value="Baja de Comprobante"  name="obser" id="obser">
                    </td>
                    <td>
                      <input class="monom form-control"  name="monnom" id="monnom"  value="" readonly="readonly">
                    </td>
                    <td>
                      <input type="number" class="ccaitv form-control"  name="ccaitv" id="ccaitv" value="" readonly="readonly"></td>
                </tr>
                </tbody>
              </table>
     
          </div>
        </div>  
      </div>
    </div>
  </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <button type="submit" id="btn" name="btn"  class="btn btn-primary"><strong>REGISTRAR BAJA</strong></button>
                     <a href="{{config('global.ruta')}}/SisFact"><button type="button" class="btn btn-danger"><strong>CANCELAR</strong></button></a>
                </div>
            </div>  
        </div>
        
        {!!Form::close()!!}     
@endsection