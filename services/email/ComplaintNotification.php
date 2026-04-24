<?php
/**
 * Complaint Email Notifications
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 * 
 * Handles specific email notifications for complaint events
 */

require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/../../config/admin_config.php';

class ComplaintNotification {
    private $emailService;

    public function __construct() {
        // Set timezone to Manila, Philippines for accurate timestamps
        date_default_timezone_set('Asia/Manila');
        $this->emailService = new EmailService();
    }

    /**
     * Set custom sender email address
     */
    public function setFromAddress($email, $name = null) {
        return $this->emailService->setFrom($email, $name);
    }

    /**
     * Send notification when complaint is submitted
     * Notifies both complainant and admin
     * Includes all uploaded documents as attachments for the complainant
     */
    public function sendComplaintSubmittedNotification($complaintData) {
        // Validate required complainant information (existing form fields)
        $complainantName = $complaintData['name_pangalan'] ?? null;
        $complainantEmail = $complaintData['email_address'] ?? null;
        $complainantContact = $complaintData['contact_number'] ?? null;
        
        // Check if any required field is missing
        if (empty($complainantName) || empty($complainantEmail) || empty($complainantContact)) {
            error_log("Email notification skipped: Missing required complainant information (name, email, or contact)");
            return false;
        }
        
        $referenceNumber = $complaintData['reference_number'];
        $submittedDate = date('F j, Y \a\t g:i A');
        $complaintId = $complaintData['id'] ?? null;

        // Gather all uploaded documents for attachments
        $attachments = $this->getComplaintAttachments($complaintId);

        // Send to complainant with attachments
        $this->sendToComplainant($complainantEmail, $complainantName, $referenceNumber, $submittedDate, $complaintId, $complaintData, $attachments);

        // Send to admin/assigned office (without attachments for security)
        $this->sendToAdmin($complaintData, $submittedDate);

        return true;
    }

    /**
     * Get all attachments for a complaint
     * Returns array of attachment info with paths and names
     */
    private function getComplaintAttachments($complaintId) {
        $attachments = [];
        
        if (!$complaintId) {
            return $attachments;
        }

        // Base directory for centralized uploads
        $baseDir = dirname(dirname(__DIR__)) . '/';
        
        // Try to get document info from database
        try {
            require_once dirname(dirname(__DIR__)) . '/config/database.php';
            $db = Database::getInstance();
            $sql = "SELECT file_name, file_path, original_name, category FROM complaint_documents WHERE complaint_id = ? ORDER BY upload_date ASC";
            $documents = $db->query($sql, [$complaintId])->fetchAll();

            foreach ($documents as $doc) {
                // Use relative path from file_path column, fallback to old structure
                if (!empty($doc['file_path'])) {
                    $filePath = $baseDir . $doc['file_path'];
                } else {
                    // Fallback for old records
                    $filePath = $baseDir . 'uploads/complaints/' . $complaintId . '/' . $doc['file_name'];
                }
                
                if (file_exists($filePath)) {
                    // Create a descriptive name with category prefix
                    $categoryLabels = [
                        'handwritten_form' => 'Complaint Form',
                        'supporting' => 'Supporting Document',
                        'valid_id' => 'Valid ID'
                    ];
                    $categoryLabel = $categoryLabels[$doc['category']] ?? 'Document';
                    $displayName = $categoryLabel . ' - ' . $doc['original_name'];
                    
                    $attachments[] = [
                        'path' => $filePath,
                        'name' => $displayName
                    ];
                }
            }
        } catch (Exception $e) {
            // If database query fails, fallback to old directory structure
            error_log("Error fetching complaint documents: " . $e->getMessage());
            
            $uploadDir = $baseDir . 'uploads/complaints/' . $complaintId . '/';
            if (is_dir($uploadDir)) {
                $files = glob($uploadDir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $attachments[] = [
                            'path' => $file,
                            'name' => basename($file)
                        ];
                    }
                }
            }
        }

        return $attachments;
    }

    /**
     * Send notification to complainant when complaint is submitted
     * Includes all their uploaded documents as attachments
     */
    private function sendToComplainant($email, $name, $referenceNumber, $date, $complaintId, $complaintData, $attachments = []) {
        $subject = "Complaint Submitted - Reference: {$referenceNumber}";
        
        // Build attachment list for email body if there are attachments
        $attachmentListHtml = '';
        if (!empty($attachments)) {
            $attachmentListHtml = '<h3 style="color: #1b4a9a; margin-top: 25px;">Attached Documents</h3>';
            $attachmentListHtml .= '<p style="color: #475569; margin-bottom: 10px;">The following documents you provided are attached to this email for your records:</p>';
            $attachmentListHtml .= '<ul style="color: #475569; margin-left: 20px;">';
            foreach ($attachments as $attachment) {
                $docName = htmlspecialchars($attachment['name'] ?? basename($attachment['path']));
                $attachmentListHtml .= '<li>' . $docName . '</li>';
            }
            $attachmentListHtml .= '</ul>';
        }
        
        $body = $this->getEmailTemplate('complaint_submitted_complainant', [
            'name' => $name,
            'reference_number' => $referenceNumber,
            'date' => $date,
            'complaint_name' => $complaintData['name_pangalan'] ?? $name,
            'complaint_email' => $complaintData['email_address'] ?? $email,
            'complaint_contact' => $complaintData['contact_number'] ?? 'N/A',
            'tracking_url' => TRACKING_URL,
            'attachment_list' => $attachmentListHtml,
            'base_url' => SYSTEM_BASE_URL
        ]);

        // Use sendWithMultipleAttachments if there are attachments, otherwise use regular send
        if (!empty($attachments)) {
            return $this->emailService->sendWithMultipleAttachments(
                $email,
                $subject,
                $body,
                $attachments,
                'complaint_submitted_complainant',
                $complaintId
            );
        }

        return $this->emailService->send(
            $email,
            $subject,
            $body,
            'complaint_submitted_complainant',
            $complaintId
        );
    }

    /**
     * Send notification to admin when complaint is submitted
     */
    private function sendToAdmin($complaintData, $date) {
        $adminEmails = $this->getAdminEmails($complaintData['referred_to'] ?? 'OSDS');
        
        if (empty($adminEmails)) {
            return false;
        }

        $subject = "New Complaint Received - Reference: {$complaintData['reference_number']}";
        
        $body = $this->getEmailTemplate('complaint_submitted_admin', [
            'reference_number' => $complaintData['reference_number'],
            'complaint_name' => $complaintData['name_pangalan'] ?? 'N/A',
            'complaint_email' => $complaintData['email_address'] ?? 'N/A',
            'complaint_contact' => $complaintData['contact_number'] ?? 'N/A',
            'referred_to' => $complaintData['referred_to'] ?? '',
            'date' => $date,
            'complaint_preview' => $this->truncateText($complaintData['narration_complaint'] ?? '', 200),
            'admin_url' => SYSTEM_BASE_URL . '/admin/complaints.php',
            'base_url' => SYSTEM_BASE_URL
        ]);

        foreach ($adminEmails as $adminEmail) {
            $this->emailService->send(
                $adminEmail,
                $subject,
                $body,
                'complaint_submitted_admin',
                $complaintData['id'] ?? null
            );
        }

        return true;
    }

    /**
     * Send notification when complaint is resolved
     */
    public function sendComplaintResolvedNotification($complaintData, $resolutionNotes = '') {
        $referenceNumber = $complaintData['reference_number'];
        $complainantEmail = $complaintData['email_address'];
        $complainantName = $complaintData['name_pangalan'];
        $resolvedDate = date('F j, Y \a\t g:i A');
        $complaintId = $complaintData['id'] ?? null;

        $subject = "Complaint Resolved - Reference: {$referenceNumber}";
        
        $body = $this->getEmailTemplate('complaint_resolved', [
            'name' => $complainantName,
            'reference_number' => $referenceNumber,
            'date' => $resolvedDate,
            'resolution_notes' => $resolutionNotes ?: 'Your complaint has been reviewed and resolved.',
            'tracking_url' => TRACKING_URL,
            'base_url' => SYSTEM_BASE_URL
        ]);

        return $this->emailService->send(
            $complainantEmail,
            $subject,
            $body,
            'complaint_resolved',
            $complaintId
        );
    }

    /**
     * Send notification for status changes (optional)
     */
    public function sendStatusChangeNotification($complaintData, $newStatus, $notes = '') {
        $referenceNumber = $complaintData['reference_number'];
        $complainantEmail = $complaintData['email_address'];
        $complainantName = $complaintData['name_pangalan'];
        $updateDate = date('F j, Y \a\t g:i A');
        $complaintId = $complaintData['id'] ?? null;

        $statusLabel = STATUS_CONFIG[$newStatus]['label'] ?? ucfirst($newStatus);
        
        $subject = "Complaint Status Update - Reference: {$referenceNumber}";
        
        $body = $this->getEmailTemplate('status_update', [
            'name' => $complainantName,
            'reference_number' => $referenceNumber,
            'status' => $statusLabel,
            'notes' => $notes,
            'date' => $updateDate,
            'tracking_url' => TRACKING_URL,
            'base_url' => SYSTEM_BASE_URL
        ]);

        return $this->emailService->send(
            $complainantEmail,
            $subject,
            $body,
            'status_change_' . $newStatus,
            $complaintId
        );
    }

    /**
     * Get admin emails for notification
     */
    private function getAdminEmails($referredTo = null) {
        $emails = [];

        // Get from environment variable
        if (ADMIN_EMAIL_RECIPIENTS) {
            $configEmails = array_map('trim', explode(',', ADMIN_EMAIL_RECIPIENTS));
            $emails = array_merge($emails, $configEmails);
        }

        // Optionally, get from database based on unit assignment
        // This can be expanded to fetch emails based on $referredTo unit

        return array_unique(array_filter($emails));
    }

    /**
     * Get email template with variable substitution
     */
    private function getEmailTemplate($templateName, $variables = []) {
        $templatePath = EMAIL_TEMPLATES_PATH . $templateName . '.html';
        
        if (file_exists($templatePath)) {
            $template = file_get_contents($templatePath);
        } else {
            // Use default inline templates
            $template = $this->getDefaultTemplate($templateName);
        }

        // Variables that should NOT be HTML escaped (contain HTML content)
        $htmlVariables = ['attachment_list'];

        // Replace variables
        foreach ($variables as $key => $value) {
            if (in_array($key, $htmlVariables)) {
                // Don't escape HTML variables
                $template = str_replace('{{' . $key . '}}', $value ?? '', $template);
            } else {
                $template = str_replace('{{' . $key . '}}', htmlspecialchars($value ?? ''), $template);
            }
        }

        return $template;
    }

    /**
     * Default email templates (fallback)
     */
    private function getDefaultTemplate($templateName) {
        $header = $this->getEmailHeader();
        $footer = $this->getEmailFooter();

        switch ($templateName) {
            case 'complaint_submitted_complainant':
                return $header . '
                <h2 style="color: #1b4a9a; margin-bottom: 20px;">Complaint Submitted Successfully</h2>
                
                <p>Dear <strong>{{name}}</strong>,</p>
                
                <p>Your complaint has been successfully submitted to  The Schools Division Office of San Pedro City Complaint Tracking System.</p>
                
                <div style="background: #1b4a9a; border-left: 4px solid #1b4a9a; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px; color: #ffffff;"><strong>Event Type:</strong> Complaint Submission</p>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #ffffff;"><strong>Date & Time:</strong> {{date}}</p>
                    <p style="margin: 10px 0 0 0; color: #ffffff;"><strong>Reference Number:</strong></p>
                    <p style="margin: 5px 0 0 0; font-size: 24px; font-weight: bold; color: #ffffff; font-family: monospace;">{{reference_number}}</p>
                </div>
                
                <p><strong>Important:</strong> Please save this reference number. You will need it to track the status of your complaint.</p>
                
                <p>You can track your complaint status at any time by visiting:</p>
                <p><a href="{{tracking_url}}" style="color: #1b4a9a;">{{tracking_url}}</a></p>
                
                <h3 style="color: #1b4a9a; margin-top: 30px;">Complainant Information</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; width: 35%;"><strong>Name:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #fff;">{{complaint_name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; color: #64748b;"><strong>Email:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #fff;"><a href="mailto:{{complaint_email}}" style="color: #1b4a9a;">{{complaint_email}}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; color: #64748b;"><strong>Contact:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #fff;">{{complaint_contact}}</td>
                    </tr>
                </table>
                
                {{attachment_list}}
                
                <h3 style="color: #1b4a9a; margin-top: 30px;">What Happens Next?</h3>
                <ol style="color: #475569;">
                    <li>Your complaint will be reviewed by the appropriate office.</li>
                    <li>You may be contacted for additional information if needed.</li>
                    <li>You will receive email updates as your complaint progresses.</li>
                </ol>
                ' . $footer;

            case 'complaint_submitted_admin':
                return $header . '
                <h2 style="color: #1b4a9a; margin-bottom: 20px;">New Complaint Received</h2>
                
                <p>A new complaint has been submitted to the system and requires attention.</p>
                
                <div style="background: #1b4a9a; border-left: 4px solid #1b4a9a; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px; color: #ffffff;"><strong>Event Type:</strong> New Complaint Submission</p>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #ffffff;"><strong>Date & Time:</strong> {{date}}</p>
                    <p style="margin: 10px 0 0 0; color: #ffffff;"><strong>Reference Number:</strong></p>
                    <p style="margin: 5px 0 0 0; font-size: 24px; font-weight: bold; color: #ffffff; font-family: monospace;">{{reference_number}}</p>
                </div>
                
                <h3 style="color: #1b4a9a;">Complainant Information</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; width: 35%;"><strong>Name:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #fff;">{{complaint_name}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; color: #64748b;"><strong>Email:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #fff;"><a href="mailto:{{complaint_email}}" style="color: #1b4a9a;">{{complaint_email}}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; color: #64748b;"><strong>Contact:</strong></td>
                        <td style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; background: #fff;">{{complaint_contact}}</td>
                    </tr>
                </table>
                
                <h3 style="color: #1b4a9a;">Complaint Preview</h3>
                <p style="background: #1b4a9a; padding: 15px; border-radius: 6px; color: #ffffff; border: 1px solid #1b4a9a;">{{complaint_preview}}</p>
                
                <p style="margin-top: 25px; text-align: center;">
                    <a href="{{admin_url}}" style="display: inline-block; background: linear-gradient(135deg, #1b4a9a 0%, #1b4a9a 100%); color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">View in Admin Panel</a>
                </p>
                ' . $footer;

            case 'complaint_resolved':
                return $header . '
                <h2 style="color: #059669; margin-bottom: 20px;">Complaint Resolved</h2>
                
                <p>Dear <strong>{{name}}</strong>,</p>
                
                <p>We are pleased to inform you that your complaint has been <strong style="color: #059669;">resolved</strong>.</p>
                
                <div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px; color: #065f46;"><strong>Event Type:</strong> Complaint Resolved</p>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #065f46;"><strong>Date & Time:</strong> {{date}}</p>
                    <p style="margin: 10px 0 0 0;"><strong>Reference Number:</strong></p>
                    <p style="margin: 5px 0 0 0; font-size: 24px; font-weight: bold; color: #059669; font-family: monospace;">{{reference_number}}</p>
                </div>
                
                <h3 style="color: #1b4a9a;">Resolution Details</h3>
                <p style="background: #f8fafc; padding: 15px; border-radius: 4px; color: #475569;">{{resolution_notes}}</p>
                
                <p>You can view the complete details and history of your complaint at:</p>
                <p><a href="{{tracking_url}}" style="color: #1b4a9a;">{{tracking_url}}</a></p>
                
                <p style="margin-top: 20px;">Thank you for using the SDO CTS complaint tracking system. If you have any further concerns, please do not hesitate to file another complaint.</p>
                ' . $footer;

            case 'status_update':
                return $header . '
                <h2 style="color: #1b4a9a; margin-bottom: 20px;">Complaint Status Update</h2>
                
                <p>Dear <strong>{{name}}</strong>,</p>
                
                <p>The status of your complaint has been updated.</p>
                
                <div style="background: #1b4a9a; border-left: 4px solid #1b4a9a; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px; color: #ffffff;"><strong>Event Type:</strong> Status Update</p>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #ffffff;"><strong>Date & Time:</strong> {{date}}</p>
                    <p style="margin: 10px 0 0 0; color: #ffffff;"><strong>Reference Number:</strong></p>
                    <p style="margin: 5px 0 0 0; font-size: 24px; font-weight: bold; color: #ffffff; font-family: monospace;">{{reference_number}}</p>
                    <p style="margin: 15px 0 0 0; color: #ffffff;"><strong>New Status:</strong></p>
                    <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: bold; color: #ffffff;">{{status}}</p>
                </div>
                
                <h3 style="color: #1b4a9a;">Notes</h3>
                <p style="background: #f8fafc; padding: 15px; border-radius: 4px; color: #475569;">{{notes}}</p>
                
                <p>You can track the full history and status of your complaint at:</p>
                <p><a href="{{tracking_url}}" style="color: #1b4a9a;">{{tracking_url}}</a></p>
                ' . $footer;

            default:
                return $header . '<p>{{content}}</p>' . $footer;
        }
    }

    /**
     * Email header template
     */
    private function getEmailHeader() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1b4a9a 0%, #1b4a9a 100%); padding: 30px; border-radius: 8px 8px 0 0;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="80" align="left" valign="middle">
                                        <img src="cid:sdo_logo" alt="SDO Logo" width="70" height="70" style="border-radius: 50%; display: block;">
                                    </td>
                                    <td align="center" valign="middle">
                                        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">SDO CTS</h1>
                                        <p style="color: #ffffff; margin: 5px 0 0 0; font-size: 14px;">The Schools Division Office of San Pedro City<br>Complaint Tracking System</p>
                                    </td>
                                    <td width="80" align="right" valign="middle">
                                        <img src="cid:bagongpilipinas_logo" alt="Bagong Pilipinas Logo" width="70" height="70" style="display: block;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">';
    }

    /**
     * Email footer template
     */
    private function getEmailFooter() {
        $year = date('Y');
        return '
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px; border-radius: 0 0 8px 8px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #64748b; font-size: 12px;">This is an automated message from the SDO CTS.</p>
                            <p style="margin: 5px 0 0 0; color: #64748b; font-size: 12px;">Department of Education - San Pedro Division Office</p>
                            <p style="margin: 10px 0 0 0; color: #94a3b8; font-size: 11px;">© ' . $year . ' SDO CTS. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Truncate text with ellipsis
     */
    private function truncateText($text, $maxLength = 200) {
        $text = strip_tags($text);
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        return substr($text, 0, $maxLength) . '...';
    }

    /**
     * Get email service for advanced operations
     */
    public function getEmailService() {
        return $this->emailService;
    }
}
