<?php
require_once 'config.php';
requireLogin();

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_number = sanitize($_POST['doc_number']);
    $title = sanitize($_POST['title']);
    $doc_type = sanitize($_POST['doc_type']);
    $description = sanitize($_POST['description']);
    $status = sanitize($_POST['status']);
    
    // Обработка загрузки файла
    $file_path = null;
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = UPLOAD_DIR;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = uniqid() . '_' . basename($_FILES['document_file']['name']);
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $target_path)) {
            $file_path = 'uploads/' . $file_name;
        } else {
            $error = "Ошибка при загрузке файла";
        }
    }
    
    if (!isset($error)) {
        try {
                $stmt = $pdo->prepare("
                    INSERT INTO documents 
                    (doc_number, title, doc_type, description, file_path, status, created_by, direction) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $doc_number, 
                    $title, 
                    $doc_type, 
                    $description, 
                    $file_path, 
                    $status, 
                    $user['id'],
                    $_POST['direction']
                ]);

            $_SESSION['success'] = "Документ успешно создан";
            header('Location: ' . URL_ROOT . '/documents.php');
            exit();
        } catch (PDOException $e) {
            $error = "Ошибка при создании документа: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать документ | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Шапка и боковое меню -->
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Форма создания документа -->
        <div class="form-container">
            <div class="form-card">
                <h2 class="form-title"><i class="fas fa-file-alt"></i> Создать новый документ</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="doc_number" class="form-label">Номер документа</label>
                        <input type="text" id="doc_number" name="doc_number" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Название документа</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="doc_type" class="form-label">Тип документа</label>
                        <select id="doc_type" name="doc_type" class="form-control" required>
                            <option value="">Выберите тип</option>
                            <option value="Договор">Договор</option>
                            <option value="Приказ">Приказ</option>
                            <option value="Заявление">Заявление</option>
                            <option value="Счет">Счет</option>
                            <option value="Акт">Акт</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="direction" class="form-label">Направление</label>
                        <select id="direction" name="direction" class="form-control" required>
                            <option value="<?php echo DOC_INCOMING; ?>">Входящий</option>
                            <option value="<?php echo DOC_OUTGOING; ?>">Исходящий</option>
                            <option value="<?php echo DOC_INTERNAL; ?>" selected>Внутренний</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Описание</label>
                        <textarea id="description" name="description" class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Статус</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Черновик">Черновик</option>
                            <option value="На согласовании">На согласовании</option>
                            <option value="Утвержден">Утвержден</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="document_file" class="form-label">Файл документа</label>
                        <input type="file" id="document_file" name="document_file" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> Сохранить документ
                        </button>
                        <a href="documents.php" class="btn" style="background-color: var(--navy); color: var(--text-light); margin-left: 10px;">
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