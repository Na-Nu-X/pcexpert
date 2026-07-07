<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/admin_functions.php";
    require "../classes/Database.php";

    $database_object = new Database(); // Initializes The Database Methods
    
    $connection = $database_object->connectionDB(); // Connects To The DB

    $offset = intval($_POST["offset"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach(getErrorLog($connection, $offset) as $one_error): ?>
        <div class="error" data-error_id="<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>">
            <p>
                <strong>[<?php echo htmlspecialchars($one_error["error_time"], ENT_QUOTES, "UTF-8"); ?>]</strong> - 
                <?php echo htmlspecialchars($one_error["message"], ENT_QUOTES, "UTF-8"); ?>
            </p>

            <div class="error_details"></div>

            <button class="show_error_details" type="button" title="Podrobnosti">Podrobnosti</button>
            <button class="hide_error_details" type="button" title="Skryť">Skryť</button>

            <label class="delete_error_log" for="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>">Vymazať</label>
            <label class="cancel_delete_error_log" for="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>">Zrušiť</label>

            <input 
                class="delete_error_log_checkbox"
                type="checkbox"
                name="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>"
                id="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>"
                value="yes"
            >
        </div>

    <?php endforeach; ?>
<?php endif; ?>