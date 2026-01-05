<?php
/**
 * SDO CTS - Track Complaint Page
 */

session_start();
require_once __DIR__ . '/models/Complaint.php';

$complaint = null;
$documents = [];
$history = [];
$error = null;
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ref'])) {
    $searched = true;
    $referenceNumber = $_POST['reference_number'] ?? $_GET['ref'] ?? '';
    $email = $_POST['email'] ?? $_GET['email'] ?? '';
    
    if (empty($referenceNumber) || empty($email)) {
        $error = 'Please enter both your reference number and email address.';
    } else {
        try {
            $complaintModel = new Complaint();
            $complaint = $complaintModel->track($referenceNumber, $email);
            
            if ($complaint) {
                $documents = $complaintModel->getDocuments($complaint['id']);
                $history = $complaintModel->getStatusHistory($complaint['id']);
            } else {
                $error = 'No complaint found with the provided reference number and email combination.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred while searching. Please try again.';
        }
    }
}

// Status display mapping
$statusLabels = [
    'pending' => ['label' => 'Pending', 'icon' => '⏳'],
    'under_review' => ['label' => 'Under Review', 'icon' => '🔍'],
    'in_progress' => ['label' => 'In Progress', 'icon' => '⚙️'],
    'resolved' => ['label' => 'Resolved', 'icon' => '✅'],
    'closed' => ['label' => 'Closed', 'icon' => '📁']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Complaint - SDO CTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: var(--section-gap);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .status-card .ref-info h3 {
            font-size: 0.9rem;
            font-weight: 400;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }
        .status-card .ref-info .ref-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .current-status {
            text-align: right;
        }
        .current-status .label {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }
        .current-status .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
        }
        .progress-tracker {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            position: relative;
        }
        .progress-tracker::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 4px;
            background: var(--input-border);
            z-index: 0;
        }
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }
        .progress-step .step-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--input-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        .progress-step.active .step-icon,
        .progress-step.completed .step-icon {
            background: var(--success-color);
            color: white;
        }
        .progress-step.current .step-icon {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 4px rgba(26, 95, 122, 0.2);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 4px rgba(26, 95, 122, 0.2); }
            50% { box-shadow: 0 0 0 8px rgba(26, 95, 122, 0.1); }
        }
        .progress-step .step-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
        }
        .progress-step.active .step-label,
        .progress-step.current .step-label {
            color: var(--text-primary);
            font-weight: 600;
        }
        @media (max-width: 600px) {
            .status-card {
                flex-direction: column;
                text-align: center;
            }
            .current-status {
                text-align: center;
            }
            .progress-step .step-label {
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="nav-bar no-print">
            <div class="nav-links">
                <a href="index.php">📝 File Complaint</a>
                <a href="track.php" class="active">🔍 Track Complaint</a>
            </div>
        </nav>

        <!-- Header -->
        <header class="form-header">
            <h1>🔍 Track Your Complaint</h1>
            <p class="subtitle">Enter your reference number and email to view your complaint status</p>
        </header>

        <!-- Search Form -->
        <section class="form-section">
            <div class="section-header">
                <span class="section-icon">🔎</span>
                Find Your Complaint
            </div>
            <div class="section-content">
                <form method="POST" action="" class="track-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="reference_number">
                                Reference Number <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="reference_number" 
                                   name="reference_number" placeholder="e.g., CTS-2025-00001"
                                   value="<?php echo htmlspecialchars($_POST['reference_number'] ?? $_GET['ref'] ?? ''); ?>" 
                                   required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">
                                E-mail Address <span class="required">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" 
                                   name="email" placeholder="your.email@example.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? $_GET['email'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                        🔍 Track Complaint
                    </button>
                </form>
            </div>
        </section>

        <?php if ($error): ?>
        <div class="review-notice" style="background: #f8d7da; border-left-color: #dc3545; color: #721c24;">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($complaint): ?>
        <!-- Status Card -->
        <div class="status-card">
            <div class="ref-info">
                <h3>Reference Number</h3>
                <div class="ref-number"><?php echo htmlspecialchars($complaint['reference_number']); ?></div>
            </div>
            <div class="current-status">
                <div class="label">Current Status</div>
                <div class="badge">
                    <?php 
                    $status = $complaint['status'];
                    echo $statusLabels[$status]['icon'] . ' ' . $statusLabels[$status]['label'];
                    ?>
                </div>
            </div>
        </div>

        <!-- Progress Tracker -->
        <section class="form-section">
            <div class="section-header">
                <span class="section-icon">📊</span>
                Progress Tracker
            </div>
            <div class="section-content">
                <?php
                $statusOrder = ['pending', 'under_review', 'in_progress', 'resolved', 'closed'];
                $currentIndex = array_search($complaint['status'], $statusOrder);
                ?>
                <div class="progress-tracker">
                    <?php foreach ($statusOrder as $index => $step): ?>
                    <?php
                    $stepClass = '';
                    if ($index < $currentIndex) {
                        $stepClass = 'completed';
                    } elseif ($index === $currentIndex) {
                        $stepClass = 'current';
                    }
                    ?>
                    <div class="progress-step <?php echo $stepClass; ?>">
                        <div class="step-icon">
                            <?php echo $index < $currentIndex ? '✓' : $statusLabels[$step]['icon']; ?>
                        </div>
                        <span class="step-label"><?php echo $statusLabels[$step]['label']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Complaint Details -->
        <section class="form-section">
            <div class="section-header">
                <span class="section-icon">📋</span>
                Complaint Details
            </div>
            <div class="section-content">
                <div class="review-field">
                    <label>Date/Petsa Submitted</label>
                    <div class="value"><?php echo date('F j, Y \a\t g:i A', strtotime($complaint['date_petsa'])); ?></div>
                </div>
                
                <div class="review-field">
                    <label>Referred To</label>
                    <div class="value">
                        <?php 
                        echo htmlspecialchars($complaint['referred_to']);
                        if ($complaint['referred_to'] === 'Others' && !empty($complaint['referred_to_other'])) {
                            echo ' - ' . htmlspecialchars($complaint['referred_to_other']);
                        }
                        ?>
                    </div>
                </div>
                
                <div class="review-field">
                    <label>Name/Pangalan</label>
                    <div class="value"><?php echo htmlspecialchars($complaint['name_pangalan']); ?></div>
                </div>
                
                <div class="review-field">
                    <label>Office/School/Person Involved</label>
                    <div class="value">
                        <?php echo htmlspecialchars($complaint['involved_full_name']); ?> 
                        (<?php echo htmlspecialchars($complaint['involved_position']); ?>)
                        <br>
                        <small><?php echo htmlspecialchars($complaint['involved_school_office_unit']); ?></small>
                    </div>
                </div>
                
                <div class="review-field">
                    <label>Narration of Complaint/Inquiry</label>
                    <div class="value long-text"><?php echo nl2br(htmlspecialchars(substr($complaint['narration_complaint'], 0, 500))); ?><?php echo strlen($complaint['narration_complaint']) > 500 ? '...' : ''; ?></div>
                </div>
                
                <?php if (!empty($documents)): ?>
                <div class="review-field">
                    <label>Supporting Documents</label>
                    <div class="value">
                        <?php foreach ($documents as $doc): ?>
                        <div style="padding: 0.5rem 0;">
                            📎 <?php echo htmlspecialchars($doc['original_name']); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Status History -->
        <?php if (!empty($history)): ?>
        <section class="form-section">
            <div class="section-header">
                <span class="section-icon">📜</span>
                Status History
            </div>
            <div class="section-content">
                <div class="timeline">
                    <?php foreach ($history as $entry): ?>
                    <div class="timeline-item">
                        <div class="time">
                            <?php echo date('M j, Y \a\t g:i A', strtotime($entry['created_at'])); ?>
                        </div>
                        <div class="status">
                            <?php echo $statusLabels[$entry['status']]['icon'] . ' ' . $statusLabels[$entry['status']]['label']; ?>
                        </div>
                        <?php if (!empty($entry['notes'])): ?>
                        <div class="notes"><?php echo htmlspecialchars($entry['notes']); ?></div>
                        <?php endif; ?>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                            Updated by: <?php echo htmlspecialchars($entry['updated_by']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php elseif ($searched && !$error): ?>
        <div class="form-section">
            <div class="section-content" style="text-align: center; padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
                <h3 style="color: var(--text-secondary); margin-bottom: 0.5rem;">No Results Found</h3>
                <p style="color: var(--text-muted);">
                    We couldn't find a complaint matching your search criteria.<br>
                    Please verify your reference number and email address.
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="form-footer">
            <p>SDO CTS - San Pedro Division Office Complaint Tracking System</p>
            <p>Department of Education - San Pedro Division</p>
        </footer>
    </div>
</body>
</html>
