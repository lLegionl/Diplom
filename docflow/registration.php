<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $password_repeat = sanitize($_POST['password_repeat']);


    
        try { 
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (empty($user['username']) && ($password == $password_repeat)) {

            $stmt = $pdo->prepare("
                INSERT INTO users 
                (username, password, full_name, email) 
                VALUES (?, ?, ?, ?)
            ");

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt->execute([
                $username,
                $hashed_password,
                $name,
                $email
            ]);
            header('Location: ' . URL_ROOT . '/index.php');
            exit();
        } else {
            $error = "Ошибка при регистрации в систему: ";
        }
        }catch (PDOException $e) {
            $error = "Ошибка при регистрации в систему: " . $e->getMessage();
        }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
    body {
            background-color: var(--dark-blue);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;

    }
</style>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-file-alt"></i> DOCFLOW</h1>
            <p>Система электронного документооборота</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Логин" required>
            </div>

            <div class="form-group">
                <label for="username">ФИО</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Иванов Иван Иванович" required>
            </div>

            <div class="form-group">
                <label for="username">Почта</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@email.com" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Пароль" required>
            </div>

            <div class="form-group">
                <label for="password">Подтверждение пароля</label>
                <input type="password" id="password_repeat" name="password_repeat" class="form-control" placeholder="Подтверждение пароля" required>
            </div>

            <button type="submit" class="btn">Зарегистрироваться</button>
            <a href="login.php">Есть аккаунт?</a>
        </form>
    </div>
</body>
</html>