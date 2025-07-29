<?php 
include 'components/header.php'; 

// Function to get industries from API
function getIndustries() {
    $apiUrl = 'app/apiIndustries.php';
    
    try {
        $response = file_get_contents($apiUrl);
        if ($response === false) {
            throw new Exception('Failed to fetch data from API');
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }
        
        if (isset($data['success']) && $data['success'] && isset($data['data'])) {
            return $data['data'];
        } else {
            throw new Exception('API returned error: ' . ($data['message'] ?? 'Unknown error'));
        }
    } catch (Exception $e) {
        // Return default industries on error
        return [
            [
                'IndustryName' => 'Residential',
                'IndustryImage' => 'assets/img/industries1.png'
            ],
            [
                'IndustryName' => 'Commercial',
                'IndustryImage' => 'assets/img/industries2.png'
            ],
            [
                'IndustryName' => 'Industrial',
                'IndustryImage' => 'assets/img/industries3.png'
            ],
            [
                'IndustryName' => 'Healthcare',
                'IndustryImage' => 'assets/img/industries4.png'
            ],
            [
                'IndustryName' => 'Education',
                'IndustryImage' => 'assets/img/industries5.png'
            ],
            [
                'IndustryName' => 'Infrastructure',
                'IndustryImage' => 'assets/img/industries6.png'
            ]
        ];
    }
}

// Function to get company information from API
function getCompanyInfo() {
    $apiUrl = 'app/apiCompanyInfo.php';
    
    try {
        $response = file_get_contents($apiUrl);
        if ($response === false) {
            throw new Exception('Failed to fetch data from API');
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }
        
        if (isset($data['success']) && $data['success'] && isset($data['data'])) {
            return $data['data'];
        } else {
            throw new Exception('API returned error: ' . ($data['message'] ?? 'Unknown error'));
        }
    } catch (Exception $e) {
        // Return default values on error
        return [
            'CompanyName' => 'Makabayan Construction',
            'Tagline' => 'Building Excellence, Delivering Quality',
            'Description' => 'Your trusted partner in construction and development projects.',
            'Mission' => 'To provide exceptional construction services with integrity and innovation.',
            'AboutImage' => 'assets/img/about.png',
            'LogoImage' => 'assets/img/logo.png'
        ];
    }
}

// Get data from API
$industries = getIndustries();
$companyInfo = getCompanyInfo();
?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

    <!-- OUR WORK SPEAKS FOR ITSELF SECTION -->
    <section class="bg-warning text-white py-4 py-md-5" style="min-height: auto;">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h2 class="fw-bolder fs-1 fs-md-1 text-uppercase text-black">Industries We Serve</h2>
            </div>
            <div class="row justify-content-center">
                <?php foreach ($industries as $industry): ?>
                <div class="col-12 col-md-4 mb-4 mb-md-4">
                    <div class="position-relative project-category overflow-hidden">
                        <img src="<?php echo htmlspecialchars($industry['IndustryImage']); ?>"
                            class="w-100 object-fit-cover"
                            alt="<?php echo htmlspecialchars($industry['IndustryName']); ?>">
                        <div class="category-overlay d-flex align-items-center justify-content-center">
                            <h3 class="text-white fw-bold text-center fs-2 fs-md-5 text-uppercase">
                                <?php echo htmlspecialchars($industry['IndustryName']); ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- OUR COMPANY & SPECIALTIES SECTION -->
    <section class="bg-dark text-white">
        <div class="row g-0">
            <div class="col-12 col-md-6 p-0 mb-3 mb-md-0">
                <a href="index.php#company">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height: 300px;">
                        <img src="assets/img/banner3.jpg" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Our Company</h2>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 p-0">
                <a href="specialties.php">
                    <div class="position-relative w-100 h-100 specialty-item" style="min-height: 300px;">
                        <img src="assets/img/banner1.png" class="w-100 h-100 object-fit-cover specialty-image">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black specialty-overlay"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-2">
                            <h2 class="fw-bold text-white fs-1 fs-md-1 text-uppercase">Discover<br>Our Specialties</h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>
</body>