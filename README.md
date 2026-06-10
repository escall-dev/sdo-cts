# SDO CTS - San Pedro Division Office Complaint Tracking System

A PHP/MySQL-based complaint tracking system for the Department of Education - San Pedro Division Office. This system digitizes the official DepEd Complaint Assisted Form and provides a structured workflow for intake, review, and tracking of complaints.

## Features

- **Digital Complaint Form** - Google Form-style layout following the official DepEd Complaint Assisted Form
- **Document Upload** - Support for PDF, JPG, and PNG file attachments
- **Digital Signature** - Both typed and hand-drawn signature options
- **Review System** - Preview submission in official form layout before confirming
- **Complaint Tracking** - Track complaint status using reference number and email
- **Status History** - Complete timeline of complaint status changes

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP recommended)

## Installation

1. **Clone or copy files** to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\CTS\
   ```

2. **Start XAMPP** services (Apache and MySQL)

3. **Run the installation script**:
   - Open your browser and navigate to:
   ```
   http://localhost/CTS/install.php
   ```
   - This will create the database, tables, and upload directories

4. **Access the system**:
   ```
   http://localhost/CTS/
   ```

## File Structure

```
CTS/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── form.js            # Form validation & interactions
├── config/
│   └── database.php           # Database configuration
├── database/
│   └── schema.sql             # Database schema
├── models/
│   └── Complaint.php          # Complaint model class
├── uploads/
│   ├── temp/                  # Temporary file uploads
│   └── complaints/            # Permanent complaint documents
├── index.php                  # Main complaint form
├── review.php                 # Review submission page
├── success.php                # Submission success page
├── track.php                  # Track complaint status
├── install.php                # Installation script
└── README.md
```

## Database Configuration

Default settings (can be modified in `config/database.php`):
- **Host**: localhost
- **Database**: sdo_cts
- **User**: root
- **Password**: (empty)

## Usage

### Filing a Complaint
1. Navigate to the main page
2. Fill in all required fields
3. Upload supporting documents (optional)
4. Agree to the certification
5. Provide signature
6. Click "Review Submission"
7. Verify all information
8. Click "Confirm & Submit"
9. Save your reference number

### Tracking a Complaint
1. Click "Track Complaint" in the navigation
2. Enter your reference number and email
3. View current status and history

## Complaint Workflow

1. **Pending** - Initial submission received
2. **Under Review** - Being reviewed by assigned office
3. **In Progress** - Action is being taken
4. **Resolved** - Complaint has been addressed
5. **Closed** - Case is closed

## Security Features

- Input validation and sanitization
- Prepared statements for database queries
- File type and size restrictions
- Upload directory protection
- Session-based data handling

## Email Notifications

The system includes email notifications using PHPMailer with SMTP. Notifications are sent:
- **When a complaint is submitted** - Email sent to complainant and admin
- **When a complaint is resolved** - Email sent to complainant

### Email Configuration

Configure email settings via environment variables in the `.env` file:

```env
# Enable/disable email notifications
MAIL_ENABLED=true

# SMTP Server Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_AUTH=true
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password

# Sender Information
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME=SDO CTS - San Pedro Division Office

# Admin notification recipients (comma-separated)
ADMIN_EMAIL_RECIPIENTS=admin@your-domain.com,supervisor@your-domain.com

# System Base URL (for links in emails)
SYSTEM_BASE_URL=http://localhost/CTS
```

### Gmail/Google Workspace Setup
1. Enable 2-Factor Authentication on your Google Account
2. Generate an App Password at https://myaccount.google.com/apppasswords
3. Use the App Password as `SMTP_PASSWORD`

### Features
- Duplicate email prevention (same event won't trigger multiple emails)
- Email logging with success/failure status
- Non-blocking (email failures don't interrupt complaint processing)
- Customizable HTML email templates in `services/email/templates/`

### Email Logs Migration
Run the migration to create the email_logs table:
```
http://localhost/CTS/migrate_email_logs.php
```

```

Notes:
- `.env` is ignored by git (see `.gitignore`).
- You can also set these as system environment variables in Windows.
- The application reads env vars in `config/admin_config.php` using a lightweight `.env` loader.

## License

This project is developed for the Department of Education - San Pedro Division Office.

---

**SDO CTS** - Department of Education, San Pedro Division


Developers: Alexander Joerenz Escallente & Redgine Pinedes