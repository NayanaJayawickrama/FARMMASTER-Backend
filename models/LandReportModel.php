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
     * Get available supervisors (Supervisors who are not currently assigned to pending reports)
     * Supervisors are considered available when:
     * 1. They have no assignments, OR
     * 2. All their assignments have status 'Approved', 'Rejected', or 'Completed'
     */
    public function getAvailableSupervisors() {
        try {
            // Get supervisors who are not currently assigned to any pending land report
            // Only users with user_role = 'Supervisor' and are active
            // A supervisor is considered "assigned" if they appear in environmental_notes of a report
            // that has status = '' (pending) or other non-completed statuses
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
            
            error_log("Available supervisors query result: " . print_r($result, true));
            
            // Return only unassigned supervisors from database - no fallback data
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
                            ELSE 'Unknown'
                        END as supervisor_name,
                        CASE 
                            WHEN lr.environmental_notes LIKE '%ID: %' THEN 
                                CONCAT('SR', LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(lr.environmental_notes, 'ID: ', -1), ')', 1), 4, '0'))
                            ELSE 'Unknown'
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
                    WHERE l.payment_status = 'paid'
                    AND lr.environmental_notes LIKE '%Assigned to:%'
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
}

?>