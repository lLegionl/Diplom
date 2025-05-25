<?php 
$dbConfig = __DIR__ . '/config/database.php';
if (!file_exists($dbConfig)) {
    die("Создайте файл config/database.php с настройками БД");
}
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <?= match($_GET['category'] ?? '') {
                'incoming' => 'Входящие документы',
                'outgoing' => 'Исходящие документы',
                'internal' => 'Внутренние документы',
                default => 'Все документы'
            } ?>
        </h2>
        <a href="/documents/create?category=<?= urlencode($_GET['category'] ?? '') ?>" class="btn">
            <i class="fas fa-plus"></i> Создать
        </a>
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
                <td><?= htmlspecialchars($doc['doc_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($doc['type'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= date('d.m.Y', strtotime($doc['created_at'])) ?></td>
                <td>
                    <span class="status status-<?= htmlspecialchars($doc['status'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($doc['status'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php 
require_once __DIR__ . '/../layouts/footer.php';
?>