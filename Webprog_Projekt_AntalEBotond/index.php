    <?php
    session_start();
    require_once 'classes/User.php';
    require_once 'classes/Transaction.php';


    // Ha nincs bejelentkezve a felhasználó, irányítsuk át a login oldalra
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $user = new User();
    $transaction = new Transaction();

    // Felhasználó adatainak lekérése
    $currentUser = $user->getUserById($userId);

    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Profilkép frissítése
        if (isset($_FILES['profile_picture'])) {
            try {
                // Profilkép frissítése
                $message = $user->updateProfilePicture($userId, $_FILES['profile_picture']);
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
        
        // Tranzakció hozzáadása
        if (isset($_POST['amount']) && isset($_POST['description'])) {
            try {
                $amount = (int)$_POST['amount'];
                $description = $_POST['description'];
                $type = $_POST['type'] ?? 'income';

                // Kiadás esetén a pénz levonása az egyenlegből
                if ($type === 'expense') {
                    $amount = -abs($amount); // Kiadás esetén biztosítjuk, hogy negatív legyen
                }

                // A tranzakció hozzáadása
                $message = $transaction->addTransaction($userId, $amount, $description, $type);

                // Egyenleg frissítése
                $user->updateBalance($userId);

                // Post-Redirect-Get minta: átirányítjuk a felhasználót a főoldalra a sikeres tranzakció után
                header("Location: index.php");
                exit;

            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
    }

    // Aktuális egyenleg lekérése
    $currentBalance = $user->getUserById($userId)['balance'];
    // Kiadások és bevételek lekérdezése
    $transactions = $transaction->getTransactions($userId);
    ?>

    <!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <title>Profil frissítése</title>
        <link rel="stylesheet" href="public/style.css">
        <style>
    body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px;
            box-sizing: border-box;
        }

        .balance {
            background-color: #1a1a1a;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
            animation: glow 1.5s ease-in-out infinite alternate;
        }

        @keyframes glow {
            0% {
                box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
            }
            100% {
                box-shadow: 0 0 20px rgba(0, 255, 0, 1);
            }
        }

        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
            margin-bottom: 50px;
        }

        .form-container form {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            flex: 1 1 calc(50% - 20px);
            box-sizing: border-box;
            min-width: 280px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .form-container form:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 255, 0, 0.5);
        }

        .form-container input[type="number"],
        .form-container input[type="text"],
        .form-container button {
            width: 100%;
            padding: 20px;
            margin: 10px 0;
            border: 2px solid #4CAF50;
            border-radius: 10px;
            font-size: 18px;
            background-color: transparent;
            color: #e0e0e0;
            outline: none;
            box-sizing: border-box;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .form-container input[type="number"]:focus,
        .form-container input[type="text"]:focus,
        .form-container button:hover {
            background-color: #4CAF50;
            border-color: #388E3C;
            color: #121212;
        }

        .form-container button {
            background-color: #388E3C;
            color: #e0e0e0;
            font-weight: bold;
            cursor: pointer;
            border: none;
            padding: 15px;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #2c6e2f;
        }

        .transaction-list {
            list-style: none;
            padding: 0;
        }

        .transaction-list li {
            background: linear-gradient(45deg, #333, #444);
            padding: 20px;
            margin: 15px 0;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            color: #fff;
            font-size: 18px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .transaction-list li:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(0, 255, 0, 0.5);
        }

        .neon-line {
            height: 2px;
            background: linear-gradient(90deg, #fff, #4CAF50, #fff);
            margin: 20px 0;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.6);
        }

        .profile {
            position: fixed;
            top: 20px;
            right: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 20px;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .profile:hover {
            transform: scale(1.05);
            background-color: rgba(0, 0, 0, 0.8);
        }

        .profile-circle {
            width: 60px;
            height: 60px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            overflow: hidden;
            border: 2px solid #388E3C;
            transition: all 0.3s ease;
        }

        .profile-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-circle:hover {
            background-color: #388E3C;
            transform: scale(1.1);
        }

        .logout-link {
            color: #ff4d4d;
            font-size: 18px;
            font-weight: bold;
            margin-left: 15px;
            transition: color 0.3s ease;
        }

        .logout-link:hover {
            color: #f44336;
        }

        .profile-picture-container {
            text-align: center;
            margin: 50px 0;
        }

        .profile-picture-container input[type="file"] {
            display: none;
        }

        .profile-picture-container label {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 20px 40px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
        }

        .profile-picture-container button {
            background-color: #388E3C;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            margin-top: 20px;
        }

        .profile-picture-container button:hover {
            background-color: #2c6e2f;
        }



        </style>
    </head>
    <body>

        <div class="container">
            <div class="profile">
                <div class="profile-circle">
                    <?php if ($currentUser['profile_picture']): ?>
                        <img src="<?= htmlspecialchars($currentUser['profile_picture']) ?>" alt="Profilkép">
                    <?php else: ?>
                        <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                Üdv, <?= htmlspecialchars($currentUser['username']) ?>!
                <a href="logout.php" class="logout-link">Kijelentkezés</a>
            </div>

            <h1>Üdvözöllek!</h1>

            <div class="balance">
                <p>Aktuális egyenleged: <strong><?= htmlspecialchars($currentBalance) ?> lej</strong></p>
            </div>
            <div class="neon-line"></div>


            <h2>Profilkép frissítése</h2>
            <div class="profile-picture-container">
                <form method="POST" enctype="multipart/form-data">
                    <label for="profile_picture">Válassz profilképet</label>
                    <input type="file" name="profile_picture" accept="image/*" id="profile_picture" required>
                    <button type="submit">Profilkép frissítése</button>
                </form>
            </div>
            <div class="neon-line"></div>


            <h2>Tranzakció hozzáadása</h2>
            <div class="form-container">
                <form method="POST">
                    <h3>Kiadás</h3>
                    <input type="number" name="amount" placeholder="Összeg" required>
                    <input type="text" name="description" placeholder="Leírás" required>
                    <input type="hidden" name="type" value="expense">
                    <button type="submit">Kiadás hozzáadása</button>
                </form>

                <form method="POST">
                    <h3>Bevétel</h3>
                    <input type="number" name="amount" placeholder="Összeg" required>
                    <input type="text" name="description" placeholder="Leírás" required>
                    <input type="hidden" name="type" value="income">
                    <button type="submit">Bevétel hozzáadása</button>
                </form>
            </div>
            <div class="neon-line"></div>


            <h2>Tranzakciók</h2>
            <ul class="transaction-list">
                <?php foreach ($transactions as $transaction): ?>
                    <li>
                        <strong><?= htmlspecialchars($transaction['amount']) ?> lej</strong> - <?= htmlspecialchars($transaction['description']) ?>
                        <br>
                        <em><?= htmlspecialchars($transaction['created_at']) ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
        </div>

    </body>
    </html>
