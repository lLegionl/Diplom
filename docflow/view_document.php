<?php
require_once 'config.php';
requireLogin();

$user = currentUser();

if (!isset($_GET['id'])) {
    header('Location: documents.php');
    exit();
}

$doc_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT d.*, u.full_name as creator 
        FROM documents d
        JOIN users u ON d.created_by = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$doc_id]);
    $document = $stmt->fetch();
    
    if (!$document) {
        $_SESSION['error'] = "Документ не найден";
        header('Location: documents.php');
        exit();
    }
} catch (PDOException $e) {
    die("Ошибка при получении документа: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр документа | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Шапка и боковое меню -->
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="document-container">
            <div class="document-card">
                <div class="document-header">
                    <h1 class="document-title"><?php echo htmlspecialchars($document['title']); ?></h1>
                    <div class="document-meta">
                        <div class="document-meta-item">
                            <i class="fas fa-hashtag"></i>
                            <span><?php echo htmlspecialchars($document['doc_number']); ?></span>
                        </div>
                        <div class="document-meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?php echo htmlspecialchars($document['doc_type']); ?></span>
                        </div>
                        <div class="document-meta-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($document['creator']); ?></span>
                        </div>
                        <div class="document-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?php echo date('d.m.Y H:i', strtotime($document['created_at'])); ?></span>
                        </div>
                        <div class="document-meta-item">
                            <?php 
                                $status_class = 'status-' . strtolower(str_replace(' ', '-', $document['status']));
                                echo '<span class="status ' . $status_class . '">' . $document['status'] . '</span>';
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="document-content">
                    <h3 style="color: var(--light-blue); margin-bottom: 1rem;">Описание</h3>
                    <p><?php echo nl2br(htmlspecialchars($document['description'])); ?></p>
                </div>
                
                <?php if ($document['file_path']): ?>
                <div class="document-file">
                    <div class="file-info">
                        <i class="fas fa-file-alt"></i>
                        <div class="file-details">
                            <div class="file-name">
                                <a href="<?php echo URL_ROOT . '/' . $document['file_path']; ?>" class="file-link" download>
                                    <?php echo basename($document['file_path']); ?>
                                </a>
                            </div>
                            <div class="file-size">
                                <?php 
                                    $file_path = UPLOAD_DIR . basename($document['file_path']);
                                    if (file_exists($file_path)) {
                                        $size = filesize($file_path);
                                        echo formatFileSize($size);
                                    }
                                ?>
                            </div>
                        </div>
                        <a href="<?php echo URL_ROOT . '/' . $document['file_path']; ?>" class="btn" download>
                            <i class="fas fa-download"></i> Скачать
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="document-actions">
                    <a href="documents.php" class="btn" style="background-color: var(--navy);">
                        <i class="fas fa-arrow-left"></i> Назад
                    </a>
                    <?php if ($user['id'] == $document['created_by'] || $user['role'] == 'admin'): ?>
                    <a href="edit_document.php?id=<?php echo $document['id']; ?>" class="btn">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
    <script>
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