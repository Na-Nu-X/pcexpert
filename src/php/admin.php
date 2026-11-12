<?php
    // Debug
    
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);

    // PHP Code

    require "../assets/functions.php";
    require "../classes/Database.php";
    require "../classes/User.php";
    require "../classes/Review.php";
    require "../classes/Admin.php";

    $database_object = new Database(); // Initializes The Database Methods
    $user_object = new User(); // Initializes The User Methods
    $review_object = new Review(); // Initializes The Review Methods
    $admin_object = new Admin(); // Initializes The Admin Methods

    session_start();

    // CSRF Token

    if(!isset($_SESSION["csrf_token"])) {
        $csrf_token = bin2hex(random_bytes(30)); // Generate Random 60 Characters Long String
        $_SESSION["csrf_token"] = $csrf_token;
    }
    
    $connection = $database_object->connectionDB(); // Connects To The DB

    // Theme

    $theme = "light"; // Default Theme

    if(isset($_COOKIE["theme"])) {
        $theme = $_COOKIE["theme"];
    }

    // Logged In User ID From Session

    $user_id = $_SESSION["logged_in_user_id"] ?? null; // Gets The Logged In User ID

    // Admin Form After Submitting

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["admin_form_submit"])) {
        if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            errorLogSave($connection, "CSRF Token Verification Failed While Editing In Admin Page.", "../errors/error.log");
            die("CSRF Token nie je platný.");
        }

        // User Role

        foreach($admin_object->getAllUsers($connection, $_SESSION["logged_in_user_id"], 0, 100) as $one_user) {
            if(isset($_POST["user_role_checkbox_" . $one_user["id"]])) {
                $admin_object->changeUserRole($connection, $one_user["id"], $_POST["user_role_checkbox_" . $one_user["id"]]);
            }
        }

        // Delete User

        foreach($admin_object->getAllUsers($connection, $_SESSION["logged_in_user_id"], 0, 100) as $one_user) {
            if(isset($_POST["delete_user_checkbox_" . $one_user["id"]]) and $_POST["delete_user_checkbox_" . $one_user["id"]] === "yes") {
                $user_object->deleteUser($connection, $one_user["id"]);
            }
        }

        // Delete Error

        foreach($admin_object->getErrorLog($connection, 0, 100) as $one_error) {
            if(isset($_POST["delete_error_log_checkbox_" . $one_error["error_id"]]) and $_POST["delete_error_log_checkbox_" . $one_error["error_id"]] === "yes") {
                $admin_object->deleteErrorLogById($connection, $one_error["error_id"]);
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

        if($admin_object->approveReview($connection, $review_id)) {
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

        if($admin_object->rejectReview($connection, $review_id)) {
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"> <!-- Inter & Plus Jakarta Sans Font Styles -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

    <?php require "../assets/partials/flash_messages.php"; ?>
    <?php require "../assets/partials/cookie_policy.php"; ?>

    <header class="site_header">
        <?php
            $navigation_links = [
                ["label" => "Domov", "href" => "/domov"],
                ["label" => "Recenzie", "href" => "#pending_reviews"],
                ["label" => "Používatelia", "href" => "#users"],
                ["label" => "Protokoly chýb", "href" => "#error_log"]
            ];
        ?>
        <?php require "../assets/partials/navigation.php"; ?>
    </header>

    <main class="admin_page">
        <?php if(isset($_SESSION["logged_in_user_id"]) and $user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "role") === "admin"): ?>
            <script src="/src/ts/dist/pages/admin/components/admin.js" type="module"></script>

            <?php
                $pending_reviews = $admin_object->getPendingReviews($connection, 0, 50) ?? [];
                $pending_reviews_count = $admin_object->countPendingReviews($connection);
                $all_users = $admin_object->getAllUsers($connection, $user_id) ?? [];
                $users_count = $admin_object->countUsers($connection, $user_id);
                $all_errors = $admin_object->getErrorLog($connection) ?? [];
                $errors_count = $admin_object->countErrors($connection);
            ?>

            <div class="container">
                <div class="admin_header">
                    <p class="eyebrow"><i class="fa-solid fa-shield-halved"></i> Administrácia</p> <!-- https://fontawesome.com/icons/shield-halved -->
                    <h1>Prehľad</h1>
                    <p>Schvaľujte recenzie, spravujte používateľov a sledujte systémové chyby.</p>
                </div>

                <div class="admin_stats">
                    <a class="stat_tile" href="#pending_reviews">
                        <span class="stat_icon warning"><i class="fa-solid fa-star-half-stroke"></i></span> <!-- https://fontawesome.com/icons/star-half-stroke -->
                        <span class="stat_text">
                            <span class="stat_label">Recenzie na schválenie</span>
                            <span class="stat_value"><?php echo $pending_reviews_count; ?></span>
                        </span>
                    </a>

                    <a class="stat_tile" href="#users">
                        <span class="stat_icon"><i class="fa-solid fa-users"></i></span> <!-- https://fontawesome.com/icons/users -->
                        <span class="stat_text">
                            <span class="stat_label">Používatelia</span>
                            <span class="stat_value"><?php echo $users_count; ?></span>
                        </span>
                    </a>

                    <a class="stat_tile" href="#error_log">
                        <span class="stat_icon danger"><i class="fa-solid fa-bug"></i></span> <!-- https://fontawesome.com/icons/bug -->
                        <span class="stat_text">
                            <span class="stat_label">Protokoly chýb</span>
                            <span class="stat_value"><?php echo $errors_count; ?></span>
                        </span>
                    </a>
                </div>

                <!-- Pending Reviews -->

                <section class="panel pending_reviews" id="pending_reviews">
                    <div class="panel_header">
                        <div>
                            <h2>Kontrola recenzií</h2>
                            <p>Nové recenzie sa na webe zobrazia až po schválení.</p>
                        </div>

                        <span class="badge <?php echo $pending_reviews_count > 0 ? "warning" : "neutral"; ?>"><?php echo $pending_reviews_count; ?></span>
                    </div>

                    <?php if(empty($pending_reviews)): ?>
                        <div class="empty_state">
                            <span class="empty_state_icon"><i class="fa-solid fa-circle-check"></i></span> <!-- https://fontawesome.com/icons/circle-check -->
                            <p>Všetky recenzie sú vybavené.</p>
                        </div>

                    <?php else: ?>
                        <div class="review_grid">
                            <?php foreach($pending_reviews as $one_review): ?>
                                <?php require "../assets/partials/admin_review_card.php"; ?>

                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>
                </section>

                <form class="admin_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "UTF-8"); ?>" method="POST" data-admin-form>
                    <!-- Users -->

                    <section class="panel users" id="users">
                        <div class="panel_header">
                            <div>
                                <h2>Používatelia</h2>
                                <p>Zmeňte rolu alebo označte účty na odstránenie a zmeny uložte.</p>
                            </div>

                            <span class="badge neutral"><?php echo $users_count; ?></span>
                        </div>

                        <?php if(empty($all_users)): ?>
                            <div class="empty_state">
                                <span class="empty_state_icon"><i class="fa-solid fa-user-plus"></i></span> <!-- https://fontawesome.com/icons/user-plus -->
                                <p>Zatiaľ sa nezaregistroval žiadny ďalší používateľ.</p>
                            </div>

                        <?php else: ?>
                            <div class="data_table users_table">
                                <div class="data_table_head" aria-hidden="true">
                                    <span>Používateľ</span>
                                    <span>Kontakt</span>
                                    <span>Registrovaný</span>
                                    <span>Rola</span>
                                    <span></span>
                                </div>

                                <div class="all_users" data-logged_in_user_id="<?php echo json_encode($user_id); ?>" data-list>
                                    <?php foreach($all_users as $one_user): ?>
                                        <?php require "../assets/partials/admin_user_row.php"; ?>

                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php if($users_count > count($all_users)): ?>
                                <button class="btn btn_secondary btn_sm load_more" type="button" data-load-more="all_users.php" data-total="<?php echo $users_count; ?>"><i class="fa-solid fa-chevron-down"></i> Načítať ďalších</button>

                            <?php endif; ?>
                        <?php endif; ?>
                    </section>

                    <!-- Error Log -->

                    <section class="panel error_log" id="error_log">
                        <div class="panel_header">
                            <div>
                                <h2>Protokoly chýb</h2>
                                <p>Záznamy o chybách zachytených na webe, od najnovších.</p>
                            </div>

                            <span class="badge <?php echo $errors_count > 0 ? "danger" : "neutral"; ?>"><?php echo $errors_count; ?></span>
                        </div>

                        <?php if(empty($all_errors)): ?>
                            <div class="empty_state">
                                <span class="empty_state_icon"><i class="fa-solid fa-circle-check"></i></span> <!-- https://fontawesome.com/icons/circle-check -->
                                <p>Nenašli sa žiadne chybové záznamy.</p>
                            </div>

                        <?php else: ?>
                            <div class="data_table">
                                <div class="all_errors" data-list>
                                    <?php foreach($all_errors as $one_error): ?>
                                        <?php require "../assets/partials/admin_error_row.php"; ?>

                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php if($errors_count > count($all_errors)): ?>
                                <button class="btn btn_secondary btn_sm load_more" type="button" data-load-more="all_errors.php" data-total="<?php echo $errors_count; ?>"><i class="fa-solid fa-chevron-down"></i> Načítať ďalšie</button>

                            <?php endif; ?>
                        <?php endif; ?>
                    </section>

                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">

                    <!-- Save Bar -->

                    <div class="admin_savebar">
                        <p data-pending-changes>Žiadne neuložené zmeny</p>
                        <button class="btn admin_form_submit" type="submit" name="admin_form_submit" disabled data-admin-save><i class="fa-solid fa-floppy-disk"></i> Uložiť zmeny</button> <!-- https://fontawesome.com/icons/floppy-disk -->
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="container narrow">
                <div class="empty_state no_admin">
                    <span class="empty_state_icon danger"><i class="fa-solid fa-lock"></i></span> <!-- https://fontawesome.com/icons/lock -->
                    <h1>Prístup zamietnutý</h1>
                    <p>K obsahu tejto stránky nemáte povolený prístup.</p>
                    <a class="btn" href="/domov"><i class="fa-solid fa-arrow-left"></i> Späť na úvodnú stránku</a>
                </div>
            </div>

        <?php endif; ?>
    </main>

    <footer>
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>