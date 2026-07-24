

  <div class="row">
  
      <table id="table-presentaciones" style="width:100%;" class="table table-responsive table-striped">
         <thead >
            <th><center>AGREGAR</center></th>
            <th><center>DOCUMENTO</center></th>
             <th><center>N° DOCUMENTO</center></th>
             <th><center>CLIENTE</center></th>
         </thead>
               <tbody>
   
    	@foreach($clientes as $cli)
          <tr>
             <td style="width:15%;"><BUTTON  onclick="agregarcliente('{{$cli->clicod}}','{{$cli->clinum}}','{{$cli->clinom}}','{{$cli->clidir}}','{{$cli->tdicod}}','{{$cli->clicor}}')" class="btn btnpresentaciones btn-block btn-success">AGREGAR</BUTTON></td>
             <td>{{$cli->tdides}}</td>
             <td>{{$cli->clinum}}</td>
             <td>{{$cli->clinom}}</td>
             
            </tr>
      @endforeach




     </tbody>
      </table>
  </div>
