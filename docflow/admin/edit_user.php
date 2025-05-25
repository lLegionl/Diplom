<?php
require_once '../config.php';
requireLogin();

// Только администраторы могут получить доступ
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . URL_ROOT . '/index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Получаем данные пользователя
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "Пользователь не найден";
        header('Location: users.php');
        exit();
    }
} catch (PDOException $e) {
    die("Ошибка при получении пользователя: " . $e->getMessage());
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
    
    if (empty($full_name)) {
        $errors[] = "ФИО обязательно";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email";
    }
    
    // Проверка уникальности username и email (если изменились)
    if ($username != $user['username'] || $email != $user['email']) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetch()) {
                $errors[] = "Пользователь с таким именем или email уже существует";
            }
        } catch (PDOException $e) {
            $errors[] = "Ошибка при проверке пользователя: " . $e->getMessage();
        }
    }
    
    if (empty($errors)) {
        try {
            // Обновляем пароль только если он был изменен
            $password_sql = '';
            $params = [$username, $full_name, $email, $role, $user_id];
            
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $password_sql = ", password = ?";
                array_splice($params, 1, 0, $hashed_password);
            }
            
            $sql = "UPDATE users SET 
                    username = ?, 
                    full_name = ?, 
                    email = ?, 
                    role = ? 
                    $password_sql 
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $_SESSION['success'] = "Данные пользователя успешно обновлены";
            header('Location: users.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Ошибка при обновлении пользователя: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать пользователя | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Все стили из предыдущих форм */
        
        .password-note {
            font-size: 0.8rem;
            color: var(--text-dark);
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="form-container">
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-user-edit"></i> Редактировать пользователя</h2>
                
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
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Новый пароль</label>
                        <input type="password" id="password" name="password" class="form-control">
                        <div class="password-note">Оставьте пустым, если не хотите менять пароль</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name" class="form-label">ФИО</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">Роль</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Пользователь</option>
                            <option value="manager" <?php echo $user['role'] == 'manager' ? 'selected' : ''; ?>>Менеджер</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Администратор</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Сохранить изменения
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
</html>