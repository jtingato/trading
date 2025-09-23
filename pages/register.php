<?php
session_start();
require_once __DIR__ . '/../data/sql.php';

$dbPath = __DIR__ . '/../data/monarch.db';
$pdo = new PDO("sqlite:" . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Username and password are required.";
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already taken.";
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword
            ]);
            $success = "Registration successful. You can now log in.";
        }
    }
}
?>

<form method="post">
    <h2>Register</h2>
    <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?php echo $success; ?></p><?php endif; ?>
    <label>Username: <input type="text" name="username"></label><br>
    <label>Password: <input type="password" name="password"></label><br>
    <button type="submit">Register</button>
</form>
