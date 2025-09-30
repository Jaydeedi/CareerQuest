<?php
// admin/quiz_edit.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_GET['id'])) {
    header('Location: quizzes.php');
    exit;
}

$db = getDbConnection();
$quiz_id = $_GET['id'];
$message = '';
$page_title = "Edit Quiz Question";
require_once '../includes/header.php'; 

// I-fetch ang lahat ng Lessons para sa dropdown
$stmt_lessons = $db->query("
    SELECT l.id, l.lesson_title, m.module_title, c.title AS course_title
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    ORDER BY c.id, m.module_order, l.lesson_order
");
$lessons = $stmt_lessons->fetchAll(PDO::FETCH_ASSOC);


// 1. I-handle ang POST Request (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson_id = trim($_POST['lesson_id']);
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = trim($_POST['correct_option']);
    
    if (empty($lesson_id) || empty($question) || empty($option_a) || empty($option_b) || empty($correct_option)) {
        $message = '<div class="alert alert-danger">All required fields must be filled.</div>';
    } elseif (!in_array($correct_option, ['A', 'B', 'C', 'D'])) {
        $message = '<div class="alert alert-danger">Correct Option must be A, B, C, or D.</div>';
    } else {
        $sql = "UPDATE quizzes SET lesson_id = ?, question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        if ($stmt->execute([$lesson_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $quiz_id])) {
            $message = '<div class="alert alert-success">Quiz question updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to update quiz question.</div>';
        }
    }
}

// 2. I-fetch ang current Quiz data (Gagawin ito ulit para ma-reflect ang changes)
$stmt = $db->prepare("SELECT lesson_id, question, option_a, option_b, option_c, option_d, correct_option FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    header('Location: quizzes.php');
    exit;
}

// Gamitin ang data para i-populate ang form
$lesson_id = $quiz['lesson_id'];
$question = $quiz['question'];
$option_a = $quiz['option_a'];
$option_b = $quiz['option_b'];
$option_c = $quiz['option_c'];
$option_d = $quiz['option_d'];
$correct_option = $quiz['correct_option'];
?>

<div class="container mt-5">
    <h2 class="fw-bold mb-4">Edit Quiz Question: ID #<?php echo $quiz_id; ?></h2>
    
    <?php echo $message; ?>

    <form action="quiz_edit.php?id=<?php echo $quiz_id; ?>" method="POST" class="card p-4 shadow-sm">
        
        <div class="mb-3">
            <label for="lesson_id" class="form-label">Lesson (Module/Course)</label>
            <select class="form-select" id="lesson_id" name="lesson_id" required>
                <option value="">Select Lesson</option>
                <?php foreach ($lessons as $lesson): ?>
                    <option value="<?php echo $lesson['id']; ?>" 
                        <?php echo ($lesson_id == $lesson['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($lesson['lesson_title']) . ' (' . htmlspecialchars($lesson['course_title']) . ' / ' . htmlspecialchars($lesson['module_title']) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr>

        <div class="mb-3">
            <label for="question" class="form-label">Quiz Question</label>
            <textarea class="form-control" id="question" name="question" rows="3" required><?php echo htmlspecialchars($question); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="option_a" class="form-label">Option A</label>
                <input type="text" class="form-control" id="option_a" name="option_a" value="<?php echo htmlspecialchars($option_a); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="option_b" class="form-label">Option B</label>
                <input type="text" class="form-control" id="option_b" name="option_b" value="<?php echo htmlspecialchars($option_b); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="option_c" class="form-label">Option C</label>
                <input type="text" class="form-control" id="option_c" name="option_c" value="<?php echo htmlspecialchars($option_c); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="option_d" class="form-label">Option D</label>
                <input type="text" class="form-control" id="option_d" name="option_d" value="<?php echo htmlspecialchars($option_d); ?>">
            </div>
        </div>
        
        <div class="mb-3">
            <label for="correct_option" class="form-label fw-bold text-success">Correct Option (A, B, C, or D)</label>
            <select class="form-select" id="correct_option" name="correct_option" required>
                <?php $options = ['A', 'B', 'C', 'D']; foreach($options as $opt): ?>
                    <option value="<?php echo $opt; ?>" <?php echo ($correct_option == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <a href="quizzes.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Quiz</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>