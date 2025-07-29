<?php 
include 'components/header.php'; 

// Function to get project categories from API
function getProjectCategories() {
    $apiUrl = 'app/apiProjectCategories.php';
    
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
        // Return default project categories on error
        return [
            [
                'CategoryName' => 'Residential Projects',
                'CategoryImage' => 'assets/img/project1.png'
            ],
            [
                'CategoryName' => 'Commercial Buildings',
                'CategoryImage' => 'assets/img/project2.png'
            ],
            [
                'CategoryName' => 'Industrial Facilities',
                'CategoryImage' => 'assets/img/project3.png'
            ],
            [
                'CategoryName' => 'Healthcare Facilities',
                'CategoryImage' => 'assets/img/project4.png'
            ],
            [
                'CategoryName' => 'Educational Institutions',
                'CategoryImage' => 'assets/img/project5.png'
            ],
            [
                'CategoryName' => 'Infrastructure Projects',
                'CategoryImage' => 'assets/img/project6.png'
            ]
        ];
    }
}

// Function to get projects from API
function getProjects() {
    $apiUrl = 'app/apiProjects.php';
    
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
        // Return default projects on error
        return [
            [
                'ProjectTitle' => 'Modern Residential Complex',
                'ProjectDescription' => 'A state-of-the-art residential complex featuring modern amenities and sustainable design principles.',
                'ProjectOwner' => 'ABC Development Corp.',
                'ProjectLocation' => 'Metro Manila',
                'TurnoverDate' => '2024-01-15',
                'ProjectImage1' => 'assets/img/project7.png',
                'ProjectImage2' => 'assets/img/project8.png'
            ],
            [
                'ProjectTitle' => 'Commercial Office Tower',
                'ProjectDescription' => 'A premium office tower designed for modern businesses with cutting-edge facilities.',
                'ProjectOwner' => 'XYZ Properties Inc.',
                'ProjectLocation' => 'Makati City',
                'TurnoverDate' => '2023-12-20',
                'ProjectImage1' => 'assets/img/project1.png',
                'ProjectImage2' => 'assets/img/project2.png'
            ]
        ];
    }
}

// Get data from API
$projectCategories = getProjectCategories();
$projects = getProjects();
?>

<body class="bg-warning">
    <?php include 'components/topNav.php'; ?>

    <!-- OUR WORK SPEAKS FOR ITSELF SECTION -->
    <section class="bg-warning text-white py-4 py-md-5" style="min-height: auto;">
        <div class="container">
            <div class="text-center mb-4 mb-md-5">
                <h2 class="fw-bolder fs-2 fs-md-1 text-uppercase text-black">Our Work Speaks For Itself</h2>
            </div>
            <div class="row justify-content-center">
                <?php foreach ($projectCategories as $category): ?>
                <div class="col-12 col-md-4 mb-4 mb-md-4">
                    <div class="position-relative project-category overflow-hidden">
                        <img src="<?php echo htmlspecialchars($category['CategoryImage']); ?>"
                            class="w-100 object-fit-cover"
                            alt="<?php echo htmlspecialchars($category['CategoryName']); ?>">
                        <div class="category-overlay d-flex align-items-center justify-content-center">
                            <h3 class="text-white fw-bold text-center fs-2 fs-md-5 text-uppercase">
                                <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php 
    $bgClass = 'bg-black';
    $textClass = 'text-white';
    $titleClass = 'text-warning';
    $dotClass = 'bg-white';
    $justifyClass = '';
    
    foreach ($projects as $index => $project): 
        // Alternate background colors
        if ($index % 2 == 0) {
            $bgClass = 'bg-black';
            $textClass = 'text-white';
            $titleClass = 'text-warning';
            $dotClass = 'bg-white';
            $justifyClass = '';
        } else {
            $bgClass = 'bg-warning';
            $textClass = 'text-black';
            $titleClass = 'text-black';
            $dotClass = 'bg-black';
            $justifyClass = 'justify-content-end';
        }
    ?>
    <!-- PROJECT DETAIL SECTION -->
    <section class="<?php echo $bgClass; ?> <?php echo $textClass; ?> py-4 py-md-5" style="min-height: 100vh;">
        <div class="container h-100 d-flex align-items-center justify-content-center flex-column">
            <div class="row align-items-center">
                <div class="col-12 col-lg-6 mb-4 mb-lg-0 <?php echo ($index % 2 == 1) ? 'order-lg-2' : ''; ?>">
                    <h2 class="fw-bolder <?php echo $titleClass; ?> mb-3 mb-md-4 fs-1 fs-md-1 text-uppercase">
                        <?php echo htmlspecialchars($project['ProjectTitle']); ?></h2>
                    <div class="mb-3 mb-md-4">
                        <p class="mb-2 fs-5">Owner:
                            <strong><?php echo htmlspecialchars($project['ProjectOwner']); ?></strong></p>
                        <p class="mb-2 fs-5">Turnover:
                            <strong><?php echo date('F Y', strtotime($project['TurnoverDate'])); ?></strong></p>
                        <p class="mb-2 fs-5">Location:
                            <strong><?php echo htmlspecialchars($project['ProjectLocation']); ?></strong></p>
                    </div>
                    <p class="mb-3 mb-md-4 fs-5">
                        <?php echo htmlspecialchars($project['ProjectDescription']); ?>
                    </p>
                    <div class="d-flex gap-2 <?php echo $justifyClass; ?>">
                        <div class="<?php echo $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?php echo $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?php echo $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?php echo $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                        <div class="<?php echo $dotClass; ?>" style="width: 12px; height: 12px;"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 <?php echo ($index % 2 == 1) ? 'order-lg-1' : ''; ?>">
                    <div class="row g-3">
                        <?php if (!empty($project['ProjectImage1'])): ?>
                        <div class="col-12">
                            <img src="<?php echo htmlspecialchars($project['ProjectImage1']); ?>"
                                class="w-100 h-100 object-fit-cover" style="height: 200px;"
                                alt="<?php echo htmlspecialchars($project['ProjectTitle']); ?> - Image 1">
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($project['ProjectImage2'])): ?>
                        <div class="col-12">
                            <img src="<?php echo htmlspecialchars($project['ProjectImage2']); ?>"
                                class="w-100 h-100 object-fit-cover" style="height: 200px;"
                                alt="<?php echo htmlspecialchars($project['ProjectTitle']); ?> - Image 2">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endforeach; ?>

    <!-- OUR COMPANY & SPECIALTIES SECTION -->
    <section class="bg-dark text-white">
        <div class="row g-0">
            <div class="col-12 col-md-6 p-0 mb-3 mb-md-0">
                <a href="index.php#company">
                    <div class="position-relative w-100 h-100 specialty-item">
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
                    <div class="position-relative w-100 h-100 specialty-item">
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