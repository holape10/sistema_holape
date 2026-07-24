
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
        
       


     
            <li><a href="/pedidos"><i class="fa fa-cash-register"></i> <span>Pedidos</span></a></li>
          
    
      


    
     
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

 