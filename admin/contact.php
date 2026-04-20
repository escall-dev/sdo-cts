<?php
/**
 * Contact/Help Page
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 */

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px;">
    <div class="detail-card" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0;">
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
            <a href="https://wfh-sdospc.com/ICTHelpdesk-Online/login.php" target="_blank" rel="noopener noreferrer" class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; font-size: 1.1rem; text-decoration: none;">
                <i class="fas fa-external-link-alt"></i> Connect with Us
            </a>
        </div>
    </div>

    <div class="detail-card" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0;">
        <div class="detail-card-header">
            <h3><i class="fas fa-star"></i> Client Satisfaction</h3>
        </div>
        <div class="detail-card-body" style="text-align: center; padding: 40px 20px;">
            <i class="fas fa-star"
                style="font-size: 4rem; color: #10b981; margin-bottom: 20px;"></i>
            <h4 style="margin-bottom: 15px; font-size: 1.5rem;">Client Satisfaction Measurement</h4>
            <p style="margin-bottom: 30px; color: var(--text-secondary, #64748b); line-height: 1.6;">
                Your feedback is vital for our continuous improvement. Please take a moment to share your experience with
                the LDP Passbook System through our survey.
            </p>
            <a href="https://wfh-sdospc.com/csm/csm.php" target="_blank" rel="noopener noreferrer" class="btn"
                style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; font-size: 1.1rem; text-decoration: none; background: #10b981; color: #ffffff; border-radius: 8px;">
                <i class="fas fa-clipboard-check"></i> Take the Survey
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
