<?php
require_once 'config.php';
requireLogin();

$user = currentUser();
$errors = [];
$success = false;

// Обработка формы обновления данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Валидация данных
    if (empty($full_name)) {
        $errors[] = "ФИО обязательно для заполнения";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email";
    }

    // Если пытаются изменить пароль
    if (!empty($current_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Текущий пароль неверен";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Новые пароли не совпадают";
        }
    }

    // Проверка уникальности email (если изменился)
    if ($email !== $user['email'] && empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $errors[] = "Пользователь с таким email уже существует";
            }
        } catch (PDOException $e) {
            $errors[] = "Ошибка при проверке email: " . $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            if (!empty($current_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users SET
                    full_name = ?,
                    email = ?,
                    password = ?
                    WHERE id = ?
                ");
                $success = $stmt->execute([$full_name, $email, $hashed_password, $user['id']]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users SET
                    full_name = ?,
                    email = ?
                    WHERE id = ?
                ");
                $success = $stmt->execute([$full_name, $email, $user['id']]);
            }

            if ($success) {
                $_SESSION['success'] = "Данные успешно обновлены";
                header('Location: account.php');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Ошибка при обновлении данных: " . $e->getMessage();
        }
    }
}
// Получаем актуальные данные пользователя
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['user_name'] = $user['full_name'];
} catch (PDOException $e) {
    die("Ошибка при получении данных пользователя: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой аккаунт | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="account-container">
                <div class="account-header">
                    <div class="account-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <h1 class="account-title">Мой аккаунт</h1>
                        <span class="account-role">
                            <?php 
                            echo $user['role'] === 'admin' ? 
                                '<i class="fas fa-shield-alt"></i> Администратор' : 
                                '<i class="fas fa-user"></i> Пользователь';
                            ?>
                        </span>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-card">
                    <form method="POST" action="">
                        <div class="form-section">
                            <h3 class="form-section-title">
                                <i class="fas fa-user"></i> Основная информация
                            </h3>
                            
                            <div class="form-group">
                                <label for="login">Логин</label>
                                <input type="text" id="login" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">ФИО</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                        </div>
                        
                        <div class="form-section">
                            <h3 class="form-section-title">
                                <i class="fas fa-lock"></i> Смена пароля
                            </h3>
                            <p class="form-hint" style="color: var(--text-dark); margin-bottom: 1rem;">
                                Оставьте эти поля пустыми, если не хотите менять пароль
                            </p>
                            
                            <div class="form-group">
                                <label for="current_password">Текущий пароль</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Новый пароль</label>
                                <input type="password" id="new_password" name="new_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Подтвердите новый пароль</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>