<?php
session_start();

// Passwort definieren
define('ADMIN_PASSWORD', 'geheim');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Falsches Passwort!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: sans-serif; background: #f5f5f5; padding: 50px; text-align: center; }
        form { display: inline-block; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="password"] { padding: 10px; font-size: 1em; width: 200px; }
        button { padding: 10px 20px; font-size: 1em; background: #007acc; color: #fff; border: none; border-radius: 5px; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Admin Login</h1>
    <form method="post">
        <input type="password" name="password" placeholder="Passwort" required>
        <br><br>
        <button type="submit">Einloggen</button>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </form>
</body>
</html>
