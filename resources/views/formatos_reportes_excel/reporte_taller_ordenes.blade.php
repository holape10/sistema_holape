	<table  style="font-size:8pt;">
						<thead >
							<tr >
							
								<th style="vertical-align:middle;text-align:center;">Fecha Ingreso</th>
								<th style="vertical-align:middle;text-align:center;">Estado</th>
								<th style="vertical-align:middle;text-align:center;">N° Orden</th>

								
								<th style="vertical-align:middle;text-align:center;">Nombre o Razón Social</th>
								<th style="vertical-align:middle;text-align:center;">Tel&eacute;fono</th>
								<th style="vertical-align:middle;text-align:center;">Modelo</th>
								<th style="vertical-align:middle;text-align:center;">Serie</th>
								<th style="vertical-align:middle;text-align:center;">S&iacute;ntoma</th>
					
								<th style="vertical-align:middle;text-align:center;">Supervisor</th>
								<th style="vertical-align:middle;text-align:center;">Coordinador</th>
								<th style="vertical-align:middle;text-align:center;">T&eacute;cnico</th>
							
							
							</tr>
						</thead>

						<tbody>
							@foreach($ordenes as $comp)
							<tr>
							
								<td>{{Carbon::parse($comp->fechacot)->format('d-m-Y')}}</td>
								<td>{{$comp->est_ord_nom}}</td>
								<td>{{$comp->serdoc}}-{{$comp->numdoc}}</td>
							
								<td style="vertical-align:middle;">{{$comp->ccanom}}</td>
								<td style="vertical-align:middle;">{{$comp->telefono}}</td>
								<td style="vertical-align:middle;">{{$comp->mod_nom}}</td>
								<td style="vertical-align:middle;">{{$comp->equi_ser}}</td>
								<td style="vertical-align:middle;">{{$comp->observaciones}}</td>

							
								<td style="vertical-align:middle;">{{$comp->nom_sup}} {{$comp->ape_sup}}</td>
								<td style="vertical-align:middle;">{{$comp->nom_coor}} {{$comp->ape_coor}}</td>
								<td style="vertical-align:middle;">{{$comp->nom_tec}} {{$comp->nom_tec}}</td>
							
							
							</tr>
							@endforeach
						</tbody>
					</table><br>