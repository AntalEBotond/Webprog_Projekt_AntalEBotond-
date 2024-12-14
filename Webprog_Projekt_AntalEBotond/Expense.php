<?php
require_once 'Database.php';

class Expense extends Database {
    public function addExpense($userId, $amount, $category, $date, $description = null) {
        if (empty($amount) || empty($category) || empty($date)) {
            throw new Exception("Minden mezőt ki kell tölteni!");
        }

        $sql = "INSERT INTO expenses (user_id, amount, category, date, description) 
                VALUES (:user_id, :amount, :category, :date, :description)";
        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            'user_id' => $userId,
            'amount' => $amount,
            'category' => $category,
            'date' => $date,
            'description' => $description
        ]);
        return "Kiadás sikeresen hozzáadva!";
    }

    public function getExpenses($userId) {
        $sql = "SELECT * FROM expenses WHERE user_id = :user_id ORDER BY date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
