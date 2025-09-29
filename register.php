<?php
require_once 'config/db.php';
require_once 'includes/header.php'; // I-include ang header

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDbConnection();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "The email format is invalid.";
    } elseif (strlen($password) < 6) {
        $error_message = "The password must be at least 6 characters long.";
    } else {
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error_message = "This email is already registered.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
                if ($stmt->execute([$name, $email, $password_hash])) {
                    $success_message = "Registration successful! You can now log in.";
                } else {
                    $error_message = "There was a problem. Please try again.";
                }
            }
        } catch (Exception $e) {
            $error_message = "There was a problem. Please try again. " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card p-5 auth-card">
            <h2 class="card-title text-center mb-4 fw-bold text-success">Create Your Account</h2>

            <?php if ($error_message): ?>
                <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success text-center"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($name ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password (Min. 6 Characters)</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-green w-100 mt-3">REGISTER</button>
            </form>
            
            <p class="text-center mt-4 text-secondary">
                Have an Account? <a href="login.php" class="text-primary fw-bold">LOGIN</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>