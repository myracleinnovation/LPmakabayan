<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
$current_section = isset($_GET['section']) ? $_GET['section'] : '';
?>

<!-- Sticky Navbar (hidden by default, shows on scroll) -->
<nav id="scroll-navbar"
    class="navbar navbar-expand-lg fixed-top custom-navbar <?php echo ($current_page != 'index.php') ? 'position-sticky show-navbar' : ''; ?>">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/img/logo_landscape.png" alt="Logo" class="me-2" style="width: 150px;">
        </a>

        <!-- Hamburger button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="index.php#company"
                        class="nav-link <?php echo ($current_page == 'index.php' && ($current_section == 'company' || $current_section == '')) ? 'active bg-warning' : ''; ?>">Our
                        Company</a>
                </li>
                <li class="nav-item">
                    <a href="specialties.php"
                        class="nav-link <?php echo ($current_page == 'specialties.php') ? 'active bg-warning' : ''; ?>">Our
                        Specialties</a>
                </li>
                <li class="nav-item">
                    <a href="industries.php"
                        class="nav-link <?php echo ($current_page == 'industries.php') ? 'active bg-warning' : ''; ?>">Our
                        Industries</a>
                </li>
                <li class="nav-item">
                    <a href="project.php"
                        class="nav-link <?php echo ($current_page == 'project.php') ? 'active bg-warning' : ''; ?>">Our
                        Projects</a>
                </li>
                <li class="nav-item">
                    <a href="connect.php"
                        class="nav-link <?php echo ($current_page == 'connect.php') ? 'active bg-warning' : ''; ?>">Connect
                        Now</a>
                </li>
            </ul>
        </div>
    </div>
</nav>