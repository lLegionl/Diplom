<?php
// Установка заголовка UTF-8
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'DOCFLOW', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">DOCFLOW</div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="margin-right: 1rem;"><?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <a href="/auth/logout" style="color: #64ffda;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="container">
        <aside class="sidebar">
            <ul class="nav-menu">
                <li class="nav-item <?= basename($_SERVER['REQUEST_URI']) === 'documents' ? 'active' : '' ?>">
                    <div class="nav-link" id="documents-menu">
                        <i class="fas fa-file-alt"></i>
                        Документы
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a href="/documents?category=incoming"><i class="fas fa-inbox"></i> Входящие</a></li>
                        <li><a href="/documents?category=outgoing"><i class="fas fa-paper-plane"></i> Исходящие</a></li>
                        <li><a href="/documents?category=internal"><i class="fas fa-file-signature"></i> Внутренние</a></li>
                    </ul>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>