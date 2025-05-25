<?php
require_once 'config.php';
requireLogin();

$user = currentUser();

// Получаем последние документы
try {
    $stmt = $pdo->prepare("
        SELECT d.*, u.full_name as creator 
        FROM documents d
        JOIN users u ON d.created_by = u.id
        ORDER BY d.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении документов: " . $e->getMessage());
}

// Получаем количество задач
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as task_count FROM tasks WHERE assigned_to = ? AND status != 'Завершена'");
    $stmt->execute([$user['id']]);
    $task_count = $stmt->fetch()['task_count'];
} catch (PDOException $e) {
    $task_count = 0;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная | <?php echo APP_NAME; ?></title>
    <!-- Иконки Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Основное содержимое -->
        <main class="main-content">
            <!-- Карточка быстрых действий -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Быстрые действия</h2>
                </div>
                <div>
                    <a href="create_document.php" class="btn" style="margin-right: 10px;">
                        <i class="fas fa-plus"></i> Создать документ
                    </a>
                    <a href="upload.php" class="btn">
                        <i class="fas fa-upload"></i> Загрузить файл
                    </a>
                </div>
            </div>
            
            <!-- Карточка последних документов -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Последние документы</h2>
                    <a href="documents.php" style="color: var(--light-blue); text-decoration: none; font-size: 0.9rem;">Все документы →</a>
                </div>
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Номер</th>
                            <th>Название</th>
                            <th>Тип</th>
                            <th>Дата</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['doc_number']); ?></td>
                            <td><?php echo htmlspecialchars($doc['title']); ?></td>
                            <td><?php echo htmlspecialchars($doc['doc_type']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($doc['created_at'])); ?></td>
                            <td>
                                <?php 
                                    $status_class = 'status-' . strtolower(str_replace(' ', '-', $doc['status']));
                                    echo '<span class="status ' . $status_class . '">' . $doc['status'] . '</span>';
                                ?>
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