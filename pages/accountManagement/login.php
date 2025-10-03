<?php
session_start();
require_once __DIR__ . '/../data/sql.php';

$dbPath = __DIR__ . '/../data/monarch.db';
$pdo = new PDO("sqlite:" . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        header("Location: ../index.php?page=journal"); // <-- fixed path
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<form method="post">
    <h2>Login</h2>
    <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
    <label>Username: <input type="text" name="username"></label><br>
    <label>Password: <input type="password" name="password"></label><br>
    <button type="submit">Login</button>
</form>
