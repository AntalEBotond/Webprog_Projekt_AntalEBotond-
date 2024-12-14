<?php
require_once 'classes/User.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = new User();

    try {
        $message = $user->register($username, $email, $password);
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="public/register.css">
</head>
<body>
    
    <div class="form-container">
        <h1>Regisztráció</h1>
        <form method="POST" action="">
            <div class="input-group">
                <label for="username">Felhasználónév</label>
                <input type="text" id="username" name="username" placeholder="Add meg a felhasználóneved" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Add meg az email címed" required>
            </div>
            <div class="input-group">
                <label for="password">Jelszó</label>
                <input type="password" id="password" name="password" placeholder="Add meg a jelszavad" required>
            </div>
            <button type="submit" class="btn">Regisztráció</button>
            <p class="message"><?= $message ?></p>
            <p>Már van fiókod? <a href="login.php">Bejelentkezés</a></p>
        </form>
    </div>
</body>
</html>
