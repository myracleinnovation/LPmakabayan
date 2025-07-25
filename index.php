<?php include 'components/header.php'; ?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

    <!-- HERO SECTION -->
    <div class="position-relative min-vh-100 d-flex align-items-center justify-content-center"
        style="background-image: url('assets/img/bg.png'); background-size: cover; background-position: center;">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
        <div class="container position-relative z-1 text-center py-5 px-3 px-md-5">
            <img src="assets/img/logo2.png" alt="Logo" class="mb-3 mt-5 img-fluid"
                style="max-width: 180px; min-width: 90px;">
            <h1 class="fw-bold text-white text-uppercase fs-1">Building a Better Future</h1>
            <p class="lead text-white w-100 w-md-75 pb-2 pb-md-5 mx-auto mt-3 fs-3">
                Superior and quality construction services grounded in modern principles, sustainable solutions, and
                client satisfaction.
            </p>

            <div class="d-flex flex-column flex-lg-row justify-content-center align-items-center gap-2 mt-5 pt-5">
                <a href="#company" class="btn btn-warning-hover active px-4 py-2 border border-0 border-white">Our
                    Company</a>
                <a href="specialties.php" class="btn btn-warning-hover px-4 py-2 border border-0 border-white">Our
                    Specialties</a>
                <a href="project.php" class="btn btn-warning-hover px-4 py-2 border border-0 border-white">Our
                    Projects</a>
                <a href="connect.php" class="btn btn-warning-hover px-4 py-2 border border-0 border-white">Connect
                    Now</a>
            </div>
        </div>
    </div>

    <!-- OUR COMPANY SECTION -->
    <section id="company" class="bg-warning py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center mb-5 flex-column flex-md-row">
                <div class="col-12 col-md-6 mb-4 mb-md-0">
                    <h2 class="fw-bold mb-3 text-uppercase fs-5">Our Company</h2>
                    <p class="fs-5"><b>Makabayan Avellanosa Construction</b> is committed to delivering top-tier
                        architectural, civil, mechanical, electrical, and plumbing works backed by a highly dedicated
                        and skilled team.</p>
                    <p class="fs-5">To deliver the highest quality of service through the dedication and
                        expertise of our skilled workforce. We uphold strong ethical standards, foster a passion for
                        excellence, remain committed to our craft, and continuously strive for growth and innovation in
                        the industry.</p>
                </div>
                <div class="col-12 col-md-6 mb-4 mb-md-0 pb-4">
                    <img src="assets/img/about.png" class="img-fluid object-fit-cover w-100" alt="About Our Company">
                </div>
            </div>
        </div>
    </section>

    <!-- MORE THAN JUST CONSTRUCTION SECTION -->
    <section class="bg-black text-white py-5 min-vh-100">
        <div class="container">
            <div class="text-center mb-4 mt-5 pt-5 mb-5 pb-5">
                <h2 class="fw-bold fs-1">More Than Just Construction</h2>
            </div>
            <div class="row justify-content-center text-center">
                <div class="col-12 col-sm-6 col-md-3 mb-4">
                    <div class="mx-auto mb-2 rounded d-flex align-items-center justify-content-center w-100">
                        <img src="assets/img/pentagon1.png" class="w-100 h-100 object-fit-cover"
                            alt="Modern Construction">
                    </div>
                    <div class="fs-4">Modern Construction Techniques</div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-4">
                    <div class="mx-auto mb-2 rounded d-flex align-items-center justify-content-center w-100">
                        <img src="assets/img/pentagon2.png" class="w-100 h-100 object-fit-cover"
                            style="border-radius: 10px;" alt="Sustainable Practices">
                    </div>
                    <div class="fs-4">Sustainable Practices</div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-4">
                    <div class="mx-auto mb-2 rounded d-flex align-items-center justify-content-center w-100">
                        <img src="assets/img/pentagon3.png" class="w-100 h-100 object-fit-cover"
                            style="border-radius: 10px;" alt="Comprehensive Services">
                    </div>
                    <div class="fs-4">Comprehensive Services</div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-4">
                    <div class="mx-auto mb-2 rounded d-flex align-items-center justify-content-center w-100">
                        <img src="assets/img/pentagon4.png" class="w-100 h-100 object-fit-cover"
                            style="border-radius: 10px;" alt="Ethical Standards">
                    </div>
                    <div class="fs-4">Ethical Standards and Client Commitment</div>
                </div>
            </div>
        </div>
    </section>

    <!-- SPECIALTIES & BUILDS SECTION -->
    <section id="specialties">
        <div class="row g-0">
            <div class="col-12 col-md-6 p-0 mb-4 mb-md-0">
                <a href="specialties.php">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height:300px;">
                        <img src="assets/img/banner1.png" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Discover<br>Our Specialties</h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 p-0">
                <a href="project.php">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height:300px;">
                        <img src="assets/img/banner2.png" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Check Out<br>Our Builds</h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>
</body>