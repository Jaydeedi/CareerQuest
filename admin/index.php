<?php
// admin/index.php
session_start();
require_once '../config/db.php';

// I-check kung naka-login at kung Admin ang user
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$page_title = "Admin Dashboard";
// Gumamit ng header/footer na shared sa buong site, pero i-adjust ang path
require_once '../includes/header.php'; 

// Kumuha ng simple stats para sa dashboard
$total_courses = $db->query("SELECT COUNT(id) FROM courses")->fetchColumn();
$total_lessons = $db->query("SELECT COUNT(id) FROM lessons")->fetchColumn();
$total_quizzes = $db->query("SELECT COUNT(id) FROM quizzes")->fetchColumn();
$total_users = $db->query("SELECT COUNT(id) FROM users")->fetchColumn();
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4 text-danger">Admin Dashboard</h2>
    <p class="lead">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Manage your learning platform content here.</p>

    <div class="row mt-4">
        
        <div class="col-md-3 mb-4">
            <a href="courses.php" class="text-decoration-none">
                <div class="card bg-primary text-white shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Courses</h5>
                        <h1 class="display-4 fw-bold"><?php echo $total_courses; ?></h1>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="lessons.php" class="text-decoration-none">
                <div class="card bg-success text-white shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Lessons</h5>
                        <h1 class="display-4 fw-bold"><?php echo $total_lessons; ?></h1>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4">
            <a href="quizzes.php" class="text-decoration-none">
                <div class="card bg-warning text-dark shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Quizzes</h5>
                        <h1 class="display-4 fw-bold"><?php echo $total_quizzes; ?></h1>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a href="users.php" class="text-decoration-none">
                <div class="card bg-info text-white shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <h1 class="display-4 fw-bold"><?php echo $total_users; ?></h1>
                    </div>
                </div>
            </a>
        </div>
        
    </div>
    
    <hr>
    <p class="text-muted">Click on the cards above to manage the content.</p>
</div>

<?php require_once '../includes/footer.php'; ?>