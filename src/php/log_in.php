<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/google_account_functions.php";

    session_start();

    $connection = connectionDB();

    // Theme

    $theme = "light"; // Default Theme

    if(isset($_COOKIE["theme"])) {
        $theme = $_COOKIE["theme"];
    }

    // PHP Mailer

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require "../phpmailer/vendor/autoload.php";

    // Change Password

    if(isset($_GET["password_reset"]) and $_GET["password_reset"] === "true") {
        if(getUserId($connection, $_COOKIE["email_address"])) {
            $user_id = getUserId($connection, $_COOKIE["email_address"]);

            // Generate 6-Digit Code

            $password_reset_code_array = array();

            for($i = 0; $i < 6; $i++) {
                array_push($password_reset_code_array, rand(0, 9));
            }

            $password_reset_code = (int)implode($password_reset_code_array); // Integer

            insertPasswordResetCode($connection, $user_id, $password_reset_code);

            // Send Code To User's Email

            $user_email_address = $_COOKIE["email_address"];
            $user_password_reset_code = getUserInformations($connection, $user_id, "password_reset_code");

            $mail = new PHPMailer(true);

            try {
                // Server settings

                $mail->isSMTP();
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;

                $mail->CharSet = "UTF-8";
                $mail->Encoding = "base64";

                $mail->Username = "behulpatrik@gmail.com";
                $mail->Password = "ogqxkmwxfpfwjeel"; // ogqxkmwxfpfwjeel
                $mail->SMTPSecure = "ssl";
                $mail->Port = 465;

                // Recipients

                $mail->setFrom($user_email_address, "PC Expert");
                $mail->addAddress($user_email_address);

                // Content

                $mail->isHTML(true);
                $mail->Subject = "Obnovenie hesla - " . $user_password_reset_code;
                $mail->Body = "
                    Dobrý deň, dostali sme žiadosť o obnovenie hesla k vášmu účtu. Ak ste to boli vy, použite nasledujúci odkaz a zadajte overovací kód.<br><br>
                    <a href='http://pcexpert.local/password_reset'>Obnoviť heslo</a> - <strong>$user_password_reset_code</strong><br><br>
                    Ak ste o obnovu hesla nežiadali, tento e-mail prosím ignorujte.<br>
                    Tím PCExpert.
                ";

                $mail->send();

                setcookie("password_reset_code_timer", "password_reset_code_timer", time() + (60 * 10), "/"); // 10 Minutes Timed Cookie

                $_SESSION["green_report_message"] = "Overovací&nbsp;kód&nbsp;bol&nbsp;odoslaný&nbsp;na&nbsp;adresu " . $user_email_address;

                throw new Exception();
            }

            catch(Exception $e) {
                $_SESSION["red_report_message"] = "Overovací&nbsp;kód&nbsp;sa&nbsp;nepodarilo&nbsp;odoslať";

                errorLogSave($connection, "Error While Sending Password Reset Code To User's Email. (" . $e->getMessage() . ")", "../errors/error.log");
            }
        }

        else {
            $_SESSION["red_report_message"] = "Zadaný&nbsp;e-mail&nbsp;nie&nbsp;je&nbsp;zaregistrovaný";
        }
    }

    $email_address = "";
    
    if($_SERVER["REQUEST_METHOD"] === "POST") {
        $connection = connectionDB();
    
        $email_address = $_POST["email_address"];
        $password = $_POST["password"];
        $remember_me = isset($_POST["remember_me"]) ? "yes" : "no";
    
        if(authentication($connection, $email_address, $password)) {
            $id = getUserId($connection, $email_address);
    
            session_regenerate_id(true); // Protection For Fixation Attack
    
            $_SESSION["is_logged_in"] = true;
            $_SESSION["logged_in_user_id"] = $id;
            $_SESSION["green_report_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8");

            if($remember_me === "yes") {
                $remember_me_token = bin2hex(random_bytes(30)); // Generate Random 60 Characters Long String

                setcookie("remember_me_token", $remember_me_token, time() + (60 * 60 * 24 * 30), "/"); // Create remember_me_token Cookie With $remember_me_token Value As A Plain Text

                insertRememberMeToken($connection, $id, $remember_me_token); // Insert Hashed Value Of $remember_me_token Variable To Database
            }

            else {
                // Delete remember_me_token Cookie If Is Setted

                if(isset($_COOKIE["remember_me_token"])) {
                    unset($_COOKIE["remember_me_token"]);
                    setcookie("remember_me_token", "", -1, "/");
                }
            }
    
            redirectURL("/domov");
        }
    
        else {
            $message = "Nepodarilo sa prihlásiť.";

            errorLogSave($connection, "Nepodarilo sa prihlásiť.", "../errors/error.log");
        }
    }

    // Log In With Google Account

    require "../OAuth/vendor/autoload.php";

    $client = new Google\Client;

    $client->setClientId("904461928265-a71ha9f0ijid425muufb1uutouk7os5m.apps.googleusercontent.com");
    $client->setClientSecret("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");
    $client->setRedirectUri("http://localhost/src/php/log_in.php");

    $client->addScope("email");
    $client->addScope("profile");

    $url = $client->createAuthUrl();

    // if(!isset($_GET["code"])) {
    //     echo "Prihlásenie pomocou účtu Google zlyhalo.";
    //     exit;
    // }

    if(isset($_GET["code"])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);
        $client->setAccessToken($token["access_token"]);
        $oauth = new Google\Service\Oauth2($client);

        $user_info = $oauth->userinfo->get();

        $google_id = $user_info->id;
        $first_name = $user_info->givenName;
        $last_name = $user_info->familyName;
        $email_address = $user_info->email;
        $google_profile_picture_url = $user_info->picture;

        if(!empty(getGoogleUserInformations($connection, $google_id, "google_id"))) {
            // echo "Už je zaregistrovaný.";
    
            session_regenerate_id(true); // Protection For Fixation Attack
    
            $_SESSION["is_logged_in"] = true;
            $_SESSION["logged_in_user_id"] = getGoogleUserInformations($connection, $google_id, "id");
            $_SESSION["green_report_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8");

            redirectURL("/domov");
        }
        
        else {
            createGoogleUser($connection, $first_name, $last_name, $email_address, $google_id, $google_profile_picture_url);
    
            session_regenerate_id(true); // Protection For Fixation Attack
    
            $_SESSION["is_logged_in"] = true;
            $_SESSION["logged_in_user_id"] = getGoogleUserInformations($connection, $google_id, "id");
            $_SESSION["green_report_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8");

            redirectURL("/domov");
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

    <title>PC Expert - Prihlásenie</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
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
                <li class="navigation_bar_item home">
                    <a href="./domov">DOMOV</a>
                </li>

                <li class="navigation_bar_item">
                    <a href="./registracia">REGISTRÁCIA</a>
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
        <section class="log_in">
            <a href="./domov">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png" alt="Späť na domovskú stránku"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./prihlasenie">Prihlásenie</a></h2>
            
            <form class="log_in_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                <input 
                    class="email_address"
                    type="email"
                    name="email_address"
                    placeholder="Zadajte váš e-mail"
                    autocomplete="email"
                    value="<?php echo htmlspecialchars($email_address, ENT_QUOTES, "UTF-8"); ?>"
                    required
                >

                <div class="password_container">
                    <div class="password_icon">
                        <i class="fa-regular fa-eye-slash" title="zobraziť / skryť heslo..."></i>
                        <!-- <i class="fa-regular fa-eye"></i> -->
                    </div>
                    
                    <input class="password" type="text" name="password" placeholder="Zadajte vaše heslo" autocomplete="off" required>
                </div>

                <p class="form_report_paragraph"></p>

                <article>
                    <p class="dialogue_paragraph invalid">
                        <?php if(!empty($message)): ?>
                            <?php
                                echo $message; 
                                $message = "";
                            ?>

                        <?php endif; ?>
                    </p>
                </article>

                <button type="submit">Prihlásiť sa</button>

                <div class="login_method">
                    <a href="<?php echo $url ?>">
                        <img src="/content/images/google.png" alt="Prihlásiť sa Google účtom"> <!-- https://www.flaticon.com/free-icon/search_281764 -->

                        <span>Prihlásiť&nbsp;sa&nbsp;cez&nbsp;Google</span>
                    </a>
                    
                    <a href="#">
                        <img src="/content/images/facebook_2.png" alt="Prihlásiť sa Facebook účtom"> <!-- https://www.flaticon.com/free-icon/facebook_5968764 -->

                        <span>Prihlásiť&nbsp;sa&nbsp;cez&nbsp;Facebook</span>
                    </a>
                </div>

                <div class="forgot_password">
                    <p>Zabudli ste heslo? <a href="?password_reset=true" title="Odoslať kód">Zmeniť heslo</a></p>
                </div>

                <div class="have_account">
                    <p>Ešte nemáte účet? <a href="./registracia">Registrovať</a></p>
                </div>

                <div class="agree">
                    <input type="checkbox" class="remember_me" name="remember_me" id="remember_me" value="yes" checked>
                    <label for="remember_me">Ostať prihlásený</label>
                </div>
            </form>
        </section>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>

    <script>
        // JavaScript Code

        "use strict"

        document.addEventListener("DOMContentLoaded", function(event) {
            // Toggle Show / Hide Password

            const password = document.querySelector(".password")
            const password_icon = document.querySelector(".fa-regular")

            password_icon.addEventListener("click", function(event) {
                if(password_icon.classList[1] === "fa-eye-slash") {
                    password_icon.classList.remove("fa-eye-slash")
                    password_icon.classList.add("fa-eye")

                    password.style.webkitTextSecurity = "none"
                }

                else if(password_icon.classList[1] === "fa-eye") {
                    password_icon.classList.remove("fa-eye")
                    password_icon.classList.add("fa-eye-slash")

                    password.style.webkitTextSecurity = "disc"
                }
            })

            // Password Reset

            const password_reset = document.querySelector(".forgot_password p a")
            const email_address = document.querySelector(".email_address")
            const form_report_paragraph = document.querySelector(".form_report_paragraph")

            password_reset.addEventListener("click", function(event) {
                if(email_address.value !== "" && email_address.value.includes("@") && email_address.value.includes(".")) {
                    form_report_paragraph.textContent = ""

                    document.cookie = `email_address=${email_address.value}; max-age=` + 60 * 10 + "; path=/"; // 10 Minutes Timed Cookie With User's Email Address
                }

                else {
                    event.preventDefault()

                    form_report_paragraph.textContent = "Zadajte váš e-mail."
                    form_report_paragraph.classList.add("invalid")
                }
            })
        })
    </script>

    <script src="/src/js/navigation_bar.js"></script>
    <script src="/src/js/cookie_policy.js"></script>
</body>
</html>