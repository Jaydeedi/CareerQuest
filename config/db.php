<?php
// Configuration (I-edit ayon sa inyong server settings)
define('DB_HOST', 'jstnagls.shop');
define('DB_NAME', 'u163142708_career_quest');
define('DB_USER', 'u163142708_career_quest');
define('DB_PASS', 'Careerquest12345');

function getDbConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Database connection error: " . $e->getMessage());
    }
}
?>