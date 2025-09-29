<?php
session_start();
require_once 'config/db.php';
$db = getDbConnection(); 

$is_logged_in = isset($_SESSION['user_id']);

if ($is_logged_in) {
    $page_title = "Available Courses";
    require_once 'includes/header.php';
    
    $stmt = $db->prepare("SELECT id, title, description, icon_class FROM courses WHERE is_active = TRUE ORDER BY id ASC");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="container mt-5">
        <h1>Available Courses</h1>
        <p class="lead text-muted">Continue your learning journey with our available courses.</p>
        <hr>

        <div class="row">
            <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h5 class="text-success mb-3"><i class="<?php echo htmlspecialchars($course['icon_class'] ?? 'bi-code-slash'); ?>"></i> <?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text text-secondary"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-green mt-3 w-100">Start Learning</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">There are no courses listed yet.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>

<?php
} else {
    $page_title = "Welcome to Career Quest";
	
	$stmt_preview = $db->prepare("SELECT title, description, icon_class FROM courses WHERE is_active = TRUE ORDER BY id ASC LIMIT 3");
    $stmt_preview->execute();
    $preview_courses = $stmt_preview->fetchAll(PDO::FETCH_ASSOC);
    require_once 'includes/header.php';
    ?>
    
    <header class="text-center py-5 mb-5 bg-white shadow-sm">
        <div class="container">
            <h1 class="display-3 fw-bolder text-primary">Career Quest</h1>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Interactive coding challenges and structured courses designed to make you a professional developer.
            </p>
            <div class="mt-4">
                <a class="btn btn-blue btn-lg mx-2" href="register.php" role="button">Start for Free!</a>
                <a class="btn btn-outline-secondary btn-lg mx-2" href="login.php" role="button">Login</a>
            </div>
        </div>
    </header>

    <section id="about" class="container py-5">
        <h2 class="text-center fw-bold mb-4">About Us</h2>
        <div class="row align-items-center">
            <p class="lead">We are committed to providing high-quality, free programming education to everyone.</p>
                <p class="text-secondary">
                    Career Quest was founded to fill the gap in online programming education, especially for PHP and web development. We believe that practical hands-on experience is the best way to learn, which is why our lessons include interactive code editors and quizzes.
                </p>
        </div>
    </section>

    <hr>
    
    <section id="courses" class="container py-5 bg-light rounded shadow-sm">
        <h2 class="text-center fw-bold mb-5">Our Course Offerings</h2>
        <div class="row text-center">
            
            <?php if (count($preview_courses) > 0): ?>
                <?php foreach ($preview_courses as $p_course): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-success">
                            <div class="card-body">
                                <i class="<?php echo htmlspecialchars($p_course['icon_class'] ?? 'bi-file-earmark-code'); ?> display-4 text-success"></i>
                                <h4 class="mt-3"><?php echo htmlspecialchars($p_course['title']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars(substr($p_course['description'], 0, 80)) . '...'; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                     <p class="text-danger">No courses available for preview yet.</p>
                </div>
            <?php endif; ?>
            
        </div>
        <div class="text-center mt-4">
             <p class="text-muted fst-italic">Login or Register to access the full course content.</p>
        </div>
    </section>

    <hr>

    <section id="testimonials" class="container py-5">
        <h2 class="text-center fw-bold mb-5">What Our Students Say</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <i class="bi-chat-quote display-6 text-info"></i>
                    <p class="card-text fst-italic mt-2">"The interactive editor made learning so much easier. I immediately applied what I learned to my projects!"</p>
                    <footer class="blockquote-footer mt-2">Juan Dela Cruz</footer>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <i class="bi-chat-quote display-6 text-info"></i>
                    <p class="card-text fst-italic mt-2">"Before Career Quest, PHP was daunting. Now, I feel confident building full-stack applications."</p>
                    <footer class="blockquote-footer mt-2">Maria Santos</footer>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card p-3 h-100 shadow-sm border-0">
                    <i class="bi-chat-quote display-6 text-info"></i>
                    <p class="card-text fst-italic mt-2">"The quizzes are challenging but effective. The course completion certificate is a great bonus!"</p>
                    <footer class="blockquote-footer mt-2">Peter Lim</footer>
                </div>
            </div>
        </div>
    </section>

    <hr>
    
    <section id="team" class="container py-5">
        <h2 class="text-center fw-bold mb-5">Meet the Team</h2>
        <div class="row justify-content-center text-center">
            <div class="col-md-4 mb-4">
                <i class="bi-person-circle display-1 text-primary"></i>
                <h4 class="mt-3">The PHP Architect</h4>
                <p class="text-muted">Main Course Creator & Developer</p>
            </div>
            <div class="col-md-4 mb-4">
                <i class="bi-person-circle display-1 text-success"></i>
                <h4 class="mt-3">The Content Strategist</h4>
                <p class="text-muted">Education and Learning Specialist</p>
            </div>
        </div>
    </section>

    <hr>

    <section id="faq" class="container py-5">
        <h2 class="text-center fw-bold mb-5">Frequently Asked Questions</h2>
        
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        1. Are the courses free?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, all of our core courses are 100% free. You just need to register to get started.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        2. What makes Career Quest different from other platforms?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Career Quest focuses on **interactive coding**. You can practice and test your code immediately in each lesson.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        3. Will I get a Certificate?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, you will get a Course Completion Certificate after completing all lessons and quizzes in a course.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr>

    <section id="contact" class="container py-5">
        <h2 class="text-center fw-bold mb-5">Contact Us</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-info text-center">
                    <i class="bi-envelope-fill me-2"></i> For inquiries, email: **support@careerquest.com**
                </div>
                <form>
                    <div class="mb-3">
                        <label for="contactName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="contactName" required>
                    </div>
                    <div class="mb-3">
                        <label for="contactEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="contactEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="contactMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="contactMessage" rows="4" required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-blue btn-lg">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
    
<?php
}
?>