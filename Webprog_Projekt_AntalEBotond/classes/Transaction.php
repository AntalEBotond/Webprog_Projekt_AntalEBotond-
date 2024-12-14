<?php
class Transaction {
    private $conn;

    public function __construct() {
        // Csatlakozás az adatbázishoz
        $this->conn = new PDO("mysql:host=localhost;dbname=expense_tracker", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Tranzakció hozzáadása
    public function addTransaction($userId, $amount, $description, $type) {
        if (empty($amount) || empty($description)) {
            throw new Exception("A tranzakció összege és leírása szükséges!");
        }

        if ($type === 'expense' && $amount < 0) {
            $amount = -abs($amount);
        }

        // Tranzakció rögzítése
        $sql = "INSERT INTO transactions (user_id, amount, description) VALUES (:user_id, :amount, :description)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'amount' => $amount,
            'description' => $description
        ]);

        // A kiadásokat és bevételeket is kezeljük
        $this->updateBalance($userId, $amount);

        return "A tranzakció sikeresen hozzáadva!";
    }

    // Egyenleg frissítése
    public function updateBalance($userId, $amount) {
        $sql = "UPDATE users SET balance = balance + :amount WHERE id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'amount' => $amount
        ]);
    }

    // Tranzakciók lekérése
    public function getTransactions($userId) {
        $sql = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
