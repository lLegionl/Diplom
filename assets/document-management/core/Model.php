<?php
class Model {
    protected $db;

    public function __construct() {
        $configFile = __DIR__ . '/../../config/database.php';
        
        // Альтернативный путь для Windows
        if (!file_exists($configFile)) {
            $configFile = str_replace('/', '\\', $configFile);
        }
        
        if (!file_exists($configFile)) {
            throw new RuntimeException("Файл конфигурации не найден по пути: " . $configFile);
        }

        $config = require $configFile;
        
        try {
            $this->db = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            throw new RuntimeException("Ошибка подключения к БД: " . $e->getMessage());
        }
    }
}