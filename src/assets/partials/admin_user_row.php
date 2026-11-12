<?php
    /**
     * Admin User Row (Used By admin.php And all_users.php)
     *
     * @var array $one_user - User Row From Admin::getAllUsers()
    **/

    $row_user_id = (int)$one_user["id"];

    if(!empty($one_user["profile_picture_name"])) $row_avatar = "/content/uploads/" . $row_user_id . "/" . $one_user["profile_picture_name"];
    elseif(!empty($one_user["google_profile_picture_url"])) $row_avatar = $one_user["google_profile_picture_url"];
    else $row_avatar = "/content/images/avatar.png"; // https://www.flaticon.com/free-icon/user_847969

    $row_role = $one_user["role"] === "admin" ? "admin" : "user";
?>

<div class="data_row user_row" data-role="<?php echo $row_role; ?>">
    <div class="cell cell_user">
        <span class="avatar">
            <img class="profile_picture" src="<?php echo htmlspecialchars($row_avatar, ENT_QUOTES, "UTF-8"); ?>" alt="" referrerpolicy="no-referrer" loading="lazy">

            <?php if(!empty($one_user["google_id"])): ?>
                <img class="avatar_badge" src="/content/images/google.png" alt="Google" title="Google účet"> <!-- https://www.flaticon.com/free-icon/search_281764 -->

            <?php endif; ?>
        </span>

        <div class="cell_text">
            <p class="primary_text"><?php echo htmlspecialchars($one_user["first_name"] . " " . $one_user["last_name"], ENT_QUOTES, "UTF-8"); ?></p>
            <p class="secondary_text">ID <?php echo $row_user_id; ?></p>
        </div>
    </div>

    <div class="cell cell_contact">
        <p class="primary_text"><a href="mailto:<?php echo htmlspecialchars($one_user["email_address"], ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($one_user["email_address"], ENT_QUOTES, "UTF-8"); ?></a></p>
        <p class="secondary_text"><?php echo !empty($one_user["phone_number"]) ? htmlspecialchars($one_user["phone_number"], ENT_QUOTES, "UTF-8") : "Bez telefónu"; ?></p>
    </div>

    <div class="cell cell_date">
        <span class="cell_label">Registrovaný</span>
        <p class="secondary_text"><?php echo htmlspecialchars(date("j. n. Y", strtotime($one_user["creation_time"])), ENT_QUOTES, "UTF-8"); ?></p>
    </div>

    <div class="cell cell_role">
        <label class="cell_label" for="user_role_<?php echo $row_user_id; ?>">Rola</label>

        <select class="role_select" id="user_role_<?php echo $row_user_id; ?>" name="user_role_checkbox_<?php echo $row_user_id; ?>" data-initial="<?php echo $row_role; ?>">
            <option value="user" <?php echo $row_role === "user" ? "selected" : ""; ?>>Používateľ</option>
            <option value="admin" <?php echo $row_role === "admin" ? "selected" : ""; ?>>Administrátor</option>
        </select>
    </div>

    <div class="cell cell_actions">
        <label class="delete_toggle" title="Označiť účet na odstránenie">
            <input type="checkbox" name="delete_user_checkbox_<?php echo $row_user_id; ?>" value="yes">
            <i class="fa-regular fa-trash-can" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/trash-can -->
            <span class="when_off">Odstrániť</span>
            <span class="when_on">Zrušiť</span>
        </label>
    </div>
</div>
