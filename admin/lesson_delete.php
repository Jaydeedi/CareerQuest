<?php
// admin/lesson_delete.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: lessons.php');
    exit;
}

$db = getDbConnection();
$lesson_id = $_GET['id'];

try {
    // MAHALAGA: Tiyakin na ang foreign keys (quizzes, user_progress) ay may ON DELETE CASCADE sa database.

    // I-delete ang Lesson
    $stmt = $db->prepare("DELETE FROM lessons WHERE id = ?");
    if ($stmt->execute([$lesson_id])) {
        $_SESSION['admin_message'] = '<div class="alert alert-success">Lesson ID ' . $lesson_id . ' and related data deleted successfully!</div>';
    } else {
        $_SESSION['admin_message'] = '<div class="alert alert-danger">Failed to delete lesson.</div>';
    }
} catch (PDOException $e) {
    $_SESSION['admin_message'] = '<div class="alert alert-danger">Error deleting lesson. Check foreign key constraints.</div>';
}

// I-redirect pabalik sa listahan ng lessons
header('Location: lessons.php');
exit;