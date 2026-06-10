<?php
/**
 * Google OAuth Callback Handler
 */

require_once __DIR__ . '/../includes/auth.php';

$auth = auth();

// Check for authorization code
if (!isset($_GET['code'])) {
    header('Location: /CTS/admin/login.php?error=google_auth_failed');
    exit;
}

$code = $_GET['code'];

try {
    // Exchange code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $tokenData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($tokenData),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    
    $tokenResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to get access token');
    }

    $tokenInfo = json_decode($tokenResponse, true);
    $accessToken = $tokenInfo['access_token'] ?? null;

    if (!$accessToken) {
        throw new Exception('Invalid token response');
    }

    // Get user info from Google
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    $ch = curl_init($userInfoUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken]
    ]);
    
    $userResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Failed to get user info');
    }

    $userInfo = json_decode($userResponse, true);
    
    $googleId = $userInfo['id'] ?? null;
    $email = $userInfo['email'] ?? null;
    $name = $userInfo['name'] ?? '';
    $avatar = $userInfo['picture'] ?? null;

    if (!$googleId || !$email) {
        throw new Exception('Invalid user info');
    }

    // Check allowed email domains
    if (!empty(ALLOWED_EMAIL_DOMAINS)) {
        $emailDomain = substr(strrchr($email, "@"), 1);
        if (!in_array($emailDomain, ALLOWED_EMAIL_DOMAINS)) {
            header('Location: /CTS/admin/login.php?error=email_domain_not_allowed');
            exit;
        }
    }

    // Try to authenticate/login (with auto-registration enabled)
    if ($auth->loginWithGoogle($googleId, $email, $name, $avatar, true)) {
        $redirect = $_SESSION['redirect_after_login'] ?? '/CTS/admin/';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    } else {
        // Registration/login failed
        header('Location: /CTS/admin/login.php?error=google_auth_failed');
        exit;
    }

} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    header('Location: /CTS/admin/login.php?error=google_auth_failed');
    exit;
}

