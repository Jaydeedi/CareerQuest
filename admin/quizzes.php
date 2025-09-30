<?php
// admin/quizzes.php
session_start();
require_once '../config/db.php';

// I-check kung Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$page_title = "Manage Quizzes";
require_once '../includes/header.php'; 

// Kunin ang lahat ng Quizzes
$stmt = $db->query("
    SELECT 
        q.id, q.question, q.correct_option, 
        l.lesson_title, m.module_title
    FROM quizzes q
    JOIN lessons l ON q.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id
    ORDER BY l.id DESC
");
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']); // I-alis para hindi na ulit lumabas
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Manage Quizzes</h2>
	
	<?php echo $message; ?> 
    
    <a href="quiz_add.php" class="btn btn-success mb-3">Add New Quiz Question</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Question (Preview)</th>
                <th>Correct Answer</th>
                <th>Lesson Title</th>
                <th>Module</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?php echo $quiz['id']; ?></td>
                <td><?php echo htmlspecialchars(substr($quiz['question'], 0, 80)) . '...'; ?></td>
                <td><span class="badge bg-success"><?php echo htmlspecialchars($quiz['correct_option']); ?></span></td>
                <td><?php echo htmlspecialchars($quiz['lesson_title']); ?></td>
                <td><?php echo htmlspecialchars($quiz['module_title']); ?></td>
                <td>
                    <a href="quiz_edit.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="quiz_delete.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sigurado ka ba na gusto mong i-delete ang Quiz na ito?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>