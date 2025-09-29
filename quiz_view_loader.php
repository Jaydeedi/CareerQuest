<?php
session_start();
require_once 'config/db.php'; 

$lesson_id = $_GET['lesson_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? 0;

if (!$lesson_id || !$user_id || !is_numeric($lesson_id)) {
    echo '<div class="alert alert-danger">Invalid request or user not logged in.</div>';
    exit;
}

$db = getDbConnection();

$stmt = $db->prepare("
    SELECT l.instructions, m.module_title, up.is_completed, up.quiz_completed
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    LEFT JOIN user_progress up ON up.lesson_id = l.id AND up.user_id = ?
    WHERE l.id = ?
");
$stmt->execute([$user_id, $lesson_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo '<div class="alert alert-warning">Lesson data not found.</div>';
    exit;
}

$lesson = ['instructions' => $data['instructions']]; 
$module_title = $data['module_title'];
$is_completed = $data['is_completed'] ?? false;
$is_quiz_completed = $data['quiz_completed'] ?? false;

require_once 'quiz_view.php'; 
?>