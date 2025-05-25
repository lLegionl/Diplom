<?php
require_once 'config.php';

// Проверяем, не установлена ли уже система
try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
    die("Система уже установлена. Удалите install.php в целях безопасности.");
} catch (PDOException $e) {
    // Продолжаем установку
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_username = sanitize($_POST['admin_username']);
    $admin_password = sanitize($_POST['admin_password']);
    $admin_email = sanitize($_POST['admin_email']);
    $admin_name = sanitize($_POST['admin_name']);
    
    if (empty($admin_username) || empty($admin_password) || empty($admin_email) || empty($admin_name)) {
        $error = "Все поля обязательны для заполнения";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Введите корректный email адрес";
    } else {
        try {
            // Создаем таблицы
            $pdo->exec("
            CREATE TABLE `users` (
                `id` int NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `password` varchar(255) NOT NULL,
                `full_name` varchar(100) NOT NULL,
                `email` varchar(100) NOT NULL,
                `role` enum('admin','user','manager') DEFAULT 'user',
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `last_login` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
            ");
            
            $pdo->exec("
            CREATE TABLE `documents` (
                `id` int NOT NULL AUTO_INCREMENT,
                `doc_number` varchar(50) NOT NULL,
                `title` varchar(255) NOT NULL,
                `doc_type` enum('Договор','Приказ','Заявление','Счет','Акт') NOT NULL,
                `description` text,
                `file_path` varchar(255) DEFAULT NULL,
                `status` enum('Черновик','На согласовании','Утвержден','Отклонен','Архив') DEFAULT 'Черновик',
                `created_by` int NOT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NULL DEFAULT NULL,
                `direction` enum('Входящие','Исходящие','Внутренние','Архив') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Входящие',
                PRIMARY KEY (`id`),
                UNIQUE KEY `doc_number` (`doc_number`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
            ");
            
            
            // Создаем администратора
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users 
                (username, password, full_name, email, role) 
                VALUES (?, ?, ?, ?, 'admin')
            ");
            $stmt->execute([$admin_username, $hashed_password, $admin_name, $admin_email]);
            
            // Создаем папку для загрузок
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            // Удаляем install.php в целях безопасности
            unlink(__FILE__);
            
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $error = "Ошибка установки: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка системы документооборота</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<link rel="stylesheet" href="assets/css/style.css">
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
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-file-alt"></i> DOCFLOW</h1>
            <p>Установка системы электронного документооборота</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Имя пользователя администратора</label>
                <input type="text" name="admin_username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Пароль администратора</label>
                <input type="password" name="admin_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email администратора</label>
                <input type="email" name="admin_email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">ФИО администратора</label>
                <input type="text" name="admin_name" class="form-control" required>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-cogs"></i> Установить систему
            </button>
        </form>
    </div>
</body>
</html>