<?php
// Simple log viewer to see the reset password debug messages
$logFile = 'C:\xampp\php\logs\php_error_log'; // Default XAMPP location

// Alternative locations to try
$possibleLogFiles = [
    'C:\xampp\php\logs\php_error_log',
    'C:\xampp\apache\logs\error.log',
    'C:\xampp\logs\php_error_log',
    ini_get('error_log')
];

echo "<h2>Recent PHP Error Logs (Last 50 lines)</h2>";

foreach ($possibleLogFiles as $logPath) {
    if (file_exists($logPath)) {
        echo "<h3>Log file: $logPath</h3>";
        echo "<pre style='background:#f5f5f5; padding:10px; max-height:400px; overflow:auto;'>";
        
        $lines = file($logPath);
        if ($lines) {
            // Get last 50 lines
            $recentLines = array_slice($lines, -50);
            foreach ($recentLines as $line) {
                // Highlight lines containing "Reset Password"
                if (strpos($line, 'Reset Password') !== false) {
                    echo "<strong style='color:red;'>$line</strong>";
                } else {
                    echo htmlspecialchars($line);
                }
            }
        }
        
        echo "</pre><hr>";
        break; // Only show the first existing log file
    }
}

if (!file_exists($logFile)) {
    echo "<p>PHP error log not found. Try checking your XAMPP control panel for log locations.</p>";
}
?>