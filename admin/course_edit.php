<?php
// admin/course_edit.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: courses.php');
    exit;
}

$db = getDbConnection();
$course_id = $_GET['id'];
$message = '';
$page_title = "Edit Course";
require_once '../includes/header.php'; 

// 1. I-handle ang POST Request (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    if (empty($title) || empty($description)) {
        $message = '<div class="alert alert-danger">Course title and description are required.</div>';
    } else {
        $stmt = $db->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$title, $description, $course_id])) {
            $message = '<div class="alert alert-success">Course updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to update course.</div>';
        }
    }
}

// 2. I-fetch ang current Course data (Gagawin ito ulit para ma-reflect ang changes pagkatapos mag-POST)
$stmt = $db->prepare("SELECT title, description FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    // Kung hindi mahanap ang course ID
    header('Location: courses.php');
    exit;
}

// Gamitin ang data para i-populate ang form
$title = $course['title'];
$description = $course['description'];
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Edit Course: <?php echo htmlspecialchars($title); ?></h2>
    
    <?php echo $message; ?>

    <form action="course_edit.php?id=<?php echo $course_id; ?>" method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="title" class="form-label">Course Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="d-flex justify-content-between">
            <a href="courses.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Course</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>