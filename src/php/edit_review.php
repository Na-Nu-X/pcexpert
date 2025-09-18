<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/reviews.php";
    
    session_start();
    
    $connection = connectionDB();

    // Theme

    $theme = "light"; // Default Theme

    if(isset($_COOKIE["theme"])) {
        $theme = $_COOKIE["theme"];
    }

    $user_id = $_SESSION["logged_in_user_id"] ?? null;

    if($_SERVER["REQUEST_METHOD"] === "POST") {
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

    <link rel="stylesheet" href="/src/css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
    <header>
        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item home">
                    <a href="./domov">DOMOV</a>
                </li>

                <div class="navigation_icon">
                    <i class="fa-solid fa-bars"></i>
                    <!-- <i class="fa-solid fa-xmark"></i> -->
                </div>
            </ul>
        </nav>

        <div class="cookie_policy">
            <img src="/content/images/cookie.png" alt="Cookie"> <!-- https://www.flaticon.com/free-icon/cookie_1047711 -->
            <h2>Cookie Policy</h2>
            <p>Táto&nbsp;stránka&nbsp;používa&nbsp;súbory&nbsp;cookies.</p>
            <button class="cookie_policy_button">ROZUMIEM</button>
        </div>
    </header>

    <main>
        <section class="edit_review">
            <a href="./domov">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png" alt="Späť na domovskú stránku"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./upravit-recenziu">Recenzia</a></h2>
            
            <?php if(userReview($connection, $user_id) !== null and userReview($connection, $user_id) !== false): ?>
                <form class="edit_review_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                    <div class="review_container">
                        <?php if(!empty(getProfilePictureName($connection, $_SESSION["logged_in_user_id"]))): ?>
                            <img 
                                class="profile_picture"
                                src="/content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>"
                                alt="Profilový obrázok"
                            >

                        <?php else: ?>
                            <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                        <?php endif; ?>

                        <p>
                            <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>

                            <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>

                            -

                            <?php echo htmlspecialchars(date("j.n.Y", strtotime(userReview($connection, $_SESSION["logged_in_user_id"])["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                            <?php if(userReview($connection, $_SESSION["logged_in_user_id"])["edited"] === "yes"): ?>
                                (upravená)

                            <?php endif; ?>
                        </p>
                        
                        <!-- Rating -->
                        
                        <div class="stars">
                            <?php for($i = 1; $i <=5; $i++): ?>
                                <?php if($i <= userReview($connection, $_SESSION["logged_in_user_id"])["rating"]): ?>
                                    <img src="/content/images/star.png" alt="Hviezda" data-rating="<?php echo $i; ?>"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                                <?php else: ?>
                                    <img src="/content/images/empty_star.png" alt="Prázdna hviezda" data-rating="<?php echo $i; ?>"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                                <?php endif; ?>

                            <?php endfor; ?>

                            <input class="rating" type="hidden" name="rating" value="<?php echo (int)userReview($connection, $_SESSION['logged_in_user_id'])['rating']; ?>">
                        </div>
                    </div>

                    <!-- Review -->

                    <textarea type="textarea" name="review" placeholder="Zanechajte nám vaše hodnotenie" maxlength=200><?php echo userReview($connection, $_SESSION["logged_in_user_id"])["review"]; ?></textarea>

                    <p class="form_report_paragraph"></p>

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

    <script>
        // JavaScript Code

        "use strict"

        document.addEventListener("DOMContentLoaded", function(event) {
            // Stars

            const stars = document.querySelectorAll(".stars img")
            const rating = document.querySelector(".rating")
            let selected_rating = parseInt(rating.value) || 0

            function updateStars(hover_value = 0) {
                stars.forEach(function(star, index) {
                    if(hover_value > 0) {
                        if(index < hover_value) {
                            star.src = "/content/images/star.png"
                        }

                        else {
                            star.src = "/content/images/empty_star.png"
                        }
                    }

                    else {
                        if(index < selected_rating) {
                            star.src = "/content/images/star.png"
                        }

                        else {
                            star.src = "/content/images/empty_star.png"
                        }
                    }
                })
            }

            stars.forEach(function(star, index) {
                const rating_number = index + 1

                star.addEventListener("mouseover", function(event) {
                    updateStars(rating_number)
                })

                star.addEventListener("mouseout", function(event) {
                    updateStars()
                })

                star.addEventListener("click", function(event) {
                    selected_rating = rating_number
                    rating.value = rating_number
                    updateStars()
                })
            })

            updateStars()

            // Update Review

            const form_report_paragraph = document.querySelector(".form_report_paragraph")
            const edit_review_submit = document.querySelector(".edit_review_submit")

            edit_review_submit.addEventListener("click", function(event) {
                if(rating.value === "" || parseInt(rating.value) === 0) {
                    event.preventDefault()

                    form_report_paragraph.textContent = "Ukážte nám vašu spokojnosť."
                    form_report_paragraph.classList.add("invalid")
                }

                else {
                    form_report_paragraph.textContent = ""
                    form_report_paragraph.classList.remove("invalid")
                }
            })

            // Delete Review Warning

            const delete_review_input = document.querySelector("#delete_review")
            const delete_review_image = document.querySelector(".delete_review")

            delete_review_input.addEventListener("click", function(event) {
                if(delete_review_input.checked) {
                    delete_review_image.style.opacity = 1

                    form_report_paragraph.textContent = "Vaša recenzia bude odstránená."
                    form_report_paragraph.classList.add("invalid")
                }

                else {
                    delete_review_image.style.opacity = 0.8

                    form_report_paragraph.textContent = ""
                }
            })
        })
    </script>

    <script src="/src/js/navigation_bar.js"></script>
    <script src="/src/js/cookie_policy.js"></script>
</body>
</html>