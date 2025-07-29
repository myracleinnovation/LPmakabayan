<?php 
include 'components/header.php'; 

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
            'Tagline' => 'Building a Better Future',
            'Description' => 'Superior and quality construction services grounded in modern principles, sustainable solutions, and client satisfaction.',
            'Mission' => 'To deliver the highest quality of service through the dedication and expertise of our skilled workforce.',
            'AboutImage' => 'assets/img/about.png',
            'LogoImage' => 'assets/img/logo.png'
        ];
    }
}

// Function to get contact information from API
function getContactInfo() {
    $apiUrl = 'app/apiContacts.php';
    
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
        // Return default contact info on error
        return [
            [
                'ContactType' => 'Phone',
                'ContactValue' => '+63 912 345 6789',
                'DisplayOrder' => 1
            ],
            [
                'ContactType' => 'Email',
                'ContactValue' => 'info@makabayanconstruction.com',
                'DisplayOrder' => 2
            ],
            [
                'ContactType' => 'Address',
                'ContactValue' => 'Makati City, Metro Manila, Philippines',
                'DisplayOrder' => 3
            ]
        ];
    }
}

// Get data from API
$companyInfo = getCompanyInfo();
$contactInfo = getContactInfo();
?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

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