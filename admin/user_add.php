<?php
// admin/user_add.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$message = '';
$page_title = "Add New User";
require_once '../includes/header.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    if (empty($name) || empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Please provide a valid name, email, and password.</div>';
    } else {
        // Tiyakin na hindi pa registered ang email
        $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            $message = '<div class="alert alert-danger">This email is already registered.</div>';
        } else {
            // Hash ang password bago i-store
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $sql = "INSERT INTO users (name, email, password_hash, is_admin, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $db->prepare($sql);
                if ($stmt->execute([$name, $email, $hashed_password, $is_admin])) {
                    $message = '<div class="alert alert-success">User added successfully!</div>';
                    // I-clear ang form data
                    $name = $email = $password = '';
                    $is_admin = 0;
                } else {
                    $message = '<div class="alert alert-danger">Failed to add user.</div>';
                }
            } catch (PDOException $e) {
                $message = '<div class="alert alert-danger">Database Error: ' . $e->getMessage() . '</div>';
            }
        }
    }
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Add New User</h2>
    
    <?php echo $message; ?>

    <form action="user_add.php" method="POST" class="card p-4 shadow-sm">
        
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password (Temporary)</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <small class="text-muted">Users will be able to change this after login.</small>
        </div>
        
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" value="1" id="is_admin" name="is_admin" <?php echo (isset($is_admin) && $is_admin == 1) ? 'checked' : ''; ?>>
            <label class="form-check-label fw-bold text-danger" for="is_admin">
                Grant Admin Privileges
            </label>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="users.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Create User</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>