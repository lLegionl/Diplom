<?php if (!defined('APP_NAME')) die(); ?>
<aside class="sidebar">
    <ul class="nav-menu">
        <li class="nav-item" id="documents-menu">
            <div class="nav-link">
                <i class="fas fa-file-alt"></i>
                Документы
                <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 0.8rem;"></i>
            </div>
            <ul class="dropdown-menu">
                <li><a href="documents.php?type=Входящие"><i class="fas fa-inbox"></i> Входящие</a></li>
                <li><a href="documents.php?type=Исходящие"><i class="fas fa-paper-plane"></i> Исходящие</a></li>
                <li><a href="documents.php?type=Внутренние"><i class="fas fa-file-signature"></i> Внутренние</a></li>
                <li><a href="documents.php?status=Архив"><i class="fas fa-archive"></i> Архив</a></li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="tasks.php" class="nav-link">
                <i class="fas fa-tasks"></i>
                Задачи
                <?php if ($task_count > 0): ?>
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
        </li>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <li class="nav-item">
            <a href="admin/" class="nav-link">
                <i class="fas fa-cog"></i>
                Администрирование
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>