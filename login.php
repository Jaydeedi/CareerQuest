<?php
require_once 'config/db.php';
require_once 'includes/header.php'; // I-include ang header

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDbConnection();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $db->prepare("SELECT id, name, password_hash, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
			$_SESSION['is_admin'] = $user['is_admin'] ?? 0;
			if($user['is_admin'] == '1')
			{
				header('Location: admin/index.php');
			}
			else
			{
				header('Location: index.php');
			}

            exit;
        } else {
            $error_message = "Incorrect email or password.";
        }
    } catch (Exception $e) {
        $error_message = "A login error occurred.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card p-5 auth-card">
            <h2 class="card-title text-center mb-4 fw-bold text-primary">Sign In</h2>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">LOGIN</button>
            </form>
            
            <p class="text-center mt-4 text-secondary">
                Don't have an account? <a href="register.php" class="text-success fw-bold">REGISTER</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>