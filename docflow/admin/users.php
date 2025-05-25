<?php
require_once '../config.php';
requireLogin();

// Только администраторы могут получить доступ
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . URL_ROOT . '/index.php');
    exit();
}

// Получаем список пользователей
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении пользователей: " . $e->getMessage());
}

// Обработка удаления пользователя
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Нельзя удалить самого себя
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Вы не можете удалить свой собственный аккаунт";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = "Пользователь успешно удален";
            header('Location: users.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при удалении пользователя: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="admin-container">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-users-cog"></i> Управление пользователями</h2>
                    <a href="add_user.php" class="btn">
                        <i class="fas fa-user-plus"></i> Добавить пользователя
                    </a>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя пользователя</th>
                            <th>ФИО</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php 
                                    $role_class = 'role-' . strtolower($user['role']);
                                    echo '<span class="' . $role_class . '">' . $user['role'] . '</span>';
                                ?>
                            </td>
                            <td>
                                <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                <?php if ($user['last_login']): ?>
                                <div class="last-login">
                                    Последний вход: <?php echo date('d.m.Y H:i', strtotime($user['last_login'])); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-link" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?php echo $user['id']; ?>" class="action-link" title="Удалить" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
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