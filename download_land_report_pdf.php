<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get report_id from query parameter
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

if (!$report_id) {
    header("HTTP/1.1 400 Bad Request");
    echo "No report_id provided.";
    exit;
}

try {
    // Get detailed land report information
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
                u.email,
                u.phone
            FROM land_report lr
            JOIN land l ON lr.land_id = l.land_id
            JOIN user u ON lr.user_id = u.user_id
            WHERE lr.report_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("HTTP/1.1 404 Not Found");
        echo "Report not found.";
        exit;
    }

    $report = $result->fetch_assoc();
    
    
    // Set HTML headers for browser to display/print
    header("Content-Type: text/html; charset=UTF-8");
    
    // Generate HTML content that can be printed as PDF by browser
    $html_content = generateReportHTML($report);
    
    echo $html_content;
    
} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Failed to generate PDF: " . $e->getMessage();
}

function generateReportHTML($report) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Land Assessment Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
            .header { text-align: center; border-bottom: 2px solid #22c55e; padding-bottom: 20px; margin-bottom: 30px; }
            .title { color: #22c55e; font-size: 28px; font-weight: bold; margin-bottom: 10px; }
            .subtitle { color: #666; font-size: 14px; }
            .section { margin-bottom: 25px; }
            .section-title { color: #22c55e; font-size: 18px; font-weight: bold; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
            .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .info-table th, .info-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            .info-table th { background-color: #f9fafb; font-weight: bold; color: #374151; }
            .status-approved { color: #22c55e; font-weight: bold; }
            .status-pending { color: #f59e0b; font-weight: bold; }
            .status-rejected { color: #ef4444; font-weight: bold; }
            .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #666; font-size: 12px; }
            .print-button { background-color: #22c55e; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 20px 0; }
            .print-button:hover { background-color: #16a34a; }
            @media print {
                .no-print { display: none !important; }
                body { margin: 0; }
            }
        </style>
    </head>
    <body>
        <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 15px; background-color: #f0f9f0; border: 1px solid #22c55e; border-radius: 6px;">
            <p style="margin: 0 0 10px 0; color: #16a34a; font-weight: bold;">Land Assessment Report - Ready for Print/Save</p>
            <button class="print-button" onclick="window.print()">🖨️ Print Report</button>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">Click Print button above, then choose Save as PDF in your browser print dialog</p>
        </div>
        
        <div class="header">
            <div class="title">Farm Master - Land Assessment Report</div>
            <div class="subtitle">Report ID: ' . $report['report_id'] . ' | Generated on: ' . date('F j, Y', strtotime($report['report_date'])) . '</div>
        </div>

        <div class="section">
            <div class="section-title">Landowner Information</div>
            <table class="info-table">
                <tr><th>Name:</th><td>' . htmlspecialchars($report['first_name'] . ' ' . $report['last_name']) . '</td></tr>
                <tr><th>Email:</th><td>' . htmlspecialchars($report['email']) . '</td></tr>
                <tr><th>Phone:</th><td>' . htmlspecialchars($report['phone']) . '</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Land Information</div>
            <table class="info-table">
                <tr><th>Location:</th><td>' . htmlspecialchars($report['location']) . '</td></tr>
                <tr><th>Size:</th><td>' . htmlspecialchars($report['size']) . ' acres</td></tr>
                <tr><th>Payment Status:</th><td>' . htmlspecialchars($report['payment_status']) . '</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Soil Analysis Results</div>
            <table class="info-table">
                <tr><th>pH Value:</th><td>' . ($report['ph_value'] ? number_format($report['ph_value'], 1) : 'Not tested') . '</td></tr>
                <tr><th>Organic Matter:</th><td>' . ($report['organic_matter'] ? number_format($report['organic_matter'], 2) . '%' : 'Not tested') . '</td></tr>
                <tr><th>Nitrogen Level:</th><td>' . ($report['nitrogen_level'] ? htmlspecialchars($report['nitrogen_level']) : 'Not tested') . '</td></tr>
                <tr><th>Phosphorus Level:</th><td>' . ($report['phosphorus_level'] ? htmlspecialchars($report['phosphorus_level']) : 'Not tested') . '</td></tr>
                <tr><th>Potassium Level:</th><td>' . ($report['potassium_level'] ? htmlspecialchars($report['potassium_level']) : 'Not tested') . '</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Environmental Assessment</div>
            <p>' . ($report['environmental_notes'] ? nl2br(htmlspecialchars($report['environmental_notes'])) : 'No environmental notes recorded.') . '</p>
        </div>

        <div class="section">
            <div class="section-title">Assessment Details</div>
            <table class="info-table">
                <tr><th>Report Status:</th><td><span class="status-' . strtolower($report['status']) . '">' . htmlspecialchars($report['status']) . '</span></td></tr>
                <tr><th>Land Description:</th><td>' . htmlspecialchars($report['land_description']) . '</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Crop Recommendations</div>
            <p>' . nl2br(htmlspecialchars($report['crop_recomendation'])) . '</p>
        </div>

        <div class="footer">
            <p>This report was generated by Farm Master Assessment System</p>
            <p>For questions about this report, please contact our support team</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

$conn->close();
?>
