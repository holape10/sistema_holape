

  <div class="row">
  
      <table id="table-presentaciones" style="width:100%;" class="table table-responsive table-striped">
         <thead >
            <th><center>AGREGAR</center></th>
             <th><center>DIRECCIONES</center></th>
         </thead>
               <tbody>
   
    	 @if(!empty($direcciones->clidir))
          <tr>
             <td style="width:15%;"><BUTTON  onclick="agregardireccion('{{$direcciones->clidir}}')" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$direcciones->clidir}}</td>
             
            </tr>
           @endif

            @if(!empty($direcciones->direccion1))
            <tr>
             <td style="width:15%;"><BUTTON  onclick="agregardireccion('{{$direcciones->direccion1}}')" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$direcciones->direccion1}}</td>
            </tr>
            @endif
             @if(!empty($direcciones->direccion1))
               <tr>
             <td style="width:15%;"><BUTTON  onclick="agregardireccion('{{$direcciones->direccion2}}')" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$direcciones->direccion2}}</td>
             
            </tr>
            @endif
             @if(!empty($direcciones->direccion1))
               <tr>
             <td style="width:15%;"><BUTTON  onclick="agregardireccion('{{$direcciones->direccion3}}')" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$direcciones->direccion3}}</td>
             
            </tr>
            @endif
             @if(!empty($direcciones->direccion1))
               <tr>
             <td style="width:15%;"><BUTTON  onclick="agregardireccion('{{$direcciones->direccion4}}')" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$direcciones->direccion4}}</td>
             
            </tr>
            @endif
             @if(!empty($direcciones->direccion1))

               <tr>
             <td style="width:15%;"><BUTTON  onclick="agregardireccion('{{$direcciones->direccion5}}')" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$direcciones->direccion5}}</td>
             
            </tr>
            @endif

     </tbody>
      </table>
  </div>
