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
        $id = $_SESSION["logged_in_user_id"];

        $image_name = $_FILES["profile_picture"]["name"];
        $image_full_path = $_FILES["profile_picture"]["full_path"];
        $image_type = $_FILES["profile_picture"]["type"];
        $image_tmp_name = $_FILES["profile_picture"]["tmp_name"];
        $image_size = $_FILES["profile_picture"]["size"];
        $image_error = $_FILES["profile_picture"]["error"];
        $new_image_name = getProfilePictureName($connection, $id);
        $delete_profile_picture = isset($_POST["delete_profile_picture"]) ? "yes" : "no";

        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $email_address = $_POST["email_address"];
        $phone_number = str_replace(" ", "", $_POST["phone_number"]);
        $current_password = $_POST["current_password"];
        $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

        $delete_account = isset($_POST["delete_account"]) ? "yes" : "no";

        if($delete_account === "yes") {
            if(passwordAuthenticationWithId($connection, $id, $current_password)) {
                if(deleteUser($connection, $id)) {
                    $old_image_upload_path = "../../content/uploads/" . $id . "/" . $new_image_name;
                    if(file_exists($old_image_upload_path)) {
                        unlink($old_image_upload_path);
                    }

                    session_destroy();

                    session_start();
                    $_SESSION["delete_account_successful_message"] = "Účet bol odstránený";

                    redirectURL("/src/php/index.php");
                    exit;
                }

                else {
                    $message = "Nepodarilo sa vymazať váš účet.";
                }
            }

            else {
                $message = "Pre vymazanie účtu zadajte heslo.";
            }
        }

        else if($delete_account === "no") {
            if($delete_profile_picture === "yes") {
                if(deleteProfilePicture($connection, $id) === true) {
                    $old_image_upload_path = "../../content/uploads/" . $id . "/" . $new_image_name;
                    if(file_exists($old_image_upload_path)) {
                        unlink($old_image_upload_path);
                    }

                    $new_image_name = "";
                }

                else {
                    $message = "Obrázok sa nepodarilo vymazať.";
                }
            }

            if(!empty(emailAuthentication($connection, $email_address)) and emailAuthentication($connection, $email_address) !== getUserInformations($connection, $id, 'email_address')) {
                $message = "Tento email už je zaregistrovaný.";
            }

            if(isset($_FILES["profile_picture"]) and !empty($image_name)) {
                if($image_error === 0) {
                    if($image_size > 8000000) { // 8MB
                        $message = "Vybraný súbor je príliš veľký.";
                    }

                    else {
                        $supported_extensions = ["png", "jpg", "jpeg"];
                        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                        if(in_array($image_extension, $supported_extensions)) {
                            $new_image_name = uniqid("IMG-", true) . "." . $image_extension;

                            if(!file_exists("../../content/uploads/" . $id)) {
                                mkdir("../../content/uploads/" . $id, 0777, true);
                            }

                            $image_upload_path = "../../content/uploads/" . $id . "/" . $new_image_name;
                            move_uploaded_file($image_tmp_name, $image_upload_path);
                        }

                        else {
                            $message = "Prípona súboru nie je podporovaná.";
                        }
                    }
                }

                else {
                    $message = "Pri nahrávaní obrázku došlo k chybe.";
                }
            }

            if(empty($message)) {
                if(updateUser($connection, $id, $first_name, $last_name, $email_address, $phone_number, $new_image_name) === true) {
                    $_SESSION["edit_account_successful_message"] = "Zmeny boli uložené";
                } 
                
                else {
                    $message = "Pri úprave vášho účtu došlo k chybe.";
                }

                if(!empty($current_password) and !empty($_POST["new_password"])) {
                    if(passwordAuthenticationWithId($connection, $id, $current_password)) {
                        updatePassword($connection, $id, $new_password);
                    } 
                    
                    else {
                        $message = "Zadali ste nesprávne aktuálne heslo";
                    }
                }

                if(empty($message)) {
                    redirectURL("/src/php/index.php");
                    exit;
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Profilový obrázok</title>

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
        <section class="edit_account">
            <a href="./index.php">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>

            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                <h2><a href="./edit_account.php">Môj&nbsp;účet</a></h2>

                <form class="edit_account_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="profile_picture_container">
                        <h3>Profilový obrázok</h3>

                        <?php if(!empty(getProfilePictureName($connection, $_SESSION["logged_in_user_id"]))): ?>
                            <label for="profile_picture">
                                <img class="profile_picture" title="Zmeniť profilový obrázok" src="/content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>">
                            </label>

                            <input id="profile_picture" type="file" name="profile_picture">

                            <label for="delete_profile_picture">
                                <img class="delete_profile_picture" title="Odstrániť profilový obrázok" src="/content/images/trash.png"> <!-- https://www.flaticon.com/free-icon/trash_8848798 -->
                            </label>
                            
                            <input type="checkbox" name="delete_profile_picture" id="delete_profile_picture" value="yes">

                        <?php else: ?>
                            <label for="profile_picture">
                                <img class="profile_picture" src="/content/images/user.png"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->
                            </label>
                            
                            <input id="profile_picture" type="file" name="profile_picture">

                        <?php endif; ?>
                    </div>

                    <h3>Osobné údaje</h3>

                    <div class="full_name_container">
                        <!-- <h3>Meno</h3> -->

                        <input
                            type="text"
                            name="first_name"
                            placeholder="Zadajte vaše meno"
                            autocomplete="given-name"
                            value="<?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'first_name'), ENT_QUOTES, 'UTF-8'); ?>"
                        >

                        <input
                            type="text"
                            name="last_name"
                            placeholder="Zadajte vaše priezvisko"
                            autocomplete="family-name"
                            value="<?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'last_name'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="email_address_container">
                        <!-- <h3>E-mailová adresa</h3> -->

                        <input
                            type="email"
                            name="email_address"
                            placeholder="Zadajte váš e-mail"
                            autocomplete="email"
                            value="<?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'email_address'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="phone_number_container">
                        <!-- <h3>Telefónne číslo</h3> -->

                        <input
                            type="text"
                            name="phone_number"
                            placeholder="Zadajte vaše telefónne číslo"
                            autocomplete="tel"
                            value="<?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'phone_number'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="current_password_container">
                        <input class="current_password" type="text" name="current_password" placeholder="Zadajte vaše heslo" autocomplete="off">
                    </div>

                    <div class="new_password_container">
                        <div class="generate_password">
                            <i class="fa-solid fa-key" title="vygenerovať heslo..."></i>
                        </div>

                        <div class="password_icon">
                            <i class="fa-regular fa-eye-slash" title="zobraziť / skryť heslo..."></i>
                            <!-- <i class="fa-regular fa-eye"></i> -->
                        </div>

                        <input class="new_password password" type="text" name="new_password" placeholder="Vytvorte nové heslo" autocomplete="off">
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

                    <button type="submit" name="submit">Uložiť zmeny</button>

                    <div class="forgot_password">
                        <p>Zabudli ste heslo? <a href="#">Zmeniť heslo</a></p>
                    </div>

                    <div class="delete_account_container">
                        <p>Chcete nenávratne odstrániť váš účet?</p>
                        <label for="delete_account">
                            <img class="delete_account" title="Odstrániť účet" src="/content/images/delete_account.png"> <!-- https://www.flaticon.com/free-icon/delete_11502978 -->
                        </label>

                        <input type="checkbox" name="delete_account" id="delete_account" value="yes">
                    </div>
                </form>

            <?php else: ?>
                <div class="no_logged_in">
                    <h2><a href="./edit_account.php">Môj&nbsp;účet</a></h2>

                    <p>Pre zobrazenie podrobností o vašom účte sa musíte najprv <a href="./log_in.php" title="Prihlásiť sa">prihlásiť</a> alebo <a href="./registration.php" title="Vytvoriť účet">zaregistrovať.</a></p>
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

        // Delete Account Warning

        const delete_account_input = document.querySelector("#delete_account")
        const delete_account_image = document.querySelector(".delete_account")
        const form_report_paragraph = document.querySelector(".form_report_paragraph")
        const dialogue_paragraph = document.querySelector(".dialogue_paragraph")

        delete_account_input.addEventListener("click", function(event) {
            if(delete_account_input.checked) {
                delete_account_image.style.opacity = 1

                form_report_paragraph.textContent = "Váš účet bude odstránený"
                form_report_paragraph.classList.add("invalid")
            }

            else {
                delete_account_image.style.opacity = 0.8

                form_report_paragraph.textContent = ""
            }
        })

        // Delete Profile Picture Warning

        const delete_profile_picture_input = document.querySelector("#delete_profile_picture")
        const delete_profile_picture_image = document.querySelector(".delete_profile_picture")

        delete_profile_picture_input.addEventListener("click", function(event) {
            if(delete_profile_picture_input.checked) {
                delete_profile_picture_image.style.opacity = 1
            }

            else {
                delete_profile_picture_image.style.opacity = 0.8
            }
        })

        // Random Password Generator

        const password = document.querySelector(".password")
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
    </script>

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>