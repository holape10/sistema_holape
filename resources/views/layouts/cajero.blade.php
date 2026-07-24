

<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar control-sidebar control-sidebar-dark">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
      <div  class="pull-left image">
       <img src="" class="img-circle" >
     </div>
     <div class="pull-left info">
      <p>{{Auth::user()->email}}</p>
      <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
    </div>
  </div>
  

  <!-- sidebar menu: : style can be found in sidebar.less -->
  <ul class="sidebar-menu" data-widget="tree">
    <li class="header">MENU PRINCIPAL</li>
    <li><a href="/mall"><i class="fas fa-concierge-bell"></i> <span>MALL</span></a></li>
        <li><a href="/seleccion"><i class="fas fa-utensils"></i> <span>Comandas</span></a></li>
    <!--<li><a href="/consolamozo"><i class="fas fa-utensils"></i> <span>Comandas</span></a></li>-->
    <li><a href="/consolacaja"><i class="fas fa-utensils"></i> <span>Caja</span></a></li>
 <li><a href="/ventacaja"><i class="fa fa-cash-register"></i> <span>Punto Venta</span></a></li>
 
           
           <!-- <li><a href="/contingencia"><i class="fa fa-cash-register"></i> <span>Contingencia</span></a></li>-->
            <!-- <li><a href="/posmv"> <i class="fa fa-mobile"></i> <span>Movil</span></a></li>-->
            <li class="treeview">
              <a href="#">
                <i class="fas fa-chart-line"></i>
                <span>Ventas</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                <li><a href="/SisFact"><i class="far fa-dot-circle"></i> Panel Ventas</a></li>
                 <li><a href="/ingresos"><i class="far fa-dot-circle"></i> Ingresos</a></li>
                   <li><a href="/guiasremision"><i class="far fa-dot-circle"></i> Guías Remisión</a></li>
             </ul>
           </li>


           <li class="treeview">
            <a href="#">
              <i class="fas fa-cart-arrow-down"></i>
              <span>Compras</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
             
              <li><a href="/gastos"><i class="far fa-dot-circle"></i> Gastos</a></li>
          
            </ul>
          </li>

          <li class="treeview">
            <a href="#">
              <i class="fas fa-money-bill-alt"></i>
              <span>Control Caja</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">    
              <li><a href="/caja"><i class="far fa-dot-circle"></i> Listar Cajas</a></li>
            </ul>
          </li>

          <li class="treeview">
            <a href="#">
              <i class="fas fa-cloud-upload-alt"></i>
              <span>SUNAT</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class=""><a href="/facturacionelectronica"><i class="far fa-dot-circle"></i>  Envío de Comprobantes</a></li>
              <li class=""><a href="/listarresumenes"><i class="far fa-dot-circle"></i>  Resumen Diario</a></li>
            </ul>
          </li>
          <li class="treeview">
            <a href="#">
              <i class="fas fas fa-users-cog"></i>
              <span>Contactos</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class=""><a href="{{route('clientes.index') }}"><i class="far fa-dot-circle" aria-hidden="true"></i> Clientes</a></li>
            </ul>
          </li>

          <li><a href="{{route('productos.index') }}"><i class="fas fa-utensils"></i> <span>Productos</span></a></li>


        
        </ul>
      </section>
    </aside>

    