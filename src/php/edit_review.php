<?php
    // PHP Code

    require "../assets/functions.php";
    
    session_start();
    
    $connection = connectionDB();

    // Theme

    $theme = "light"; // Default Theme

    if(isset($_COOKIE["theme"])) {
        $theme = $_COOKIE["theme"];
    }

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["edit_review_submit"])) {
        $rating = $_POST["rating"];
        $review = $_POST["review"];

        if(updateReview($connection, $_SESSION["logged_in_user_id"], $rating, $review)) {
            redirectURL("/src/php/index.php");
        }

        else {
            // Error Code
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Upraviť recenziu</title>

    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
    <header>
        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item home">
                    <a href="./index.php">DOMOV</a>
                </li>

                <div class="navigation_icon">
                    <i class="fa-solid fa-bars"></i>
                    <!-- <i class="fa-solid fa-xmark"></i> -->
                </div>
            </ul>
        </nav>
    </header>

    <main>
        <section class="edit_review">
            <a href="./index.php">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png" alt="Späť na domovskú stránku"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./edit_review.php">Recenzia</a></h2>
            
            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                <form class="edit_review_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                    <!-- Profile Picture -->

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

                        <!-- User Name and Review Time -->
                        
                        <p>
                            <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>
                            <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>
                            -
                            <?php echo htmlspecialchars(date("j.n.Y", strtotime(findUserReview($connection, $_SESSION["logged_in_user_id"])["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                            <?php if(findUserReview($connection, $_SESSION["logged_in_user_id"])["edited"] === "yes"): ?>
                                (upravená)

                            <?php endif; ?>
                        </p>
                        
                        <!-- Rating -->
                        
                        <div class="stars">
                            <?php for($i = 1; $i <=5; $i++): ?>
                                <?php if($i <= findUserReview($connection, $_SESSION["logged_in_user_id"])["rating"]): ?>
                                    <img src="/content/images/star.png" alt="Hviezda" data-rating="<?php echo $i; ?>"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                                <?php else: ?>
                                    <img src="/content/images/empty_star.png" alt="Prázdna hviezda" data-rating="<?php echo $i; ?>"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                                <?php endif; ?>

                            <?php endfor; ?>

                            <input class="rating" type="hidden" name="rating" value="<?php echo (int)findUserReview($connection, $_SESSION["logged_in_user_id"])["rating"]; ?>">
                        </div>
                    </div>

                    <!-- Review -->

                    <textarea type="textarea" name="review" placeholder="Zanechajte nám vaše hodnotenie" maxlength=200><?php echo findUserReview($connection, $_SESSION["logged_in_user_id"])["review"]; ?></textarea>

                    <p class="form_report_paragraph"></p>

                    <button class="edit_review_submit" type="submit" name="edit_review_submit">Upraviť recenziu</button>
                </form>

            <?php else: ?>
                <div class="no_logged_in">
                    <p>Pre úpravu vašej recenzie sa musíte najprv <a href="./log_in.php" title="Prihlásiť sa">prihlásiť</a> alebo <a href="./registration.php" title="Vytvoriť účet">zaregistrovať.</a></p>
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

        // Write Review

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

        const form_report_paragraph = document.querySelector(".form_report_paragraph")
        const write_review_submit = document.querySelector(".edit_review_submit")

        write_review_submit.addEventListener("click", function(event) {
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
    </script>

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>