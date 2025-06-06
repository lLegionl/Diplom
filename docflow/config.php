<?php
// Защита от сессионной фиксации
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Только для HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
// Настройки базы данных
define('DB_HOST', 'MySQL-8.2'); // Хост Бд
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'docflow');

// Настройки приложения
define('APP_NAME', 'DOCFLOW');
define('APP_ROOT', dirname(dirname(__FILE__)));
define('URL_ROOT', 'http://diplom/docflow');
define('UPLOAD_DIR', APP_ROOT . '/uploads/');

// Настройки потока док-ов
define('DOC_INCOMING', 'Входящие');
define('DOC_OUTGOING', 'Исходящие');
define('DOC_INTERNAL', 'Внутренние');

// Инициализация сессии
session_start();

// Подключение к базе данных
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функция для защиты от XSS
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Проверка авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Редирект если не авторизован
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . URL_ROOT . '/login.php');
        exit();
    }
}

// Получение информации о текущем пользователе
function currentUser() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

// Функция для форматирования размера файла
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}
?>