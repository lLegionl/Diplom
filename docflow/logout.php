<?php
require_once 'config.php';

// Уничтожаем сессию
session_unset();
session_destroy();

// Перенаправляем на страницу входа
header('Location: http://diplom/docflow/login.php');
exit();
?>