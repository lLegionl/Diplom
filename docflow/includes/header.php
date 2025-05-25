<?php if (!defined('APP_NAME')) die(); ?>
<header>
    <div class="logo"><?php echo APP_NAME; ?></div>
    <div>
        <span style="margin-right: 1rem;"><?php echo htmlspecialchars($_SESSION['user_name']); ?> (<?php echo htmlspecialchars($_SESSION['user_role']); ?>)</span>
        <i class="fas fa-bell" style="color: var(--light-blue); margin-right: 1rem;"></i>
        <a href="logout.php" style="color: var(--light-blue); text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</header>