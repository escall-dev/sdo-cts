<?php
/**
 * Admin Login Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';

$auth = auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $redirect = $_SESSION['redirect_after_login'] ?? '/CTS/admin/';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;
}

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        if ($auth->login($email, $password)) {
            $redirect = $_SESSION['redirect_after_login'] ?? '/CTS/admin/';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SDO CTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0f4c75;
            --primary-light: #1b6ca8;
            --primary-dark: #0a2f4a;
            --accent: #bbe1fa;
            --gold: #d4af37;
            --bg-dark: #0a1628;
            --bg-card: #111d2e;
            --text: #e8f1f8;
            --text-muted: #7a9bb8;
            --border: rgba(187, 225, 250, 0.1);
            --error: #ef4444;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }


        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            margin: auto;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 28px;
            backdrop-filter: blur(20px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 16px;
            font-size: 2rem;
            margin-bottom: 16px;
            position: relative;
        }

        .logo-badge::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 19px;
            background: linear-gradient(135deg, var(--gold) 0%, transparent 50%);
            z-index: -1;
            opacity: 0.5;
        }

        .login-header h1 {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            font-size: 0.95rem;
            font-family: inherit;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            transition: all 0.2s ease;
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .btn-google {
            background: white;
            color: #333;
            border: 2px solid #e5e7eb;
            font-weight: 600;
        }

        .btn-google:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-2px);
        }

        .btn-google svg {
            margin-right: 4px;
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .login-footer a:hover {
            color: white;
        }

        .brand-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .login-card {
                padding: 24px 20px;
                border-radius: 16px;
            }

            .login-header {
                margin-bottom: 20px;
            }

            .logo-badge {
                width: 90px;
                height: 90px;
                margin-bottom: 12px;
            }

            .logo-badge img {
                width: 90px !important;
                height: 90px !important;
            }

            .login-header h1 {
                font-size: 1.25rem;
            }

            .login-header p {
                font-size: 0.8rem;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .form-control {
                padding: 10px 12px;
            }

            .btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }

            .divider {
                margin: 16px 0;
            }

            .login-footer {
                margin-top: 16px;
                padding-top: 16px;
            }

            .brand-footer {
                margin-top: 16px;
            }
        }

        @media (max-height: 700px) {
            body {
                align-items: flex-start;
                padding-top: 16px;
                padding-bottom: 16px;
            }

            .login-card {
                padding: 24px 24px;
            }

            .login-header {
                margin-bottom: 20px;
            }

            .logo-badge {
                width: 56px;
                height: 56px;
                margin-bottom: 12px;
            }

            .logo-badge img {
                width: 52px !important;
                height: 52px !important;
            }

            .brand-footer {
                margin-top: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-badge"><img src="/CTS/assets/img/sdo-logo.jpg" alt="SDO San Pedro Logo"
                    style="width:90px;height:90px;border-radius:50%;margin-bottom:13px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                </div>
                <h1>SDO CTS Admin</h1>

                <p>Schools Division Office of San Pedro City - Complaint Tracking System</p>

            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <span><i class="fas fa-exclamation-triangle"></i></span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Email/Password Login Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($email); ?>" placeholder="your.email@deped.gov.ph" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class=""></i> Sign In
                </button>
            </form>

            <div class="login-footer">
                <a href="/CTS/"><i class="fas fa-arrow-left"></i> Back to CTS</a>
            </div>
        </div>

        <div class="brand-footer">
            <p>DepEd — Schools Division Office of San Pedro City</p>
            <span>&copy; <?php echo date('Y'); ?> ICT Unit</span>
        </div>
    </div>

    <script>
        (function() {
            function blockInteraction(event) {
                event.preventDefault();
                event.stopPropagation();
                if (typeof event.stopImmediatePropagation === 'function') {
                    event.stopImmediatePropagation();
                }
                return false;
            }

            window.addEventListener('contextmenu', blockInteraction, true);
            document.addEventListener('contextmenu', blockInteraction, true);
            window.oncontextmenu = function() { return false; };
            document.oncontextmenu = function() { return false; };

            document.addEventListener('keydown', function(event) {
                const key = (event.key || '').toLowerCase();
                const isInspectShortcut =
                    event.key === 'F12' ||
                    (event.ctrlKey && event.shiftKey && (key === 'i' || key === 'j' || key === 'c')) ||
                    (event.ctrlKey && key === 'u');

                if (isInspectShortcut) {
                    blockInteraction(event);
                }
            }, true);
        })();
    </script>
</body>

</html>