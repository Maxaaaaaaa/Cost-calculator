<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config/database.php';
require '../src/encrypt.php'; // Подключаем файл с функциями шифрования
session_start();

$key = 'your-encryption-key'; // Замените на ваш ключ шифрования

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Проверка существования пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            try {
                // Дешифрование пароля
                $decryptedPassword = decryptData($user['password'], $key);

                if (password_verify($password, $decryptedPassword)) {
                    $_SESSION['user_id'] = $user['id'];
                    header('Location: index.php');
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            } catch (Exception $e) {
                $error = "An error occurred while decrypting the password: " . $e->getMessage();
            }
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: rgb(34,193,195);
            background: linear-gradient(0deg, rgba(34,193,195,1) 0%, rgba(121,32,153,1) 86%);
            background-attachment: fixed;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            width: 400px;
            margin: 20vh auto 0 auto;
            background-color: whitesmoke;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
        }

        .logo {
            width: 100px;
            margin-bottom: 20px;
        }

        h1 {
            text-align: center;
            color: #792099;
        }

        button {
            background-color: #792099;
            color: white;
            border: 1px solid #792099;
            border-radius: 5px;
            padding: 10px;
            margin: 20px 0px;
            cursor: pointer;
            font-size: 20px;
            width: 100%;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .input-group input {
            border-radius: 5px;
            font-size: 20px;
            margin-top: 5px;
            padding: 10px;
            border: 1px solid rgb(34,193,195);
        }

        .input-group input:focus {
            outline: 0;
        }

        .input-group .error {
            color: rgb(242, 18, 18);
            font-size: 16px;
            margin-top: 5px;
        }

        .input-group.success input {
            border-color: #0cc477;
        }

        .input-group.error input {
            border-color: rgb(206, 67, 67);
        }

        .input-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 85px; /* Перемещаем иконку глаза немного влево */
            top: 55%; /* Перемещаем иконку глаза немного вниз */
            transform: translateY(-50%);
            cursor: pointer;
            color: #000000;
        }

        .forgot-password {
            display: block;
            margin: 10px 0;
            color: #007BFF;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .signup-link {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .signup-link a {
            color: #007BFF;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="img/calculator.png" alt="Logo" class="logo">
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="input-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" required>
            </div>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
        </div>
        <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
        <button type="submit" class="login-button">Login</button>
    </form>
    <p class="signup-link">Not a member? <a href="register.php">Sign up now</a></p>
</div>
<script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        const toggleIcon = document.querySelector(".toggle-password");
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }
    }
</script>
</body>
</html>
