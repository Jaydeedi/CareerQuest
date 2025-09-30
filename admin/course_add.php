<?php
// admin/course_add.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$message = '';
$page_title = "Add New Course";
require_once '../includes/header.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id']; // Ang user na nag-create
    
    if (empty($title) || empty($description)) {
        $message = '<div class="alert alert-danger">Course title and description are required.</div>';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO courses (user_id, title, description, created_at) VALUES (?, ?, ?, NOW())");
            if ($stmt->execute([$user_id, $title, $description])) {
                $message = '<div class="alert alert-success">Course added successfully!</div>';
                // I-clear ang form data
                $title = $description = ''; 
            } else {
                $message = '<div class="alert alert-danger">Failed to add course.</div>';
            }
        } catch (PDOException $e) {
            // Maaaring may error sa database
            $message = '<div class="alert alert-danger">Database Error: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Add New Course</h2>
    
    <?php echo $message; ?>

    <form action="course_add.php" method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="title" class="form-label">Course Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="courses.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Add Course</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>