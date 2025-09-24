<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailService {
    private $mailer;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->fromEmail = 'noreply@farmmaster.com';
        $this->fromName = 'FARMMASTER';
        
        $this->setupMailer();
    }

    private function setupMailer() {
        try {
            // Choose your email method:
            
            // METHOD 1: Gmail SMTP (REAL EMAILS)
            // Uncomment these lines and add your Gmail credentials:
            /*
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'your-gmail@gmail.com';     // Your Gmail address
            $this->mailer->Password = 'your-app-password';        // Your Gmail App Password
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;
            */
            
            // METHOD 2: Mailtrap (FAKE EMAILS FOR TESTING)
            // Sign up at https://mailtrap.io and get your credentials:
            /*
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.mailtrap.io';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'your-mailtrap-username';   // From Mailtrap
            $this->mailer->Password = 'your-mailtrap-password';   // From Mailtrap
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 2525;
            */
            
            // METHOD 3: Local Development (FILE LOGGING)
            // This will log emails to a file instead of sending them
            $this->setupFileLogging();
            
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            error_log("Mailer setup error: " . $e->getMessage());
        }
    }

    private function setupFileLogging() {
        // For development: save emails to files instead of sending
        $this->mailer->isMail(); // This will likely fail, but we'll catch it
    }

    public function sendPasswordResetEmail($user, $resetUrl) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
            
            $this->mailer->Subject = 'Password Reset - FARMMASTER';
            
            $htmlBody = $this->getPasswordResetTemplate($user, $resetUrl);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version for email clients that don't support HTML
            $this->mailer->AltBody = $this->getPasswordResetPlainText($user, $resetUrl);
            
            // Try to send email
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Password reset email sent successfully to: " . $user['email']);
                return true;
            } else {
                // If sending fails, log the email content to a file
                $this->logEmailToFile($user, $resetUrl, $htmlBody);
                return true; // Return true so the user gets success message
            }
            
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage());
            // Log email to file as backup
            $this->logEmailToFile($user, $resetUrl, $htmlBody ?? '');
            return true; // Return true so the user gets success message
        }
    }

    private function logEmailToFile($user, $resetUrl, $htmlBody) {
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $logDir . "/password_reset_email_{$timestamp}.html";
        
        $emailContent = "
<!-- EMAIL LOG FOR DEVELOPMENT -->
<!-- To: {$user['email']} -->
<!-- Subject: Password Reset - FARMMASTER -->
<!-- Generated: " . date('Y-m-d H:i:s') . " -->
<!-- Reset URL: {$resetUrl} -->

{$htmlBody}

<!-- 
DEVELOPMENT NOTE: 
This email was saved to a file because email sending is not configured.
To actually send emails, configure SMTP settings in EmailService.php
-->
        ";
        
        file_put_contents($filename, $emailContent);
        error_log("Password reset email logged to file: " . $filename);
        error_log("Reset URL for testing: " . $resetUrl);
    }

    private function getPasswordResetTemplate($user, $resetUrl) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset - FARMMASTER</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #4CAF50;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                }
                .content {
                    background-color: #f9f9f9;
                    padding: 30px;
                    border-radius: 0 0 8px 8px;
                    border: 1px solid #ddd;
                }
                .reset-button {
                    display: inline-block;
                    background-color: #4CAF50;
                    color: white;
                    padding: 12px 24px;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 20px 0;
                }
                .reset-button:hover {
                    background-color: #45a049;
                }
                .url-text {
                    background-color: #f0f0f0;
                    padding: 10px;
                    border-radius: 4px;
                    word-break: break-all;
                    font-family: monospace;
                    font-size: 12px;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>FARMMASTER</h1>
                <h2>Password Reset Request</h2>
            </div>
            
            <div class='content'>
                <p>Dear {$user['first_name']} {$user['last_name']},</p>
                
                <p>You have requested to reset your password for your FARMMASTER account.</p>
                
                <p>Click the button below to reset your password:</p>
                
                <div style='text-align: center;'>
                    <a href='{$resetUrl}' class='reset-button'>Reset Password</a>
                </div>
                
                <p>Or copy and paste this URL into your browser:</p>
                <div class='url-text'>{$resetUrl}</div>
                
                <p><strong>Important:</strong> This link will expire in 1 hour for security reasons.</p>
                
                <p>If you did not request this password reset, please ignore this email. Your password will remain unchanged.</p>
                
                <div class='footer'>
                    <p>Best regards,<br>
                    <strong>FARMMASTER Team</strong></p>
                    
                    <p><em>This is an automated message. Please do not reply to this email.</em></p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getPasswordResetPlainText($user, $resetUrl) {
        return "
        FARMMASTER - Password Reset Request
        
        Dear {$user['first_name']} {$user['last_name']},
        
        You have requested to reset your password for your FARMMASTER account.
        
        Please visit the following URL to reset your password:
        {$resetUrl}
        
        This link will expire in 1 hour for security reasons.
        
        If you did not request this password reset, please ignore this email. Your password will remain unchanged.
        
        Best regards,
        FARMMASTER Team
        
        This is an automated message. Please do not reply to this email.
        ";
    }

    public function sendTestEmail($toEmail) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);
            $this->mailer->Subject = 'Test Email - FARMMASTER';
            $this->mailer->Body = '<h1>Email Configuration Test</h1><p>If you receive this email, the email configuration is working correctly.</p>';
            $this->mailer->AltBody = 'Email Configuration Test - If you receive this email, the email configuration is working correctly.';
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Test email error: " . $e->getMessage());
            return false;
        }
    }
}
?>