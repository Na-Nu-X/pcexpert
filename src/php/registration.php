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

            $connection = connectionDB();
    
            $first_name = $_POST["first_name"];
            $last_name = $_POST["last_name"];
            $email_address = $_POST["email_address"];
            $phone_number = str_replace(" ", "", $_POST["phone_number"]);
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $news = isset($_POST["news"]) ? "yes" : "no";
            $role = "user";

            if(!empty(emailAuthentication($connection, $email_address))) {
                $message = "Tento email už je zaregistrovaný.";
            }

            else {
                $id = createUser($connection, $first_name, $last_name, $email_address, $phone_number, $password, $news, $role);
        
                if(!empty($id)) {
                    session_regenerate_id(true); // Protection For Fixation Attack
            
                    $_SESSION["is_logged_in"] = true;
                    $_SESSION["logged_in_user_id"] = $id;
                    $_SESSION["green_report_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars(getUserName($connection, $id), ENT_QUOTES, "UTF-8");
            
                    redirectURL("/domov");
                }
            
                else {
                    $message = "Nepodarilo sa registrovať.";
                }
            }

            exit;
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

    <link rel="stylesheet" href="/src/css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
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
        <section class="registration">
            <a href="./domov">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png" alt="Späť na domovskú stránku"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
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
                    <div class="generate_password">
                        <i class="fa-solid fa-key" title="vygenerovať heslo..."></i>
                    </div>

                    <div class="password_icon">
                        <i class="fa-regular fa-eye-slash" title="zobraziť / skryť heslo..."></i>
                        <!-- <i class="fa-regular fa-eye"></i> -->
                    </div>
                    
                    <div class="copy_password">
                        <i class="fa-solid fa-copy copy_password_icon" title="kopírovať heslo..."></i>
                    </div>
                    
                    <input class="password" type="text" name="password" placeholder="Vytvorte heslo" autocomplete="off" required>
                </div>

                <div class="password_check_container">
                    <div class="paste_password">
                        <i class="fa-regular fa-copy paste_password_icon" title="prilepiť..."></i>
                    </div>

                    <input class="password_check" type="text" placeholder="Overte heslo" autocomplete="off" required>
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

                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <button class="registration_form_submit" type="submit">Zaregistrovať sa</button>

                <div class="have_account">
                    <p>Už máte účet? <a href="./prihlasenie">Prihlásiť</a></p>
                </div>

                <div class="agree">
                    <input class="terms" type="checkbox" name="terms" id="terms" required>
                    <label class="terms_label" for="terms">Súhlasím s podmienkami.</label><br>

                    <input type="checkbox" name="news" id="news" value="yes" checked>
                    <label for="news">Súhlasím so zasielaním noviniek.</label>
                </div>

                <div class="grecaptcha_badge_container"></div>
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
            // Phone Number Country Flag

            const phone_number = document.querySelector(".phone_number")
            const slovakia_icon = document.querySelector(".slovakia_icon")
            const czech_republic_icon = document.querySelector(".czech_republic_icon")
            const spain_icon = document.querySelector(".spain_icon")
            const united_states_icon = document.querySelector(".united_states_icon")

            phone_number.addEventListener("input", function(event) {
                if(event.target.value.includes("+421") || event.target.value.includes("09")) {
                    // console.log("Slovakia")
                    slovakia_icon.style.visibility = "visible"
                }

                else if(event.target.value.includes("+420")) {
                    // console.log("Czech Republic")
                    czech_republic_icon.style.visibility = "visible"
                }

                else if(event.target.value.includes("+34")) {
                    // console.log("Spain")
                    spain_icon.style.visibility = "visible"
                }

                else if(event.target.value.includes("+1")) {
                    // console.log("USA")
                    united_states_icon.style.visibility = "visible"
                }

                else {
                    slovakia_icon.style.visibility = "hidden"
                    czech_republic_icon.style.visibility = "hidden"
                    spain_icon.style.visibility = "hidden"
                    united_states_icon.style.visibility = "hidden"
                }
            })

            // Password Verification

            const password = document.querySelector(".password")
            const password_check = document.querySelector(".password_check")
            const form_report_paragraph = document.querySelector(".form_report_paragraph")
            const dialogue_paragraph = document.querySelector(".dialogue_paragraph")

            password.addEventListener("input", function(event){
                if(password.value.length < 8) {
                    form_report_paragraph.textContent = "Heslo je príliš krátke."
                    form_report_paragraph.classList.add("invalid")
                    form_report_paragraph.classList.remove("valid")
                    dialogue_paragraph.textContent = ""
                }

                else {
                    form_report_paragraph.textContent = ""
                }
            })

            password_check.addEventListener("input", function(event){
                if(password.value === password_check.value) {
                    form_report_paragraph.textContent = "Heslá sa zhodujú."
                    form_report_paragraph.classList.add("valid")
                    form_report_paragraph.classList.remove("invalid")
                    dialogue_paragraph.textContent = ""
                }

                else {
                    form_report_paragraph.textContent = "Heslá sa nezhodujú."
                    form_report_paragraph.classList.add("invalid")
                    form_report_paragraph.classList.remove("valid")
                    dialogue_paragraph.textContent = ""
                }


                if (password.value === "" && password_check.value === "") {
                    form_report_paragraph.textContent = ""
                }
            })
            
            // Random Password Generator

            const generate_password = document.querySelector(".fa-key")

            const alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"]
            const numbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
            const special_chars = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '[', ']', '{', '}', ';', ':', "'", '"', ',', '.', '<', '>', '/', '?', '\\', '|', '`', '~']

            generate_password.addEventListener("click", function(event) {
                function shuffleString(string) {
                    let array = string.split("")

                    for(let i = array.length - 1; i > 0; i--) {
                        let j = Math.floor(Math.random() * (i + 1));
                        [array[i], array[j]] = [array[j], array[i]]
                    }

                    return array.join("");
                }

                let generated_password = ""

                for(let x = 0; x < 5; x++) {
                    generated_password = generated_password + alphabet[Math.floor(Math.random() * alphabet.length)] + numbers[Math.floor(Math.random() * numbers.length)] + special_chars[Math.floor(Math.random() * special_chars.length)]
                }

                password.value = shuffleString(generated_password)
            })

            // Toggle Show / Hide Password

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

            // Copy Password To Clipboard

            const copy_password = document.querySelector(".copy_password_icon")

            copy_password.addEventListener("click", function(event) {
                password.select()
                password.setSelectionRange(0, password.value.length)
                document.execCommand("copy")
                // navigator.clipboard.writeText(password.value) // Only HTTPS
            })

            // Paste From Clipboard

            const paste_password = document.querySelector(".paste_password_icon")

            paste_password.addEventListener("click", function(event) {
                // password_check.value = navigator.clipboard.readText() // Only HTTPS
                password_check.value = password.value
            })

            // Terms Check

            const registration_form_submit = document.querySelector(".registration_form_submit")
            const terms = document.querySelector(".terms")
            const terms_label = document.querySelector(".terms_label")

            registration_form_submit.addEventListener("click", function(event) {
                if(!terms.checked) {
                    terms_label.style.color = "rgb(250, 50, 50)"
                }
            })

            terms.addEventListener("input", function(event) {
                if(terms.checked) {
                    terms_label.style.color = "rgb(180, 180, 180)"
                }
            })
        })
    </script>

    <script src="/src/js/navigation_bar.js"></script>
    <script src="/src/js/cookie_policy.js"></script>
</body>
</html>