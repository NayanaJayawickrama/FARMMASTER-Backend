<?php
// Email Configuration for FarmMaster
// Copy this file to email_config.php and fill in your email credentials

return [
    // SMTP Configuration
    'smtp_host' => 'smtp.gmail.com',  // Gmail SMTP (change if using different provider)
    'smtp_port' => 587,               // Port for STARTTLS
    'smtp_auth' => true,
    'smtp_secure' => 'tls',           // 'tls' or 'ssl'
    
    // Email Credentials (FILL THESE IN)
    'smtp_username' => '',            // Your email address (e.g., 'your-email@gmail.com')
    'smtp_password' => '',            // Your app password (NOT your regular password)
    
    // Email Addresses
    'from_email' => '',               // Same as smtp_username usually
    'from_name' => 'FarmMaster Team',
    'reply_to' => '',                 // Optional: different reply-to address
    
    // Other Email Providers Examples:
    /*
    // For Outlook/Hotmail:
    'smtp_host' => 'smtp.live.com',
    'smtp_port' => 587,
    
    // For Yahoo:
    'smtp_host' => 'smtp.mail.yahoo.com',
    'smtp_port' => 587,
    
    // For custom SMTP (like hosting provider):
    'smtp_host' => 'mail.yourdomain.com',
    'smtp_port' => 587,
    */
];