<?php
// admin/users.php
session_start();
require_once '../config/db.php';

// I-check kung Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$page_title = "Manage Users";
require_once '../includes/header.php'; 

// Kunin ang lahat ng Users
$stmt = $db->query("
    SELECT id, name, email, is_admin, created_at
    FROM users
    ORDER BY id DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']); // I-alis para hindi na ulit lumabas
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Manage Users</h2>
	
	<?php echo $message; ?> 
    
    <a href="user_add.php" class="btn btn-success mb-3">Add New User</a>
    
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Joined</th>
                <th>Admin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                <td>
                    <?php if ($user['is_admin'] == 1): ?>
                        <span class="badge bg-danger">YES</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">NO</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sigurado ka ba na gusto mong i-delete ang User na ito?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>