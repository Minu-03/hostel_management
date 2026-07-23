<?php
require_once __DIR__ . '/auth.php';

$currentUser = current_user();
$role = $currentUser['role'] ?? 'student';
$pageTitle = $pageTitle ?? 'Hostel Management System';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1 class="logo">HostelMS</h1>
            <p class="logo-sub">Management System</p>
        </div>
        <nav class="sidebar-nav">
            <?php if ($role === 'admin'): ?>
                <a href="<?= base_url('admin/dashboard.php') ?>"     class="nav-link <?= $activePage==='dashboard'?'active':'' ?>"><span class="nav-icon">&#8962;</span> Dashboard</a>
                <a href="<?= base_url('admin/students.php') ?>"       class="nav-link <?= $activePage==='students'?'active':'' ?>"><span class="nav-icon">&#128100;</span> Students</a>
                <a href="<?= base_url('admin/rooms.php') ?>"          class="nav-link <?= $activePage==='rooms'?'active':'' ?>"><span class="nav-icon">&#127968;</span> Rooms</a>
                <a href="<?= base_url('admin/allocations.php') ?>"    class="nav-link <?= $activePage==='allocations'?'active':'' ?>"><span class="nav-icon">&#128203;</span> Allocations</a>
                <a href="<?= base_url('admin/payments.php') ?>"      class="nav-link <?= $activePage==='payments'?'active':'' ?>"><span class="nav-icon">&#128176;</span> Payments</a>
                <a href="<?= base_url('admin/complaints.php') ?>"     class="nav-link <?= $activePage==='complaints'?'active':'' ?>"><span class="nav-icon">&#128172;</span> Complaints</a>
                <a href="<?= base_url('admin/visitors.php') ?>"       class="nav-link <?= $activePage==='visitors'?'active':'' ?>"><span class="nav-icon">&#128101;</span> Visitors</a>
                <a href="<?= base_url('admin/reports.php') ?>"        class="nav-link <?= $activePage==='reports'?'active':'' ?>"><span class="nav-icon">&#128202;</span> Reports</a>
                <a href="<?= base_url('help.php') ?>"                 class="nav-link <?= $activePage==='help'?'active':'' ?>"><span class="nav-icon">&#10067;</span> Help</a>
            <?php else: ?>
                <a href="<?= base_url('student/dashboard.php') ?>"   class="nav-link <?= $activePage==='dashboard'?'active':'' ?>"><span class="nav-icon">&#8962;</span> Dashboard</a>
                <a href="<?= base_url('student/profile.php') ?>"     class="nav-link <?= $activePage==='profile'?'active':'' ?>"><span class="nav-icon">&#128100;</span> My Profile</a>
                <a href="<?= base_url('student/payments.php') ?>"    class="nav-link <?= $activePage==='payments'?'active':'' ?>"><span class="nav-icon">&#128176;</span> My Payments</a>
                <a href="<?= base_url('student/complaints.php') ?>"  class="nav-link <?= $activePage==='complaints'?'active':'' ?>"><span class="nav-icon">&#128172;</span> My Complaints</a>
                <a href="<?= base_url('student/visitors.php') ?>"    class="nav-link <?= $activePage==='visitors'?'active':'' ?>"><span class="nav-icon">&#128101;</span> My Visitors</a>
                <a href="<?= base_url('help.php') ?>"                class="nav-link <?= $activePage==='help'?'active':'' ?>"><span class="nav-icon">&#10067;</span> Help</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= base_url('logout.php') ?>" class="logout-link"><span class="nav-icon">&#8634;</span> Logout</a>
        </div>
    </aside>

    <!-- Main content -->
    <div class="main-content" id="mainContent">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">&#9776;</button>
            <div class="topbar-title">
                <h2><?= e($pageTitle) ?></h2>
            </div>
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?></div>
                <div class="user-meta">
                    <span class="user-name"><?= e($currentUser['name'] ?? 'Guest') ?></span>
                    <span class="user-role"><?= ucfirst($role) ?></span>
                </div>
            </div>
        </header>

        <main class="page-content">
            <?php
            $flash = get_flash();
            if ($flash):
            ?>
                <div class="alert alert-<?= e($flash['type']) ?>" id="flashAlert">
                    <?= e($flash['message']) ?>
                    <button class="alert-close" onclick="document.getElementById('flashAlert').remove()">&times;</button>
                </div>
            <?php endif; ?>
