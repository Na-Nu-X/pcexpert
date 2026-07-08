<?php
    // Debug
    
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);

    // PHP Code

    require "../assets/functions.php";
    require "../classes/Database.php";
    require "../classes/User.php";

    $database_object = new Database(); // Initializes The Database Methods
    $user_object = new User(); // Initializes The User Methods
    
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

    // Password Reset

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
            errorLogSave($connection, "CSRF Token Verification Failed While Trying To Reset A Password.", "../errors/error.log");
            die("CSRF Token nie je platný.");
        }

        $user_id = $_SESSION["logged_in_user_id"] ?? $user_object->getUserId($connection, $_COOKIE["email_address"]);

        $password_reset_code_database = $user_object->getUserInformation($connection, $user_id, "password_reset_code");
        $password_reset_code = (int)$_POST["password_reset_code"];

        if($password_reset_code_database === $password_reset_code) {
            $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
            
            $user_object->updatePassword($connection, $user_id, $new_password);
            $user_object->deletePasswordResetCode($connection, $user_id);

            if(isset($_COOKIE["password_reset_code_timer"])) {
                unset($_COOKIE["password_reset_code_timer"]);
                setcookie("password_reset_code_timer", "", -1, "/");
            }

            $_SESSION["green_report_message"] = "Heslo&nbsp;bolo&nbsp;zmenené";

            redirectURL("/domov");
        }

        else {
            $_SESSION["red_report_message"] = "Overovací&nbsp;kód&nbsp;sa&nbsp;nezhoduje";
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

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/forms.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
</head>

<body class="<?php echo $theme; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

    <header>
        <?php if(isset($_SESSION["red_report_message"]) and !empty($_SESSION["red_report_message"])): ?>
            <p class="red_report_message"><?php echo $_SESSION["red_report_message"]; ?></p>
            <?php $_SESSION["red_report_message"] = ""; ?>

        <?php endif; ?>
        
        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item home">
                    <a href="./domov">DOMOV</a>
                </li>

                <li class="navigation_bar_item">
                    <a href="./moj-ucet">MÔJ ÚČET</a>
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
        <section class="password_reset">
            <a href="./domov" class="back_icon" title="Späť na domovskú stránku" aria-label="Späť na domovskú stránku">
                <img src="/content/images/back.png" alt=""> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./obnova-hesla">Obnova&nbsp;hesla</a></h2>

            <?php if(isset($_COOKIE["password_reset_code_timer"])): ?>
                <script src="/src/ts/dist/pages/password_reset/components/password_reset_form.js" type="module"></script>
                
                <form class="password_reset_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                    <input type="text" name="password_reset_code" placeholder="Zadajte 6 miestny kód" maxlength="6" autocomplete="off" required>

                    <div class="new_password_container">
                        <button class="generate_password" type="button" title="Navrhnúť heslo">
                            <i class="fa-solid fa-key"></i>
                        </button>

                        <button class="show_hide_password" type="button" title="Zobraziť heslo">
                            <i class="fa-regular fa-eye-slash"></i>
                            <!-- <i class="fa-regular fa-eye"></i> -->
                        </button>
                        
                        <input class="new_password" type="text" name="new_password" placeholder="Vytvorte nové heslo" autocomplete="off" required>
                    </div>

                    <p class="form_report valid"></p>

                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
</body>
</html>