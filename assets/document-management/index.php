<?php
// Отключение вывода ошибок на продакшене
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/core/',
        __DIR__ . '/app/controllers/',
        __DIR__ . '/app/models/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

try {
    // Проверка обязательных файлов
    if (!file_exists(__DIR__ . '/config/database.php')) {
        throw new Exception('Файл config/database.php не найден');
    }
    
    require_once __DIR__ . '/core/App.php';
    new App();
} catch (Throwable $e) {
    // Логирование ошибки
    file_put_contents(__DIR__ . '/error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    // Пользовательское сообщение
    die('Произошла ошибка системы. Пожалуйста, попробуйте позже.');
}