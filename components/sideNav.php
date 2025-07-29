<?php 
  $pageName = basename($_SERVER['PHP_SELF']);

  $index = "collapsed";


  if($pageName == "index.php"){
      $index = "";
  }
?>


<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link <?php echo $index; ?>" href="index">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

    </ul>

</aside><!-- End Sidebar-->