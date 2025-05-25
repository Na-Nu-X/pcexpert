<?php
    // PHP Code

    require "../assets/functions.php";

    session_start();

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
            $_SESSION["log_in_successful_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8");

            if($remember_me === "yes") {
                
            }

            else {
                
            }
    
            redirectURL("/src/html/index.php");
        }
    
        else {
            $message = "Nepodarilo sa prihlásiť.";
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Prihlásenie</title>

    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>
<body>
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
        <section class="log_in">
            <h2>Prihlásenie</h2>
            
            <form class="log_in_form" action="log_in.php" method="POST">
                <input type="email" name="email_address" placeholder="Zadajte váš e-mail" autocomplete="email" value="<?php echo htmlspecialchars($email_address, ENT_QUOTES, "UTF-8"); ?>" required>

                <div class="password_container">
                    <div class="password_icon">
                        <i class="fa-regular fa-eye-slash"></i>
                        <!-- <i class="fa-regular fa-eye"></i> -->
                    </div>
                    
                    <input class="password" type="text" name="password" placeholder="Zadajte vaše heslo" autocomplete="off" required>
                </div>

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

                <div class="forgot_password">
                    <p>Zabudli ste heslo? <a href="#">Zmeniť heslo</a></p>
                </div>

                <div class="have_account">
                    <p>Ešte nemáte účet? <a href="./registration.php">Registrovať</a></p>
                </div>

                <div class="agree">
                    <input type="checkbox" class="remember_me" name="remember_me" id="remember_me" value="yes" checked>
                    <label for="remember_me">Ostať prihlásený</label>
                </div>

                <div class="login_method">
                    <img src="/content/images/google.png" title="Prihlásiť sa Google účtom"> <!-- https://www.flaticon.com/free-icon/search_281764 -->
                    <img src="/content/images/facebook_2.png" title="Prihlásiť sa Facebook účtom"> <!-- https://www.flaticon.com/free-icon/facebook_5968764 -->
                </div>
            </form>
        </section>
    </main>

    <footer>
        <?php require "../assets/footer.php"; ?>
    </footer>

    <!-- JavaScript Code -->

    <script>
        "use strict"

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
    </script>

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>