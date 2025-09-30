<?php
// Tiyakin na naka-start ang session sa bawat page
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Career Quest'); ?></title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f7f9fc; }
        .navbar-brand { font-weight: 700; color: #28a745 !important; }
        .auth-card { margin-top: 10vh; border-radius: 1rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .btn-green { background-color: #28a745; border-color: #28a745; color: white; }
        .btn-green:hover { background-color: #1e7e34; border-color: #1c7430; }
        /* Idinagdag ang kulay para sa blue button */
        .btn-blue { background-color: #0d6efd; border-color: #0d6efd; color: white; } 
        .btn-blue:hover { background-color: #0b5ed7; border-color: #white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="index.php">Career Quest</a>
    <div class="collapse navbar-collapse">
    <ul class="navbar-nav ms-auto align-items-center">
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] == 0): ?>
        <li class="nav-item">
            <a class="nav-link text-dark" href="index.php">Courses</a>
        </li>
        <?php endif; ?>

        <?php 
        // Admin Panel Link: Lalabas LANG kung Admin
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): 
        ?>
            <li class="nav-item me-2">
                <a class="nav-link text-danger fw-bold" href="index.php">Admin Panel</a>
            </li>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item me-3">
                <span class="nav-link text-success fw-bold">Hi, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>!</span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">Profile</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-outline-danger btn-sm" href="logout.php">Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item me-2">
                <a class="btn btn-blue btn-sm" href="login.php">LOGIN</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-blue btn-sm" href="register.php">REGISTER</a>
            </li>
        <?php endif; ?>
        
    </ul>
</div>
  </div>
</nav>

<main class="container">