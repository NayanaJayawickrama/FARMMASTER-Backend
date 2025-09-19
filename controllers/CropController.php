<?php

require_once __DIR__ . '/../models/CropModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/SessionManager.php';

class CropController {
    private $cropModel;
    private $validStatus = ['Available', 'Growing', 'Harvested', 'Sold'];

    public function __construct() {
        $this->cropModel = new CropModel();
    }

    public function getCrops() {
        try {
            $filters = [];
            
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['crop_name'])) {
                $filters['crop_name'] = $_GET['crop_name'];
            }

            $crops = $this->cropModel->getAllCrops($filters);
            
            // Add status field for display compatibility
            foreach ($crops as &$crop) {
                if (!isset($crop['status'])) {
                    $crop['status'] = 'Available';
                }
            }
            
            Response::success("Crops retrieved successfully", $crops);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getCrop($cropId) {
        try {
            $crop = $this->cropModel->getCropById($cropId);
            
            if (!$crop) {
                Response::notFound("Crop not found");
            }

            // Add status field for display compatibility
            if (!isset($crop['status'])) {
                $crop['status'] = 'Available';
            }
            
            Response::success("Crop retrieved successfully", $crop);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function addCrop() {
        try {
            // Check authentication and authorization
            SessionManager::requireRole(['Landowner', 'Operational_Manager', 'Supervisor']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $cropName = Validator::required($data['crop_name'] ?? '', 'Crop name');
            $cropDuration = Validator::numeric($data['crop_duration'] ?? 0, 'Crop duration', 1);
            $quantity = Validator::numeric($data['quantity'] ?? 0, 'Quantity', 0);
            $status = isset($data['status']) ? 
                     Validator::inArray($data['status'], $this->validStatus, 'Status') : 
                     'Available';

            // Check if crop name already exists
            if ($this->cropModel->cropNameExists($cropName)) {
                Response::error("Crop name already exists");
            }

            $cropData = [
                'crop_name' => $cropName,
                'crop_duration' => $cropDuration,
                'quantity' => $quantity,
                'status' => $status
            ];

            $result = $this->cropModel->addCrop($cropData);

            if ($result['success']) {
                Response::success($result['message'], $result, 201);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateCrop($cropId) {
        try {
            // Check authentication and authorization
            SessionManager::requireRole(['Landowner', 'Operational_Manager', 'Supervisor']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            // Check if crop exists
            $existingCrop = $this->cropModel->getCropById($cropId);
            if (!$existingCrop) {
                Response::notFound("Crop not found");
            }

            $cropData = [];

            if (isset($data['crop_name'])) {
                $cropName = Validator::required($data['crop_name'], 'Crop name');
                // Check if new crop name already exists (excluding current crop)
                if ($this->cropModel->cropNameExists($cropName, $cropId)) {
                    Response::error("Crop name already exists");
                }
                $cropData['crop_name'] = $cropName;
            }

            if (isset($data['crop_duration'])) {
                $cropData['crop_duration'] = Validator::numeric($data['crop_duration'], 'Crop duration', 1);
            }

            if (isset($data['quantity'])) {
                $cropData['quantity'] = Validator::numeric($data['quantity'], 'Quantity', 0);
            }

            if (isset($data['status'])) {
                $cropData['status'] = Validator::inArray($data['status'], $this->validStatus, 'Status');
            }

            if (empty($cropData)) {
                Response::error("No valid fields to update");
            }

            $result = $this->cropModel->updateCrop($cropId, $cropData);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                // Check if it's a "no changes" message and return as info instead of error
                if (strpos($result['message'], 'No changes made') !== false) {
                    Response::info($result['message']);
                } else {
                    Response::error($result['message']);
                }
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateCropStatus($cropId) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['status'])) {
            Response::error("Status is required", 400);
            return;
        }
        $status = trim($data['status']);
        $validStatuses = ['Available', 'Unavailable', 'Sold'];
        if (!in_array($status, $validStatuses)) {
            Response::error("Invalid status", 400);
            return;
        }
        $result = $this->cropModel->updateCropStatus($cropId, $status);
        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message'], 400);
        }
    } catch (Exception $e) {
        Response::error($e->getMessage(), 500);
    }
}

    

    public function updateCropQuantity($cropId) {
        try {
            SessionManager::requireRole(['Landowner', 'Operational_Manager', 'Supervisor']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                Response::error("Invalid JSON data");
            }

            $quantity = Validator::numeric($data['quantity'] ?? 0, "Quantity", 0);

            $result = $this->cropModel->updateCropQuantity($cropId, $quantity);

            if ($result['success']) {
                Response::success($result['message']);
            } else {
                Response::error($result['message']);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function searchCrops() {
        try {
            $searchTerm = $_GET['search'] ?? '';

            if (empty($searchTerm)) {
                Response::error("Search term is required");
            }

            $crops = $this->cropModel->searchCrops($searchTerm);

            // Add status field for display compatibility
            foreach ($crops as &$crop) {
                if (!isset($crop['status'])) {
                    $crop['status'] = 'Available';
                }
            }

            Response::success("Search completed", $crops);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getCropStats() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Financial_Manager', 'Supervisor']);

            $stats = $this->cropModel->getCropStats();

            Response::success("Crop statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getLowQuantityCrops() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Landowner', 'Supervisor']);

            $threshold = $_GET['threshold'] ?? 10;
            $threshold = Validator::numeric($threshold, "Threshold", 1);

            $crops = $this->cropModel->getLowQuantityCrops($threshold);

            // Add status field for display compatibility
            foreach ($crops as &$crop) {
                if (!isset($crop['status'])) {
                    $crop['status'] = 'Available';
                }
            }

            Response::success("Low quantity crops retrieved", $crops);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getCropsByStatus($status) {
        try {
            $status = Validator::inArray($status, $this->validStatus, "Status");

            $crops = $this->cropModel->getCropsByStatus($status);

            Response::success("Crops retrieved successfully", $crops);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getDashboardStats() {
        try {
            SessionManager::requireAuth();

            $stats = $this->cropModel->getDashboardStats();

            Response::success("Dashboard statistics retrieved", $stats);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getCropByName($cropName) {
        try {
            $crop = $this->cropModel->getCropByName($cropName);
            
            if (!$crop) {
                Response::notFound("Crop not found");
            }

            // Add status field for display compatibility
            if (!isset($crop['status'])) {
                $crop['status'] = 'Available';
            }
            
            Response::success("Crop retrieved successfully", $crop);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    // Batch operations
    public function bulkUpdateStatus() {
        try {
            SessionManager::requireRole(['Operational_Manager', 'Supervisor']);

            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data || !isset($data['crop_ids']) || !isset($data['status'])) {
                Response::error("Crop IDs and status are required");
            }

            $cropIds = $data['crop_ids'];
            $status = Validator::inArray($data['status'], $this->validStatus, 'Status');

            if (!is_array($cropIds) || empty($cropIds)) {
                Response::error("Invalid crop IDs");
            }

            $successCount = 0;
            $errors = [];

            foreach ($cropIds as $cropId) {
                $result = $this->cropModel->updateCrop($cropId, ['status' => $status]);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errors[] = "Crop ID {$cropId}: {$result['message']}";
                }
            }

            $message = "Updated {$successCount} out of " . count($cropIds) . " crops";
            
            if (!empty($errors)) {
                Response::success($message, ['errors' => $errors]);
            } else {
                Response::success($message);
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}

?>