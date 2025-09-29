<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['course_id'])) {
    die("Invalid request.");
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'];
$db = getDbConnection();


$stmt = $db->prepare("
    SELECT c.title AS course_title, u.name AS user_name, u.email, MAX(up.completion_date) AS final_completion_date
    FROM courses c
    JOIN users u ON u.id = ?
    LEFT JOIN lessons l ON l.module_id IN (SELECT id FROM modules WHERE course_id = c.id)
    LEFT JOIN user_progress up ON up.lesson_id = l.id AND up.user_id = u.id
    WHERE c.id = ?
    GROUP BY c.id, u.id
");
$stmt->execute([$user_id, $course_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Course or user data not found.");
}


$completion_date = $data['final_completion_date'] ? date("F j, Y", strtotime($data['final_completion_date'])) : date("F j, Y");


header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap');
        
        body {
            background-color: #f8f9fa;
        }
        .certificate-container {
            width: 1000px; /* Standard certificate size */
            height: 700px;
            margin: 50px auto;
            border: 20px solid #007bff;
            padding: 40px;
            text-align: center;
            background: linear-gradient(180deg, #ffffff, #f0f8ff);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .cert-header {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: #007bff;
            margin-bottom: 20px;
        }
        .cert-subheader {
            font-size: 24px;
            color: #343a40;
            margin-bottom: 50px;
        }
        .cert-name {
            font-family: 'Playfair Display', serif;
            font-size: 60px;
            color: #28a745; /* Success Green */
            border-bottom: 3px dashed #28a745;
            padding-bottom: 10px;
            margin-bottom: 50px;
            display: inline-block;
        }
        .cert-text {
            font-size: 20px;
            color: #555;
            line-height: 1.5;
        }
        .cert-course {
            font-style: italic;
            font-weight: bold;
            color: #007bff;
        }
        .cert-date {
            margin-top: 50px;
            font-size: 18px;
            color: #343a40;
        }
        @media print {
            .certificate-container {
                border: none;
                box-shadow: none;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center pt-3">
    <button onclick="window.print()" class="btn btn-lg btn-success">Print Certificate</button>
    <a href="course.php?id=<?php echo $course_id; ?>" class="btn btn-lg btn-secondary">Go Back to Course</a>
</div>

<div class="certificate-container">
    <div class="cert-header">CERTIFICATE OF ACHIEVEMENT</div>
    <div class="cert-subheader">This certifies that</div>
    
    <div class="cert-name"><?php echo htmlspecialchars($data['user_name']); ?></div>

    <div class="cert-text">
        Has successfully completed the online course
        <br><br>
        <span class="cert-course">"<?php echo htmlspecialchars($data['course_title']); ?>"</span>
        <br><br>
        with 100% mastery, demonstrating strong proficiency in the subject matter.
    </div>

    <div class="cert-date">
        Awarded on: <?php echo $completion_date; ?>
        <br>
        <hr style="width: 300px; margin: 50px auto 0;">
        Career Quest
    </div>
</div>

</body>
</html>