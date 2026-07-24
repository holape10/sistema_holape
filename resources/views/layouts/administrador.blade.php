<aside class="main-sidebar control-sidebar control-sidebar-dark">
    <section class="sidebar">

        @php
            $empresaActual = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first();
            // Por defecto asignamos 1 (GENERAL) si no encuentra el dato
            $tipoSistema = $empresaActual ? $empresaActual->id_tipo_sistema : 1; 
            
            // LEYENDA DE IDs (Ejemplo de tu tabla tipos_sistemas):
            // 1 = GENERAL / COMERCIAL
            // 2 = RESTAURANTE
            // 3 = FARMACIA / CLINICA / BOTICA
            // 4 = VETERINARIA
            // 5 = CONSULTORIO
            // 6 = GRIFO
            // 7 = ESTACIONAMIENTOS
        @endphp

        <div class="user-panel">
            <div class="pull-left image">
                <img src="" class="img-circle">
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->email }}</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MENU PRINCIPAL</li>

            <li hidden="hidden" class="treeview">
                <a href="#">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="../../index.html"><i class="far fa-dot-circle"></i> Dashboard v1</a></li>
                    <li><a href="../../index2.html"><i class="far fa-dot-circle"></i> Dashboard v2</a></li>
                </ul>
            </li>

            <li><a href="/inicio"><i class="fas fa-th"></i> <span>Inicio</span></a></li>
            <li><a href="/dashboard"><i class="fas fa-dashboard"></i> <span>Dashboard</span></a></li>
            <li><a href="/pos"><i class="fa fa-cash-register"></i> <span>Punto Venta</span></a></li>
            @if($tipoSistema == 3)
            <li><a href="/consultorio"><i class="fas fa-stethoscope"></i> <span>historia Clinica</span></a></li>
            @endif
            @if($tipoSistema == 2)
            <li><a href="/seleccion"><i class="fas fa-concierge-bell"></i> <span>Comandas</span></a></li>
            <li><a href="/consolacaja"><i class="fas fa-money-bill-wave"></i> <span>Caja</span></a></li>
            @endif

            @if($tipoSistema == 1)
            <li hidden="hidden"><a href="/ventas/masiva"><i class="fa fa-cash-register"></i> <span>Punto Venta Masiva</span></a></li>
            <li hidden='hidden'><a href="/calcular_stock"><i class="fas fa-arrow-circle-o-up"></i> <span>Actualizar Stock</span></a></li>
            @endif
            <li hidden='hidden'><a href="/consola"><i class="fas fa-utensils"></i> <span>Comandas</span></a></li>
            <li hidden='hidden'><a href="/mall"><i class="fas fa-concierge-bell"></i> <span>MALL</span></a></li>
            <li hidden='hidden'><a href="/ventacaja"><i class="fa fa-cash-register"></i> <span>Punto Venta</span></a></li>
            <li hidden='hidden'><a href="/ventacaja6"><i class="fa fa-cash-register"></i> <span>Punto Venta Tactil</span></a></li>

            <!-- MÓDULO ESTACIONAMIENTO -->
            @if($tipoSistema == 7)
            <li class="treeview">
                <a href="#">
                    <i class="fas fa-parking"></i> <span>ESTACIONAMIENTO</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/estacionamiento/ingreso"><i class="far fa-dot-circle"></i> Playa de Estacionamiento</a></li>
                    <li><a href="/estacionamiento/tarifas"><i class="far fa-dot-circle"></i> Configurar Tarifas</a></li>
                    <li><a href="/estacionamiento/reportes"><i class="far fa-dot-circle"></i> Reportes por Punto</a></li>
                </ul>
            </li> 
            @endif           

            <li class="treeview">
                <a href="#">
                    <i class="fas fa-chart-line"></i> <span>Ventas</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/SisFact"><i class="far fa-dot-circle"></i> Panel Ventas</a></li>
                    <li><a href="/ingresos"><i class="far fa-dot-circle"></i> Ingresos</a></li>
                    <li hidden="hidden"><a href="/indexcomandas"><i class="far fa-dot-circle"></i> Comandas</a></li>
                    <li hidden="hidden"><a href="/autoconsumos"><i class="far fa-dot-circle"></i> Cortesias</a></li>
                    <li><a href="/guiasremision"><i class="far fa-dot-circle"></i> Guías Remisión</a></li>
                    
                    <li class="treeview">
                        <a href="#"><i class="far fa-dot-circle"></i> Reportes
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                        <ul class="treeview-menu">
                            <li hidden='hidden'><a href="/arqueodiario"><i class="far fa-dot-circle"></i> Arqueo de Caja</a></li>
                            <li><a href="/reportes/1"><i class="far fa-dot-circle"></i> Ventas</a></li>
                            <li><a href="/reportes/2"><i class="far fa-dot-circle"></i> Ventas por Vendedor</a></li>
                            <li><a href="/reportes/3"><i class="far fa-dot-circle"></i> Ventas por Cliente</a></li>
                            <li><a href="/reportes/6"><i class="far fa-dot-circle"></i> Ventas por Producto</a></li>
                            <li hidden='hidden'><a href="/reportepedido"><i class="far fa-dot-circle"></i> Comandas Obs.</a></li>
                            <li><a href="/reportes/7"><i class="far fa-dot-circle"></i> Productos (+/-) Vendidos</a></li>
                            <li><a href="/reportes/8"><i class="far fa-dot-circle"></i> Rentabilidad</a></li>
                            <li hidden='hidden'><a href="/reportes/10"><i class="far fa-dot-circle"></i> Comisiones-Vendedor</a></li>
                            <li hidden='hidden'><a href="/reportes/9"><i class="far fa-dot-circle"></i> Comisiones-Producto</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="treeview" hidden="hidden">
                <a href="#">
                    <i class="fas fa-chart-line"></i> <span>Reportes Sunat</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/reportesunat"><i class="far fa-dot-circle"></i> TXT - Ventas - Compras</a></li>
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fas fas fa-coins"></i> <span>Cuentas Cobrar</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/cuentascobrar"><i class="far fa-dot-circle"></i> Cuentas Cobrar</a></li>
                    <li class="treeview">
                        <a href="#"><i class="far fa-dot-circle"></i> Reportes
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="/reportecuentas"><i class="far fa-dot-circle"></i> Cuentas por Cobrar</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="treeview">
          <a href="#">
            <i class="fas fas fa-coins"></i>
            <span>Cuentas Pagar</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
              
               <li><a href="/cuentaspagar"><i class="fas fa-hand-holding-usd"></i> Cuentas Pagar</a></li>
               <li hidden="hidden" class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
               
                      <li><a href="/reportecuentas"><i class="far fa-dot-circle"></i> Cuentas por Cobrar</a></li>
                
                </ul>
              </li>

              
          </ul>
        </li>

            <li class="treeview">
                <a href="#">
                    <i class="fas fa-cart-arrow-down"></i> <span>Compras</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/compras"><i class="far fa-dot-circle"></i> Compras</a></li>
                    <li><a href="/gastos"><i class="far fa-dot-circle"></i> Gastos</a></li>
                    <li class="treeview">
                        <a href="#"><i class="far fa-dot-circle"></i> Reportes
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="/reportes/11"><i></i> Compras</a></li>
                            <li><a href="/reportes/12"><i></i> Compras por Proveedor</a></li>
                            <li><a href="/reportes/13"><i></i> Compras por Productos</a></li>
                            <li><a href="/reportes/14"><i></i> Gastos</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fas fa-warehouse"></i> <span> Almacén</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/almacen/listaralmacenes"><i class="far fa-dot-circle"></i> Almacenes</a></li>
                    <li><a href="/inventarios"><i class="far fa-dot-circle"></i> Inventarios</a></li>
                    <li hidden="hidden"><a href="/transferencias"><i class="far fa-dot-circle"></i> Transferencias</a></li>
                    <li hidden="hidden"><a href="/salidasproductos"><i class="far fa-dot-circle"></i> Salidas Productos</a></li>
                    <li hidden="hidden"><a href="/ingresosproductos"><i class="far fa-dot-circle"></i> Ingresos Productos</a></li>
                    <li class="treeview">
                        <a href="#"><i class="far fa-dot-circle"></i> Reportes
                            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="/reporteajustes"><i></i> Ajustes</a></li>
                            <li><a href="/reporteinventario"><i></i> Inventario</a></li>
                            <li><a href="/kardex"><i></i> Kardex</a></li>
                            <li><a href="/stockproductos"><i></i> Stock Productos</a></li>
                            <li><a href="/reportes_stock_general"><i></i> Stock General</a></li>
                        </ul>
                    </li>
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fas fa-money-bill-alt"></i> <span>Control Caja</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/caja"><i class="far fa-dot-circle"></i> Listar Cajas</a></li>
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fas fa-sync"></i> <span>SUNAT</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/facturacionelectronica"><i class="far fa-dot-circle"></i> Envío de Comprobantes</a></li>
                    <li><a href="/listarresumenes"><i class="far fa-dot-circle"></i> Resumen Diario</a></li>
                    <li><a href="/consulta-cpe"><i class="far fa-dot-circle"></i> Consulta CPE Individual</a></li>
                    <li><a href="/consulta-multiple-v2"><i class="far fa-dot-circle"></i> Consulta CPE Masivo</a></li>
                </ul>
            </li>

            <li class="treeview" hidden="hidden">
                <a href="#">
                    <i class="fas fa-balance-scale"></i> <span>CONTABILIDAD</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li>
                        <a href="{{ route('plan-contable.index') }}">
                            <i class="fas fa-list-ul text-info"></i> Plan Contable
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('ventas.index') }}">
                            <i class="fas fa-shopping-cart text-success"></i> Centralizar Ventas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('compras.index') }}">
                            <i class="fas fa-truck-loading text-danger"></i> Centralizar Compras
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('asientos.index') }}">
                            <i class="fas fa-book text-primary"></i> Libro Diario
                        </a>
                    </li>
                </ul>
            </li>

            
            <li class="treeview" hidden="hidden">
                <a href="#">
                    <i class="fas fa-warehouse"></i> <span> SIRE</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/sire/sunat"><i class="fa fa-cash-register"></i> <span>CONSULTAR SIRE</span></a></li>
                    <li><a href="/sire/buscar"><i class="fa fa-cash-register"></i> <span>GENERAR TXT</span></a></li>
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fas fas fa-users-cog"></i> <span>Contactos</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('clientes.index') }}"><i class="far fa-dot-circle"></i> Clientes</a></li>
                    <li><a href="{{ route('empleado.index') }}"><i class="far fa-dot-circle"></i> Usuarios</a></li>
                    <li><a href="{{ route('proveedor.index') }}"><i class="far fa-dot-circle"></i> Proveedores</a></li>
                </ul>
            </li>

            <li class="treeview">
                <a href="#">
                    <i class="fas fa-clock"></i> <span>Asistencia</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('asistencia.index') }}"><i class="far fa-dot-circle"></i> Marcar Asistencia</a></li>
                    <li><a href="{{ route('asistencia.motivos') }}"><i class="far fa-dot-circle"></i> Motivos</a></li>
                    
                    <!-- Reportes -->
                    <li style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;" hidden="hidden">
                        <a href="{{ route('asistencia.reporte') }}"><i class="far fa-circle text-info"></i> Reporte Diario</a>
                    </li>
                    <li style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;">
                        <a href="{{ route('asistencia.tareo') }}"><i class="far fa-circle text-info"></i> Reporte Tareo</a>
                    </li>
                    <li>
                        <a href="{{ route('asistencia.reporte_detallado') }}"><i class="far fa-check-circle text-success"></i> <strong>Reporte de Jornadas (8h)</strong></a>
                    </li>

                    <!-- Configuración -->
                    <li style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;">
                        <a href="{{ route('asistencia.horarios') }}"><i class="far fa-dot-circle"></i> Matriz de Turnos</a>
                    </li>
                    <li><a href="{{ route('asistencia.turnos') }}"><i class="far fa-dot-circle"></i> Gestionar Turnos</a></li>
                    <li><a href="{{ route('asistencia.configurar_ip') }}"><i class="far fa-dot-circle"></i> Configurar IP Local</a></li>
                </ul>
            </li>

            

            <li class="treeview">
                <a href="#">
                    <i class="fas fa-cogs"></i> <span>Mantenimiento</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="/administrador/empresas"><i class="far fa-dot-circle"></i> Empresas</a></li>
                    <li><a href="/negocios"><i class="far fa-dot-circle"></i> Sucursales</a></li>
                    <li><a href="/tipocambio"><i class="far fa-dot-circle"></i> Tipo Cambio</a></li>
                    <li><a href="{{ route('mediospagos.index') }}"><i class="far fa-dot-circle"></i> Medios de Pago</a></li>
                    <li><a href="movimientos_preparados"><i class="far fa-dot-circle"></i> Gestion Preparados</a></li>
                    <li><a href="mermas"><i class="far fa-dot-circle"></i> Mermas</a></li>
                    <li><a href="{{ route('tipoproducto.index') }}"><i class="far fa-dot-circle"></i> Linea</a></li>
                    <li><a href="{{ route('categorias.index') }}"><i class="far fa-dot-circle"></i> Sub Lineas</a></li>
                    <li><a href="{{ route('productos.index') }}"><i class="far fa-dot-circle"></i> Productos</a></li>
                    <li><a href="/insumos"><i class="far fa-dot-circle"></i> Insumos</a></li>
                    <li><a href="/combos"><i class="far fa-dot-circle"></i> Combos</a></li>
                    <li hidden='hidden'><a href="/confconcar"><i class="far fa-dot-circle"></i> Parámetros Concar</a></li>
                    <li><a href="{{ route('backup.vista') }}"><i class="fa fa-database"></i> <span>Generar Backup</span></a></li>
                </ul>
            </li>

            <li class="treeview" hidden="hidden">
                <a href="#">
                    <i class="fas fa-gift"></i> <span>Fidelización</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('puntos.index') }}"><i class="far fa-dot-circle"></i> Configurar Reglas</a></li>
                    <li hidden><a href="#"><i class="far fa-dot-circle"></i> Historial de Canjes</a></li>
                </ul>
            </li>

            @if($tipoSistema == 2)
            <li class="treeview">
                <a href="#">
                    <i class="fas fa-window-restore"></i> <span>RESTAURANTE</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('pisos.index') }}"><i class="far fa-dot-circle"></i> Pisos</a></li>
                    <li><a href="{{ route('mesa.index') }}"><i class="far fa-dot-circle"></i> Mesas</a></li>
                </ul>
            </li>
            @endif

            <li class="treeview" hidden='hidden'>
                <a href="#">
                    <i class="fas fa-toolbox"></i> <span>Farmacias</span>
                    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>
                <ul class="treeview-menu">
                    <li><a href="{{ route('laboratorio.index') }}"><i class="far fa-dot-circle"></i> Laboratorio</a></li>
                    <li><a href="{{ route('tiposmedicamentos.index') }}"><i class="far fa-dot-circle"></i> Tipos Medicamentos</a></li>
                    <li><a href="{{ route('principioactivo.index') }}"><i class="far fa-dot-circle"></i> Principios Activos</a></li>
                </ul>
            </li>

            <li class="nav-item" hidden="hidden">
                <a class="nav-link" href="{{ route('concar.index') }}">
                    <i class="fa fa-database"></i> <span>CONCAR</span>
                </a>
            </li>
            <li>
                <a href="{{ route('soporte.index') }}">
                    <i class="fas fa-headset"></i> <span>Soporte</span>
                </a>
            </li>
            <li><a href="/monitoreo_impresiones"><i class="fas fa-th"></i> <span>Monitor de Impresiones NUBE</span></a></li>
        </ul>
    </section>
</aside>