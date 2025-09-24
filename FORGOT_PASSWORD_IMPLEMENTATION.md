# FARMMASTER Forgot Password Feature - Testing Guide

## Overview
The forgot password feature has been successfully implemented with the following components:

### Backend Components
1. **PasswordResetModel** - Handles password reset token storage and validation
2. **EmailService** - Sends formatted password reset emails using PHPMailer
3. **UserController** - Added `forgotPassword()` and `resetPassword()` methods
4. **API Routes** - Added endpoints:
   - `POST /api/users/forgot-password` - Request password reset
   - `POST /api/users/reset-password` - Reset password with token

### Frontend Components
1. **ForgotPassword.jsx** - Form to request password reset
2. **ResetPassword.jsx** - Form to set new password using token
3. **Updated Login.jsx** - Added "Forgot your password?" link
4. **Updated App.jsx** - Added routes for the new components

## Testing Steps

### 1. Start the Development Servers
```bash
# Start backend (XAMPP with Apache and MySQL running)
# Navigate to: http://localhost/FARMMASTER-Backend/

# Start frontend
cd "C:\Users\User 01\Documents\GitHub\FARMMASTER"
npm run dev
```

### 2. Test the Frontend Integration
1. Go to `http://localhost:5173/login`
2. Verify you can see the "Forgot your password?" link
3. Click the link - should navigate to `/forgot-password`
4. Enter an existing user email and submit
5. Check that the success message appears

### 3. Test Backend API Endpoints

#### Test Forgot Password Request:
```bash
curl -X POST http://localhost/FARMMASTER-Backend/api/users/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"lo@gmail.com"}'
```

#### Test Reset Password:
```bash
curl -X POST http://localhost/FARMMASTER-Backend/api/users/reset-password \
  -H "Content-Type: application/json" \
  -d '{"token":"[TOKEN_FROM_DATABASE]","password":"NewPassword123"}'
```

### 4. Database Verification
Check the `password_resets` table to see if tokens are being created:
```sql
SELECT * FROM password_resets ORDER BY id DESC LIMIT 5;
```

### 5. Email Testing Notes
⚠️ **Important**: The email functionality requires proper mail server configuration.

**For Development Testing:**
- Emails are sent using PHP's `mail()` function
- May not work without proper SMTP configuration
- Check error logs for email sending issues
- Consider using a service like **Mailtrap** for email testing

**To configure SMTP (optional):**
Edit `services/EmailService.php` and uncomment the SMTP configuration lines:
```php
$this->mailer->isSMTP();
$this->mailer->Host = 'smtp.gmail.com';
$this->mailer->SMTPAuth = true;
$this->mailer->Username = 'your-email@gmail.com';
$this->mailer->Password = 'your-app-password';
$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$this->mailer->Port = 587;
```

## Security Features Implemented

1. **Token Expiration** - Tokens expire after 1 hour
2. **Rate Limiting** - Prevents multiple reset requests within 5 minutes
3. **Secure Tokens** - Uses cryptographically secure random tokens
4. **Password Validation** - Enforces strong password requirements
5. **Token Cleanup** - Tokens are deleted after use
6. **Email Privacy** - Doesn't reveal if email exists in system

## Troubleshooting

### Common Issues:
1. **Email not sending**: Check PHP mail configuration or use SMTP
2. **Token not found**: Verify token hasn't expired or been used
3. **CORS issues**: Ensure frontend URL is allowed in backend
4. **Database errors**: Verify `password_resets` table exists

### Check Error Logs:
- Backend errors: Check Apache error logs
- Frontend errors: Check browser console
- Email errors: Check PHP error logs

## Files Modified/Created:

### Backend:
- `models/PasswordResetModel.php` (NEW)
- `services/EmailService.php` (NEW)
- `controllers/UserController.php` (MODIFIED - added methods)
- `api.php` (routes already existed)

### Frontend:
- `src/pages/ForgotPassword.jsx` (NEW)
- `src/pages/ResetPassword.jsx` (NEW)
- `src/pages/Login.jsx` (MODIFIED - added link)
- `src/App.jsx` (MODIFIED - added routes)

## Next Steps:
1. Test the complete flow with a real email service
2. Customize email templates if needed
3. Add email configuration to environment variables
4. Consider adding password strength indicators
5. Add proper logging for audit purposes