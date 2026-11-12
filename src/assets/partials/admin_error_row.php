<?php
    /**
     * Admin Error Row (Used By admin.php And all_errors.php)
     *
     * @var array $one_error - Error Row From Admin::getErrorLog()
    **/

    $row_error_id = (int)$one_error["error_id"];
?>

<div class="data_row error_row" data-error_id="<?php echo $row_error_id; ?>">
    <div class="error_summary">
        <span class="error_icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></span> <!-- https://fontawesome.com/icons/triangle-exclamation -->

        <div class="cell_text">
            <p class="primary_text"><?php echo htmlspecialchars($one_error["message"], ENT_QUOTES, "UTF-8"); ?></p>
            <p class="secondary_text mono"><?php echo htmlspecialchars(date("j. n. Y H:i:s", strtotime($one_error["error_time"])), ENT_QUOTES, "UTF-8"); ?> · #<?php echo $row_error_id; ?></p>
        </div>

        <div class="error_actions">
            <button class="btn btn_ghost btn_sm" type="button" aria-expanded="false" data-error-details-toggle>
                <span>Podrobnosti</span> <i class="fa-solid fa-chevron-down" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/chevron-down -->
            </button>

            <label class="delete_toggle" title="Označiť záznam na vymazanie">
                <input type="checkbox" name="delete_error_log_checkbox_<?php echo $row_error_id; ?>" value="yes">
                <i class="fa-regular fa-trash-can" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/trash-can -->
                <span class="when_off">Vymazať</span>
                <span class="when_on">Zrušiť</span>
            </label>
        </div>
    </div>

    <div class="error_details" hidden data-error-details></div>
</div>
