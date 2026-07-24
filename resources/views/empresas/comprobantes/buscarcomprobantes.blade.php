

<!--{!! Form::model(Request::all(),['Route'=>'/SisFact','method'=>'GET','autocomplete'=>'off'])!!}-->
{!!Form::open(array('url'=>$rutaBusqueda ?? '/SisFact','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}

<!--<div class="col-lg-3">
  <div class="form-group form-group-sm">
    <h4><i class='glyphicon glyphicon-search'></i> CONSULTAR COMPROBANTES</h4>
  </div>
</div>-->
<style>
  input[type=date]::-webkit-inner-spin-button, 
  input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }

</style>
<div class="row">
  <div class="col-lg-2">
    <div class="form-group form-group-sm">
      <label>Empresa</label>
      <select class="form-control" name="sucursal">
        @foreach($negocios as $negocio)
           @if($negocio->id_empresa_negocio == $sucursal)
               <option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
           @else
              <option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
           @endif
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-lg-2">
    <div class="form-group form-group-sm">
       <label class="control-label" for="fecin">Desde </label>
       <input type="date" name="fecin" value="{{$fecin}}" class="form-control input-sm">
      <!--<input type="date" name="fecin" class="form-control" value="{{Carbon::now()->format('Y-m-d')}}">-->
  
    </div>
  </div>
  <div class="col-lg-2">
    <div class="form-group form-group-sm">
      <label class="control-label" for="fecfin">Hasta </label>
      <input type="date" name="fecfin" value="{{$fecfin}}" class="form-control input-sm">
      <!--<input type="date" name="fecfin" class="form-control" value="{{Carbon::now()->format('Y-m-d')}}">-->
    
    </div>
  </div>

  <div class="col-lg-2">
    <div class="form-group form-group-sm">
      <label class="control-label">Cliente</label>
      <input type="text" name="cliente" class="form-control" value="{{$razsoc}}">
    
    </div>
  </div>  
  <div class="col-lg-2">
    <div class="form-group form-group-sm">
      <label class="control-label">Comprobante</label>
      <input type="text" name="comp" class="form-control" value="{{$documentoBusqueda}}" placeholder="Ej: F001-123 o 123">
    </div>
  </div>  
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="btn-toolbar" role="toolbar" aria-label="...">
    <div class="btn-group">

        <button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
    
    
    </div>
    <div class="btn-group" >
  
        <!--<a href="/SisFact/create/{{$tdocod='01'}}"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nueva Venta</button></a>-->
    </div>
    
      
  </div>
  </div>

  
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}