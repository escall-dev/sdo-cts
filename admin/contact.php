<?php
/**
 * Contact/Help Page
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 */

require_once __DIR__ . '/includes/header.php';
?>

<style>
.contact-grid {
    max-width: 1040px;
    width: 100%;
    margin: 40px auto;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.contact-card {
    border: 1px solid #d8dee8;
    border-radius: 10px;
    background: #ffffff;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.contact-card .detail-card-header {
    padding: 11px 14px;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
}

.contact-card .detail-card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}

.contact-body {
    text-align: center;
    padding: 40px 20px;
}

.contact-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.contact-title {
    margin: 0 0 15px;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.25;
}

.contact-desc {
    margin: 0 0 24px;
    color: var(--text-secondary, #64748b);
    line-height: 1.6;
    font-size: 1.05rem;
}

.contact-btn {
    width: auto;
    min-width: 158px;
    justify-content: center;
    text-decoration: none;
    padding: 12px 24px;
    font-size: 1.05rem;
}

.contact-btn.survey {
    background: #10b981;
    color: #ffffff;
    border: none;
}

.mobile-only {
    display: none;
}

.desktop-only {
    display: inline;
}

@media (max-width: 992px) {
    .contact-grid {
        max-width: 100%;
        gap: 12px;
        margin: 26px auto;
    }

    .contact-title {
        font-size: 1.35rem;
    }

    .contact-desc {
        font-size: 1rem;
    }

    .contact-btn {
        width: 100%;
        font-size: 0.95rem;
        min-width: 0;
    }
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin: 16px auto;
    }

    .contact-card .detail-card-header {
        padding: 10px;
    }

    .contact-card .detail-card-header h3 {
        font-size: 0.95rem;
    }

    .contact-body {
        padding: 14px 10px 10px;
    }

    .contact-icon {
        font-size: 1.85rem;
        margin-bottom: 10px;
    }

    .contact-title {
        font-size: 1.15rem;
        margin-bottom: 6px;
    }

    .contact-desc {
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .contact-btn {
        padding: 9px 8px;
        font-size: 0.88rem;
        border-radius: 10px;
    }

    .mobile-only {
        display: inline;
    }

    .desktop-only {
        display: none;
    }
}

@media (max-width: 400px) {
    .contact-title {
        font-size: 1.05rem;
    }

    .contact-desc {
        font-size: 0.84rem;
    }
}
</style>

<div class="contact-grid">
    <div class="detail-card contact-card">
        <div class="detail-card-header">
            <h3><i class="fas fa-headset"></i> Need Help?</h3>
        </div>
        <div class="detail-card-body contact-body">
            <i class="fas fa-question-circle contact-icon" style="color: #1b4a9a;"></i>
            <h4 class="contact-title">
                <span class="desktop-only">ICT Helpdesk Support</span>
                <span class="mobile-only">ICT Helpdesk</span>
            </h4>
            <p class="contact-desc">
                For technical difficulties and system concerns, connect with our ICT Helpdesk through the support portal.
            </p>
            <a href="https://wfh-sdospc.com/ICTHelpdesk-Online/login.php" target="_blank" rel="noopener noreferrer" class="btn btn-primary contact-btn">
                <i class="fas fa-external-link-alt"></i>
                <span class="desktop-only">Connect with Us</span>
                <span class="mobile-only">Connect</span>
            </a>
        </div>
    </div>

    <div class="detail-card contact-card">
        <div class="detail-card-header">
            <h3>
                <i class="fas fa-star"></i>
                <span class="desktop-only">Client Satisfaction</span>
                <span class="mobile-only">Survey</span>
            </h3>
        </div>
        <div class="detail-card-body contact-body">
            <i class="fas fa-star contact-icon" style="color: #10b981;"></i>
            <h4 class="contact-title">
                <span class="desktop-only">Client Satisfaction Measurement</span>
                <span class="mobile-only">Client Survey</span>
            </h4>
            <p class="contact-desc">
                Your feedback helps us improve the LDP Passbook System. Please share your experience through our survey.
            </p>
            <a href="https://wfh-sdospc.com/csm/csm.php" target="_blank" rel="noopener noreferrer" class="btn contact-btn survey">
                <i class="fas fa-clipboard-check"></i>
                <span class="desktop-only">Take the Survey</span>
                <span class="mobile-only">Survey</span>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
