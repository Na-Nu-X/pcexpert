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

    $user_id = $_SESSION["logged_in_user_id"] ?? getUserId($connection, $_COOKIE["email_address"]);

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        $password_reset_code_database = getUserInformations($connection, $user_id, "password_reset_code");
        $password_reset_code = (int)$_POST["password_reset_code"];

        if($password_reset_code_database === $password_reset_code) {
            $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

            updatePassword($connection, $user_id, $new_password);

            deletePasswordResetCode($connection, $user_id);

            if(isset($_COOKIE["password_reset_code_timer"])) {
                unset($_COOKIE["password_reset_code_timer"]);
                setcookie("password_reset_code_timer", "", -1, "/");
            }

            $_SESSION["green_report_message"] = "Heslo&nbsp;bolo&nbsp;zmenené";

            redirectURL("/index");
            exit;
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

                    <div class="new_password_container">
                        <div class="generate_password">
                            <i class="fa-solid fa-key" title="vygenerovať heslo..."></i>
                        </div>

                        <div class="password_icon">
                            <i class="fa-regular fa-eye-slash" title="zobraziť / skryť heslo..."></i>
                            <!-- <i class="fa-regular fa-eye"></i> -->
                        </div>
                        
                        <input class="new_password" type="text" name="new_password" placeholder="Vytvorte nové heslo" autocomplete="off" required>
                    </div>

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

        document.addEventListener("DOMContentLoaded", function(event) {
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

                new_password.value = shuffleString(generated_password)
            })

            // Toggle Show / Hide Password

            const password_icon = document.querySelector(".fa-regular")

            password_icon.addEventListener("click", function(event) {
                if(password_icon.classList[1] === "fa-eye-slash") {
                    password_icon.classList.remove("fa-eye-slash")
                    password_icon.classList.add("fa-eye")

                    new_password.style.webkitTextSecurity = "none"
                }

                else if(password_icon.classList[1] === "fa-eye") {
                    password_icon.classList.remove("fa-eye")
                    password_icon.classList.add("fa-eye-slash")

                    new_password.style.webkitTextSecurity = "disc"
                }
            })
        })
    </script>

    <script src="/src/js/navigation_bar.js"></script>
    <script src="/src/js/cookie_policy.js"></script>
</body>
</html>