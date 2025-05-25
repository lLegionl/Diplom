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
                (doc_number, title, doc_type, description, file_path, status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $doc_number, 
                $title, 
                $doc_type, 
                $description, 
                $file_path, 
                $status, 
                $user['id']
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
    <style>
        /* Основные стили из index.php */
        :root {
            --dark-blue: #0a192f;
            --navy: #172a45;
            --light-blue: #64ffda;
            --text-light: #ccd6f6;
            --text-dark: #8892b0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--dark-blue);
            color: var(--text-light);
            line-height: 1.6;
        }
        
        header {
            background-color: var(--navy);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--light-blue);
        }
        
        .container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        
        .sidebar {
            width: 250px;
            background-color: var(--navy);
            padding: 1.5rem;
            border-right: 1px solid rgba(100, 255, 218, 0.1);
        }
        
        /* Форма создания документа */
        .form-container {
            flex: 1;
            padding: 2rem;
        }
        
        .form-card {
            background-color: var(--navy);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid rgba(100, 255, 218, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-title {
            color: var(--light-blue);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(100, 255, 218, 0.2);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            background-color: rgba(10, 25, 47, 0.5);
            border: 1px solid rgba(100, 255, 218, 0.2);
            border-radius: 5px;
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--light-blue);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--light-blue);
            color: var(--dark-blue);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background-color: rgba(100, 255, 218, 0.8);
        }
        
        .error-message {
            color: #ff6b6b;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .success-message {
            color: var(--light-blue);
            margin-bottom: 1rem;
            padding: 0.8rem;
            background-color: rgba(100, 255, 218, 0.1);
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Шапка -->
    <header>
        <div class="logo"><?php echo APP_NAME; ?></div>
        <div>
            <span style="margin-right: 1rem;"><?php echo htmlspecialchars($user['full_name']); ?></span>
            <a href="logout.php" style="color: var(--light-blue); text-decoration: none;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>
    
    <!-- Основной контейнер -->
    <div class="container">
        <!-- Боковая панель (можно включить из index.php) -->
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
</html>