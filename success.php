<?php
/**
 * SDO CTS - Submission Success Page
 */

session_start();

// Check if success data exists
if (!isset($_SESSION['submission_success'])) {
    header('Location: index.php');
    exit;
}

$success = $_SESSION['submission_success'];
$referenceNumber = $success['reference_number'];
$email = $success['email'];

// Clear the success session
unset($_SESSION['submission_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful - SDO CTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .copy-btn {
            background: none;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        .copy-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        .copy-btn.copied {
            background: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: left;
        }
        .info-card h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        .info-card ul {
            list-style: none;
            padding: 0;
        }
        .info-card li {
            padding: 0.5rem 0;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: var(--text-secondary);
        }
        .info-card li span {
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="nav-bar no-print">
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-file-alt"></i> File Complaint</a>
                <a href="track.php"><i class="fas fa-search"></i> Track Complaint</a>
                <a href="contact.php"><i class="fas fa-phone-alt"></i> Contact Us</a>
            </div>
        </nav>

        <!-- Success Card -->
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h2>Complaint Submitted Successfully!</h2>
            <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto;">
                Your complaint has been received and recorded in our system. 
                Please save your reference number for tracking purposes.
            </p>
            
            <div class="reference-number">
                <label>Your Complaint Reference Number</label>
                <div class="number" id="refNumber"><?php echo htmlspecialchars($referenceNumber); ?></div>
                <button type="button" class="copy-btn" onclick="copyReference()">
                    📋 Copy Reference Number
                </button>
            </div>
            
            <div class="info-card">
                <h4>📌 What Happens Next?</h4>
                <ul>
                    <li>
                        <span>1️⃣</span>
                        <div>Your complaint has been forwarded to the appropriate office for review.</div>
                    </li>
                    <li>
                        <span>2️⃣</span>
                        <div>You can track the status of your complaint using your reference number and email.</div>
                    </li>
                    <li>
                        <span>3️⃣</span>
                        <div>Updates will be reflected in the tracking system as your complaint progresses.</div>
                    </li>
                    <li>
                        <span>4️⃣</span>
                        <div>You may be contacted at <strong><?php echo htmlspecialchars($email); ?></strong> for additional information if needed.</div>
                    </li>
                </ul>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
                <a href="track.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Track Your Complaint
                </a>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-file-alt"></i> File Another Complaint
                </a>
            </div>
        </div>

        <!-- Footer -->
        <footer class="form-footer">
            <p>SDO CTS - San Pedro Division Office Complaint Tracking System</p>
            <p>Department of Education - San Pedro Division</p>
        </footer>
    </div>

    <script>
        // Copy reference number
        function copyReference() {
            const refNumber = document.getElementById('refNumber').textContent;
            navigator.clipboard.writeText(refNumber).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.classList.add('copied');
                btn.innerHTML = '✓ Copied!';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = '📋 Copy Reference Number';
                }, 2000);
            });
        }
    </script>
</body>
</html>


