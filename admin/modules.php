<?php
// admin/modules.php
session_start();
require_once '../config/db.php';

// I-check kung Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$page_title = "Manage Modules";
require_once '../includes/header.php'; 

// Kunin ang lahat ng Modules
$stmt = $db->query("
    SELECT 
        m.id, m.module_title, m.module_order, c.title AS course_title
    FROM modules m
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.id, m.module_order
");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Manage Modules</h2>
    
    <a href="module_add.php" class="btn btn-success mb-3">Add New Module</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Module Title</th>
                <th>Course</th>
                <th>Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($modules as $module): ?>
            <tr>
                <td><?php echo $module['id']; ?></td>
                <td><?php echo htmlspecialchars($module['module_title']); ?></td>
                <td><?php echo htmlspecialchars($module['course_title']); ?></td>
                <td><?php echo $module['module_order']; ?></td>
                <td>
                    <a href="module_edit.php?id=<?php echo $module['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="module_delete.php?id=<?php echo $module['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sigurado ka ba na gusto mong i-delete ang Module na ito?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>