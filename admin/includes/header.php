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
    'complaints' => 'Complaint Management',
    'complaint-view' => 'View Complaint',
    'users' => 'User Management',
    'logs' => 'Activity Logs',
    'settings' => 'Settings',
    'profile' => 'My Profile'
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/SDO-cts/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="/SDO-cts/assets/img/sdo-logo.jpg" alt="SDO Logo" class="logo-img">
                    <div class="logo-text">
                        <span class="logo-title">SDO CTS</span>
                        <span class="logo-subtitle">ADMIN PANEL</span>
                    </div>
                </div>
                
            </div>
            
            <nav class="sidebar-nav">
                <a href="/SDO-cts/admin/" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>" data-tooltip="Dashboard">
                    <span class="nav-icon">
                        <i class="fas fa-chart-line"></i>
                        <?php if ($notificationCounts['dashboard'] > 0): ?>
                        <span class="nav-badge" title="<?php echo $notificationCounts['dashboard']; ?> this week"><?php echo $notificationCounts['dashboard'] > 99 ? '99+' : $notificationCounts['dashboard']; ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>
                
                <?php if ($auth->hasPermission('complaints.view')): ?>
                <a href="/SDO-cts/admin/complaints.php" class="nav-item <?php echo in_array($currentPage, ['complaints', 'complaint-view']) ? 'active' : ''; ?>" data-tooltip="Complaints" id="nav-complaints">
                    <span class="nav-icon">
                        <i class="fas fa-clipboard-list"></i>
                        <span class="nav-badge" id="complaints-badge" title="<?php echo $notificationCounts['complaints']; ?> pending" style="<?php echo $notificationCounts['complaints'] > 0 ? '' : 'display:none;'; ?>"><?php echo $notificationCounts['complaints'] > 99 ? '99+' : $notificationCounts['complaints']; ?></span>
                    </span>
                    <span class="nav-text">Complaints</span>
                </a>
                <?php endif; ?>
                
                <?php if ($auth->isSuperAdmin()): ?>
                <a href="/SDO-cts/admin/users.php" class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>" data-tooltip="Users">
                    <span class="nav-icon"><i class="fas fa-users"></i></span>
                    <span class="nav-text">Users</span>
                </a>
                <?php endif; ?>
                
                <?php if ($auth->hasPermission('logs.view')): ?>
                <a href="/SDO-cts/admin/logs.php" class="nav-item <?php echo $currentPage === 'logs' ? 'active' : ''; ?>" data-tooltip="Activity Logs">
                    <span class="nav-icon"><i class="fas fa-history"></i></span>
                    <span class="nav-text">Activity Logs</span>
                </a>
                <?php endif; ?>
                
                <a href="/SDO-cts/admin/profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" data-tooltip="My Profile">
                    <span class="nav-icon"><i class="fas fa-user-cog"></i></span>
                    <span class="nav-text">My Profile</span>
                </a>
                
                <a href="/SDO-cts/" class="nav-item" target="_blank" data-tooltip="View Public Site">
                    <span class="nav-icon"><i class="fas fa-globe"></i></span>
                    <span class="nav-text">View CTS</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($currentUser['avatar_url']); ?>" alt="Avatar" class="user-avatar">
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
                <a href="/SDO-cts/admin/logout.php" class="logout-btn-new" title="Logout">
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
                <div class="top-bar-right">
                    <span class="current-date"><?php echo date('l, F j, Y'); ?></span>
                </div>
            </header>
            
            <div class="content-wrapper">

