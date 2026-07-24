<div class="form-group form-group-sm">
    <table class="table table-striped table-hover table-bordered table-condensed"  id="tbl_detalle">
        <thead style="background:orange;">
            <tr style="text-align:center;font-weight:bold;">
                <td colspan="5">
                    <label id="lbl_pis_mes">@if(!empty($dat_pis)) {{$dat_pis->pis_nom}} / @endif @if(!empty($dat_mes)) {{$dat_mes->mes_nom}} @endif</label>
                    <select name="mozo" id="mozo" class="form-control input-block"> 
                        <option></option>
                        @foreach($mozos as $mz)
                            @if($cabecera->mozo == $mz->IdUsuario)
                                <option selected="selected" value="{{$mz->IdUsuario}}">{{$mz->name}} {{$mz->apeusu}}</option>
                            @else
                                <option value="{{$mz->IdUsuario}}">{{$mz->name}} {{$mz->apeusu}}</option>
                            @endif
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr style="text-align:center;font-weight:bold;">
                <td hidden="hidden"></td>
                <td>PRODUCTO</td>
                <td>CANTIDAD</td>
                <td>PRECIO</td>
                <td>OBSERVACIONES</td>
                <td>ELIMINAR</td>
            </tr>
        </thead>
        <tbody id="items_pedidos">
            @foreach($detalle as $det)
            <tr>
                <td hidden="hidden" for="id"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="{{$det->IdProducto}}"></td>
                <td><input type="hidden" class="form-control" name="descripcion[]" value="{{$det->descripcion}}">{{$det->descripcion}}</td>
                <!-- ✅ CAMBIO AQUÍ: usar cantidad_pendiente -->
                <td><input type="number" style="text-align:center;" step="any" class="form-control"  onkeyup="calcular_total();" onChange="calcular_total();" name="txt_cantidad[]" value="{{$det->cantidad_pendiente}}" min="1"></td>
                <td  hidden="hidden" style="text-align:right;">{{$det->ped_det_pre}}</td>
                <td style="text-align:right;" ><input type="number"  class="form-control" step="any" readonly="readonly" name="precios[]" id="precios[]" onkeyup="calcular_total();" onChange="calcular_total();"  value="{{$det->ped_det_pre}}"></td>
                <td style="text-align:right;"><input  class="form-control" type="text"   name="item_obs[]" value="{{$det->item_obs}}"></td>
                <td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="{{$det->icbper_ind}}"></td>
                <td  style="text-align:center;"><button type="button" onClick="eliminar_item_registrado(this,{{$det->IdProducto}});" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>