@extends('layouts.empresas')
@section('contenido')

<html>

<title></title>

<body>
   <BR><div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                @if(session()->has('info'))
                    <br><br><br><br><div class="alert alert-danger">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Alerta!</strong> {{ session('info') }}
                    </div>
                @endif


                @if(session()->has('success'))
                    <div class="alert alert-success">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Información!</strong> {{ session('success') }}
                    </div>
                @endif
         
            </div>
        </div>
    </div>


  <div class="container-fluid">
     <div class="row">
          <div class="col-lg-8 col-xs-12">
              <div class="btn-toolbar" role="toolbar" aria-label="...">
                <div class="btn-group">
                  <div class="form-group form-group-sm"><a href="/listallevar"><button  class="btn btn-sm btn-warning" ><strong>PEDIDOS PARA LLEVAR</strong></button></a>
                   </div>
                </div>
                <div class="btn-group">
                   <div class="form-group form-group-sm"><a href="/listos"><button  class="btn btn-sm btn-success" ><strong>SEGUIMIENTO PEDIDOS</strong></button></a></div>
                </div>
                @foreach($pisos as $piso)
                  <div class="btn-group">
                    <div class="form-group form-group-sm"><a href="/mostrarpiso/{{$piso->pis_id}}"><button value="{{$piso->pis_id}}" class="btn btn-sm btn-primary" ><strong>{{$piso->pis_nom}}</strong></button></a></div>
                  </div>
                @endforeach
              </div>
          </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border" style="background-color:blue;">
              @if(!empty($primer_piso))
                <center><font color="white"><strong>{{$primer_piso->pis_nom}}</strong></font></center>
              @endif
            </div>
      <div class="box-body" style="height:790px">
     
        <?php $i=0; ?>
        @if(!empty($mesas))
        @foreach($mesas as $mesas)
        <?php $i=$i+1; ?>
        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6" >
             @if($mesas->mes_est=='Ocupado')
             <a href="" data-target="#modal-opciones-{{$mesas->mes_id}}" data-toggle="modal">
             <center><div style="border-style:solid ; width:150px; border-radius:10px; background:#E74C3C ">
              @elseif($mesas->mes_est=='Libre')
              <a href="/pedido/{{$mesas->mes_id}}">
            <center><div style="border-style:solid ; width:150px; border-radius:10px; background:#52BE80">
              @else
            <center><div style="border-style:solid ; width:150px; border-radius:10px; background:#F4D03F">
              @endif
          <img src="/img/habitacion.png" height="70px" width="80px"></br></br>
          <p><font style="color:#FDFEFE"><strong >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$mesas->mes_nom}}</strong></font></p>
        </div></center>
          </br></br>
        </a>
         </div>
          @include('empresas.puntosventas.opcionesmodal')
        @endforeach
      @endif
      </div>
    </div>
  </div>

  </div>

  </div>
  </div>

</body>


</html>

@endsection
