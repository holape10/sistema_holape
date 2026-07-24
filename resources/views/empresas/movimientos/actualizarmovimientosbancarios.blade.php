@extends ('layouts.empresas')
@section ('contenido')

<section class="content">


  <div class="row">
    <div class="col-xs-12">
      <div class="box">
       <div class="box-header" style="background:blue;">
        <font size="3" color="white"><center><strong>ACTUALIZAR MOVIMIENTO BANCARIO</strong></center></font>
      </div>
      <div class="box-body">

       {!!Form::open(array('url'=>'/movimientosbancarios/actualizar','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
       {{Form::token()}}
       <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
          <div class="form-group form-group-sm">
            <label for="mov_fecha">FECHA</label>
            <input type="date" name="mov_fecha" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
          </div>
        </div>
        <input type="hidden" name="mov_ban_id" value="{{$movimiento->mov_ban_id}}">
        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
          <div class="form-group form-group-sm">
            <label for="mov_tip" >TIPO MOVIMIENTO</label>
            <select name="mov_tip" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
             @if($movimiento->mov_tip =='debe')
             <option selected="selected" value="debe">DEBE</option>
             <option value="haber">HABER</option>
             @elseif($movimiento->mov_tip =='haber')
             <option value="debe">DEBE</option>
             <option selected="selected"  value="haber">HABER</option>
             @endif


           </select>
         </div>
       </div>


       <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
          <label for="concepto_id">Concepto</label>
          <select name="concepto_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
           @foreach($conceptos as $concepto)
           @if($concepto->concepto_id == $movimiento->concepto_id)
           <option  selected="selected" value="{{$concepto->concepto_id}}">{{$concepto->concepto_nom}}</option>
           @else
           <option value="{{$concepto->concepto_id}}">{{$concepto->concepto_nom}}</option>
           @endif

           @endforeach
         </select>
       </div>
     </div>
     <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
      <div class="form-group form-group-sm">
        <label for="clicod">Clientes</label>
        <select name="clicod" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
         @foreach($clientes as $cliente)
         @if($cliente->clicod == $movimiento->clicod)
         <option selected="selected" value="{{$cliente->clicod}}">{{$cliente->clinom}}</option>
         @else
         <option value="{{$cliente->clicod}}">{{$cliente->clinom}}</option>
         @endif
         @endforeach
       </select>

     </div>
   </div>
 </div>
 <div class="row">


  <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
      <label for="doc_id">Tipo Documento</label>
      <select name="doc_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
       @foreach($documentos as $documento)
       @if($movimiento->doc_id == $documento->doc_id)
       <option selected="selected" value="{{$documento->doc_id}}">{{$documento->doc_nom}}</option>
       @else
       <option value="{{$documento->doc_id}}">{{$documento->doc_nom}}</option>
       @endif

       @endforeach
     </select>

   </div>
 </div>

 <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label for="mov_num_doc">N° Documento</label>
    <input type="text" name="mov_num_doc" value="{{$movimiento->mov_num_doc}}" class="form-control" >

  </div>
</div>

<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label for="ban_id">Banco</label>
    <select name="ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
     @foreach($bancos as $banco)
     @if($banco->ban_id == $movimiento->ban_id)
     <option selected="selected" value="{{$banco->ban_id}}">{{$banco->ban_nom}}</option>
     @else
     <option value="{{$banco->ban_id}}">{{$banco->ban_nom}}</option>
     @endif

     @endforeach
   </select>
 </div>
</div>
<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label for="cuen_ban_id">Cuenta Bancaria</label>
    <select name="cuen_ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
      @foreach($bancos as $banco)
      @if($banco->cuen_ban_id == $movimiento->cuen_ban_id)
      <option selected="selected" value="{{$banco->cuen_ban_id}}">{{strtoupper($banco->ban_nom)}} - CUENTA {{strtoupper($banco->tip_cuen_nom)}} {{strtoupper($banco->monnom)}} {{strtoupper($banco->cuen_ban_num)}}</option>
      @else
      <option value="{{$banco->cuen_ban_id}}">{{strtoupper($banco->ban_nom)}} - CUENTA {{strtoupper($banco->tip_cuen_nom)}} {{strtoupper($banco->monnom)}} {{strtoupper($banco->cuen_ban_num)}}</option>
      @endif
      
      @endforeach
    </select>
    
  </div>
</div>




<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
  <div class="form-group form-group-sm">
    <label for="mov_num_oper">N° Operaci&oacute;n</label>
    <input type="text" name="mov_num_oper" value="{{$movimiento->mov_num_oper}}" class="form-control" >

  </div>
</div>

</div>
<div class="row">
 <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label for="importe">Importe</label>
    <input type="text" name="importe" value="{{$movimiento->importe}}" class="form-control" >

  </div>
</div>  

<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label for="estado">Estado</label>
    <select name="estado" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
     @if($movimiento->estado =='1')
     <option selected="selected" value="1">Validar</option>
     <option value="0">Por Validar</option>
     @elseif($movimiento->estado=='0')
     <option value="1">Validar</option>
     <option selected="selected" value="0">Por Validar</option>
     @endif

   </select>

 </div>
</div>

</div>
<div class="row">
  <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
    <div class="form-group form-group-sm">
      <label for="mov_com">Comentario</label>
      <textarea class="form-control" name="mov_com">{{$movimiento->mov_com}}</textarea>
    </div>
  </div>
</div>



<div class="row">
 <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
   <div class="form-group form-group-sm">
     <button class="btn btn-primary" type="submit">Guardar</button>
     <a href="/movimientosbancarios"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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