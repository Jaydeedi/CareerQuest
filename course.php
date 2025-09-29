<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'] ?? 0;

$course_id = $_GET['id'] ?? null;

if (!$course_id || !is_numeric($course_id)) {
    echo '<div class="alert alert-danger mt-4">Invalid course ID.</div>';
    require_once 'includes/footer.php';
    exit;
}

$db = getDbConnection();

$stmt = $db->prepare("SELECT title, description FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo '<div class="alert alert-danger mt-4">Course not found.</div>';
    require_once 'includes/footer.php';
    exit;
}

$stmt = $db->prepare("
    SELECT 
        m.id AS module_id,
        m.module_title, 
        l.id AS lesson_id, 
        l.lesson_title, 
        l.lesson_order,
        MAX(up.is_completed) AS is_completed
    FROM modules m
    JOIN lessons l ON m.id = l.module_id
    LEFT JOIN user_progress up ON l.id = up.lesson_id AND up.user_id = ?
    WHERE m.course_id = ?
    GROUP BY 
        m.id, m.module_title, 
        l.id, l.lesson_title, l.lesson_order 
    ORDER BY m.module_order ASC, l.lesson_order ASC
");
$stmt->execute([$user_id, $course_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$modules = [];
$total_course_lessons = 0;
$total_completed_lessons = 0;

foreach ($results as $row) {
    $module_title = $row['module_title'];
    $module_id = $row['module_id'];
    
    if (!isset($modules[$module_id])) {
        $modules[$module_id] = [
            'title' => $module_title,
            'lessons' => [],
            'total_lessons' => 0,
            'completed_lessons' => 0,
            'is_unlocked' => true,
            'last_lesson_completed' => true
        ];
    }
    
    $is_completed = $row['is_completed'] ?? 0;
    
    if ($is_completed) {
        $modules[$module_id]['completed_lessons']++;
        $total_completed_lessons++;
    } 
    
    $modules[$module_id]['total_lessons']++;
    $total_course_lessons++;
    
    $modules[$module_id]['lessons'][] = [
        'lesson_id' => $row['lesson_id'],
        'lesson_title' => $row['lesson_title'],
        'is_completed' => (bool)$is_completed
    ];
}


$stmt_total_lessons = $db->prepare("
    SELECT COUNT(l.id) 
    FROM lessons l 
    JOIN modules m ON l.module_id = m.id 
    WHERE m.course_id = ?
");
$stmt_total_lessons->execute([$course_id]);
$total_lessons = $stmt_total_lessons->fetchColumn();


$stmt_completed_lessons = $db->prepare("
    SELECT COUNT(up.lesson_id) 
    FROM user_progress up
    JOIN lessons l ON up.lesson_id = l.id
    JOIN modules m ON l.module_id = m.id 
    WHERE m.course_id = ? 
    AND up.user_id = ?
    AND up.is_completed = 1        -- Code Completed
    AND up.quiz_completed = 1      -- Quiz Completed
");
$stmt_completed_lessons->execute([$course_id, $user_id]);
$fully_completed_lessons = $stmt_completed_lessons->fetchColumn();


$progress_percent = 0;
if ($total_lessons > 0) {
    $progress_percent = round(($fully_completed_lessons / $total_lessons) * 100);
}


$is_course_completed = ($progress_percent == 100 && $total_lessons > 0);

$course_completion_percent = ($total_course_lessons > 0) ? round(($total_completed_lessons / $total_course_lessons) * 100) : 0;
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="fw-bold"><?php echo htmlspecialchars($course['title']); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($course['description']); ?></p>

        <div class="card shadow-sm p-3 mb-4">
            <h5 class="mb-2">Your Progress: <?php echo $progress_percent; ?>%</h5>
            <div class="progress" style="height: 25px;">
                <div 
                    class="progress-bar <?php echo $is_course_completed ? 'bg-success' : 'bg-info'; ?>" 
                    role="progressbar" 
                    style="width: <?php echo $progress_percent; ?>%" 
                    aria-valuenow="<?php echo $progress_percent; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100">
                    <?php echo $progress_percent; ?>% Complete
                </div>
            </div>
            
            <?php if ($is_course_completed): ?>
                <div class="mt-3 text-center">
                    <a href="generate_certificate.php?course_id=<?php echo $course_id; ?>" target="_blank" class="btn btn-lg btn-warning fw-bold">
                        🏆 Claim Your Certificate!
                    </a>
                    <p class="mt-2 text-success fw-bold">Congratulations! You have successfully finished this course.</p>
                </div>
            <?php endif; ?>
        </div>
        
        </div>
</div>


<div class="accordion" id="courseAccordion">
    <?php 
    $module_index = 0;
    $previous_lesson_completed = true;

    foreach ($modules as $module_id => &$module):

        $module_index++;
        
        $module_percent = ($module['total_lessons'] > 0) ? round(($module['completed_lessons'] / $module['total_lessons']) * 100) : 0;
        $is_module_completed = ($module_percent === 100);
    ?>
    <div class="accordion-item shadow-sm mb-3 border-0">
        <h2 class="accordion-header" id="heading<?php echo $module_id; ?>">
            <button class="accordion-button fw-bold <?php echo $module_index > 1 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $module_id; ?>" aria-expanded="<?php echo $module_index === 1 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $module_id; ?>">
                Module <?php echo $module_index; ?>: <?php echo htmlspecialchars($module['title']); ?> 
                <span class="ms-3 badge <?php echo $is_module_completed ? 'bg-success' : 'bg-primary'; ?>">
                    <?php echo $is_module_completed ? '100% Complete' : "{$module_percent}%"; ?>
                </span>
            </button>
        </h2>
        <div id="collapse<?php echo $module_id; ?>" class="accordion-collapse collapse <?php echo $module_index === 1 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $module_id; ?>" data-bs-parent="#courseAccordion">
            <div class="accordion-body p-0">
                <ul class="list-group list-group-flush">
                    <?php 
                    $lesson_index = 0;
                    foreach ($module['lessons'] as &$lesson): 
                        $lesson_index++;
                        
                        $is_completed = $lesson['is_completed'];
                        
                        $is_unlocked = $previous_lesson_completed;
                        $btn_disabled = ($user_id === 0 || !$is_unlocked) ? 'disabled' : '';
                        $lesson_status = ($is_completed) ? '✅ Completed' : (($is_unlocked) ? 'Ready' : '🔒 Locked');
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                **<?php echo $lesson_index; ?>. <?php echo htmlspecialchars($lesson['lesson_title']); ?>**
                                <span class="ms-2 badge <?php echo $is_completed ? 'bg-success' : ($is_unlocked ? 'bg-info' : 'bg-secondary'); ?>">
                                    <?php echo $lesson_status; ?>
                                </span>
                            </span>
                            
                            <?php if ($is_completed): ?>
                                <a href="lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-sm btn-outline-success">Review</a>
                            <?php else: ?>
                                <a href="lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-sm btn-green <?php echo $btn_disabled; ?>" <?php echo $btn_disabled; ?>>
                                    Start Lesson
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php 
                        $previous_lesson_completed = $is_completed;
                    
                    endforeach; 

                    ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>