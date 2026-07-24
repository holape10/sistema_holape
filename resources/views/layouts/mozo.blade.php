

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
  
    <li hidden='hidden'><a href="/consola"><i class="fas fa-utensils"></i> <span>Comandas</span></a></li>
    <li><a href="/seleccion"><i class="fas fa-utensils"></i> <span>Comandas</span></a></li>

        </ul>
      </section>
    </aside>

    