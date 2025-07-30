<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="index.php" class="logo d-flex align-items-center ">
            <span class="d-lg-block fs-6">Makabayan Avellanosa Construction</span>
        </a>
    </div>
    <nav class="header-nav ms-auto d-flex justify-content-center align-items-center">
        <ul class="d-flex align-items-center">
            <li class="nav-item dropdown pe-3">
                <div class="d-flex align-items-center">
                    <a class="nav-link nav-profile d-flex align-items-center  tx-base-color pe-0 ps-2" href="#"
                        data-bs-toggle="dropdown"><i class="bi bi-person-circle fs-1"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end profile me-3">
                        <li class="dropdown-header text-start">
                            <h6><b><?php echo isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin'; ?></b>
                            </h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="logout.php"
                                onclick="clearSession()">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </nav>
</header>

<script>
    function clearSession() {
        fetch('logout.php')
            .then(response => {
                if (response.ok) {
                    window.location.href = '../login.php';
                } else {
                    console.error('Logout failed');
                    window.location.href = '../login.php';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = '../login.php';
            });
    }
</script>