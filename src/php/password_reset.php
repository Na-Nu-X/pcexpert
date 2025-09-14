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

    // Password Reset

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        $password_reset_code = getUserInformations($connection, $_SESSION["logged_in_user_id"], "password_reset_code");

        if($password_reset_code === (int)$_POST["password_reset_code"]) {
            $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

            updatePassword($connection, $_SESSION["logged_in_user_id"], $new_password);

            // Delete Cookie
            // Redirect To Home Page
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Obnova hesla</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->
</head>

<body class="<?php echo $theme; ?>">
    <header>
        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item home">
                    <a href="./index">DOMOV</a>
                </li>

                <li class="navigation_bar_item">
                    <a href="./edit_account">MÔJ ÚČET</a>
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
        <section class="password_reset">
            <a href="./index">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png" alt="Späť na domovskú stránku"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./password_reset">Obnova&nbsp;hesla</a></h2>

            <?php if(isset($_COOKIE["password_reset_code_timer"])): ?>
                <form class="password_reset_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                    <input type="text" name="password_reset_code" placeholder="Zadajte 6 miestny kód" maxlength="6" autocomplete="off" required>
                    <input class="new_password" type="text" name="new_password" placeholder="Vytvorte nové heslo" autocomplete="off" required>

                    <p class="form_report_paragraph"></p>

                    <button type="submit" name="submit">Zmeniť heslo</button>
                </form>

            <?php else: ?>
                <div class="code_expired">
                    <p>Platnosť overovacieho kódu vypršala.</p>
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

        // Password Length Verification

        const new_password = document.querySelector(".new_password")
        const form_report_paragraph = document.querySelector(".form_report_paragraph")

        new_password.addEventListener("input", function(event){
            if(new_password.value.length < 8 && new_password.value.length > 0) {
                form_report_paragraph.textContent = "Heslo je príliš krátke."
                form_report_paragraph.classList.add("invalid")
                form_report_paragraph.classList.remove("valid")
            }

            else {
                form_report_paragraph.textContent = ""
            }
        })
    </script>

    <script src="/src/js/navigation_bar.js"></script>
    <script src="/src/js/cookie_policy.js"></script>
</body>
</html>