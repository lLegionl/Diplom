<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../models/Document.php';

class DocumentsController {
    private $docModel;

    public function __construct() {
        $this->docModel = new Document(); // Модель для работы с документами
    }

    /**
     * Главная страница документов (список)
     */
    public function index() {
        // Получаем категорию из URL (входящие/исходящие/внутренние)
        $category = $_GET['category'] ?? 'incoming';
        
        // Загружаем документы из БД
        $documents = $this->docModel->getByCategory($category);
        
        // Подключаем HTML-шаблон
        require __DIR__ . '/../views/documents/index.php';
    }

    /**
     * Форма создания документа
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Обработка отправки формы
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'type' => $_POST['type'],
                'category' => $_POST['category']
            ];
            
            // Сохраняем документ в БД
            $docNumber = $this->docModel->create($data);
            
            // Перенаправляем на список документов
            header("Location: /documents?category={$data['category']}");
            exit;
        }
        
        // Показываем форму
        require __DIR__ . '/../views/documents/create.php';
    }
}