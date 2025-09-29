<?php
require_once 'config/db.php';

header('Content-Type: application/json');

session_start(); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['code']) || !isset($_POST['lesson_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$lesson_id = $_POST['lesson_id'];
$user_code = $_POST['code'];
$db = getDbConnection();

$stmt = $db->prepare("SELECT expected_output FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo json_encode(['success' => false, 'message' => 'Lesson not found.']);
    exit;
}
$expected_output = trim($lesson['expected_output']);

$filename = __DIR__ . '/temp/user_code_' . uniqid() . '.php';
if (!is_dir(__DIR__ . '/temp')) { mkdir(__DIR__ . '/temp', 0777, true); }
file_put_contents($filename, $user_code); 
$php_path = 'php'; 
$command = $php_path . " " . escapeshellarg($filename) . " 2>&1"; 
$raw_output = shell_exec($command);
unlink($filename);

$actual_output = trim($raw_output);

if ($actual_output === $expected_output) 
{
	if (isset($_SESSION['user_id'])) 
	{
        $user_id = $_SESSION['user_id'];

        $stmt = $db->prepare("
            INSERT INTO user_progress (user_id, lesson_id, is_completed) 
            VALUES (?, ?, TRUE) 
            ON DUPLICATE KEY UPDATE is_completed = TRUE
        ");
        $stmt->execute([$user_id, $lesson_id]);
    }
	
    echo json_encode([
        'success' => true,
        'output' => $raw_output,
        'message' => 'Correct!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'output' => $raw_output,
        'expected' => $expected_output,
        'message' => 'Incorrect. Check the output.'
    ]);
}
?>