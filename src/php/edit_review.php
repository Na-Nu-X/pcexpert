<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/reviews.php";
    
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

    $user_id = $_SESSION["logged_in_user_id"] ?? null;

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        $current_time = time();
        $last_edit = strtotime(userReview($connection, $_SESSION["logged_in_user_id"])["review_time"]);
        $edit_cooldown = 60 * 60 * 24 * 30; // 30 Days
        $next_edit = date("j.n.Y", $last_edit + $edit_cooldown);

        if($current_time - $last_edit >= $edit_cooldown) {
            if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                errorLogSave($connection, "CSRF Token Verification Failed While Editing A Review.", "../errors/error.log");
                die("CSRF Token nie je platný.");
            }

            $rating = $_POST["rating"];
            $review = $_POST["review"];

            $delete_review = isset($_POST["delete_review"]) ? "yes" : "no";

            if($delete_review === "yes") {
                if(deleteReview($connection, $_SESSION["logged_in_user_id"])) {
                    $_SESSION["red_report_message"] = "Recenzia&nbsp;bola&nbsp;odstránená";

                    redirectURL("/domov");
                }
            }

            else if($delete_review === "no") {
                if(updateReview($connection, $_SESSION["logged_in_user_id"], $rating, $review)) {
                    redirectURL("/domov");
                }

                else {
                    // Error Code
                }
            }
        }

        else {
            $message = "Ďalšie úpravy budú možné ${next_edit}";
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-L62G1B08VS"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-L62G1B08VS');
    </script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Upraviť recenziu</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/forms.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
    <script src="/src/ts/dist/pages/edit_review/components/edit_review_form.js" type="module"></script>
</head>

<body class="<?php echo $theme; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

    <header>
        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item home">
                    <a href="./domov">DOMOV</a>
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

    <main>
        <section class="edit_review">
            <a href="./domov" class="back_icon" title="Späť na domovskú stránku" aria-label="Späť na domovskú stránku">
                <img src="/content/images/back.png" alt=""> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./upravit-recenziu">Recenzia</a></h2>
            
            <?php if(userReview($connection, $user_id) !== null and userReview($connection, $user_id) !== false): ?>
                <form class="edit_review_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                    <div class="review_container">
                        <div class="user">
                            <?php if(!empty(getProfilePictureName($connection, $_SESSION["logged_in_user_id"])) or !empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'))): ?>
                                <img 
                                    class="profile_picture"
                                    src="
                                        <?php if(!empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url')) and empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'))): ?>
                                            <?php echo getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'); ?>
    
                                        <?php else: ?>
                                            /content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>
    
                                        <?php endif; ?>
                                    "
                                    alt="Profilový obrázok"
                                >
    
                            <?php else: ?>
                                <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->
    
                            <?php endif; ?>
    
                            <p class="name">
                                <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>
    
                                <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>
    
                                -
    
                                <?php echo htmlspecialchars(date("j.n.Y", strtotime(userReview($connection, $_SESSION["logged_in_user_id"])["review_time"])), ENT_QUOTES, "UTF-8"); ?>
    
                                <?php if(userReview($connection, $_SESSION["logged_in_user_id"])["edited"] === "yes"): ?>
                                    (upravená)
    
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <!-- Rating -->
                        
                        <div class="stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= userReview($connection, $_SESSION["logged_in_user_id"])["rating"]): ?>
                                    <div class="star" data-rating="<?php echo $i; ?>">
                                        <i class="fa-solid fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                    </div>

                                <?php else: ?>
                                    <div class="star" data-rating="<?php echo $i; ?>">
                                        <i class="fa-regular fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                    </div>
                                    
                                <?php endif; ?>

                            <?php endfor; ?>

                            <input class="rating" type="hidden" name="rating" value="<?php echo (int)userReview($connection, $_SESSION['logged_in_user_id'])['rating']; ?>">
                        </div>
                    </div>

                    <!-- Review -->

                    <textarea type="textarea" name="review" placeholder="Zanechajte nám vaše hodnotenie" maxlength=200><?php echo userReview($connection, $_SESSION["logged_in_user_id"])["review"]; ?></textarea>

                    <p class="form_report valid"></p>

                    <article>
                        <p class="dialogue_paragraph invalid" style="top: 229px;">
                            <?php if(!empty($message)): ?>
                                <?php
                                    echo $message; 
                                    $message = "";
                                ?>

                            <?php endif; ?>
                        </p>
                    </article>

                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <button class="edit_review_submit" type="submit" name="edit_review_submit">Upraviť recenziu</button>
                    
                    <div class="delete_review_container">
                        <p>Chcete odstrániť vašu recenziu?</p>

                        <label for="delete_review">
                            <img class="delete_review" title="Odstrániť recenziu" src="/content/images/trash.png" alt="Odstrániť recenziu"> <!-- https://www.flaticon.com/free-icon/trash_8848798 -->
                        </label>
                        
                        <input type="checkbox" name="delete_review" id="delete_review" value="yes">
                    </div>
                </form>

            <?php elseif(!isset($_SESSION["is_logged_in"])): ?>
                <div class="no_logged_in">
                    <p>Pre úpravu vašej recenzie sa musíte najprv <a href="./prihlasenie" title="Prihlásiť sa">prihlásiť</a> alebo <a href="./registracia" title="Vytvoriť účet">zaregistrovať.</a></p>
                </div>

            <?php else: ?>
                <div class="no_review">
                    <p>Zatiaľ ste nenapísali žiadnu recenziu.</p>
                </div>

            <?php endif; ?>
        </section>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>