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

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        
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
                    <!-- Code -->
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

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>