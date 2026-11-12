<?php
    /**
     * Flash Messages (One Time Toast Notifications Stored In The Session)
    **/
?>

<?php foreach(["green_report_message", "red_report_message"] as $flash_key): ?>
    <?php if(!empty($_SESSION[$flash_key])): ?>
        <p class="<?php echo $flash_key; ?>" role="status"><?php echo $_SESSION[$flash_key]; ?></p>
        <?php $_SESSION[$flash_key] = ""; ?>

    <?php endif; ?>

<?php endforeach; ?>
