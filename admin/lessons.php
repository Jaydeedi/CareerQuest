<?php
// admin/lessons.php
session_start();
require_once '../config/db.php';

// I-check kung Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$page_title = "Manage Lessons";
require_once '../includes/header.php'; 

// Kunin ang lahat ng Lessons kasama ang Module at Course Title
$stmt = $db->query("
    SELECT 
        l.id, l.lesson_title, l.lesson_order, 
        m.module_title, c.title AS course_title
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.id, m.module_order, l.lesson_order
");
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']); // I-alis para hindi na ulit lumabas
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Manage Lessons</h2>
	
	<?php echo $message; ?> 
    
    <a href="lesson_add.php" class="btn btn-success mb-3">Add New Lesson</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Lesson Title</th>
                <th>Module</th>
                <th>Course</th>
                <th>Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lessons as $lesson): ?>
            <tr>
                <td><?php echo $lesson['id']; ?></td>
                <td><?php echo htmlspecialchars($lesson['lesson_title']); ?></td>
                <td><?php echo htmlspecialchars($lesson['module_title']); ?></td>
                <td><?php echo htmlspecialchars($lesson['course_title']); ?></td>
                <td><?php echo $lesson['lesson_order']; ?></td>
                <td>
                    <a href="lesson_edit.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="lesson_delete.php?id=<?php echo $lesson['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lesson?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
</div>

<?php require_once '../includes/footer.php'; ?>