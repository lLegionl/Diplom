<?php if (!defined('APP_NAME')) die(); ?>
<header>
    <a class="logo" href="index.php"><?php echo APP_NAME; ?></a>
    <div>
        <span style="margin-right: 1rem;"><?php echo htmlspecialchars($_SESSION['user_name']); ?> (<?php echo htmlspecialchars($_SESSION['user_role']); ?>)</span>
        <i class="fas fa-bell" style="color: var(--light-blue); margin-right: 1rem;"></i>
        <a href="logout.php" style="color: var(--light-blue); text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</header>