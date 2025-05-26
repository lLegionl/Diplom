<?php if (!defined('APP_NAME')) die(); ?>
<aside class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="documents.php" class="nav-link">
                <i class="fas fa-list"></i> 
                Все документы</a></li>
        <li class="nav-item">
            <a href="<?= URL_ROOT . '/documents_show.php?direction=Входящие'?>" class="nav-link">
                <i class="fas fa-inbox"></i> 
                Входящие
            </a></li>
        <li class="nav-item">
            <a href="<?= URL_ROOT . '/documents_show.php?direction=Исходящие'?>" class="nav-link">
                <i class="fas fa-paper-plane"></i>
                Исходящие
            </a></li>
        <li class="nav-item">
            <a href="<?= URL_ROOT . '/documents_show.php?direction=Внутренние?'?>" class="nav-link">
                <i class="fas fa-building"></i> 
                Внутренние
            </a></li>
        <li class="nav-item" class="nav-link">
            <a href="<?= URL_ROOT . '/documents_show.php?direction=Архив'?>" class="nav-link">
                <i class="fas fa-archive"></i> 
                Архив
            </a></li>
        <!-- <li class="nav-item">
            <a href="tasks.php" class="nav-link">
                <i class="fas fa-tasks"></i>
                Задачи
                <?php if (!empty($task_count) && $task_count > 0): ?>
                    <span style="margin-left: auto; background: var(--light-blue); color: var(--dark-blue); border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;"><?php echo $task_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="calendar.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i>
                Календарь
            </a>
        </li>
        <li class="nav-item">
            <a href="reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i>
                Отчеты
            </a>
        </li> -->
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <li class="nav-item" id="admin-menu">
            <div class="nav-link">
                <i class="fas fa-cog"></i>
                Админ-меню
                <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 0.8rem;"></i>
            </div>
            <ul class="dropdown-menu">
                <li><a href="<?= URL_ROOT . '/admin/users.php'?>"><i class="fas fa-users"></i> Пользователи</a></li>
                <li><a href="<?= URL_ROOT . '/admin/add_user.php'?>"><i class="fas fa-user-plus"></i> Добавление пользователя</a></li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
</aside>
