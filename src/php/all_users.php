<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/admin_functions.php";
    
    $connection = connectionDB();

    $offset = intval($_POST["offset"] ?? 0);
    $user_id = intval($_POST["user_id"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach(allUsers($connection, $user_id, $offset) as $one_user):  ?>
        <div class="user <?php echo $theme; ?>">
            <div class="basic_info">
                <?php if(!empty($one_user["profile_picture_name"])): ?>
                    <img
                        class="profile_picture"
                        src="/content/uploads/<?php echo htmlspecialchars($one_user['id'], ENT_QUOTES, 'UTF-8'); ?>/<?php echo htmlspecialchars($one_user['profile_picture_name'], ENT_QUOTES, 'UTF-8'); ?>"
                    >

                <?php else: ?>
                    <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                <?php endif; ?>

                <p class="user_name">
                    <?php echo htmlspecialchars($one_user["first_name"], ENT_QUOTES, "UTF-8"); ?>

                    <?php echo htmlspecialchars($one_user["last_name"], ENT_QUOTES, "UTF-8"); ?>

                    <span class="user_role"
                        <?php if($one_user["role"] === "admin"): ?>
                            style="color: rgb(250, 50, 50);"

                        <?php endif; ?>
                    >
                        
                        <span class="user_role_value">
                            <?php echo "- " . htmlspecialchars($one_user["role"], ENT_QUOTES, "UTF-8"); ?>
                        </span>
                        
                        <label for="user_role_checkbox<?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?>">
                            <i class="fa-solid fa-pen user_role_icon"></i> <!-- https://fontawesome.com/icons/pen?f=classic&s=solid -->
                        </label>

                        <input 
                            class="user_role_checkbox"
                            type="checkbox"
                            name="user_role_checkbox<?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?>"
                            id="user_role_checkbox<?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?>"
                            value="user"
                        >
                    </span>
                </p>
            </div>

            <div class="other_info">
                <div>
                    <h3>Kontaktné údaje</h3>

                    <p><strong>E-mail: </strong><?php echo htmlspecialchars($one_user["email_address"], ENT_QUOTES, "UTF-8"); ?></p>

                    <p><strong>Telefónne číslo: </strong><?php echo htmlspecialchars($one_user["phone_number"], ENT_QUOTES, "UTF-8"); ?></p>
                </div>

                <div>
                    <h3>Ostatné údaje</h3>

                    <p><strong>ID používateľa: </strong><?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?></p>

                    <p><strong>Vytvorenie účtu: </strong><?php echo htmlspecialchars(date("j.n.Y", strtotime($one_user["creation_time"])), ENT_QUOTES, "UTF-8"); ?></p>
                </div>
            </div>
            
            <p class="form_report_paragraph"></p>

            <div class="delete_user_container">
                <p>Chcete nenávratne odstrániť tento účet?</p>
                
                <label for="delete_user_checkbox_<?php echo htmlspecialchars($one_user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <img class="delete_user" title="Odstrániť účet" src="/content/images/delete_account.png" alt="Odstrániť účet"> <!-- https://www.flaticon.com/free-icon/delete_11502978 -->
                </label>

                <input 
                    class="delete_user_checkbox"
                    type="checkbox"
                    name="delete_user_checkbox_<?php echo htmlspecialchars($one_user['id'], ENT_QUOTES, 'UTF-8'); ?>"
                    id="delete_user_checkbox_<?php echo htmlspecialchars($one_user['id'], ENT_QUOTES, 'UTF-8'); ?>"
                    value="yes"
                >
            </div>
        </div>

    <?php endforeach; ?>
<?php endif; ?>