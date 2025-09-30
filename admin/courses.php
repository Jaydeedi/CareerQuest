<?php
// admin/courses.php
session_start();
require_once '../config/db.php';

// I-check kung Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$page_title = "Manage Courses";
require_once '../includes/header.php'; 

// Kunin ang lahat ng Courses
$stmt = $db->query("
    SELECT 
        c.id, c.title, c.description, u.name AS created_by
    FROM courses c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.id DESC
");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']); // I-alis para hindi na ulit lumabas
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Manage Courses</h2>
	<?php echo $message; ?> 
    
    <a href="course_add.php" class="btn btn-success mb-3">Add New Course</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Created By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo $course['id']; ?></td>
                <td><?php echo htmlspecialchars($course['title']); ?></td>
                <td><?php echo htmlspecialchars(substr($course['description'], 0, 50)) . (strlen($course['description']) > 50 ? '...' : ''); ?></td>
                <td><?php echo htmlspecialchars($course['created_by']); ?></td>
                <td>
                    <a href="course_edit.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="course_delete.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sigurado ka ba na gusto mong i-delete ang Course na ito?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>