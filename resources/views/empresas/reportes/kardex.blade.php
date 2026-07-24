@extends('layouts.empresas')
@section('contenido')
<script src=”https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js”></script>
<script src=”https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js”></script>

<script type="text/javascript">

  
     $(document).ready(function()
    {



        $("#btnGenPdf").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
            $('#frmReporte').attr('target', '_blank');
          $('#frmReporte').submit();
        });

             $("#btnGenExcel").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
             $('#frmReporte').attr('target', '_blank');
          $('#frmReporte').submit();
        });
        

        $("#btnSubmit").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
          

     $("#sucursal").change(function() {
         
                var sucursal = $("#sucursal").val();
               
                $("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacen").html(respuesta.vista);
               
                });

      });
});
     </script>

<section class="content"> 
  <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header box-success" style="background-color:#337ab7;">
              <font color="white" size="4"><center><strong>KARDEX</strong></center></font>
            </div>
              <div class="box-body">
                @include('empresas.reportes.buscarkardex')
              </div>
           </div>
      </div>
  </div> 
  
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
                <div class="box-body">
            
          </div>  
        
        </div>
    </div>
  </div>
</section>



@endsection