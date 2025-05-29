<?php
require_once 'config.php';
requireLogin();

$user = currentUser();

if (!isset($_GET['id'])) {
    header('Location: documents.php');
    exit();
}

$doc_id = (int)$_GET['id'];

// Получаем документ для редактирования
try {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$doc_id]);
    $document = $stmt->fetch();
    
    if (!$document) {
        $_SESSION['error'] = "Документ не найден";
        header('Location: documents.php');
        exit();
    }
    
    // Проверяем права на редактирование
    if ($document['created_by'] != $user['id'] && $user['role'] != 'admin') {
        $_SESSION['error'] = "У вас нет прав для редактирования этого документа";
        header('Location: documents.php');
        exit();
    }
} catch (PDOException $e) {
    die("Ошибка при получении документа: " . $e->getMessage());
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_number = sanitize($_POST['doc_number']);
    $title = sanitize($_POST['title']);
    $doc_type = sanitize($_POST['doc_type']);
    $description = sanitize($_POST['description']);
    $status = sanitize($_POST['status']);
    
    // Проверка уникальности номера документа (если изменился)
    if ($doc_number != $document['doc_number']) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM documents WHERE doc_number = ?");
            $stmt->execute([$doc_number]);
            if ($stmt->fetch()) {
                $error = "Документ с таким номером уже существует";
            }
        } catch (PDOException $e) {
            $error = "Ошибка при проверке номера документа: " . $e->getMessage();
        }
    }
    
    // Обработка загрузки нового файла (если есть)
    $file_path = $document['file_path'];
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Удаляем старый файл, если он есть
        if ($file_path && file_exists(__DIR__ . '/' . $file_path)) {
            unlink(__DIR__ . '/' . $file_path);
        }
        
        $file_name = uniqid() . '_' . basename($_FILES['document_file']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $target_path)) {
            $file_path = 'uploads/' . $file_name;
        } else {
            $error = "Ошибка при загрузке файла";
        }
    }

    // Удаление файла, если запрошено
    if (isset($_POST['delete_file']) && $_POST['delete_file'] == '1' && $file_path) {
        if (file_exists(__DIR__ . '/' . $file_path)) {
            unlink(__DIR__ . '/' . $file_path);
        }
        $file_path = null;
    }   
     
    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE documents SET
                doc_number = ?,
                title = ?,
                doc_type = ?,
                description = ?,
                status = ?,
                file_path = ?,
                direction = ?,
                updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $doc_number, 
                $title, 
                $doc_type, 
                $description, 
                $status, 
                $file_path,
                $_POST['direction'],
                $document['id']
            ]);   

            $_SESSION['success'] = "Документ успешно обновлен";
            header('Location: view_document.php?id=' . $document['id']);
            exit();
        } catch (PDOException $e) {
            $error = "Ошибка при обновлении документа: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать документ | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="form-container">
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-edit"></i> Редактировать документ</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="doc_number" class="form-label">Номер документа</label>
                        <input type="text" id="doc_number" name="doc_number" class="form-control" 
                               value="<?php echo htmlspecialchars($document['doc_number']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Название документа</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($document['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="doc_type" class="form-label">Тип документа</label>
                        <select id="doc_type" name="doc_type" class="form-control" required>
                            <option value="">Выберите тип</option>
                            <option value="Договор" <?php echo $document['doc_type'] == 'Договор' ? 'selected' : ''; ?>>Договор</option>
                            <option value="Приказ" <?php echo $document['doc_type'] == 'Приказ' ? 'selected' : ''; ?>>Приказ</option>
                            <option value="Заявление" <?php echo $document['doc_type'] == 'Заявление' ? 'selected' : ''; ?>>Заявление</option>
                            <option value="Счет" <?php echo $document['doc_type'] == 'Счет' ? 'selected' : ''; ?>>Счет</option>
                            <option value="Акт" <?php echo $document['doc_type'] == 'Акт' ? 'selected' : ''; ?>>Акт</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="direction" class="form-label">Направление</label>
                        <select id="direction" name="direction" class="form-control" required>
                            <option value="<?php echo DOC_INCOMING; ?>" <?php echo $document['direction'] == DOC_INCOMING ? 'selected' : ''; ?>>Входящий</option>
                            <option value="<?php echo DOC_OUTGOING; ?>" <?php echo $document['direction'] == DOC_OUTGOING ? 'selected' : ''; ?>>Исходящий</option>
                            <option value="<?php echo DOC_INTERNAL; ?>" <?php echo $document['direction'] == DOC_INTERNAL ? 'selected' : ''; ?>>Внутренний</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Описание</label>
                        <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($document['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Статус</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Черновик" <?php echo $document['status'] == 'Черновик' ? 'selected' : ''; ?>>Черновик</option>
                            <option value="На согласовании" <?php echo $document['status'] == 'На согласовании' ? 'selected' : ''; ?>>На согласовании</option>
                            <option value="Утвержден" <?php echo $document['status'] == 'Утвержден' ? 'selected' : ''; ?>>Утвержден</option>
                            <option value="Отклонен" <?php echo $document['status'] == 'Отклонен' ? 'selected' : ''; ?>>Отклонен</option>
                            <option value="Архив" <?php echo $document['status'] == 'Архив' ? 'selected' : ''; ?>>Архив</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="document_file" class="form-label">Файл документа</label>
                        <input type="file" id="document_file" name="document_file" class="form-control">
                        
                        <?php if ($document['file_path']): ?>
                        <div class="file-preview">
                            <div class="file-preview-info">
                                <i class="fas fa-file-alt"></i>
                                <div>
                                    <div><?php echo basename($document['file_path']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-dark);">
                                        <?php 
                                            $file_path = UPLOAD_DIR . basename($document['file_path']);
                                            if (file_exists($file_path)) {
                                                $size = filesize($file_path);
                                                echo formatFileSize($size);
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="file-preview-actions">
                                <a href="<?php echo URL_ROOT . '/' . $document['file_path']; ?>" class="btn" download>
                                    <i class="fas fa-download"></i> Скачать
                                </a>
                                <a href="<?php echo URL_ROOT . '/' . $document['file_path']; ?>" target="_blank" class="btn" style="background-color: var(--navy);">
                                    <i class="fas fa-eye"></i> Просмотреть
                                </a>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="delete_file" name="delete_file" value="1">
                                <label for="delete_file">Удалить файл</label>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Сохранить изменения
                        </button>
                        <a href="view_document.php?id=<?php echo $document['id']; ?>" class="btn" style="background-color: var(--navy); color: var(--text-light); margin-left: 10px;">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
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