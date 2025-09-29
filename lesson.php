<?php
session_start(); 
require_once 'config/db.php';
require_once 'includes/header.php';


$lesson_id = $_GET['id'] ?? null;

if (!$lesson_id || !is_numeric($lesson_id)) {
    echo '<div class="alert alert-danger mt-4">Invalid lesson ID.</div>';
    require_once 'includes/footer.php';
    exit;
}

$db = getDbConnection();

$stmt = $db->prepare("
    SELECT
        l.lesson_title, l.instructions, l.starter_code, l.expected_output,
        l.lesson_type, 
        m.module_title,
        c.title as course_title, c.id as course_id
    FROM lessons l
    JOIN modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ?
");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo '<div class="alert alert-danger mt-4">Lesson not found.</div>';
    require_once 'includes/footer.php';
    exit;
}

$course_title = htmlspecialchars($lesson['course_title']);
$module_title = htmlspecialchars($lesson['module_title']);
$lesson_title = htmlspecialchars($lesson['lesson_title']);
$course_id = $lesson['course_id'];

$is_completed = false;
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id) {
    $stmt = $db->prepare("SELECT is_completed FROM user_progress WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $lesson_id]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_completed = $progress['is_completed'] ?? false;
}
?>

<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Courses</a></li>
        <li class="breadcrumb-item"><a href="course.php?id=<?php echo $course_id; ?>"><?php echo $course_title; ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $lesson_title; ?></li>
    </ol>
</nav>

<h1 class="fw-bold mb-4 text-primary"><?php echo $lesson_title; ?>
    <?php if ($is_completed): ?>
        <span class="badge bg-success ms-2 fs-6">✅ Code Completed</span>
        
        <?php 
        $stmt_quiz_check = $db->prepare("SELECT COUNT(id) FROM quizzes WHERE lesson_id = ?");
        $stmt_quiz_check->execute([$lesson_id]);
        $quiz_exists = $stmt_quiz_check->fetchColumn() > 0;
        
        if ($quiz_exists): 
            $stmt_quiz_done = $db->prepare("SELECT quiz_completed FROM user_progress WHERE user_id = ? AND lesson_id = ?");
            $stmt_quiz_done->execute([$user_id, $lesson_id]);
            $is_quiz_completed = $stmt_quiz_done->fetchColumn() ?? 0;

            if ($is_quiz_completed):
        ?>
                <span class="badge bg-primary ms-2 fs-6">✅ Quiz Passed</span>
        <?php else: ?>
                <button class="btn btn-warning btn-sm ms-2 fw-bold" id="take-quiz-btn">
                    <i class="bi bi-question-circle"></i> Take Quiz
                </button>
        <?php 
            endif;
        endif;
        ?>

    <?php endif; ?>
</h1>


<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm p-4">
            <h4 class="text-success mb-3">Instructions (<?php echo $module_title; ?>)</h4>
            <div class="lesson-instructions">
                <p><?php echo nl2br($lesson['instructions']); ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-lg">
            <div class="card-header bg-dark text-white fw-bold">Code Editor (PHP)</div>
            
            <div id="editor" style="height: 350px;">
                <?php echo htmlspecialchars($lesson['starter_code']); ?>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <button id="run-code-btn" class="btn btn-primary fw-bold" <?php echo $is_completed ? 'disabled' : ''; ?>>
                    <i class="bi bi-play-fill"></i> Run Code
                </button>
                <button id="submit-code-btn" class="btn btn-green fw-bold ms-2" <?php echo $is_completed ? 'disabled' : ''; ?>>
                    <i class="bi bi-check-circle"></i> Submit Answer
                </button>
            </div>
        </div>

        <div class="mt-3">
            <div class="card bg-light shadow-sm">
                <div class="card-header fw-bold">Output Console</div>
                <pre id="output-console" class="p-3 mb-0" style="background-color: #333; color: #eee; border-radius: 0 0 0.5rem 0.5rem;">Click 'Run Code' to see the result.</pre>
            </div>
        </div>
    </div>
</div>

<div id="quiz-container" class="mt-5">
    <?php if (!$is_completed): ?>
        <div class="alert alert-info text-center">Please complete the code editor challenge above to unlock the assessment quiz.</div>
    <?php elseif ($is_completed && $quiz_exists && $is_quiz_completed): ?>
        <div class="alert alert-success text-center">✅ You have completed both the challenge and the quiz for this lesson!</div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.33.0/ace.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/php");
        editor.setFontSize(14);
        
        <?php if ($is_completed): ?>
            editor.setReadOnly(true);
        <?php endif; ?>

        const runBtn = document.getElementById('run-code-btn');
        const submitBtn = document.getElementById('submit-code-btn');
        const outputConsole = document.getElementById('output-console');
        
        const takeQuizBtn = document.getElementById('take-quiz-btn');
		const quizContainer = document.getElementById('quiz-container');

		function attachQuizFormListener() {
    const quizForm = document.getElementById('quiz-form');
    const submitBtn = document.getElementById('submit-quiz-btn');
    const quizResults = document.getElementById('quiz-results');

    if (quizForm) {
        if (quizForm.dataset.listenerAttached) {
            return;
        }
        quizForm.dataset.listenerAttached = 'true'; 

        quizForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            quizResults.innerHTML = 'Checking answers...';
            submitBtn.disabled = true;

            const formData = new FormData(quizForm);
            const urlSearchParams = new URLSearchParams(formData);

            fetch('quiz_validator.php', {
                method: 'POST',
                body: urlSearchParams.toString(),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                if(data.success) {
                    alert("Congratulations! Quiz Passed!");
                    location.reload(); 
                } else {
                    quizResults.innerHTML = `<h5 class="text-danger">❌ Try Again!</h5><p>Score: ${data.score} out of ${data.total_questions}</p><p>Kailangan mo ng 100% para matapos ang lesson.</p>`;
                }
            })
            .catch(error => {
                quizResults.innerHTML = 'Error submitting quiz: ' + error;
                submitBtn.disabled = false;
            });
        });
    }
}


if (takeQuizBtn) {
    takeQuizBtn.addEventListener('click', function() {
        quizContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div> Loading Quiz...</div>';
        takeQuizBtn.disabled = true;

        fetch('quiz_view_loader.php?lesson_id=<?php echo $lesson_id; ?>')
            .then(response => response.text())
            .then(html => {
                quizContainer.innerHTML = html;
                attachQuizFormListener(); 
            })
            .catch(error => {
                quizContainer.innerHTML = '<div class="alert alert-danger">Error loading quiz. Please try again.</div>';
                console.error('Quiz load error:', error);
            })
            .finally(() => {
                takeQuizBtn.style.display = 'none'; 
            });
    });
}

        runBtn.addEventListener('click', function() {
            outputConsole.textContent = 'Running code...';
            runBtn.disabled = true;
           
            const code = editor.getValue();
            fetch('code_runner.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'code=' + encodeURIComponent(code)
            })
            .then(response => response.text())
            .then(data => {
                outputConsole.textContent = data.trim() || "(No output)";
                runBtn.disabled = false;
            })
            .catch(error => {
                outputConsole.textContent = 'Error: Could not connect to runner.';
                runBtn.disabled = false;
            });
        });


        submitBtn.addEventListener('click', function() {
            outputConsole.textContent = 'Checking answer...';
            submitBtn.disabled = true;
            
            const code = editor.getValue();

            fetch('code_validator.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'lesson_id=<?php echo $lesson_id; ?>&code=' + encodeURIComponent(code)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    outputConsole.innerHTML = `<span style="color: green;">✅ Correct!</span><br>Output: ${data.output}`;
                    alert("Congratulations! Code Challenge Completed! The Quiz is now unlocked.");
                    location.reload(); // I-reload para lumabas ang Quiz
                } else {
                    outputConsole.innerHTML = `<span style="color: red;">❌ Incorrect.</span><br>Output: ${data.output}<br>Expected: ${data.expected}`;
                }
                submitBtn.disabled = false;
            })
            .catch(error => {
                outputConsole.textContent = 'Error submitting answer.';
                submitBtn.disabled = false;
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>