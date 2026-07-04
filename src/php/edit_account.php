<?php
    // PHP Code

    require "../assets/functions.php";
    
    session_start();

    // CSRF Token

    if(!isset($_SESSION["csrf_token"])) {
        $csrf_token = bin2hex(random_bytes(30)); // Generate Random 60 Characters Long String
        $_SESSION["csrf_token"] = $csrf_token;
    }
    
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
        // Generate 6-Digit Code

        $password_reset_code_array = array();

        for($i = 0; $i < 6; $i++) {
            array_push($password_reset_code_array, rand(0, 9));
        }

        $password_reset_code = (int)implode($password_reset_code_array); // Integer

        insertPasswordResetCode($connection, $_SESSION["logged_in_user_id"], $password_reset_code);

        // Send Code To User's Email

        $user_email_address = getUserInformations($connection, $_SESSION["logged_in_user_id"], "email_address");
        $user_password_reset_code = getUserInformations($connection, $_SESSION["logged_in_user_id"], "password_reset_code");

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
        }

        catch(Exception $e) {
            $_SESSION["red_report_message"] = "Overovací&nbsp;kód&nbsp;sa&nbsp;nepodarilo&nbsp;odoslať";

            errorLogSave($connection, "Error While Sending Password Reset Code To User's Email. (" . $e->getMessage() . ")", "../errors/error.log");
        }
    }

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        $current_time = time();
        $last_edit = strtotime(getUserInformations($connection, $_SESSION["logged_in_user_id"], "last_edit"));
        $edit_cooldown = 60 * 60 * 24 * 30; // 30 Days
        $next_edit = date("j.n.Y", $last_edit + $edit_cooldown);

        if($current_time - $last_edit >= $edit_cooldown) {
            if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                errorLogSave($connection, "CSRF Token Verification Failed While Editing An Account.", "../errors/error.log");
                die("CSRF Token nie je platný.");
            }

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
                        $_SESSION["red_report_message"] = "Účet&nbsp;bol&nbsp;odstránený";

                        redirectURL("/domov");
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
                        $_SESSION["green_report_message"] = "Zmeny&nbsp;boli&nbsp;uložené";
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
                        redirectURL("/domov");
                    }
                }
            }
        }

        else {
            $message = "Ďalšie úpravy budú možné ${next_edit}";
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

    <title>PC Expert - Môj účet</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/forms.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
    <script src="/src/ts/dist/pages/edit_account/components/edit_account_form.js" type="module"></script>
</head>

<body class="<?php echo $theme; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

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
        <section class="edit_account">
            <a href="./domov">
                <img class="back_icon" title="Späť na domovskú stránku" src="/content/images/back.png" alt="Späť na domovskú stránku"> <!-- https://www.flaticon.com/free-icon/back_2099190 -->
            </a>
            
            <h2><a href="./moj-ucet">Môj&nbsp;účet</a></h2>

            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                <form class="edit_account_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="profile_picture_container">
                        <h3>Profilový obrázok</h3>

                        <?php if(!empty(getProfilePictureName($connection, $_SESSION["logged_in_user_id"])) or !empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'))): ?>
                            <label for="profile_picture">
                                <img 
                                    class="profile_picture" 
                                    title="Zmeniť profilový obrázok" 
                                    src="
                                        <?php if(!empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url')) and empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'))): ?>
                                            <?php echo getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'); ?>

                                        <?php else: ?>
                                            /content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>

                                        <?php endif; ?>
                                    "
                                    alt="Zmeniť profilový obrázok"
                                >

                                <?php if(!empty(getUserInformations($connection, $_SESSION["logged_in_user_id"], "google_id"))): ?>
                                    <img class="google_profile_picture_frame" src="/content/images/google.png" alt="Google"> <!-- https://www.flaticon.com/free-icon/search_281764 -->

                                <?php endif; ?>
                            </label>

                            <input id="profile_picture" type="file" name="profile_picture" accept=".jpg, .jpeg, .png, .gif, .webp, .bmp, .tiff, .tif">

                            <label for="delete_profile_picture">
                                <img class="delete_profile_picture" title="Odstrániť profilový obrázok" src="/content/images/trash.png" alt="Odstrániť profilový obrázok"> <!-- https://www.flaticon.com/free-icon/trash_8848798 -->
                            </label>
                            
                            <input type="checkbox" name="delete_profile_picture" id="delete_profile_picture" value="yes">

                        <?php else: ?>
                            <label for="profile_picture">
                                <img class="profile_picture" title="Zmeniť profilový obrázok" src="/content/images/user.png" alt="Zmeniť profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->
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

                    <?php if(empty(getUserInformations($connection, $_SESSION["logged_in_user_id"], "google_id"))): ?>
                        <div class="current_password_container">
                            <input class="current_password" type="text" name="current_password" placeholder="Zadajte vaše heslo" autocomplete="off">
                        </div>

                        <div class="new_password_container">
                            <button class="generate_password" type="button" title="Navrhnúť heslo">
                                <i class="fa-solid fa-key"></i>
                            </button>

                            <button class="show_hide_password" type="button" title="Zobraziť heslo">
                                <i class="fa-regular fa-eye-slash"></i>
                                <!-- <i class="fa-regular fa-eye"></i> -->
                            </button>

                            <input class="new_password password" type="text" name="new_password" placeholder="Vytvorte nové heslo" autocomplete="off">
                        </div>

                    <?php endif; ?>

                    <p class="form_report"></p>

                    <article>
                        <p class="dialogue_paragraph invalid" style="top: 470px;">
                            <?php if(!empty($message)): ?>
                                <?php
                                    echo $message; 
                                    $message = "";
                                ?>

                            <?php endif; ?>
                        </p>
                    </article>

                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <button type="submit" name="submit">Uložiť zmeny</button>

                    <div class="forgot_password">
                        <p>Zabudli ste heslo? <a href="?password_reset=true" title="Odoslať kód">Zmeniť heslo</a></p>
                    </div>

                    <div class="delete_account_container">
                        <p>Chcete nenávratne odstrániť váš účet?</p>
                        
                        <label for="delete_account">
                            <img class="delete_account" title="Odstrániť účet" src="/content/images/delete_account.png" alt="Odstrániť účet"> <!-- https://www.flaticon.com/free-icon/delete_11502978 -->
                        </label>

                        <input type="checkbox" name="delete_account" id="delete_account" value="yes">
                    </div>
                </form>

            <?php else: ?>
                <div class="no_logged_in">
                    <p>Pre zobrazenie podrobností o vašom účte sa musíte najprv <a href="./prihlasenie" title="Prihlásiť sa">prihlásiť</a> alebo <a href="./registracia" title="Vytvoriť účet">zaregistrovať.</a></p>
                </div>

            <?php endif; ?>
        </section>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>