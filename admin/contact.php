<?php
/**
 * Contact/Help Page
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 */

require_once __DIR__ . '/includes/header.php';
?>

<div class="detail-card" style="max-width: 600px; margin: 40px auto; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
    <div class="detail-card-header">
        <h3><i class="fas fa-headset"></i> Need Help?</h3>
    </div>
    <div class="detail-card-body" style="text-align: center; padding: 40px 20px;">
        <i class="fas fa-question-circle"
            style="font-size: 4rem; color: var(--primary-color, #2563eb); margin-bottom: 20px;"></i>
        <h4 style="margin-bottom: 15px; font-size: 1.5rem;">ICT Helpdesk Support</h4>
        <p style="margin-bottom: 30px; color: var(--text-secondary, #64748b); line-height: 1.6;">
            If you are experiencing technical difficulties or have any questions about the system, please reach out to
            our ICT Helpdesk by clicking the button below. You will be redirected to our support portal.
        </p>
        <a href="https://192.168.11.1/icthelpdesk/login.php" target="_blank" class="btn btn-primary"
            style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; font-size: 1.1rem; text-decoration: none;">
            <i class="fas fa-external-link-alt"></i> Connect with Us
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
