<?php
require_once 'classes/User.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = new User();

    try {
        $message = $user->login($email, $password);
        header("Location: index.php");
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="public/login.css">
</head>
<body>
    <div class="background-video-container">
        <video autoplay muted loop id="background-video">
        <source src="/Webprog_Projekt_AntalEBotond/GradientLoopBackground.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <div class="form-container">
        <h1>Bejelentkezés</h1>
        <form method="POST" action="">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Add meg az email címed" required>
            </div>
            <div class="input-group">
                <label for="password">Jelszó</label>
                <input type="password" id="password" name="password" placeholder="Add meg a jelszavad" required>
            </div>
            <button type="submit" class="btn">Bejelentkezés</button>
            <p class="message"><?= $message ?></p>
            <p><span class="no-account-text">Nincs még fiókod? </span><a href="register.php">Regisztráció</a></p>
            </form>
    </div>
</body>
</html>
