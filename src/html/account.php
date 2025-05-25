<?php 
    // PHP Code

    require "../assets/functions.php";

    session_start();

    $connection = connectionDB();

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        $current_password = $_POST["current_password"];
        $new_password = $_POST["new_password"];
        $new_password_check = $_POST["new_password_check"];

        // echo "$current_password <br> $new_password <br> $new_password_check";


        // NEFUNGUJE TO ESTE
        if(passwordAuthentication($connection, $current_password) === $current_password) {
            echo "heslo sa zhoduje";
        }

        else {
            echo "nezhoda";
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        PC Expert - 
        
        <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
            <?php echo htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8"); ?>

        <?php else: ?>
            <?php echo "Môj účet"; ?>

        <?php endif; ?>
    </title>

    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body>
    <header>

    </header>

    <main>
        <section class="account">
            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                <h2><?php echo htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8"); ?></h2>

                <p><span style="font-weight: bold;">e-mail: </span><?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "email_address"), ENT_QUOTES, "UTF-8"); ?></p>

                <p><span style="font-weight: bold;">tel.č.: </span><?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "phone_number"), ENT_QUOTES, "UTF-8"); ?></p>

                <p><a class="change_password" href="#">Zmeniť heslo</a></p>

                <!-- <div class="change_password_dialogue"></div> -->

                <!-- <form class="change_password_form" action="" method="POST">
                    <input type="text" name="current_password" placeholder="Zadajte vaše heslo" autocomplete="off" required>

                    <input type="text" name="new_password" placeholder="Vytvorte heslo" autocomplete="off" required>

                    <input type="text" name="new_password_check" placeholder="Overte heslo" autocomplete="off" required>

                    <input type="submit" value="Zmeniť heslo">
                </form> -->

                <p><a class="delete_account" href="#">Vymazať účet</a></p>
                <div class="delete_account_dialogue_container">
                    <div class="delete_account_dialogue"></div>
                </div>
                
            <?php else: ?>
                <h2>Môj účet</h2>
                
                <p>Pre zobrazenie podrobností o vašom účte sa musíte najprv <a href="./log_in.php" title="Prihlásiť sa">prihlásiť</a> alebo <a href="./registration.php" title="Vytvoriť účet">zaregistrovať.</a></p>

            <?php endif; ?>
        </section>
    </main>

    <footer>

    </footer>
    
    <script>
        const delete_account = document.querySelector(".delete_account")
        const delete_account_dialogue = document.querySelector(".delete_account_dialogue")

        delete_account.addEventListener("click", function(event) {
            delete_account_dialogue.innerHTML = ""

            const paragraph = document.createElement("p")
            paragraph.textContent = "Naozaj chcete vymazať váš účet?"
            delete_account_dialogue.appendChild(paragraph)

            const yes_button = document.createElement("a")
            yes_button.textContent = "áno"
            yes_button.setAttribute("href", "./delete_account.php")
            delete_account_dialogue.appendChild(yes_button)

            const no_button = document.createElement("a")
            no_button.textContent = "nie"
            no_button.setAttribute("href", "#")
            delete_account_dialogue.appendChild(no_button)

            no_button.addEventListener("click", function(event) {
                delete_account_dialogue.innerHTML = ""
            })
        })
    </script>
</body>
</html>