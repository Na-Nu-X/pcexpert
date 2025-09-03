<?php 
    // PHP Code

    require "../assets/functions.php";

    session_start();

    // Theme

    $theme = "light"; // Default Theme

    if(isset($_COOKIE["theme"])) {
        $theme = $_COOKIE["theme"];
    }

    $first_name = "";
    $last_name = "";
    $email_address = "";
    $phone_number = "";

    if($_SERVER["REQUEST_METHOD"] === "POST") {
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
                $_SESSION["log_in_successful_message"] = "Prihlásený ako " . htmlspecialchars(getUserName($connection, $id), ENT_QUOTES, "UTF-8");
        
                redirectURL("/src/php/index");
            }
        
            else {
                $message = "Nepodarilo sa registrovať.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>PC Expert - Registrácia</title>

    <link rel="stylesheet" href="/src/css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
    <header>
        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item home">
                    <a href="./index">DOMOV</a>
                </li>

                <li class="navigation_bar_item">
                    <a href="./log_in">PRIHLÁSENIE</a>
                </li>

                <div class="navigation_icon">
                    <i class="fa-solid fa-bars"></i>
                    <!-- <i class="fa-solid fa-xmark"></i> -->
                </div>
            </ul>
        </nav>
    </header>

    <main>
        <section class="registration">
            <a href="./index">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png" alt="Späť na domovskú stránku"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <h2><a href="./registration">Registrácia</a></h2>

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

                <button class="registration_form_submit" type="submit">Zaregistrovať sa</button>

                <div class="have_account">
                    <p>Už máte účet? <a href="./log_in">Prihlásiť</a></p>
                </div>

                <div class="agree">
                    <input class="terms" type="checkbox" name="terms" id="terms" required>
                    <label class="terms_label" for="terms">Súhlasím s podmienkami.</label><br>

                    <input type="checkbox" name="news" id="news" value="yes" checked>
                    <label for="news">Súhlasím so zasielaním noviniek.</label>
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

        // Phone Number Flag

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
    </script>

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>