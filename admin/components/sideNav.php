<?php 
  $pageName = basename($_SERVER['PHP_SELF']);

  $index = "collapsed";
  $projects = "collapsed";
  $specialties = "collapsed";
  $industries = "collapsed";
  $features = "collapsed";
  $process = "collapsed";
  $projectCategories = "collapsed";
  $companyInfo = "collapsed";
  $settings = "collapsed";

  if($pageName == "index.php"){
      $index = "";
  } elseif($pageName == "projects.php"){
      $projects = "";
  } elseif($pageName == "specialties.php"){
      $specialties = "";
  } elseif($pageName == "industries.php"){
      $industries = "";
  } elseif($pageName == "features.php"){
      $features = "";
  } elseif($pageName == "process.php"){
      $process = "";
  } elseif($pageName == "projectCategories.php"){
      $projectCategories = "";
  } elseif($pageName == "companyInfo.php"){
      $companyInfo = "";
  } elseif($pageName == "settings.php"){
      $settings = "";
  }
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link <?php echo $index; ?>" href="index.php">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $projects; ?>" href="projects.php">
        <i class="bi bi-briefcase"></i>
        <span>Projects</span>
      </a>
    </li><!-- End Projects Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $projectCategories; ?>" href="projectCategories.php">
        <i class="bi bi-folder"></i>
        <span>Project Categories</span>
      </a>
    </li><!-- End Project Categories Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $specialties; ?>" href="specialties.php">
        <i class="bi bi-tools"></i>
        <span>Specialties</span>
      </a>
    </li><!-- End Specialties Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $industries; ?>" href="industries.php">
        <i class="bi bi-building"></i>
        <span>Industries</span>
      </a>
    </li><!-- End Industries Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $features; ?>" href="features.php">
        <i class="bi bi-star"></i>
        <span>Features</span>
      </a>
    </li><!-- End Features Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $process; ?>" href="process.php">
        <i class="bi bi-list-check"></i>
        <span>Process</span>
      </a>
    </li><!-- End Process Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $companyInfo; ?>" href="companyInfo.php">
        <i class="bi bi-info-circle"></i>
        <span>Company Info</span>
      </a>
    </li><!-- End Company Info Nav -->

    <li class="nav-item">
      <a class="nav-link <?php echo $settings; ?>" href="settings.php">
        <i class="bi bi-gear"></i>
        <span>Settings</span>
      </a>
    </li><!-- End Settings Nav -->

  </ul>

</aside><!-- End Sidebar-->