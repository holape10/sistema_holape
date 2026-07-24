

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
        <li hidden="hidden" class="treeview">
          <a href="#">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="../../index.html"><i class="far fa-dot-circle"></i>  Dashboard v1</a></li>
            <li><a href="../../index2.html"><i class="far fa-dot-circle"></i>  Dashboard v2</a></li>
          </ul>
        </li>
        
     
    


     <li><a href="/kiosko"><i class="fas fa-utensils"></i> <span>Comandas</span></a></li>
          

        <li class="treeview">
          <a href="#">
            <i class="fas fa-chart-line"></i>
            <span>Ventas</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            
            
            
              <li class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">
                   
                      <li class="divider"></li>
                      <li><a href="/reportes/1"><i class="far fa-dot-circle"></i> Ventas</a></li>
                     
                </ul>
              </li>
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
              
              <li class="treeview">
                <a href="#"><i class="far fa-dot-circle"></i>  Reportes
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>
                <ul class="treeview-menu">

                  <li><a href="/reportes/11"><i></i>Compras</a></li>
                  
                
                </ul>
              </li>
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
          
    
     
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  