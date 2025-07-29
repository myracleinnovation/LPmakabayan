<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'index.php') ? '' : 'collapsed'; ?>" href="index.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'projects.php') ? '' : 'collapsed'; ?>" href="projects.php">
                <i class="bi bi-building"></i>
                <span>Projects</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'specialties.php') ? '' : 'collapsed'; ?>" href="specialties.php">
                <i class="bi bi-tools"></i>
                <span>Specialties</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'industries.php') ? '' : 'collapsed'; ?>" href="industries.php">
                <i class="bi bi-briefcase"></i>
                <span>Industries</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'companyInfo.php') ? '' : 'collapsed'; ?>" href="companyInfo.php">
                <i class="bi bi-info-circle"></i>
                <span>Company Info</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'settings.php') ? '' : 'collapsed'; ?>" href="settings.php">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
</aside>