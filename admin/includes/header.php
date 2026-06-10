<?php
/**
 * Admin Panel Header
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../models/ComplaintAdmin.php';

$auth = auth();
$currentUser = $auth->getUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get notification counts for sidebar badges
$notificationCounts = [];
try {
    $complaintModel = new ComplaintAdmin();
    $stats = $complaintModel->getStatistics();

    // Pending complaints that need attention
    $notificationCounts['complaints'] = ($stats['by_status']['pending'] ?? 0);

    // New complaints today (for dashboard)
    $notificationCounts['dashboard'] = $stats['this_week'] ?? 0;
} catch (Exception $e) {
    $notificationCounts['complaints'] = 0;
    $notificationCounts['dashboard'] = 0;
}

// Get page title
$pageTitles = [
    'index' => 'Dashboard',
    'analytics' => 'Analytics',
    'complaints' => 'Complaint Management',
    'complaint-view' => 'View Complaint',
    'users' => 'User Management',
    'logs' => 'Activity Logs',
    'settings' => 'Settings',
    'profile' => 'My Profile',
    'email-settings' => 'Email Settings',
    'email-logs' => 'Email Logs',
    'contact' => 'Need Help?'
];

$pageTitle = $pageTitles[$currentPage] ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo ADMIN_TITLE; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <?php $adminCssVersion = @filemtime(__DIR__ . '/../assets/css/admin.css') ?: time(); ?>
    <link rel="stylesheet" href="/CTS/admin/assets/css/admin.css?v=<?php echo $adminCssVersion; ?>">
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
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="/CTS/assets/img/sdo-logo.jpg" alt="SDO Logo" class="logo-img">
                    <div class="logo-text">
                        <span class="logo-title">SDO CTS</span>
                        <span class="logo-subtitle">ADMIN PANEL</span>
                    </div>
                </div>

               
            </div>

            <!-- Sidebar Backdrop -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <nav class="sidebar-nav">
                <a href="/CTS/admin/" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>"
                    data-tooltip="Dashboard">
                    <span class="nav-icon">
                        <i class="fas fa-chart-line"></i>
                        <?php if ($notificationCounts['dashboard'] > 0): ?>
                            <span class="nav-badge"
                                title="<?php echo $notificationCounts['dashboard']; ?> this week"><?php echo $notificationCounts['dashboard'] > 99 ? '99+' : $notificationCounts['dashboard']; ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>

                <?php if ($auth->hasPermission('reports.view') || $auth->hasPermission('complaints.view')): ?>
                    <a href="/CTS/admin/analytics.php"
                        class="nav-item <?php echo $currentPage === 'analytics' ? 'active' : ''; ?>"
                        data-tooltip="Analytics">
                        <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
                        <span class="nav-text">Analytics</span>
                    </a>
                <?php endif; ?>

                <?php if ($auth->hasPermission('complaints.view')): ?>
                    <a href="/CTS/admin/complaints.php"
                        class="nav-item <?php echo in_array($currentPage, ['complaints', 'complaint-view']) ? 'active' : ''; ?>"
                        data-tooltip="Complaints" id="nav-complaints">
                        <span class="nav-icon">
                            <i class="fas fa-clipboard-list"></i>
                            <span class="nav-badge" id="complaints-badge"
                                title="<?php echo $notificationCounts['complaints']; ?> pending"
                                style="<?php echo $notificationCounts['complaints'] > 0 ? '' : 'display:none;'; ?>"><?php echo $notificationCounts['complaints'] > 99 ? '99+' : $notificationCounts['complaints']; ?></span>
                        </span>
                        <span class="nav-text">Complaints</span>
                    </a>
                <?php endif; ?>

                <?php if ($auth->isSuperAdmin()): ?>
                    <a href="/CTS/admin/users.php"
                        class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>" data-tooltip="Users">
                        <span class="nav-icon"><i class="fas fa-users"></i></span>
                        <span class="nav-text">Users</span>
                    </a>
                <?php endif; ?>

                <?php if ($auth->hasPermission('logs.view')): ?>
                    <a href="/CTS/admin/logs.php"
                        class="nav-item <?php echo $currentPage === 'logs' ? 'active' : ''; ?>"
                        data-tooltip="Activity Logs">
                        <span class="nav-icon"><i class="fas fa-history"></i></span>
                        <span class="nav-text">Activity Logs</span>
                    </a>
                <?php endif; ?>

                <a href="/CTS/admin/profile.php"
                    class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>"
                    data-tooltip="My Profile">
                    <span class="nav-icon"><i class="fas fa-user-cog"></i></span>
                    <span class="nav-text">My Profile</span>
                </a>

                <a href="/CTS/" class="nav-item" target="_blank" data-tooltip="View Public Site">
                    <span class="nav-icon"><i class="fas fa-globe"></i></span>
                    <span class="nav-text">View CTS</span>
                </a>

                <a href="/CTS/admin/contact.php" class="nav-item <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" data-tooltip="Need Help?">
                    <span class="nav-icon"><i class="fas fa-headset"></i></span>
                    <span class="nav-text">Need Help?</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($currentUser['avatar_url']); ?>" alt="Avatar"
                            class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar-placeholder">
                            <?php echo strtoupper(substr($currentUser['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($currentUser['role_name']); ?></span>
                    </div>
                </div>
                <a href="/CTS/admin/logout.php" class="logout-btn-new" title="Logout">
                    <i class="bx bx-log-out"></i>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fas fa-bars"></i></button>
                    <button class="desktop-sidebar-toggle" id="desktopSidebarToggle" title="Toggle Sidebar">
                        <i class="fas fa-columns"></i>
                    </button>
                    <h1 class="page-title"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="top-bar-right" style="display: flex; align-items: center; gap: 16px;">
                    <span id="liveDate" style="color: #64748b; font-size: 0.95rem; margin-right: 4px;"><?php echo date('l, F j, Y'); ?></span>
                    <span id="liveTime" style="color: #000000; font-size: 1.05rem; font-weight: 600; letter-spacing: 0.5px;"><?php echo date('h:i:s A'); ?></span>
                </div>
                <script>
                    function updateClock() {
                        const now = new Date();
                        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        
                        const dayName = days[now.getDay()];
                        const monthName = months[now.getMonth()];
                        const date = now.getDate();
                        const year = now.getFullYear();
                        
                        let hours = now.getHours();
                        const minutes = now.getMinutes().toString().padStart(2, '0');
                        const seconds = now.getSeconds().toString().padStart(2, '0');
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        
                        hours = hours % 12;
                        hours = hours ? hours : 12;
                        
                        const dateString = `${dayName}, ${monthName} ${date}, ${year}`;
                        const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;
                        
                        const dateEl = document.getElementById('liveDate');
                        const timeEl = document.getElementById('liveTime');
                        
                        if(dateEl) dateEl.textContent = dateString;
                        if(timeEl) timeEl.textContent = timeString;
                    }
                    setInterval(updateClock, 1000);
                    // Initialize immediately to replace PHP generated static date
                    updateClock();
                </script>
            </header>

            <div class="content-wrapper">