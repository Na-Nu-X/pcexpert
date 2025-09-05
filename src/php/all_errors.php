<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/admin_functions.php";
    
    $connection = connectionDB();

    $offset = intval($_POST["offset"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach(getErrorLog($connection, $offset) as $one_error): ?>
        <div class="error" data-error_id="<?php echo htmlspecialchars($one_error["error_id"], ENT_QUOTES, 'UTF-8'); ?>">
            <p>
                <strong>[<?php echo htmlspecialchars($one_error["error_time"], ENT_QUOTES, "UTF-8"); ?>]</strong> - 
                <?php echo htmlspecialchars($one_error["message"], ENT_QUOTES, "UTF-8"); ?>
            </p>

            <div class="error_details"></div>

            <button class="show_error_details">Podrobnosti</button>
            <button class="hide_error_details">Skryť</button>

            <button class="delete_error_log">Vymazať</button>
        </div>

    <?php endforeach; ?>
<?php endif; ?>