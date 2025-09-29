<?php

$stmt_quiz = $db->prepare("
    SELECT id, question, option_a, option_b, option_c, option_d
    FROM quizzes
    WHERE lesson_id = ?
");
$stmt_quiz->execute([$lesson_id]);
$questions = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);

if (count($questions) === 0) {
    echo '<div class="alert alert-warning mt-4">No quiz questions found for this lesson.</div>';
}
?>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card h-100 shadow-sm p-4">
            <h4 class="text-success mb-3">Instructions (<?php echo $module_title; ?>)</h4>
            <div class="lesson-instructions">
                <p><?php echo nl2br($lesson['instructions']); ?></p>
                <p class="mt-4 fw-bold text-danger">Answer all the questions on the right to complete the lesson.</p>
            </div>
        </div>
    </div>

    <div class="col-md-7 mb-4">
        <form id="quiz-form" class="card shadow-lg p-4">
            <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">

            <?php foreach ($questions as $index => $q): ?>
                <div class="mb-4 p-3 border rounded bg-light">
                    <p class="fw-bold">Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($q['question']); ?></p>
                    <input type="hidden" name="question_ids[]" value="<?php echo $q['id']; ?>">

                    <?php
                    $options = ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']];
                    foreach ($options as $key => $value):
                    ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="answer_<?php echo $q['id']; ?>" id="option_<?php echo $q['id'] . $key; ?>" value="<?php echo $key; ?>" <?php echo $is_quiz_completed ? 'disabled' : ''; ?> required>
                            <label class="form-check-label" for="option_<?php echo $q['id'] . $key; ?>">
                                **<?php echo $key; ?>.** <?php echo htmlspecialchars($value); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$is_quiz_completed): ?>
                <button type="submit" id="submit-quiz-btn" class="btn btn-green w-100 fw-bold mt-3">Submit Answers</button>
            <?php endif; ?>
        </form>

        <div class="mt-3">
            <div class="card bg-light shadow-sm">
                <div class="card-header fw-bold">Quiz Results</div>
                <div id="quiz-results" class="p-3">
                    <?php echo $is_quiz_completed ? '<span class="text-success">You have completed this quiz.</span>' : 'Submit your answers to see the results.'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() { 
        const quizForm = document.getElementById('quiz-form');
        const submitBtn = document.getElementById('submit-quiz-btn');
        const quizResults = document.getElementById('quiz-results');

        if (quizForm) {
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
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                });
            });
        }
    })();
</script>