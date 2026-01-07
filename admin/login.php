<?php
/**
 * Admin Login Page
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 */

require_once __DIR__ . '/includes/auth.php';

$auth = auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $redirect = $_SESSION['redirect_after_login'] ?? '/SDO-cts/admin/';
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
            $redirect = $_SESSION['redirect_after_login'] ?? '/SDO-cts/admin/';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Generate Google OAuth URL
$googleAuthUrl = '';
$googleConfigured = GOOGLE_CLIENT_ID !== 'your-google-client-id.apps.googleusercontent.com';
if ($googleConfigured) {
    $googleParams = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($googleParams);
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(187, 225, 250, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
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
            box-shadow: 
                0 10px 40px rgba(15, 76, 117, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 0 0 3px rgba(27, 108, 168, 0.2);
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
            box-shadow: 0 4px 14px rgba(15, 76, 117, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 76, 117, 0.5);
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
                width: 60px;
                height: 60px;
                margin-bottom: 12px;
            }
            
            .logo-badge img {
                width: 55px !important;
                height: 55px !important;
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
                <div class="logo-badge"><img src="/SDO-cts/assets/img/sdo-logo.jpg" alt="SDO San Pedro Logo" style="width:60px;height:60px;border-radius:50%;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.15);"></div>
                <h1>SDO CTS Admin</h1>
                <p>San Pedro Division Office - Complaint Tracking System</p>
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
                           value="<?php echo htmlspecialchars($email); ?>"
                           placeholder="your.email@deped.gov.ph" required>
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

            <div class="divider">or continue with</div>

            <!-- Google Sign In Button -->
            <a href="<?php echo $googleAuthUrl ?: '#'; ?>" class="btn btn-google" <?php echo !$googleConfigured ? 'onclick="alert(\'Google Sign-In is not configured yet. Please use email/password login.\'); return false;"' : ''; ?>>
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Google
            </a>

            <div class="login-footer">
                <a href="/SDO-cts/"><i class="fas fa-arrow-left"></i> Back to Public Site</a>
            </div>
        </div>

        <div class="brand-footer">
            <p>Department of Education - San Pedro Division</p>
        </div>
    </div>
</body>
</html>

