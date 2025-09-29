<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/header.php'; 

$user_id = $_SESSION['user_id'];
$db = getDbConnection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];


    if (empty($new_name) || empty($new_email)) {
        $message = '<div class="alert alert-danger">Name and Email fields are required.</div>';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Invalid email format.</div>';
    } else {
        $update_fields = [];
        $params = [];
        
        $update_fields[] = 'name = ?';
        $params[] = $new_name;


        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $message = '<div class="alert alert-danger">New password and confirm password do not match.</div>';
            } elseif (strlen($new_password) < 6) {
                $message = '<div class="alert alert-danger">Password must be at least 6 characters long.</div>';
            } else {
                $update_fields[] = 'password = ?';
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }
        

        $update_fields[] = 'email = ?';
        $params[] = $new_email;

        if (empty($message)) {
            $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $db->prepare($sql);
            if ($stmt->execute($params)) {

                $_SESSION['name'] = $new_name; 
                $message = '<div class="alert alert-success">Profile updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to update profile. Please try again.</div>';
            }
        }
    }
}

$stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    header('Location: logout.php');
    exit;
}

$stmt_total = $db->prepare("SELECT COUNT(id) FROM lessons");
$stmt_total->execute();
$total_lessons = $stmt_total->fetchColumn();


$stmt_code_count = $db->prepare("SELECT COUNT(lesson_id) FROM user_progress WHERE user_id = ? AND is_completed = 1");
$stmt_code_count->execute([$user_id]);
$completed_lessons = $stmt_code_count->fetchColumn();


$stmt_quiz_count = $db->prepare("SELECT COUNT(lesson_id) FROM user_progress WHERE user_id = ? AND quiz_completed = 1");
$stmt_quiz_count->execute([$user_id]);
$completed_quizzes = $stmt_quiz_count->fetchColumn();


$progress_percent = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;
$quiz_percent = $total_lessons > 0 ? round(($completed_quizzes / $total_lessons) * 100) : 0;


?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="fw-bold mb-4 text-primary">Account Profile</h2>
            
            <?php echo $message; ?>

            <div class="card shadow-lg p-4">
                <form action="profile.php" method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3">Change Password (Optional)</h5>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank if you don't want to change">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary fw-bold w-100 mt-3">Update Profile</button>
                </form>
            </div>
            
            <div class="mt-5">
				<h4 class="fw-bold mb-3 text-primary">Learning Progress Summary</h4>
				
				<div class="card-group">
					<div class="card text-center shadow-sm border-success me-3">
						<div class="card-body">
							<h5 class="card-title text-success fw-bold">Code Challenges Completed</h5>
							<h1 class="display-4 fw-bold text-success mb-3"><?php echo $completed_lessons; ?> / <?php echo $total_lessons; ?></h1>
							
							<div class="progress" style="height: 20px;">
								<div 
									class="progress-bar bg-success" 
									role="progressbar" 
									style="width: <?php echo $progress_percent; ?>%;" 
									aria-valuenow="<?php echo $progress_percent; ?>" 
									aria-valuemin="0" 
									aria-valuemax="100">
									<?php echo $progress_percent; ?>%
								</div>
							</div>
						</div>
					</div>

					<div class="card text-center shadow-sm border-primary">
						<div class="card-body">
							<h5 class="card-title text-primary fw-bold">Assessment Quizzes Passed</h5>
							<h1 class="display-4 fw-bold text-primary mb-3"><?php echo $completed_quizzes; ?> / <?php echo $total_lessons; ?></h1>
							
							<div class="progress" style="height: 20px;">
								<div 
									class="progress-bar bg-primary" 
									role="progressbar" 
									style="width: <?php echo $quiz_percent; ?>%;" 
									aria-valuenow="<?php echo $quiz_percent; ?>" 
									aria-valuemin="0" 
									aria-valuemax="100">
									<?php echo $quiz_percent; ?>%
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>