<?php

require_once 'Database.php';

class User extends Database {
    // Regisztráció
    public function register($username, $email, $password) {
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("Minden mezőt ki kell tölteni!");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Érvénytelen email-cím!");
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, email, password, balance) VALUES (:username, :email, :password, :balance)";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'balance' => 0 // Kezdő egyenleg
            ]);
            return "Sikeres regisztráció!";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                throw new Exception("A megadott felhasználónév vagy email már létezik.");
            }
            throw $e;
        }
    }

    // Bejelentkezés
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            throw new Exception("Minden mezőt ki kell tölteni!");
        }

        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Érvénytelen email-cím vagy jelszó!");
        }

        // Bejelentkezés során menthetjük a session-t
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        return "Sikeres bejelentkezés!";
    }

    // Kilépés
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
    }

    // Felhasználó adatainak lekérdezése
    public function getUserById($userId) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Profilkép frissítése
    public function updateProfilePicture($userId, $file) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Csak JPG, PNG, JPEG vagy GIF fájlokat lehet feltölteni.");
        }

        $targetDir = "uploads/profile_images/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $targetFile = $targetDir . basename($file["name"]);
        
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            $sql = "UPDATE users SET profile_picture = :profile_picture WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['profile_picture' => $targetFile, 'id' => $userId]);

            return "Profilkép sikeresen frissítve!";
        } else {
            throw new Exception("Hiba történt a fájl feltöltésekor.");
        }
    }

    // Egyenleg frissítése
    public function updateBalance($userId) {
        $sql = "SELECT SUM(amount) AS balance FROM transactions WHERE user_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $newBalance = $result['balance'] ?? 0;
        
        $sql = "UPDATE users SET balance = :balance WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['balance' => $newBalance, 'id' => $userId]);
    }
}
?>
