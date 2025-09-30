<?php
// admin/user_edit.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$db = getDbConnection();
$user_id = $_GET['id'];
$message = '';
$page_title = "Edit User";
require_once '../includes/header.php'; 

// 1. I-handle ang POST Request (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Please provide a valid name and email.</div>';
    } else {
        // Tiyakin na ang email ay unique (maliban sa current user)
        $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check->execute([$email, $user_id]);
        
        if ($stmt_check->fetch()) {
            $message = '<div class="alert alert-danger">This email is already used by another user.</div>';
        } else {
            $sql = "UPDATE users SET name = ?, email = ?, is_admin = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            if ($stmt->execute([$name, $email, $is_admin, $user_id])) {
                $message = '<div class="alert alert-success">User updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to update user.</div>';
            }
        }
    }
}

// 2. I-fetch ang current User data
$stmt = $db->prepare("SELECT name, email, is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit;
}

// Gamitin ang data para i-populate ang form
$name = $user['name'];
$email = $user['email'];
$is_admin = $user['is_admin'];

// Disable ang Admin privilege change kung ang user ay ang sarili niya
$disable_admin_change = ($user_id == $_SESSION['user_id']);

$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']); // I-alis para hindi na ulit lumabas
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Edit User: <?php echo htmlspecialchars($name); ?></h2>
    
    <?php echo $message; ?>

    <form action="user_edit.php?id=<?php echo $user_id; ?>" method="POST" class="card p-4 shadow-sm">
        
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" value="1" id="is_admin" name="is_admin" 
                   <?php echo ($is_admin == 1) ? 'checked' : ''; ?>
                   <?php echo $disable_admin_change ? 'disabled' : ''; // Disabled kung sarili niya ang ineed-it?>>
            <label class="form-check-label fw-bold text-danger" for="is_admin">
                Grant Admin Privileges
            </label>
            <?php if ($disable_admin_change): ?>
                <small class="text-danger d-block">You cannot change your own Admin status.</small>
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="users.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update User</button>
        </div>
    </form>
    
    <div class="mt-4 text-end">
        <a href="user_reset_password.php?id=<?php echo $user_id; ?>" class="btn btn-sm btn-warning">Reset Password</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>