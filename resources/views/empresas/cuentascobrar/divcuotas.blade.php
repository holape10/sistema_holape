
                        <table id="detcuotas"  class="table table-border table-striped">
                            <thead>
                            	<th width="400px;">Fecha Vencimiento</th>
                                <th width="400px;">Monto de Cuota</th>
								
                                
                            </thead>
                            <tbody id="">
                     
                              	@foreach($cuotas as $cuo)
                              		<tr>
                              			<td>
                              				
                              				{{Carbon::parse($cuo->ven_cuo_fec_ven)->format('d-m-Y')}}
                              			</td>
                              			<td>
                              				{{$cuo->ven_cuo_mon}}
                              			</td>
                              			
                              		</tr>
                              	@endforeach
                      
                            </tbody>
                        </table>
