<?php
require_once '../config.php';
requireLogin();

// Только администраторы могут получить доступ
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . URL_ROOT . '/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role']);
    
    // Валидация
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Имя пользователя обязательно";
    }
    
    if (empty($password)) {
        $errors[] = "Пароль обязателен";
    } elseif (strlen($password) < 6) {
        $errors[] = "Пароль должен содержать минимум 6 символов";
    }
    
    if (empty($full_name)) {
        $errors[] = "ФИО обязательно";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email";
    }
    
    // Проверка уникальности username и email
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Пользователь с таким именем или email уже существует";
        }
    } catch (PDOException $e) {
        $errors[] = "Ошибка при проверке пользователя: " . $e->getMessage();
    }
    
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users 
                (username, password, full_name, email, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $username, 
                $hashed_password, 
                $full_name, 
                $email, 
                $role
            ]);
            
            $_SESSION['success'] = "Пользователь успешно добавлен";
            header('Location: users.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Ошибка при добавлении пользователя: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить пользователя | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="form-container">
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-user-plus"></i> Добавить нового пользователя</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name" class="form-label">ФИО</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">Роль</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="user">Пользователь</option>
                            <option value="manager">Менеджер</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Сохранить
                        </button>
                        <a href="users.php" class="btn" style="background-color: var(--navy); color: var(--text-light); margin-left: 10px;">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
    <script>
        // Обработчик для выпадающего меню документов
        document.getElementById('documents-menu').addEventListener('click', function(e) {
            // Предотвращаем переход по ссылке если кликнули на сам пункт меню
            if (e.target.tagName !== 'A') {
                this.classList.toggle('active');
                
                // Поворачиваем иконку стрелки
                const icon = this.querySelector('.fa-chevron-down');
                if (this.classList.contains('active')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        });
        
        // Закрываем меню при клике вне его
        document.addEventListener('click', function(e) {
            const documentsMenu = document.getElementById('documents-menu');
            if (!documentsMenu.contains(e.target)) {
                documentsMenu.classList.remove('active');
                documentsMenu.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
            }
        });

        // Обработчик для выпадающего меню Администратора
        document.getElementById('admin-menu').addEventListener('click', function(e) {
            // Предотвращаем переход по ссылке если кликнули на сам пункт меню
            if (e.target.tagName !== 'A') {
                this.classList.toggle('active');
                
                // Поворачиваем иконку стрелки
                const icon = this.querySelector('.fa-chevron-down');
                if (this.classList.contains('active')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        });
        
        // Закрываем меню при клике вне его
        document.addEventListener('click', function(e) {
            const documentsMenu = document.getElementById('admin-menu');
            if (!documentsMenu.contains(e.target)) {
                documentsMenu.classList.remove('active');
                documentsMenu.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
            }
        });
    </script>
</html>