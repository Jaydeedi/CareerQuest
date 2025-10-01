<?php

// --- fake database classes ---
class FakeDB {
    private $users;
    public function __construct($users) { $this->users = $users; }
    public function prepare($query) { return new FakeStatement($this->users); }
}

class FakeStatement {
    private $users;
    private $params;
    public function __construct($users) { $this->users = $users; }
    public function execute($params = null) { $this->params = $params; return true; }
    public function fetch($mode = null) {
        $email = $this->params[0];
        return $this->users[$email] ?? false;
    }
}

// --- login function (simplified version of your code) ---
function loginUser($db, $email, $password)
{
    try {
        $stmt = $db->prepare("SELECT id, name, password_hash, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin']  = $user['is_admin'] ?? 0;

            return $user['is_admin'] == '1'
                ? 'admin/index.php'
                : 'index.php';
        } else {
            return "Incorrect email or password.";
        }
    } catch (Exception $e) {
        return "A login error occurred.";
    }
}

// --- pest tests ---
it('logs in successfully as normal user', function () {
    $password = password_hash('secret', PASSWORD_DEFAULT);

    $db = new FakeDB([
        'user@example.com' => [
            'id' => 1,
            'name' => 'Normal User',
            'password_hash' => $password,
            'is_admin' => 0
        ]
    ]);

    $_SESSION = [];

    $result = loginUser($db, 'user@example.com', 'secret');

    expect($result)->toBe('index.php');
    expect($_SESSION['user_name'])->toBe('Normal User');
    expect($_SESSION['is_admin'])->toBe(0);
});

it('logs in successfully as admin user', function () {
    $password = password_hash('adminpass', PASSWORD_DEFAULT);

    $db = new FakeDB([
        'admin@example.com' => [
            'id' => 2,
            'name' => 'Admin User',
            'password_hash' => $password,
            'is_admin' => 1
        ]
    ]);

    $_SESSION = [];

    $result = loginUser($db, 'admin@example.com', 'adminpass');

    expect($result)->toBe('admin/index.php');
    expect($_SESSION['is_admin'])->toBe(1);
});

it('fails with wrong password', function () {
    $password = password_hash('secret', PASSWORD_DEFAULT);

    $db = new FakeDB([
        'user@example.com' => [
            'id' => 1,
            'name' => 'Normal User',
            'password_hash' => $password,
            'is_admin' => 0
        ]
    ]);

    $_SESSION = [];

    $result = loginUser($db, 'user@example.com', 'wrong');

    expect($result)->toBe('Incorrect email or password.');
    expect($_SESSION)->toBe([]); // walang laman
});

it('fails when user not found', function () {
    $db = new FakeDB([]); // walang user

    $_SESSION = [];

    $result = loginUser($db, 'ghost@example.com', 'secret');

    expect($result)->toBe('Incorrect email or password.');
});
