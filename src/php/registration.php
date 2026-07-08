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

    $first_name = "";
    $last_name = "";
    $email_address = "";
    $phone_number = "";

    // Google reCAPTCHA

    $secret_key = "6LfbXMYrAAAAAM8u-89OhIKngVukDKxWDTOWWnrk";
    $token = $_POST["g-recaptcha-response"] ?? "";

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        if(empty($token)) {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "reCAPTCHA Token Not Found In Registration Form.", "../errors/error.log");
            exit;
        }

        $api_url = "https://www.google.com/recaptcha/api/siteverify";
        $postData = [
            "secret"   => $secret_key,
            "response" => $token,
            "remoteip" => $_SERVER["REMOTE_ADDR"] ?? ""
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);

        if($response === false) {
            $curlErr = curl_error($ch);
            curl_close($ch);

            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "reCAPTCHA cURL Error In Registration Form. (" . htmlspecialchars($curlErr) . ")", "../errors/error.log");
            exit;
        }

        curl_close($ch);

        $responseData = json_decode($response);

        if($responseData === null) {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "Invalid reCAPTCHA Server Response In Registration Form. (" . htmlspecialchars($response) . ")", "../errors/error.log");
            exit;
        }

        // reCAPTCHA V3

        $minScore = 0.5;
        if(!empty($responseData->success) and isset($responseData->score) and $responseData->score >= $minScore and isset($responseData->action) and $responseData->action === 'submit') {
            // Registration Form

            if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                errorLogSave($connection, "CSRF Token Verification Failed While Creating An Account.", "../errors/error.log");
                die("CSRF Token nie je platný.");
            }
    
            $first_name = $_POST["first_name"];
            $last_name = $_POST["last_name"];
            $email_address = $_POST["email_address"];
            $phone_number = str_replace(" ", "", $_POST["phone_number"]);
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $news = isset($_POST["news"]) ? "yes" : "no";

            if(!empty($user_object->emailAuthentication($connection, $email_address))) {
                $message = "Tento email už je zaregistrovaný.";
            }

            else {
                $id = $user_object->createUser($connection, $first_name, $last_name, $email_address, $phone_number, $password, $news);
        
                if(!empty($id)) {
                    session_regenerate_id(true); // Protection For Fixation Attack
            
                    $_SESSION["is_logged_in"] = true;
                    $_SESSION["logged_in_user_id"] = $id;
                    $_SESSION["green_report_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars($user_object->getUserInformation($connection, $id, "first_name"), ENT_QUOTES, "UTF-8") . " " . htmlspecialchars($user_object->getUserInformation($connection, $id, "last_name"), ENT_QUOTES, "UTF-8");
            
                    redirectURL("/domov");
                }
            
                else {
                    $message = "Nepodarilo sa registrovať.";
                }
            }
        }
        
        else {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "reCAPTCHA Failed In Registration Form. (" . htmlspecialchars($response) . ")", "../errors/error.log");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-L62G1B08VS"></script>

    <script>
        window.dataLayer = window.dataLayer || []
        function gtag(){dataLayer.push(arguments)}
        gtag('js', new Date())

        gtag('config', 'G-L62G1B08VS')
    </script>

    <!-- reCAPTCHA V3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LfbXMYrAAAAAJfSCq3aCjeiFxnhly7K2sgFjiEz&badge=inline"></script>
    
    <script>
        grecaptcha.ready(function() {
            const grecaptcha_badge = document.querySelector(".grecaptcha-badge")
            const grecaptcha_badge_container = document.querySelector(".grecaptcha_badge_container")

            grecaptcha_badge_container.appendChild(grecaptcha_badge)
            grecaptcha_badge.style.margin = "35px auto 0px"
            grecaptcha_badge.style.background = "#1e1e1e"
            grecaptcha_badge.style.opacity = "0.8"

            document.querySelector(".registration_form").addEventListener("submit", function(event) {
                event.preventDefault()

                grecaptcha.execute("6LfbXMYrAAAAAJfSCq3aCjeiFxnhly7K2sgFjiEz", {action: "submit"}).then(function(token) {
                    document.querySelector("#g-recaptcha-response").value = token
                    event.target.submit()
                })
            })
        })
    </script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>PC Expert - Registrácia</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/forms.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
    <script src="/src/ts/dist/pages/registration/components/registration_form.js" type="module"></script>
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
                    <a href="./prihlasenie">PRIHLÁSENIE</a>
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
        <section class="registration">
            <a href="./domov" class="back_icon" title="Späť na domovskú stránku" aria-label="Späť na domovskú stránku">
                <img src="/content/images/back.png" alt=""> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./registracia">Registrácia</a></h2>

            <form class="registration_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
                <input 
                    type="text"
                    name="first_name"
                    placeholder="Zadajte vaše meno"
                    autocomplete="given-name"
                    value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, "UTF-8"); ?>"
                    required
                >

                <input 
                    type="text"
                    name="last_name"
                    placeholder="Zadajte vaše priezvisko"
                    autocomplete="family-name"
                    value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, "UTF-8"); ?>"
                    required
                >

                <input 
                    type="email"
                    name="email_address"
                    placeholder="Zadajte váš e-mail"
                    autocomplete="email"
                    value="<?php echo htmlspecialchars($email_address, ENT_QUOTES, "UTF-8"); ?>"
                    required
                >

                <div class="phone_number_container">
                    <div class="phone_number_icon">
                        <img class="slovakia_icon" src="/content/images/slovakia.png" alt="SK"> <!-- https://www.flaticon.com/free-icon/slovakia_330594 -->
                        <img class="czech_republic_icon" src="/content/images/czech-republic.png" alt="CZ"> <!-- https://www.flaticon.com/free-icon/czech-republic_330536 -->
                        <img class="spain_icon" src="/content/images/spain.png" alt="ES"> <!-- https://www.flaticon.com/free-icon/spain_330557 -->
                        <img class="united_states_icon" src="/content/images/united-states.png" alt="US"> <!-- https://www.flaticon.com/free-icon/united-states_3013911 -->
                    </div>

                    <input 
                        class="phone_number"
                        type="text"
                        name="phone_number"
                        placeholder="Zadajte vaše telefónne číslo"
                        autocomplete="tel"
                        value="<?php echo htmlspecialchars($phone_number, ENT_QUOTES, "UTF-8"); ?>"
                        required
                    >
                </div>
                
                <div class="password_container">
                    <button class="generate_password" type="button" title="Navrhnúť heslo">
                        <i class="fa-solid fa-key"></i>
                    </button>

                    <button class="show_hide_password" type="button" title="Zobraziť heslo">
                        <i class="fa-regular fa-eye-slash"></i>
                        <!-- <i class="fa-regular fa-eye"></i> -->
                    </button>
                    
                    <button class="copy_password" type="button" title="Kopírovať">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                    
                    <input class="password" type="text" name="password" placeholder="Vytvorte heslo" autocomplete="off" required>
                </div>

                <div class="password_check_container">
                    <button class="paste_password" type="button" title="Prilepiť">
                        <i class="fa-regular fa-copy"></i>
                    </button>

                    <input class="password_check" type="text" placeholder="Overte heslo" autocomplete="off" required>
                </div>

                <p class="form_report valid" style="top: 362px;"></p>

                <article>
                    <p class="dialogue_paragraph invalid" style="top: 362px;">
                        <?php if(!empty($message)): ?>
                            <?php
                                echo $message; 
                                $message = "";
                            ?>

                        <?php endif; ?>
                    </p>
                </article>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <button class="registration_form_submit" type="submit">Zaregistrovať sa</button>

                <div class="have_account">
                    <p>Už máte účet? <a href="./prihlasenie">Prihlásiť</a></p>
                </div>

                <div class="agree">
                    <input class="terms" type="checkbox" name="terms" id="terms" required>
                    <label class="terms_label" for="terms" tabindex="0">Súhlasím s podmienkami.</label><br>

                    <input class="news" type="checkbox" name="news" id="news" value="yes" checked>
                    <label class="news_label" for="news" tabindex="0">Súhlasím so zasielaním noviniek.</label>
                </div>

                <div class="grecaptcha_badge_container"></div>
            </form>
        </section>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>