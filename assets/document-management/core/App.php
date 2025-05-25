<?php
class App {
    protected $controller = 'DocumentsController';
    protected $method = 'index';
    protected $params = [];
    
    public function __construct() {
        // Устанавливаем корневой путь
        define('ROOT_PATH', dirname(__DIR__));
        
        // Проверяем существование файла конфигурации
        $configPath = ROOT_PATH . '/config/app.php';
        if (!file_exists($configPath)) {
            die("Файл конфигурации не найден: $configPath");
        }
        
        $config = require $configPath;
        
        // Проверяем папку контроллеров
        $controllersPath = ROOT_PATH . '/app/controllers/';
        if (!is_dir($controllersPath)) {
            die("Папка контроллеров не найдена: $controllersPath");
        }

        session_start();
        $url = $this->parseUrl();
        
        // Controller
        if (isset($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerFile = $controllersPath . $controllerName . '.php';
            
            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }
        
        // Подключаем контроллер
        $controllerPath = $controllersPath . $this->controller . '.php';
        if (!file_exists($controllerPath)) {
            die("Файл контроллера не найден: $controllerPath");
        }
        
        require_once $controllerPath;
        
        if (!class_exists($this->controller)) {
            die("Класс {$this->controller} не найден в файле");
        }
        
        $this->controller = new $this->controller;
        
        // Method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }
        
        // Parameters
        $this->params = $url ? array_values($url) : [];
        
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
    
    protected function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}