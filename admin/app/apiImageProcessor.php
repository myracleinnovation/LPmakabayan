<?php
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
    require_once('../../app/Db.php');

    $conn = Db::connect();

    $response = [
        'status' => 0,
        'message' => 'No action taken',
        'data' => null
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['get_images'])) {
            try {
                $data = getImages($conn);
                
                $response = [
                    'status' => 1,
                    'message' => 'Images retrieved successfully',
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                    'data' => []
                ];
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_image_name':
                    try {
                        $oldName = $_POST['old_name'] ?? '';
                        $newName = $_POST['new_name'] ?? '';
                        
                        if (empty($oldName) || empty($newName)) {
                            throw new Exception('Old name and new name are required');
                        }
                        
                        $result = updateImageName($conn, $oldName, $newName);
                        
                        if ($result['success']) {
                            $response = [
                                'status' => 1,
                                'message' => 'Image name updated successfully',
                                'data' => $result['data']
                            ];
                        } else {
                            $response = [
                                'status' => 0,
                                'message' => $result['message'],
                                'data' => null
                            ];
                        }
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;
                    
                case 'delete_image':
                    try {
                        $imageName = $_POST['image_name'] ?? '';
                        
                        if (empty($imageName)) {
                            throw new Exception('Image name is required');
                        }
                        
                        $result = deleteImage($conn, $imageName);
                        
                        if ($result['success']) {
                            $response = [
                                'status' => 1,
                                'message' => 'Image deleted successfully',
                                'data' => null
                            ];
                        } else {
                            $response = [
                                'status' => 0,
                                'message' => $result['message'],
                                'data' => null
                            ];
                        }
                    } catch (Exception $e) {
                        $response = [
                            'status' => 0,
                            'message' => $e->getMessage(),
                            'data' => null
                        ];
                    }
                    break;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);

    function getImages($conn, $search = '', $start = 0, $length = 25, $order = []) {
        $imgDir = '../../assets/img/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $images = [];
        
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    $filepath = $imgDir . $file;
                    $size = filesize($filepath);
                    $imageInfo = getimagesize($filepath);
                    
                    $images[] = [
                        'name' => $file,
                        'size' => $size,
                        'dimensions' => $imageInfo ? $imageInfo[0] . 'x' . $imageInfo[1] : 'Unknown',
                        'type' => $extension,
                        'formatted_size' => formatBytes($size)
                    ];
                }
            }
        }
        
        // Apply search filter
        if (!empty($search)) {
            $images = array_filter($images, function($image) use ($search) {
                return stripos($image['name'], $search) !== false;
            });
        }
        
        // Apply sorting
        if (!empty($order)) {
            $column = $order[0]['column'] ?? 0;
            $direction = $order[0]['dir'] ?? 'asc';
            
            usort($images, function($a, $b) use ($column, $direction) {
                $columns = ['name', 'size', 'dimensions', 'type'];
                $col = $columns[$column] ?? 'name';
                
                $result = 0;
                if ($col === 'size') {
                    $result = $a[$col] <=> $b[$col];
                } else {
                    $result = strcasecmp($a[$col], $b[$col]);
                }
                
                return $direction === 'desc' ? -$result : $result;
            });
        }
        
        // For client-side processing, return all images
        return array_values($images);
    }

    function getTotalImages($conn, $search = '') {
        $imgDir = '../../assets/img/';
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $count = 0;
        
        if (is_dir($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    if (empty($search) || stripos($file, $search) !== false) {
                        $count++;
                    }
                }
            }
        }
        
        return $count;
    }

    function updateImageName($conn, $oldName, $newName) {
        $imgDir = '../../assets/img/';
        $oldPath = $imgDir . $oldName;
        $newPath = $imgDir . $newName;
        
        // Validate old file exists
        if (!file_exists($oldPath)) {
            return ['success' => false, 'message' => 'Image file not found'];
        }
        
        // Validate new name format
        $extension = strtolower(pathinfo($oldName, PATHINFO_EXTENSION));
        $newExtension = strtolower(pathinfo($newName, PATHINFO_EXTENSION));
        
        if ($extension !== $newExtension) {
            return ['success' => false, 'message' => 'File extension cannot be changed'];
        }
        
        // Check if new name already exists
        if (file_exists($newPath)) {
            return ['success' => false, 'message' => 'An image with this name already exists'];
        }
        
        // Validate new name characters
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', pathinfo($newName, PATHINFO_FILENAME))) {
            return ['success' => false, 'message' => 'Invalid characters in filename. Use only letters, numbers, dots, underscores, and hyphens'];
        }
        
        // Rename the file
        if (rename($oldPath, $newPath)) {
            return [
                'success' => true, 
                'message' => 'Image renamed successfully',
                'data' => [
                    'old_name' => $oldName,
                    'new_name' => $newName
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to rename image file'];
        }
    }

    function deleteImage($conn, $imageName) {
        $imgDir = '../../assets/img/';
        $filePath = $imgDir . $imageName;
        
        // Validate file exists
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'Image file not found'];
        }
        
        // Delete the file
        if (unlink($filePath)) {
            return [
                'success' => true, 
                'message' => 'Image deleted successfully'
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to delete image file'];
        }
    }

    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
?>
