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
                    "✅ Good for organic farming - Based on your soil data, we can recommend suitable crops for organic farming on your land." :
                    "❌ Not ideal for organic farming - Your current soil conditions need improvement before we can recommend organic crops.",
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
     * Analyze soil suitability for organic farming
     */
    private function analyzeSoilSuitability($report) {
        $score = 0;
        $maxScore = 100;
        $analysis = [];

        // pH analysis (25 points)
        $ph = floatval($report['ph_value']);
        if ($ph >= 6.0 && $ph <= 7.5) {
            $score += 25;
            $analysis['ph'] = "Excellent - pH level is optimal for organic farming";
        } elseif ($ph >= 5.5 && $ph < 6.0 || $ph > 7.5 && $ph <= 8.0) {
            $score += 15;
            $analysis['ph'] = "Good - pH level is acceptable but could be optimized";
        } elseif ($ph >= 5.0 && $ph < 5.5 || $ph > 8.0 && $ph <= 8.5) {
            $score += 8;
            $analysis['ph'] = "Fair - pH level needs adjustment for optimal organic farming";
        } else {
            $analysis['ph'] = "Poor - pH level requires significant correction";
        }

        // Organic Matter analysis (25 points)
        $organicMatter = floatval($report['organic_matter']);
        if ($organicMatter >= 4.0) {
            $score += 25;
            $analysis['organic_matter'] = "Excellent - High organic matter content ideal for organic farming";
        } elseif ($organicMatter >= 2.5) {
            $score += 18;
            $analysis['organic_matter'] = "Good - Adequate organic matter with room for improvement";
        } elseif ($organicMatter >= 1.5) {
            $score += 10;
            $analysis['organic_matter'] = "Fair - Organic matter needs significant enhancement";
        } else {
            $analysis['organic_matter'] = "Poor - Very low organic matter requires immediate attention";
        }

        // Nitrogen analysis (20 points)
        $nitrogen = floatval($report['nitrogen_level']);
        if ($nitrogen >= 25) {
            $score += 20;
            $analysis['nitrogen'] = "Excellent nitrogen levels for crop growth";
        } elseif ($nitrogen >= 15) {
            $score += 15;
            $analysis['nitrogen'] = "Good nitrogen levels with minor supplementation needed";
        } elseif ($nitrogen >= 8) {
            $score += 8;
            $analysis['nitrogen'] = "Moderate nitrogen levels requiring organic amendments";
        } else {
            $analysis['nitrogen'] = "Low nitrogen levels need substantial organic fertilization";
        }

        // Phosphorus analysis (15 points)
        $phosphorus = floatval($report['phosphorus_level']);
        if ($phosphorus >= 20) {
            $score += 15;
            $analysis['phosphorus'] = "Excellent phosphorus availability";
        } elseif ($phosphorus >= 10) {
            $score += 10;
            $analysis['phosphorus'] = "Good phosphorus levels";
        } elseif ($phosphorus >= 5) {
            $score += 5;
            $analysis['phosphorus'] = "Moderate phosphorus requiring supplementation";
        } else {
            $analysis['phosphorus'] = "Low phosphorus needs organic enhancement";
        }

        // Potassium analysis (15 points)
        $potassium = floatval($report['potassium_level']);
        if ($potassium >= 150) {
            $score += 15;
            $analysis['potassium'] = "Excellent potassium levels";
        } elseif ($potassium >= 80) {
            $score += 10;
            $analysis['potassium'] = "Good potassium availability";
        } elseif ($potassium >= 40) {
            $score += 5;
            $analysis['potassium'] = "Moderate potassium requiring organic supplements";
        } else {
            $analysis['potassium'] = "Low potassium needs immediate attention";
        }

        $suitabilityPercentage = ($score / $maxScore) * 100;
        $suitable = $suitabilityPercentage >= 60; // 60% threshold for organic farming suitability

        return [
            'suitable' => $suitable,
            'score' => round($suitabilityPercentage, 1),
            'analysis' => $analysis,
            'grade' => $this->getSuitabilityGrade($suitabilityPercentage)
        ];
    }

    /**
     * Get recommended crops based on soil conditions
     */
    private function getRecommendedCrops($report) {
        $ph = floatval($report['ph_value']);
        $organicMatter = floatval($report['organic_matter']);
        $nitrogen = floatval($report['nitrogen_level']);
        $phosphorus = floatval($report['phosphorus_level']);
        $potassium = floatval($report['potassium_level']);

        $recommendations = [];

        // Crop recommendations based on soil conditions
        $cropDatabase = [
            'Tomatoes' => ['ph_min' => 6.0, 'ph_max' => 7.0, 'organic_min' => 2.5, 'nitrogen_min' => 20],
            'Carrots' => ['ph_min' => 6.0, 'ph_max' => 7.0, 'organic_min' => 2.0, 'nitrogen_min' => 15],
            'Lettuce' => ['ph_min' => 6.0, 'ph_max' => 7.0, 'organic_min' => 3.0, 'nitrogen_min' => 25],
            'Potatoes' => ['ph_min' => 5.5, 'ph_max' => 6.5, 'organic_min' => 2.0, 'nitrogen_min' => 18],
            'Onions' => ['ph_min' => 6.0, 'ph_max' => 7.5, 'organic_min' => 2.5, 'nitrogen_min' => 20],
            'Cabbage' => ['ph_min' => 6.0, 'ph_max' => 7.5, 'organic_min' => 3.0, 'nitrogen_min' => 25],
            'Beans' => ['ph_min' => 6.0, 'ph_max' => 7.5, 'organic_min' => 2.0, 'nitrogen_min' => 10],
            'Spinach' => ['ph_min' => 6.5, 'ph_max' => 7.5, 'organic_min' => 3.0, 'nitrogen_min' => 20],
            'Broccoli' => ['ph_min' => 6.0, 'ph_max' => 7.0, 'organic_min' => 3.0, 'nitrogen_min' => 25],
            'Peppers' => ['ph_min' => 6.0, 'ph_max' => 7.0, 'organic_min' => 2.5, 'nitrogen_min' => 20]
        ];

        foreach ($cropDatabase as $crop => $requirements) {
            if ($ph >= $requirements['ph_min'] && $ph <= $requirements['ph_max'] &&
                $organicMatter >= $requirements['organic_min'] && $nitrogen >= $requirements['nitrogen_min']) {
                $recommendations[] = [
                    'crop_name' => $crop,
                    'suitability' => 'High',
                    'reason' => 'All soil parameters meet optimal requirements'
                ];
            } elseif ($ph >= ($requirements['ph_min'] - 0.3) && $ph <= ($requirements['ph_max'] + 0.3) &&
                     $organicMatter >= ($requirements['organic_min'] * 0.8)) {
                $recommendations[] = [
                    'crop_name' => $crop,
                    'suitability' => 'Moderate',
                    'reason' => 'Soil conditions are acceptable with minor amendments needed'
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Generate conclusion text based on analysis
     */
    private function generateConclusionText($suitability, $cropRecommendations) {
        $score = $suitability['score'];
        
        if ($score >= 80) {
            $text = "🌱 **Excellent for Organic Farming!**\n\n";
            $text .= "Your land shows exceptional potential for organic farming with a suitability score of {$score}%. ";
            $text .= "The soil conditions are ideal for sustainable agriculture practices.\n\n";
            $text .= "**Key Strengths:**\n";
            foreach ($suitability['analysis'] as $parameter => $analysis) {
                if (strpos($analysis, 'Excellent') !== false) {
                    $text .= "✓ " . ucfirst(str_replace('_', ' ', $parameter)) . ": " . $analysis . "\n";
                }
            }
        } elseif ($score >= 60) {
            $text = "🌾 **Good for Organic Farming**\n\n";
            $text .= "Your land is suitable for organic farming with a score of {$score}%. ";
            $text .= "With some improvements, you can achieve excellent results.\n\n";
            $text .= "**Areas for Improvement:**\n";
            foreach ($suitability['analysis'] as $parameter => $analysis) {
                if (strpos($analysis, 'Fair') !== false || strpos($analysis, 'Good') !== false) {
                    $text .= "• " . ucfirst(str_replace('_', ' ', $parameter)) . ": " . $analysis . "\n";
                }
            }
        } else {
            $text = "⚠️ **Needs Improvement for Organic Farming**\n\n";
            $text .= "Your land currently has a suitability score of {$score}%. ";
            $text .= "While organic farming is still possible, significant soil improvements are recommended.\n\n";
            $text .= "**Priority Improvements:**\n";
            foreach ($suitability['analysis'] as $parameter => $analysis) {
                if (strpos($analysis, 'Poor') !== false || strpos($analysis, 'Fair') !== false) {
                    $text .= "⚡ " . ucfirst(str_replace('_', ' ', $parameter)) . ": " . $analysis . "\n";
                }
            }
        }

        if (!empty($cropRecommendations)) {
            $text .= "\n**Recommended Crops:**\n";
            foreach (array_slice($cropRecommendations, 0, 5) as $crop) {
                $text .= "🌱 {$crop['crop_name']} - {$crop['suitability']} suitability\n";
            }
        }

        return $text;
    }

    /**
     * Get suitability grade based on percentage
     */
    private function getSuitabilityGrade($percentage) {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        if ($percentage >= 30) return 'D+';
        if ($percentage >= 20) return 'D';
        return 'F';
    }

    /**
     * Create proposal request for suitable land
     */
    public function createProposalRequest($reportId, $userId) {
        try {
            $report = $this->getReportById($reportId);
            if (!report) {
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
     * Get available supervisors (Field Supervisors who are not currently assigned to pending reports)
     * Supervisors are considered available when:
     * 1. They have no assignments, OR
     * 2. All their assignments have status 'Approved', 'Rejected', or 'Completed'
     */
    public function getAvailableSupervisors() {
        try {
            // Get supervisors who are not currently assigned to any pending land report
            // Only users with user_role = 'Field Supervisor' and are active
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
                    WHERE u.user_role = 'Field Supervisor' 
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
}

?>