<?php

it('returns a valid database connection', function () {
    require_once __DIR__ . '/../config/db.php';

    $db = getDbConnection();

    expect($db)->not->toBeNull();
    expect($db)->toBeInstanceOf(PDO::class); // Assuming it returns a PDO instance
});