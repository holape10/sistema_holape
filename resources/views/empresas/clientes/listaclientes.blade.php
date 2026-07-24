

  <div class="row">
  
      <table id="table-presentaciones" style="width:100%;" class="table table-responsive table-striped">
         <thead >
            <th><center>AGREGAR</center></th>
            <th><center>DOCUMENTO</center></th>
             <th><center>N° DOCUMENTO</center></th>
             <th><center>CLIENTE</center></th>
               <th><center>TELEFONOE</center></th>
         </thead>
               <tbody>
   
    	@foreach($clientes as $cli)
          <tr>
             <td style="width:15%;"><button type="button" class="btn btn-success btn-sm" 
    onclick="agregarcliente('{{$cli->clicod}}', '{{$cli->clinum}}', '{{$cli->clinom}}', '{{$cli->clidir}}', '{{$cli->tdicod}}', '{{$cli->clicor}}', '{{$cli->telefono}}', '{{$cli->fecha_nacimiento}}', '{{$cli->cuenta12}}')">
    Seleccionar
</button></td>
             <td>{{$cli->tdides}}</td>
             <td>{{$cli->clinum}}</td>
             <td>{{$cli->clinom}}</td>
               <td>{{$cli->telefono}}</td>
             
            </tr>
      @endforeach




     </tbody>
      </table>
  </div>
