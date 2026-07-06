<?php
// Top bar with user info
$full_name = $_SESSION['full_name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';
?>
<div class="top-bar">
    <div>
        <h2><?php echo ucfirst($role); ?> Panel</h2>
    </div>
    <div class="user-info">
        <span>Welcome, <?php echo $full_name; ?></span>
        <div class="avatar">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
        </div>
    </div>
</div>