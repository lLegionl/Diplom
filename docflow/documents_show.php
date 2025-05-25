<?php
require_once 'config.php';
requireLogin();

$user = currentUser();

// Параметры пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // Количество документов на странице
$offset = ($page - 1) * $per_page;

// Фильтры из GET-параметров
$direction_filter = isset($_GET['direction']) ? sanitize($_GET['direction']) : '';
// Формируем SQL запрос с учетом фильтров
$sql = "SELECT d.*, u.full_name as creator FROM documents d JOIN users u ON d.created_by = u.id WHERE d.id>=1";
$params = [];

// Добавляем условия фильтрации

if (!empty($direction_filter)) {
        if ($direction_filter=='Архив') {
    $sql .= " AND d.status = ?";
        } else {
    $sql .= " AND d.direction = ?";
        }
    $params[] = $direction_filter;
}



// Получаем общее количество документов для пагинации
$count_sql = "SELECT COUNT(*) as total FROM documents d WHERE 1=1" . 
             (!empty($type_filter) ? " AND d.doc_type = '$type_filter'" : "") .
             (!empty($status_filter) ? " AND d.status = '$status_filter'" : "");

if (!empty($search_query)) {
    $count_sql .= " AND (d.doc_number LIKE '%$search_query%' OR d.title LIKE '%$search_query%' OR d.description LIKE '%$search_query%')";
}

$total_docs = $pdo->query($count_sql)->fetch()['total'];
$total_pages = ceil($total_docs / $per_page);

// Добавляем сортировку и пагинацию
$sql .= " ORDER BY d.created_at DESC LIMIT $offset, $per_page";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка при получении документов: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Документы | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Шапка и боковое меню -->
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Управление документами</h2>
                    <a href="create_document.php" class="btn">
                        <i class="fas fa-plus"></i> Новый документ
                    </a>
                </div>
                                
                <!-- Таблица документов -->
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Номер</th>
                            <th>Название</th>
                            <th>Тип</th>
                            <th>Направление</th>
                            <th>Автор</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr>
                                <td colspan="7" class="empty-table">
                                    <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 1rem; color: var(--text-dark);"></i>
                                    <p>Документы не найдены</p>
                                    <a href="create_document.php" class="btn" style="margin-top: 1rem;">
                                        <i class="fas fa-plus"></i> Создать первый документ
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['doc_number']); ?></td>
                                <td>
                                    <a href="view_document.php?id=<?php echo $doc['id']; ?>" class="file-link">
                                        <?php echo htmlspecialchars($doc['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($doc['doc_type']); ?></td>
                                <td><?php echo htmlspecialchars($doc['direction']); ?></td>
                                <td><?php echo htmlspecialchars($doc['creator']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($doc['created_at'])); ?></td>
                                <td>
                                    <?php 
                                        $status_class = 'status-' . strtolower(str_replace(' ', '-', $doc['status']));
                                        echo '<span class="status ' . $status_class . '">' . $doc['status'] . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <a href="view_document.php?id=<?php echo $doc['id']; ?>" class="action-link" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($user['id'] == $doc['created_by'] || $user['role'] == 'admin'): ?>
                                    <a href="edit_document.php?id=<?php echo $doc['id']; ?>" class="action-link" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($doc['file_path']): ?>
                                    <a href="<?php echo URL_ROOT . '/' . $doc['file_path']; ?>" class="action-link" title="Скачать" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Пагинация -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $type_filter ? '&type=' . urlencode($type_filter) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" class="page-link">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $type_filter ? '&type=' . urlencode($type_filter) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" class="page-link">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    // Показываем ограниченное количество страниц вокруг текущей
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        echo '<span style="padding: 0.5rem 1rem;">...</span>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $type_filter ? '&type=' . urlencode($type_filter) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; 
                    
                    if ($end_page < $total_pages) {
                        echo '<span style="padding: 0.5rem 1rem;">...</span>';
                    }
                    ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $type_filter ? '&type=' . urlencode($type_filter) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" class="page-link">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $type_filter ? '&type=' . urlencode($type_filter) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>" class="page-link">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Обработчик для выпадающего меню документов
        document.getElementById('documents-menu').addEventListener('click', function(e) {
            if (e.target.tagName !== 'A') {
                this.classList.toggle('active');
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
        
        // Подтверждение удаления документа (если добавите эту функцию)
        document.querySelectorAll('.delete-document').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Вы уверены, что хотите удалить этот документ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>