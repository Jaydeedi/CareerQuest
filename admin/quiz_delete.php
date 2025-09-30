<?php
// admin/quiz_delete.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: quizzes.php');
    exit;
}

$db = getDbConnection();
$quiz_id = $_GET['id'];

try {
    // I-delete ang Quiz
    $stmt = $db->prepare("DELETE FROM quizzes WHERE id = ?");
    if ($stmt->execute([$quiz_id])) {
        $_SESSION['admin_message'] = '<div class="alert alert-success">Quiz Question ID ' . $quiz_id . ' deleted successfully!</div>';
    } else {
        $_SESSION['admin_message'] = '<div class="alert alert-danger">Failed to delete quiz question.</div>';
    }
} catch (PDOException $e) {
    $_SESSION['admin_message'] = '<div class="alert alert-danger">Error deleting quiz question.</div>';
}

// I-redirect pabalik sa listahan ng quizzes
header('Location: quizzes.php');
exit;