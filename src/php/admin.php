<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/admin_functions.php";

    session_start();

    // CSRF Token

    if(!isset($_SESSION["csrf_token"])) {
        $csrf_token = bin2hex(random_bytes(30)); // Generate Random 60 Characters Long String
        $_SESSION["csrf_token"] = $csrf_token;
    }
    
    $connection = connectionDB();

    // Theme

    $theme = "light"; // Default Theme

    if(isset($_COOKIE["theme"])) {
        $theme = $_COOKIE["theme"];
    }

    // Logged In User ID From Session

    $user_id = $_SESSION["logged_in_user_id"] ?? null;

    // Admin Form After Submitting

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["admin_form_submit"])) {
        if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            errorLogSave($connection, "CSRF Token Verification Failed While Editing In Admin Page.", "../errors/error.log");
            die("CSRF Token nie je platný.");
        }

        // User Role

        foreach(allUsers($connection, $_SESSION["logged_in_user_id"], 0, 100) as $one_user) {
            if(isset($_POST["user_role_checkbox_" . $one_user["id"]])) {
                changeUserRole($connection, $one_user["id"], $_POST["user_role_checkbox_" . $one_user["id"]]);
            }
        }

        // Delete User

        foreach(allUsers($connection, $_SESSION["logged_in_user_id"], 0, 100) as $one_user) {
            if(isset($_POST["delete_user_checkbox_" . $one_user["id"]]) and $_POST["delete_user_checkbox_" . $one_user["id"]] === "yes") {
                deleteUser($connection, $one_user["id"]);
            }
        }

        // Delete Error

        foreach(getErrorLog($connection, 0, 100) as $one_error) {
            if(isset($_POST["delete_error_log_checkbox_" . $one_error["error_id"]]) and $_POST["delete_error_log_checkbox_" . $one_error["error_id"]] === "yes") {
                deleteErrorLogById($connection, $one_error["error_id"]);
            }
        }

        $_SESSION["green_report_message"] = "Zmeny&nbsp;boli&nbsp;vykonané";
    }

    // Approve or Reject Reviews

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["approve_review"])) {
        if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            errorLogSave($connection, "CSRF Token Verification Failed While Approving A Review In Admin Page.", "../errors/error.log");
            die("CSRF Token nie je platný.");
        }

        $review_id = $_POST["review_id"];

        if(approveReview($connection, $review_id)) {
            $_SESSION["green_report_message"] = "Recenzia bola schválená.";

            redirectURL("/administracia");
        }
    }

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["reject_review"])) {
        if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            errorLogSave($connection, "CSRF Token Verification Failed While Rejecting A Review In Admin Page.", "../errors/error.log");
            die("CSRF Token nie je platný.");
        }

        $review_id = $_POST["review_id"];

        if(rejectReview($connection, $review_id)) {
            $_SESSION["red_report_message"] = "Recenzia bola zamietnutá.";

            redirectURL("/administracia");
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Administrácia</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/admin.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
    <script src="/src/ts/dist/pages/admin/components/admin.js" type="module"></script>

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

    <header>
        <?php if(isset($_SESSION["green_report_message"]) and !empty($_SESSION["green_report_message"])): ?>
            <p class="green_report_message"><?php echo $_SESSION["green_report_message"]; ?></p>
            <?php $_SESSION["green_report_message"] = ""; ?>

        <?php endif; ?>

        <?php if(isset($_SESSION["red_report_message"]) and !empty($_SESSION["red_report_message"])): ?>
            <p class="red_report_message"><?php echo $_SESSION["red_report_message"]; ?></p>
            <?php $_SESSION["red_report_message"] = ""; ?>

        <?php endif; ?>

        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item">
                    <a href="../domov">DOMOV</a>
                </li>
                
                <li class="navigation_bar_item scroll_pending_reviews">
                    <a href="#">Kontrola recenzií</a>
                </li>

                <li class="navigation_bar_item scroll_users">
                    <a href="#">POUŽÍVATELIA</a>
                </li>

                <li class="navigation_bar_item scroll_error_log">
                    <a href="#">PROTOKOLY CHÝB</a>
                </li>

                <button class="hamburger">
                    <i class="fa-solid fa-bars"></i>
                    <!-- <i class="fa-solid fa-xmark"></i> -->
                </button>
            </ul>
        </nav>

        <div class="cookie_policy">
            <img src="/content/images/cookie.png" alt="Cookie"> <!-- https://www.flaticon.com/free-icon/cookie_1047711 -->
            <h2>Cookie Policy</h2>
            <p>Táto&nbsp;stránka&nbsp;používa&nbsp;súbory&nbsp;cookies.</p>
            <button class="accept_all">ROZUMIEM</button>
        </div>
    </header>

    <main class="<?php echo $theme; ?>" style="padding: 10px 20px;">
        <?php if(isset($_SESSION["logged_in_user_id"]) and getUserRole($connection, $_SESSION["logged_in_user_id"]) === "admin"): ?>
            <section class="pending_reviews">
                <h2>Kontrola&nbsp;recenzií</h2>

                <div class="reviews <?php echo $theme; ?>">
                    <?php foreach(getPendingReviews($connection) as $one_review): ?>
                        <div class="review">
                            <?php if($one_review["user_id"] !== null): ?>
                                <div class="reviewer">
                                    <img
                                        class="profile_picture"
                                        src="
                                            <?php if(!empty(getUserInformations($connection, $one_review['user_id'], 'google_profile_picture_url')) and empty(getUserInformations($connection, $one_review['user_id'], 'profile_picture_name'))): ?>
                                                <?php echo getUserInformations($connection, $one_review['user_id'], 'google_profile_picture_url'); ?>

                                            <?php else: ?>
                                                /content/uploads/<?php echo $one_review['user_id']; ?>/<?php echo getProfilePictureName($connection, $one_review['user_id']); ?>

                                            <?php endif; ?>
                                        "
                                    >

                                    <p class="user_name">
                                        <?php echo htmlspecialchars(getUserInformations($connection, $one_review["user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>

                                        <?php echo htmlspecialchars(getUserInformations($connection, $one_review["user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>

                                        <span class="date">
                                            -

                                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                                            <?php if($one_review["edited"] === "yes"): ?>
                                                (upravená)

                                            <?php endif; ?>
                                        </span>
                                    </p>
                                </div>

                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $one_review["rating"]): ?>
                                        <img src="/content/images/star.png" alt="Hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                                    <?php else: ?>
                                        <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                                    <?php endif; ?>
                                    
                                <?php endfor; ?>

                                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php else: ?>
                                <div class="reviewer">
                                    <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                                    <p class="user_name">
                                        Anonymný zákazník

                                        <span class="date">
                                            -
                                            
                                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>
                                        </span>
                                    </p>
                                </div>

                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $one_review["rating"]): ?>
                                        <img src="/content/images/star.png" alt="Hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                                    <?php else: ?>
                                        <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                                    <?php endif; ?>
                                    
                                <?php endfor; ?>

                                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php endif; ?>
                        </div>

                        <form class="review_confirmation" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <input type="hidden" name="review_id" value="<?php echo $one_review['review_id']; ?>">

                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <button class="approve_review <?php echo $theme; ?>" type="submit" name="approve_review">Potvrdiť</button>
                            <button class="reject_review <?php echo $theme; ?>" type="submit" name="reject_review">Zahodiť</button>
                        </form>

                    <?php endforeach; ?>
                </div>

                <?php if(countPendingReviews($connection) > 5): ?>
                    <button class="show_more show_more_reviews" type="button" title="Zobraziť viac">Zobraziť viac</button>
                    <button class="show_less show_less_reviews" type="button" title="Zobraziť menej">Zobraziť menej</button>

                <?php endif; ?>

                <?php if(empty(getPendingReviews($connection))): ?>
                    <p style="text-align: center; margin-bottom: 0px;">Nenašli sa žiadne recenzie na potvrdenie.</p>

                <?php endif; ?>
            </section>

            <form class="admin_form <?php echo $theme; ?>" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <section class="users">
                    <h2>Používatelia</h2>

                    <div class="all_users" data-logged_in_user_id="<?php echo json_encode($user_id); ?>">
                        <?php foreach(allUsers($connection, $_SESSION["logged_in_user_id"]) as $one_user):  ?>
                            <div class="user <?php echo $theme; ?>" data-role="<?php echo htmlspecialchars($one_user["role"], ENT_QUOTES, "UTF-8"); ?>">
                                <div class="basic_info">
                                    <?php if(!empty($one_user["profile_picture_name"]) or !empty($one_user["google_profile_picture_url"])): ?>
                                        <img
                                            class="profile_picture"
                                            src="
                                                <?php if(!empty($one_user['google_profile_picture_url']) and empty($one_user['profile_picture_name'])): ?>
                                                    <?php echo $one_user['google_profile_picture_url']; ?>

                                                <?php else: ?>
                                                    /content/uploads/<?php echo htmlspecialchars($one_user['id'], ENT_QUOTES, 'UTF-8'); ?>/<?php echo htmlspecialchars($one_user['profile_picture_name'], ENT_QUOTES, 'UTF-8'); ?>

                                                <?php endif; ?>
                                            "
                                        >

                                    <?php else: ?>
                                        <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                                    <?php endif; ?>

                                    <?php if(!empty($one_user["google_id"])): ?>
                                        <img class="google_profile_picture_frame" src="/content/images/google.png" alt="Google"> <!-- https://www.flaticon.com/free-icon/search_281764 -->

                                    <?php endif; ?>

                                    <p class="user_name">
                                        <?php echo htmlspecialchars($one_user["first_name"], ENT_QUOTES, "UTF-8"); ?>

                                        <?php echo htmlspecialchars($one_user["last_name"], ENT_QUOTES, "UTF-8"); ?>

                                        <span class="user_role"
                                            <?php if($one_user["role"] === "admin"): ?>
                                                style="color: rgb(250, 50, 50);"

                                            <?php endif; ?>

                                            title="Zmeniť oprávnenia"
                                        >
                                            
                                            <span class="user_role_value">
                                                <?php echo "- " . htmlspecialchars($one_user["role"], ENT_QUOTES, "UTF-8"); ?>
                                            </span>
                                            
                                            <label for="user_role_checkbox_<?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?>">
                                                <i style="color: rgb(150, 150, 150);" class="fa-solid fa-pen user_role_icon"></i> <!-- https://fontawesome.com/icons/pen?f=classic&s=solid -->
                                            </label>

                                            <input 
                                                class="user_role_checkbox"
                                                type="checkbox"
                                                name="user_role_checkbox_<?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?>"
                                                id="user_role_checkbox_<?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?>"
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
                                
                                <p class="form_report valid"></p>

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
                    </div>

                    <?php if(countUsers($connection, $user_id) > 5): ?>
                        <button class="show_more show_more_users" type="button" title="Zobraziť viac">Zobraziť viac</button>
                        <button class="show_less show_less_users" type="button" title="Zobraziť menej">Zobraziť menej</button>

                    <?php endif; ?>

                    <?php if(empty(allUsers($connection, $_SESSION["logged_in_user_id"]))): ?>
                        <p style="text-align: center; margin-bottom: 0px;">Nenašli sa žiadny zaregistrovaný používatelia.</p>

                    <?php endif; ?>
                </section>

                <section class="error_log">
                    <h2>Protokoly&nbsp;chýb</h2>
                    
                    <div class="all_errors">
                        <?php foreach(getErrorLog($connection) as $one_error): ?>
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
                    </div>

                    <?php if(countErrors($connection) > 5): ?>
                        <button style="margin-bottom: 10px;" class="show_more show_more_errors" type="button" title="Zobraziť viac">Zobraziť viac</button>
                        <button style="margin-bottom: 10px;" class="show_less show_less_errors" type="button" title="Zobraziť menej">Zobraziť menej</button>

                    <?php endif; ?>

                    <?php if(empty(getErrorLog($connection))): ?>
                        <p style="text-align: center; margin-bottom: 10px;">Nenašli sa žiadne chybné záznamy.</p>

                    <?php endif; ?>
                </section>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <button class="admin_form_submit" type="submit" name="admin_form_submit">Uložiť</button>
            </form>

        <?php else: ?>
            <div class="no_admin">
                <p style="margin-bottom: 0px;">K obsahu tejto stránky nemáte povolený prístup.</p>
                <a href="./domov" title="Domov" target="_self">Späť na úvodnú stránku.</a>
            </div>

        <?php endif; ?>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>