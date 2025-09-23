<?php

require_once __DIR__ . '/../config/Database.php';

class LandReportModel extends BaseModel {
    protected $table = 'land_report';

    public function __construct() {
        parent::__construct();
    }

    public function getAllReports($filters = []) {
        $conditions = [];
        $params = [];

        if (isset($filters['user_id']) && !empty($filters['user_id'])) {
            $conditions[] = 'lr.user_id = :user_id';
            $params[':user_id'] = $filters['user_id'];
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $conditions[] = 'lr.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (isset($filters['land_id']) && !empty($filters['land_id'])) {
            $conditions[] = 'lr.land_id = :land_id';
            $params[':land_id'] = $filters['land_id'];
        }

        $sql = "SELECT 
                    lr.report_id,
                    lr.land_id,
                    lr.user_id,
                    lr.report_date,
                    lr.land_description,
                    lr.crop_recomendation,
                    lr.ph_value,
                    lr.organic_matter,
                    lr.nitrogen_level,
                    lr.phosphorus_level,
                    lr.potassium_level,
                    lr.environmental_notes,
                    lr.status,
                    l.location,
                    l.size,
                    l.payment_status,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM {$this->table} lr
                JOIN land l ON lr.land_id = l.land_id
                JOIN user u ON lr.user_id = u.user_id";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY lr.report_date DESC";

        return $this->executeQuery($sql, $params);
    }

    public function getReportById($reportId) {
        $sql = "SELECT 
                    lr.*,
                    l.location,
                    l.size,
                    l.payment_status,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM {$this->table} lr
                JOIN land l ON lr.land_id = l.land_id
                JOIN user u ON lr.user_id = u.user_id
                WHERE lr.report_id = :report_id";
        $result = $this->executeQuery($sql, [':report_id' => $reportId]);
        return $result ? $result[0] : null;
    }

    public function getUserReports($userId) {
        return $this->getAllReports(['user_id' => $userId]);
    }

    public function getLandReports($landId) {
        return $this->getAllReports(['land_id' => $landId]);
    }

    public function addReport($reportData) {
        try {
            $data = [
                'land_id' => $reportData['land_id'],
                'user_id' => $reportData['user_id'],
                'report_date' => $reportData['report_date'] ?? date('Y-m-d H:i:s'),
                'land_description' => $reportData['land_description'],
                'crop_recomendation' => $reportData['crop_recommendation'],
                'status' => $reportData['status'] ?? 'pending'
            ];

            // Optional fields
            if (isset($reportData['ph_value'])) {
                $data['ph_value'] = $reportData['ph_value'];
            }
            if (isset($reportData['organic_matter'])) {
                $data['organic_matter'] = $reportData['organic_matter'];
            }
            if (isset($reportData['nitrogen_level'])) {
                $data['nitrogen_level'] = $reportData['nitrogen_level'];
            }
            if (isset($reportData['phosphorus_level'])) {
                $data['phosphorus_level'] = $reportData['phosphorus_level'];
            }
            if (isset($reportData['potassium_level'])) {
                $data['potassium_level'] = $reportData['potassium_level'];
            }
            if (isset($reportData['environmental_notes'])) {
                $data['environmental_notes'] = $reportData['environmental_notes'];
            }

            $reportId = $this->create($data);
            
            if ($reportId) {
                return [
                    "success" => true, 
                    "message" => "Land report submitted successfully.", 
                    "report_id" => $reportId
                ];
            } else {
                return ["success" => false, "message" => "Failed to submit report."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updateReport($reportId, $reportData) {
        try {
            $data = [];
            
            if (isset($reportData['land_description'])) {
                $data['land_description'] = $reportData['land_description'];
            }
            if (isset($reportData['crop_recommendation'])) {
                $data['crop_recomendation'] = $reportData['crop_recommendation'];
            }
            if (isset($reportData['ph_value'])) {
                $data['ph_value'] = $reportData['ph_value'];
            }
            if (isset($reportData['organic_matter'])) {
                $data['organic_matter'] = $reportData['organic_matter'];
            }
            if (isset($reportData['nitrogen_level'])) {
                $data['nitrogen_level'] = $reportData['nitrogen_level'];
            }
            if (isset($reportData['phosphorus_level'])) {
                $data['phosphorus_level'] = $reportData['phosphorus_level'];
            }
            if (isset($reportData['potassium_level'])) {
                $data['potassium_level'] = $reportData['potassium_level'];
            }
            if (isset($reportData['environmental_notes'])) {
                $data['environmental_notes'] = $reportData['environmental_notes'];
            }
            if (isset($reportData['status'])) {
                $data['status'] = $reportData['status'];
            }

            if (empty($data)) {
                return ["success" => false, "message" => "No data to update."];
            }

            $result = $this->update($reportId, $data, 'report_id');
            
            if ($result) {
                return ["success" => true, "message" => "Report updated successfully."];
            } else {
                return ["success" => false, "message" => "No changes made or report not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function updateReportStatus($reportId, $status) {
        try {
            $result = $this->update($reportId, ['status' => $status], 'report_id');
            
            if ($result) {
                return ["success" => true, "message" => "Report status updated successfully."];
            } else {
                return ["success" => false, "message" => "Report not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function deleteReport($reportId) {
        try {
            $result = $this->delete($reportId, 'report_id');
            
            if ($result) {
                return ["success" => true, "message" => "Report deleted successfully."];
            } else {
                return ["success" => false, "message" => "Report not found."];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getReportStats() {
        $sql = "SELECT 
                    COUNT(*) as total_reports,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                    AVG(ph_value) as avg_ph,
                    AVG(organic_matter) as avg_organic_matter,
                    AVG(nitrogen_level) as avg_nitrogen,
                    AVG(phosphorus_level) as avg_phosphorus,
                    AVG(potassium_level) as avg_potassium
                FROM {$this->table} 
                WHERE ph_value IS NOT NULL";
        
        $result = $this->executeQuery($sql);
        return $result ? $result[0] : [];
    }

    public function getAssessmentRequests($userId) {
        $sql = "SELECT 
                    l.land_id,
                    l.location,
                    l.size,
                    l.payment_status,
                    l.created_at as request_date,
                    l.payment_date,
                    lr.report_id,
                    lr.report_date,
                    lr.land_description,
                    lr.crop_recomendation,
                    lr.ph_value,
                    lr.organic_matter,
                    lr.nitrogen_level,
                    lr.phosphorus_level,
                    lr.potassium_level,
                    lr.environmental_notes,
                    lr.status as report_status,
                    CASE 
                        WHEN l.payment_status = 'pending' THEN 'Payment Pending'
                        WHEN l.payment_status = 'failed' THEN 'Payment Failed'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NULL THEN 'Assessment Pending'
                        WHEN l.payment_status = 'paid' AND lr.report_id IS NOT NULL THEN COALESCE(lr.status, 'Report Submitted')
                        ELSE 'Unknown'
                    END as overall_status
                FROM land l
                LEFT JOIN {$this->table} lr ON l.land_id = lr.land_id
                WHERE l.user_id = ? 
                ORDER BY l.created_at DESC, lr.report_date DESC";
        
        $assessments = [];
        $result = $this->executeQuery($sql, [':user_id' => $userId]);
        $processed_lands = [];
        
        foreach ($result as $row) {
            $land_id = $row['land_id'];
            
            // Only process each land once (get the latest report if multiple exist)
            if (!in_array($land_id, $processed_lands)) {
                $assessments[] = [
                    "land_id" => $row["land_id"],
                    "location" => $row["location"],
                    "size" => $row["size"],
                    "payment_status" => $row["payment_status"],
                    "request_date" => $row["request_date"],
                    "payment_date" => $row["payment_date"],
                    "report_id" => $row["report_id"],
                    "report_date" => $row["report_date"],
                    "land_description" => $row["land_description"],
                    "crop_recommendation" => $row["crop_recomendation"],
                    "ph_value" => $row["ph_value"],
                    "organic_matter" => $row["organic_matter"],
                    "nitrogen_level" => $row["nitrogen_level"],
                    "phosphorus_level" => $row["phosphorus_level"],
                    "potassium_level" => $row["potassium_level"],
                    "environmental_notes" => $row["environmental_notes"],
                    "report_status" => $row["report_status"],
                    "overall_status" => $row["overall_status"],
                    "has_report" => !empty($row["report_id"]),
                    "is_paid" => $row["payment_status"] === 'paid'
                ];
                $processed_lands[] = $land_id;
            }
        }

        return $assessments;
    }

    /**
     * Assign supervisor to a land report (using environmental_notes for storage)
     */
    public function assignSupervisor($reportId, $supervisorName, $supervisorId) {
        try {
            // Store supervisor info in environmental_notes and update status
            $supervisorInfo = "Assigned to: {$supervisorName} (ID: {$supervisorId})";
            
            $sql = "UPDATE {$this->table} SET 
                        status = '',
                        environmental_notes = CASE 
                            WHEN environmental_notes IS NULL OR environmental_notes = '' 
                            THEN :supervisor_info1
                            ELSE CONCAT(environmental_notes, '\\n', :supervisor_info2)
                        END
                    WHERE report_id = :report_id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':supervisor_info1' => $supervisorInfo,
                ':supervisor_info2' => $supervisorInfo,
                ':report_id' => $reportId
            ]);
            
            error_log("Assignment query executed for report $reportId: " . ($result ? 'success' : 'failed'));
            if (!$result) {
                error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error assigning supervisor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available supervisors (Field Supervisors not currently assigned to pending reports)
     */
    public function getAvailableSupervisors() {
        try {
            $sql = "SELECT 
                        u.user_id,
                        u.first_name,
                        u.last_name,
                        u.email,
                        u.phone,
                        CONCAT(u.first_name, ' ', u.last_name) as full_name,
                        u.user_role as role,
                        CASE 
                            WHEN assigned_reports.supervisor_id IS NOT NULL THEN 'Assigned'
                            ELSE 'Available'
                        END as assignment_status
                    FROM user u
                    LEFT JOIN (
                        SELECT DISTINCT 
                            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(environmental_notes, 'ID: ', -1), ')', 1) AS UNSIGNED) as supervisor_id
                        FROM land_report 
                        WHERE environmental_notes LIKE '%Assigned to:%' 
                        AND environmental_notes LIKE '%ID:%'
                        AND (status = '' OR status IS NULL OR status NOT IN ('Approved', 'Rejected', 'Completed'))
                        AND SUBSTRING_INDEX(SUBSTRING_INDEX(environmental_notes, 'ID: ', -1), ')', 1) REGEXP '^[0-9]+$'
                    ) assigned_reports ON u.user_id = assigned_reports.supervisor_id
                    WHERE u.user_role = 'Supervisor' 
                    AND u.is_active = 1
                    AND assigned_reports.supervisor_id IS NULL
                    ORDER BY u.first_name, u.last_name";

            $result = $this->executeQuery($sql);
            
            return $result ? $result : [];

        } catch (Exception $e) {
            error_log("Error in getAvailableSupervisors: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get land reports for assignment management
     * Returns reports that need supervisor assignment or reassignment
     */
    public function getAssignmentReports() {
        try {
            $sql = "SELECT 
                        lr.report_id,
                        lr.land_id,
                        lr.user_id,
                        lr.report_date,
                        lr.status,
                        lr.environmental_notes,
                        l.location,
                        l.size,
                        l.created_at as request_date,
                        CONCAT(u.first_name, ' ', u.last_name) as landowner_name,
                        u.email,
                        u.phone,
                        CASE 
                            WHEN lr.environmental_notes LIKE '%Assigned to:%' THEN 
                                SUBSTRING_INDEX(SUBSTRING_INDEX(lr.environmental_notes, 'Assigned to: ', -1), ' (ID:', 1)
                            ELSE 'Not Assigned'
                        END as supervisor_name,
                        CASE 
                            WHEN lr.environmental_notes LIKE '%Assigned to:%' THEN 'Assigned'
                            ELSE 'Unassigned'
                        END as assignment_status,
                        CASE 
                            WHEN lr.status = '' OR lr.status IS NULL THEN 'Assigned'
                            WHEN lr.status = 'Approved' THEN 'Approved'  
                            WHEN lr.status = 'Rejected' THEN 'Rejected'
                            ELSE lr.status
                        END as current_status
                    FROM {$this->table} lr
                    JOIN land l ON lr.land_id = l.land_id
                    JOIN user u ON lr.user_id = u.user_id
                    WHERE l.payment_status = 'paid'
                    ORDER BY lr.report_date DESC, l.created_at DESC";
            
            $reports = $this->executeQuery($sql);
            
            // Format the data for frontend
            $formattedReports = [];
            foreach ($reports as $report) {
                $formattedReports[] = [
                    'id' => '#' . date('Y') . '-LR-' . str_pad($report['report_id'], 3, '0', STR_PAD_LEFT),
                    'report_id' => $report['report_id'],
                    'location' => $report['location'],
                    'name' => $report['landowner_name'],
                    'date' => date('Y-m-d', strtotime($report['request_date'])),
                    'supervisor' => $report['supervisor_name'],
                    'status' => $report['assignment_status'],
                    'current_status' => $report['current_status'],
                    'land_id' => $report['land_id'],
                    'user_id' => $report['user_id']
                ];
            }
            
            return $formattedReports;
            
        } catch (Exception $e) {
            error_log("Error in getAssignmentReports: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get land reports for review and approval
     * Returns completed reports that need operational manager review
     */
    public function getReviewReports() {
        try {
            $sql = "SELECT 
                        lr.report_id,
                        lr.land_id,
                        lr.user_id,
                        lr.report_date,
                        lr.status,
                        lr.environmental_notes,
                        lr.land_description,
                        lr.crop_recomendation,
                        lr.ph_value,
                        lr.organic_matter,
                        lr.nitrogen_level,
                        lr.phosphorus_level,
                        lr.potassium_level,
                        l.location,
                        l.size,
                        CONCAT(u.first_name, ' ', u.last_name) as landowner_name,
                        CASE 
                            WHEN lr.environmental_notes LIKE '%Assigned to:%' THEN 
                                SUBSTRING_INDEX(SUBSTRING_INDEX(lr.environmental_notes, 'Assigned to: ', -1), ' (ID:', 1)
                            ELSE 'Not Assigned'
                        END as supervisor_name,
                        CASE 
                            WHEN lr.environmental_notes LIKE '%ID: %' THEN 
                                CONCAT('SR', LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(lr.environmental_notes, 'ID: ', -1), ')', 1), 4, '0'))
                            ELSE 'Not Assigned'
                        END as supervisor_id,
                        CASE 
                            WHEN lr.status = 'Approved' THEN 'Approved'
                            WHEN lr.status = 'Rejected' THEN 'Rejected'
                            WHEN lr.land_description IS NOT NULL AND lr.crop_recomendation IS NOT NULL THEN 'Not Reviewed'
                            ELSE 'Not Reviewed'
                        END as review_status
                    FROM {$this->table} lr
                    JOIN land l ON lr.land_id = l.land_id
                    JOIN user u ON lr.user_id = u.user_id
                    ORDER BY lr.report_date DESC";
            
            $reports = $this->executeQuery($sql);
            
            // Format the data for frontend
            $formattedReports = [];
            foreach ($reports as $report) {
                $formattedReports[] = [
                    'id' => '#' . date('Y') . '-LR-' . str_pad($report['report_id'], 3, '0', STR_PAD_LEFT),
                    'report_id' => $report['report_id'],
                    'location' => $report['location'],
                    'name' => $report['landowner_name'],
                    'supervisorId' => $report['supervisor_id'],
                    'supervisor' => $report['supervisor_name'],
                    'status' => $report['review_status'],
                    'land_id' => $report['land_id'],
                    'user_id' => $report['user_id'],
                    'report_details' => [
                        'land_description' => $report['land_description'],
                        'crop_recommendation' => $report['crop_recomendation'],
                        'ph_value' => $report['ph_value'],
                        'organic_matter' => $report['organic_matter'],
                        'nitrogen_level' => $report['nitrogen_level'],
                        'phosphorus_level' => $report['phosphorus_level'],
                        'potassium_level' => $report['potassium_level'],
                        'environmental_notes' => $report['environmental_notes']
                    ]
                ];
            }
            
            return $formattedReports;
            
        } catch (Exception $e) {
            error_log("Error in getReviewReports: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Submit review decision for a land report
     */
    public function submitReview($reportId, $decision, $feedback = '') {
        try {
            // Map frontend decision to database status
            $status = $decision === 'Approve' ? 'Approved' : 'Rejected';
            
            // Prepare feedback to append to environmental_notes
            $reviewFeedback = "\nReview Decision: {$decision}";
            if (!empty($feedback)) {
                $reviewFeedback .= "\nFeedback: {$feedback}";
            }
            $reviewFeedback .= "\nReviewed on: " . date('Y-m-d H:i:s');
            
            $sql = "UPDATE {$this->table} SET 
                        status = :status,
                        environmental_notes = CONCAT(COALESCE(environmental_notes, ''), :feedback)
                    WHERE report_id = :report_id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':status' => $status,
                ':feedback' => $reviewFeedback,
                ':report_id' => $reportId
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error in submitReview: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new land report
     */
    public function createReport($data) {
        try {
            $reportData = [
                'land_id' => $data['land_id'],
                'user_id' => $data['user_id'],
                'report_date' => $data['report_date'] ?? date('Y-m-d H:i:s'),
                'land_description' => $data['land_description'],
                'crop_recomendation' => $data['crop_recomendation'],
                'status' => $data['status'] ?? 'Pending'
            ];

            // Optional fields
            if (isset($data['ph_value'])) {
                $reportData['ph_value'] = $data['ph_value'];
            }
            if (isset($data['organic_matter'])) {
                $reportData['organic_matter'] = $data['organic_matter'];
            }
            if (isset($data['nitrogen_level'])) {
                $reportData['nitrogen_level'] = $data['nitrogen_level'];
            }
            if (isset($data['phosphorus_level'])) {
                $reportData['phosphorus_level'] = $data['phosphorus_level'];
            }
            if (isset($data['potassium_level'])) {
                $reportData['potassium_level'] = $data['potassium_level'];
            }
            if (isset($data['environmental_notes'])) {
                $reportData['environmental_notes'] = $data['environmental_notes'];
            }

            return $this->create($reportData);
            
        } catch (Exception $e) {
            error_log("Error creating report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get reports by user with filters
     */
    public function getReportsByUser($userId, $filters = []) {
        $allFilters = array_merge(['user_id' => $userId], $filters);
        return $this->getAllReports($allFilters);
    }

    /**
     * Generate land suitability conclusion based on soil data
     */
    public function generateLandConclusion($reportId) {
        try {
            $report = $this->getReportById($reportId);
            if (!$report) {
                return ["success" => false, "message" => "Report not found."];
            }

            // Simple analysis - just check if we can recommend crops
            $canRecommendCrops = $this->canRecommendCrops($report);
            
            $conclusion = [
                'is_good_for_organic' => $canRecommendCrops,
                'conclusion_text' => $canRecommendCrops ? 
                    "SUITABLE - Good for organic farming - Based on your soil data, we can recommend suitable crops for organic farming on your land." :
                    "NOT SUITABLE - Not ideal for organic farming - Your current soil conditions need improvement before we can recommend organic crops.",
                'recommended_crops' => $canRecommendCrops ? $this->getSimpleCropRecommendations($report) : [],
                'status' => $canRecommendCrops ? 'good' : 'needs_improvement'
            ];

            // Update the report with simple conclusion
            $updateData = [
                'conclusion' => json_encode($conclusion),
                'suitability_status' => $canRecommendCrops ? 'suitable' : 'not_suitable'
            ];
            
            $this->update($reportId, $updateData, 'report_id');
            
            return ["success" => true, "data" => $conclusion];

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error generating conclusion: " . $e->getMessage()];
        }
    }

    /**
     * Simple check - can we recommend crops based on soil data?
     */
    private function canRecommendCrops($report) {
        $ph = floatval($report['ph_value'] ?? 0);
        $organicMatter = floatval($report['organic_matter'] ?? 0);
        
        // Handle text-based nutrient levels (High, Medium, Low)
        $nitrogen = $report['nitrogen_level'] ?? '';
        $phosphorus = $report['phosphorus_level'] ?? '';
        $potassium = $report['potassium_level'] ?? '';
        
        // Convert text levels to boolean "acceptable"
        $nitrogenOK = in_array(strtolower($nitrogen), ['high', 'medium']);
        $phosphorusOK = in_array(strtolower($phosphorus), ['high', 'medium']);
        $potassiumOK = in_array(strtolower($potassium), ['high', 'medium']);
        
        // More realistic criteria for Sri Lankan soils
        $phOK = ($ph >= 5.0 && $ph <= 8.5);
        $organicMatterOK = ($organicMatter >= 1.5);
        
        // At least pH and organic matter should be good, plus at least 2 out of 3 nutrients
        $nutrientCount = ($nitrogenOK ? 1 : 0) + ($phosphorusOK ? 1 : 0) + ($potassiumOK ? 1 : 0);
        
        return $phOK && $organicMatterOK && $nutrientCount >= 2;
    }

    /**
     * Get simple crop recommendations
     */
    private function getSimpleCropRecommendations($report) {
        $crops = [];
        
        $ph = floatval($report['ph_value'] ?? 0);
        $organicMatter = floatval($report['organic_matter'] ?? 0);
        
        // More flexible recommendations - most soils can grow something organically
        if ($ph >= 6.0 && $ph <= 7.5 && $organicMatter >= 2.5) {
            // Excellent conditions
            $crops = ['Tomatoes', 'Lettuce', 'Carrots', 'Beans', 'Cabbage', 'Spinach'];
        } elseif ($ph >= 5.5 && $ph <= 8.0 && $organicMatter >= 1.8) {
            // Good conditions
            $crops = ['Potatoes', 'Onions', 'Radishes', 'Spinach', 'Beans'];
        } elseif ($ph >= 5.0 && $ph <= 8.5) {
            // Basic conditions - still workable
            $crops = ['Sweet Potatoes', 'Cassava', 'Ginger', 'Turmeric'];
        } else {
            // Very challenging conditions
            $crops = ['Banana', 'Papaya']; // These are more tolerant
        }
        
        return $crops;
    }

    /**
     * Create interest request for FarmMaster partnership
     */
    public function createInterestRequest($reportId, $userId) {
        try {
            // Check if report exists and is suitable
            $report = $this->getReportById($reportId);
            if (!$report) {
                return ["success" => false, "message" => "Land report not found."];
            }

            if ($report['user_id'] != $userId) {
                return ["success" => false, "message" => "You can only create requests for your own land."];
            }

            // Check if land is marked as suitable
            if ($report['suitability_status'] !== 'suitable') {
                return ["success" => false, "message" => "Only suitable land can request partnership."];
            }

            // Check if request already exists
            $existingRequest = $this->getInterestRequestByReportId($reportId);
            if ($existingRequest) {
                return ["success" => false, "message" => "Interest request already exists for this land report."];
            }

            // Create the interest request
            $sql = "INSERT INTO interest_requests (report_id, land_id, user_id, status, created_at, updated_at) 
                    VALUES (:report_id, :land_id, :user_id, 'pending', NOW(), NOW())";
            
            $params = [
                ':report_id' => $reportId,
                ':land_id' => $report['land_id'],
                ':user_id' => $userId
            ];

            $this->executeQuery($sql, $params);
            $requestId = $this->db->lastInsertId();

            return [
                "success" => true, 
                "message" => "Interest request sent to Financial Manager successfully!", 
                "request_id" => $requestId
            ];

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error creating interest request: " . $e->getMessage()];
        }
    }

    /**
     * Get interest request by report ID
     */
    private function getInterestRequestByReportId($reportId) {
        $sql = "SELECT * FROM interest_requests WHERE report_id = :report_id";
        $result = $this->executeQuery($sql, [':report_id' => $reportId]);
        return $result ? $result[0] : null;
    }

    /**
     * Check if interest request exists for a report (public method)
     */
    public function hasInterestRequest($reportId) {
        try {
            $request = $this->getInterestRequestByReportId($reportId);
            return [
                "success" => true,
                "has_request" => $request !== null,
                "request" => $request
            ];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Error checking interest request: " . $e->getMessage()];
        }
    }

    /**
     * Create proposal request for suitable land
     */
    public function createProposalRequest($reportId, $userId) {
        try {
            $report = $this->getReportById($reportId);
            if (!$report) {
                return ["success" => false, "message" => "Report not found."];
            }

            // Check if land is suitable
            $conclusion = json_decode($report['conclusion'] ?? '{}', true);
            if (!$conclusion['is_suitable']) {
                return ["success" => false, "message" => "Land is not suitable for organic farming proposal."];
            }

            // Check if proposal request already exists
            $existingRequest = $this->getProposalRequestByReportId($reportId);
            if ($existingRequest) {
                return ["success" => false, "message" => "Proposal request already exists for this land report."];
            }

            // Create proposal request
            $proposalRequestData = [
                'report_id' => $reportId,
                'user_id' => $userId,
                'land_id' => $report['land_id'],
                'request_date' => date('Y-m-d H:i:s'),
                'status' => 'pending_review',
                'crop_recommendations' => json_encode($conclusion['crop_recommendations']),
                'suitability_score' => $conclusion['suitability_score']
            ];

            $sql = "INSERT INTO proposal_requests (report_id, user_id, land_id, request_date, status, crop_recommendations, suitability_score) 
                    VALUES (:report_id, :user_id, :land_id, :request_date, :status, :crop_recommendations, :suitability_score)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':report_id' => $proposalRequestData['report_id'],
                ':user_id' => $proposalRequestData['user_id'],
                ':land_id' => $proposalRequestData['land_id'],
                ':request_date' => $proposalRequestData['request_date'],
                ':status' => $proposalRequestData['status'],
                ':crop_recommendations' => $proposalRequestData['crop_recommendations'],
                ':suitability_score' => $proposalRequestData['suitability_score']
            ]);

            if ($result) {
                $requestId = $this->db->lastInsertId();
                return [
                    "success" => true, 
                    "message" => "Proposal request submitted successfully. Our financial team will review and create a proposal for you.",
                    "request_id" => $requestId
                ];
            } else {
                return ["success" => false, "message" => "Failed to create proposal request."];
            }

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error creating proposal request: " . $e->getMessage()];
        }
    }

    /**
     * Get proposal request by report ID
     */
    private function getProposalRequestByReportId($reportId) {
        $sql = "SELECT * FROM proposal_requests WHERE report_id = :report_id";
        $result = $this->executeQuery($sql, [':report_id' => $reportId]);
        return $result ? $result[0] : null;
    }

    /**
     * Get interest requests (public debug method)
     */
    public function getInterestRequestsDebug() {
        try {
            $sql = "SELECT 
                        ir.*,
                        lr.land_description,
                        lr.ph_value,
                        lr.organic_matter,
                        lr.nitrogen_level,
                        lr.phosphorus_level,
                        lr.potassium_level,
                        lr.conclusion,
                        l.location,
                        l.size,
                        u.first_name,
                        u.last_name,
                        u.email,
                        u.phone
                    FROM interest_requests ir
                    JOIN land_report lr ON ir.report_id = lr.report_id
                    JOIN land l ON ir.land_id = l.land_id
                    JOIN user u ON ir.user_id = u.user_id
                    ORDER BY ir.created_at DESC";
            
            $requests = $this->executeQuery($sql, []);
            
            // Parse JSON conclusions
            foreach ($requests as &$request) {
                if ($request['conclusion']) {
                    $request['conclusion'] = json_decode($request['conclusion'], true);
                }
            }
            
            return [
                'success' => true,
                'data' => $requests
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Debug error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update interest request status
     */
    public function updateInterestRequestStatus($requestId, $status, $notes = null) {
        try {
            $sql = "UPDATE interest_requests SET status = :status";
            $params = [':status' => $status, ':request_id' => $requestId];
            
            if ($notes) {
                $sql .= ", financial_manager_notes = :notes";
                $params[':notes'] = $notes;
            }
            
            $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE request_id = :request_id";
            
            $result = $this->executeQuery($sql, $params);
            
            if ($result !== false) {
                return ['success' => true, 'message' => 'Interest request status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update interest request status'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()];
        }
    }

    /**
     * Get assigned land reports for a specific field supervisor
     */
    public function getAssignedReportsForSupervisor($supervisorId) {
        try {
            // Get supervisor details
            $supervisorSql = "SELECT first_name, last_name FROM user WHERE user_id = :supervisor_id";
            $supervisorResult = $this->executeQuery($supervisorSql, [':supervisor_id' => $supervisorId]);
            
            if (!$supervisorResult) {
                return [];
            }
            
            $supervisorName = $supervisorResult[0]['first_name'] . ' ' . $supervisorResult[0]['last_name'];
            
        $sql = "SELECT 
                    lr.report_id,
                    lr.land_id,
                    lr.user_id,
                    lr.report_date,
                    lr.land_description,
                    lr.crop_recomendation,
                    lr.ph_value,
                    lr.organic_matter,
                    lr.nitrogen_level,
                    lr.phosphorus_level,
                    lr.potassium_level,
                    lr.environmental_notes,
                    lr.status,
                    lr.completion_status,
                    lr.suitability_status,
                    l.location,
                    l.size,
                    l.payment_status,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM {$this->table} lr
                JOIN land l ON lr.land_id = l.land_id
                JOIN user u ON lr.user_id = u.user_id
                WHERE lr.environmental_notes LIKE :supervisor_assignment
                ORDER BY lr.report_date DESC";            $params = [':supervisor_assignment' => '%Assigned to: ' . $supervisorName . ' (ID: ' . $supervisorId . ')%'];
            
            $reports = $this->executeQuery($sql, $params);
            
            // Process the results to extract assignment status and clean up environmental notes
            foreach ($reports as &$report) {
                $report['assignment_status'] = $this->extractAssignmentStatus($report['environmental_notes']);
                $report['assigned_date'] = $report['report_date']; // Use report_date as assigned date
                
                // Generate report ID format
                $report['formatted_report_id'] = 'RPT-' . date('Y', strtotime($report['report_date'])) . '-' . str_pad($report['report_id'], 3, '0', STR_PAD_LEFT);
                
                // Full landowner name
                $report['landowner_name'] = $report['first_name'] . ' ' . $report['last_name'];
            }
            
            return $reports;
            
        } catch (Exception $e) {
            error_log("Error in getAssignedReportsForSupervisor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract assignment status from environmental notes
     */
    private function extractAssignmentStatus($environmentalNotes) {
        if (empty($environmentalNotes)) {
            return 'Pending';
        }
        
        if (strpos($environmentalNotes, 'Assigned to:') !== false) {
            return 'In Progress';
        }
        
        return 'Pending';
    }

    /**
     * Update land report with submitted data
     */
    public function updateReportData($reportId, $data) {
        try {
            $sql = "UPDATE {$this->table} SET 
                        ph_value = :ph_value,
                        organic_matter = :organic_matter,
                        nitrogen_level = :nitrogen_level,
                        phosphorus_level = :phosphorus_level,
                        potassium_level = :potassium_level,
                        environmental_notes = :environmental_notes,
                        completion_status = :completion_status,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE report_id = :report_id";

            $params = [
                ':ph_value' => $data['ph_value'],
                ':organic_matter' => $data['organic_matter'],
                ':nitrogen_level' => $data['nitrogen_level'],
                ':phosphorus_level' => $data['phosphorus_level'],
                ':potassium_level' => $data['potassium_level'],
                ':environmental_notes' => $data['environmental_notes'],
                ':completion_status' => $data['completion_status'],
                ':report_id' => $reportId
            ];

            $result = $this->executeQuery($sql, $params);
            return $result !== false;
            
        } catch (Exception $e) {
            error_log("Error in updateReportData: " . $e->getMessage());
            return false;
        }
    }
}

?>