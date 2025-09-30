<?php
// admin/course_delete.php
session_start();
require_once '../config/db.php';

// I-check kung Admin at kung may ID
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$db = getDbConnection();
$course_id = $_GET['id'];

try {
    // MAHALAGA: Dapat may ON DELETE CASCADE ang inyong database foreign keys (modules, lessons, quizzes, user_progress) 
    // para automatic na matanggal ang lahat ng related data.

    $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
    if ($stmt->execute([$course_id])) {
        // Successful deletion
        $_SESSION['admin_message'] = '<div class="alert alert-success">Course ID ' . $course_id . ' and all related content deleted successfully!</div>';
    } else {
        // Failed deletion
        $_SESSION['admin_message'] = '<div class="alert alert-danger">Failed to delete course. Check foreign key constraints.</div>';
    }
} catch (PDOException $e) {
    // Catch database errors, lalo na kung walang CASCADE
    $_SESSION['admin_message'] = '<div class="alert alert-danger">Error deleting course. Please check if modules/lessons/quizzes are linked.</div>';
}

// I-redirect pabalik sa listahan ng courses
header('Location: courses.php');
exit;