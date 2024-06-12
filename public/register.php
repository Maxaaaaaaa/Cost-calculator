<?php
require '../config/database.php';
require '../src/encrypt.php'; // Подключаем файл с функциями шифрования
session_start();

$key = 'your-encryption-key'; // Замените на ваш ключ шифрования

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Шифрование пароля
    $encryptedPassword = encryptData($password, $key);

    // Check if the username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $userExists = $stmt->fetchColumn();

    if ($userExists) {
        echo "<script>alert('Username already exists. Please choose a different username.');</script>";
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute(['username' => $username, 'password' => $encryptedPassword]);

        // Redirect to login page or another page
        echo "<script>window.location.href = 'login.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            width: 400px; /* Уменьшаем ширину контейнера */
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

        .input-group label {
            margin-bottom: 5px; /* Добавляем отступ снизу для лейблов */
        }

        .input-wrapper {
            display: flex;
            align-items: center; /* Выравниваем элементы по вертикали */
            position: relative;
        }

        .input-wrapper i {
            margin-right: 10px; /* Отступ справа от иконки */
            color: #000; /* Делаем иконку черной */
        }

        .input-wrapper input {
            border-radius: 5px;
            font-size: 20px;
            padding: 10px;
            border: 1px solid rgb(34,193,195);
            width: calc(100% - 40px); /* Устанавливаем ширину с учетом отступов */
            box-sizing: border-box; /* Учитываем padding и border в ширине */
        }

        .input-wrapper input:focus {
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

        .toggle-password {
            position: absolute;
            right: 20px; /* Перемещаем иконку глаза немного влево */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="/image/png-transparent-computer-icons-icon-design-calculation-calculator-icon-text-rectangle-computer.png" alt="Logo" class="logo">
    <h1>Register</h1>
    <form method="POST" action="">
        <div class="input-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="error"></div>
        </div>
        <div class="input-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
            <div class="error"></div>
        </div>
        <div class="input-group">
            <label for="cpassword">Confirm Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="cpassword" name="cpassword" required>
            </div>
            <div class="error"></div>
        </div>
        <button type="submit">Register</button>
    </form>
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
