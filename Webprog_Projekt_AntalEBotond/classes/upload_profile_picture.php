<?php
session_start();
require_once 'classes/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    // Ellenőrizzük, hogy a fájl helyes típusú
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
        $userId = $_SESSION['user_id'];
        $uploadDir = 'uploads/profiles/';
        $uploadFile = $uploadDir . basename($_FILES['profile_picture']['name']);

        // Fájl feltöltése
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
            $user = new User();
            $user->updateProfilePicture($userId, $uploadFile); // Az URL-t elmentjük az adatbázisba
            header("Location: index.php");
            exit;
        } else {
            echo 'Hiba történt a fájl feltöltésekor.';
        }
    } else {
        echo 'Csak JPEG, PNG vagy GIF típusú képek engedélyezettek.';
    }
}
?>
