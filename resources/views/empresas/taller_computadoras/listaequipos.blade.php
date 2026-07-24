

  <div class="row">
  
      <table id="table-presentaciones" style="width:100%;" class="table table-responsive table-striped">
         <thead >
         	<tr>
            <th><center>AGREGAR</center></th>
            <th><center>EQUIPO</center></th>
              <th><center>MARCA</center></th>
                 <th><center>MODELO</center></th>
                   <th><center>DIAS GARANTIA</center></th>
            </tr>
         </thead>
               <tbody>
   
    	@foreach($equipos as $equipo)
          <tr>
             <td style="width:20%;"><BUTTON  onclick="agregarequipo('{{$equipo->IdProducto}}','{{$equipo->pronom}}','{{$equipo->mar_nom}}','{{$equipo->mod_nom}}')" class="btn btnequipo btn-block btn-success">AGREGAR</BUTTON></td>
             <td style="width:40%;">{{$equipo->pronom}}</td>
              <td style="width:20%;">{{$equipo->mar_nom}}</td>
               <td style="width:20%;">{{$equipo->mod_nom}}</td>
                 <td style="width:20%;">{{$equipo->dias_garantia}}</td>
            </tr>
         @endforeach
          

     </tbody>
      </table>
  </div>
