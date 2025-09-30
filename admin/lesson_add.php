<?php
// admin/lesson_add.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../index.php');
    exit;
}

$db = getDbConnection();
$message = '';
$page_title = "Add New Lesson";
require_once '../includes/header.php'; 

// I-fetch ang lahat ng Modules (Module Title at Course Title) para sa dropdown
$stmt_modules = $db->query("
    SELECT m.id, m.module_title, c.title AS course_title
    FROM modules m
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.id, m.module_order
");
$modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_id = trim($_POST['module_id']);
    $lesson_title = trim($_POST['lesson_title']);
    $instructions = trim($_POST['instructions']);
    $starter_code = $_POST['starter_code'];
    $expected_output = trim($_POST['expected_output']);
    $lesson_order = (int)$_POST['lesson_order'];
    $lesson_type = 'code'; // Hardcode muna natin to 'code' base sa setup

    if (empty($module_id) || empty($lesson_title) || empty($instructions) || empty($expected_output) || $lesson_order < 1) {
        $message = '<div class="alert alert-danger">All fields are required and Lesson Order must be positive.</div>';
    } else {
        try {
            $sql = "INSERT INTO lessons (module_id, lesson_title, instructions, starter_code, expected_output, lesson_type, lesson_order) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            if ($stmt->execute([$module_id, $lesson_title, $instructions, $starter_code, $expected_output, $lesson_type, $lesson_order])) {
                $message = '<div class="alert alert-success">Lesson added successfully!</div>';
                // I-clear ang form data
                $module_id = $lesson_title = $instructions = $starter_code = $expected_output = $lesson_order = '';
            } else {
                $message = '<div class="alert alert-danger">Failed to add lesson.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Database Error: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Add New Lesson</h2>
    
    <?php echo $message; ?>

    <form action="lesson_add.php" method="POST" class="card p-4 shadow-sm">
        
        <div class="mb-3">
            <label for="module_id" class="form-label">Module (Course)</label>
            <select class="form-select" id="module_id" name="module_id" required>
                <option value="">Select Module</option>
                <?php foreach ($modules as $module): ?>
                    <option value="<?php echo $module['id']; ?>" 
                        <?php echo (isset($module_id) && $module_id == $module['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($module['module_title']) . ' (' . htmlspecialchars($module['course_title']) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="lesson_title" class="form-label">Lesson Title</label>
            <input type="text" class="form-control" id="lesson_title" name="lesson_title" value="<?php echo htmlspecialchars($lesson_title ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="instructions" class="form-label">Instructions</label>
            <textarea class="form-control" id="instructions" name="instructions" rows="3" required><?php echo htmlspecialchars($instructions ?? ''); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="starter_code" class="form-label">Starter Code</label>
            <textarea class="form-control font-monospace" id="starter_code" name="starter_code" rows="7"><?php echo htmlspecialchars($starter_code ?? ''); ?></textarea>
            <small class="text-muted">I-paste ang PHP code dito. Pwedeng iwanang blangko.</small>
        </div>

        <div class="mb-3">
            <label for="expected_output" class="form-label">Expected Output (Case-Sensitive)</label>
            <input type="text" class="form-control" id="expected_output" name="expected_output" value="<?php echo htmlspecialchars($expected_output ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="lesson_order" class="form-label">Lesson Order (Position in Module)</label>
            <input type="number" class="form-control" id="lesson_order" name="lesson_order" value="<?php echo htmlspecialchars($lesson_order ?? 1); ?>" required min="1">
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="lessons.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-success">Add Lesson</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>