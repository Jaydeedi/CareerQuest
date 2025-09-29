<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['code'])) {
    http_response_code(400);
    die("Invalid request.");
}

$user_code = $_POST['code'];

$filename = __DIR__ . '/temp/user_code_' . uniqid() . '.php';
if (!is_dir(__DIR__ . '/temp')) {
    mkdir(__DIR__ . '/temp', 0777, true);
}

file_put_contents($filename, $user_code); 

$command = $php_path . " " . escapeshellarg($filename) . " 2>&1"; 

$output = shell_exec($command);

unlink($filename);

echo $output;
?>