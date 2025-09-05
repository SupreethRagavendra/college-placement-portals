# 📧 Email Verification Setup Guide

## 🚀 Quick Fix - Use Free SMTP Services

### Option 1: Gmail SMTP (Recommended)
1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password**:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
3. **Update .env file**:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-16-character-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="your-email@gmail.com"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

### Option 2: Outlook/Hotmail SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@outlook.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Option 3: Yahoo SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yahoo.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@yahoo.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Option 4: Free SMTP Services
- **Mailtrap** (for testing): https://mailtrap.io
- **SendGrid** (free tier): https://sendgrid.com
- **Mailgun** (free tier): https://mailgun.com

## 🔧 After Updating .env

1. **Clear Laravel config cache**:
   ```bash
   & "C:\xampp\php\php.exe" artisan config:clear
   ```

2. **Test email sending**:
   ```bash
   & "C:\xampp\php\php.exe" artisan tinker
   ```
   Then in tinker:
   ```php
   Mail::raw('Test email', function($message) {
       $message->to('test@example.com')->subject('Test');
   });
   ```

3. **Start the server**:
   ```bash
   & "C:\xampp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
   ```

## 🎯 Quick Test Setup (No SMTP Required)

If you want to test without setting up SMTP, use the log driver:

```env
MAIL_MAILER=log
```

Then check `storage/logs/laravel.log` for email content.

## 🚨 Common Issues

1. **"Connection could not be established"**: Check SMTP credentials and port
2. **"Authentication failed"**: Use app password, not regular password
3. **"SSL/TLS error"**: Try different encryption (tls/ssl) or port (587/465)

## 📝 Current Configuration

Your current .env is set to Gmail SMTP. Update these values:
- `MAIL_USERNAME`: Your Gmail address
- `MAIL_PASSWORD`: Your Gmail app password
- `MAIL_FROM_ADDRESS`: Your Gmail address

---

**Need help?** Check the Laravel logs in `storage/logs/laravel.log` for detailed error messages.

