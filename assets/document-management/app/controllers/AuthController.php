<?php
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User(); // Модель для работы с пользователями
    }

    /**
     * Страница входа
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            // Проверяем пользователя
            $user = $this->userModel->findByUsername($username);
            
            if ($user && password_verify($password, $user['password'])) {
                // Сохраняем данные в сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Перенаправляем в админку
                header('Location: /documents');
                exit;
            } else {
                $error = "Неверный логин или пароль";
            }
        }
        
        // Показываем форму входа
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Выход из системы
     */
    public function logout() {
        session_destroy();
        header('Location: /auth/login');
        exit;
    }
}