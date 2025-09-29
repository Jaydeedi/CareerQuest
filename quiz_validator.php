<?php
session_start();
header('Content-Type: application/json');
require_once 'config/db.php';

$response = ['success' => false, 'score' => 0, 'total_questions' => 0, 'message' => ''];

$lesson_id = $_POST['lesson_id'] ?? null; 
$user_id = $_SESSION['user_id'] ?? 0;

if (!$lesson_id || !is_numeric($lesson_id)) {
    $response['message'] = 'Invalid lesson ID: ' . $lesson_id;
    echo json_encode($response);
    exit;
}

if (!$user_id) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

$db = getDbConnection();


$stmt = $db->prepare("
    SELECT id, correct_option 
    FROM quizzes 
    WHERE lesson_id = ?
");
$stmt->execute([$lesson_id]);
$correct_answers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$score = 0;
$total_questions = count($correct_answers);

if ($total_questions === 0) {
    $response['message'] = 'No quiz questions found.';
    echo json_encode($response);
    exit;
}


foreach ($correct_answers as $question_id => $correct_option) {
    $user_answer = $_POST['answer_' . $question_id] ?? null;

    if ($user_answer === $correct_option) {
        $score++;
    }
}

$response['score'] = $score;
$response['total_questions'] = $total_questions;


if ($score === $total_questions) {
    $response['success'] = true;
    $response['message'] = 'Quiz passed!';


    $stmt = $db->prepare("
        UPDATE user_progress 
        SET quiz_completed = TRUE 
        WHERE user_id = ? AND lesson_id = ?
    ");

    $stmt->execute([$user_id, $lesson_id]);

} else {
    $response['message'] = 'Failed. Try again for 100%.';
}

echo json_encode($response);
?>