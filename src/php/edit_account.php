<?php
    // Debug
        
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    // PHP Code

    require_once "../../vendor/autoload.php";

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

    // PHP Mailer

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Change Password

    if(isset($_GET["password_reset"]) and $_GET["password_reset"] === "true") {
        // Generate 6-Digit Code

        $password_reset_code_array = array();

        for($i = 0; $i < 6; $i++) {
            array_push($password_reset_code_array, rand(0, 9));
        }

        $password_reset_code = (int)implode($password_reset_code_array); // Integer

        $user_object->setPasswordResetCode($connection, $_SESSION["logged_in_user_id"], $password_reset_code);

        // Send Code To User's Email

        $user_email_address = $user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "email_address");
        $user_password_reset_code = $user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "password_reset_code");

        $mail = new PHPMailer(true);

        try {
            // Server settings

            $use_tls = $_ENV["EMAIL_USE_TLS"] ?? "False";

            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;

            $mail->CharSet = "UTF-8";
            $mail->Encoding = "base64";

            $mail->Username = $_ENV["EMAIL_HOST_USER"];
            $mail->Password = $_ENV["EMAIL_HOST_PASSWORD"];
            if(strtolower($use_tls) === "true") $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            else $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $_ENV["EMAIL_PORT"];

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
        $last_edit = strtotime($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "last_edit"));
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
            $new_image_name = $user_object->getUserInformation($connection, $id, "profile_picture_name");
            $delete_profile_picture = isset($_POST["delete_profile_picture"]) ? "yes" : "no";

            $first_name = $_POST["first_name"];
            $last_name = $_POST["last_name"];
            $email_address = $_POST["email_address"];
            $phone_number = str_replace(" ", "", $_POST["phone_number"]);
            $current_password = $_POST["current_password"];
            $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

            $delete_account = isset($_POST["delete_account"]) ? "yes" : "no";

            if($delete_account === "yes") {
                if($user_object->passwordAuthenticationById($connection, $id, $current_password)) {
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
                    if($user_object->deleteProfilePicture($connection, $id) === true) {
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

                if(!empty($user_object->emailAuthentication($connection, $email_address)) and $user_object->emailAuthentication($connection, $email_address) !== $user_object->getUserInformation($connection, $id, 'email_address')) {
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
                    if($user_object->updateUser($connection, $id, $first_name, $last_name, $email_address, $phone_number, $new_image_name) === true) {
                        $_SESSION["green_report_message"] = "Zmeny&nbsp;boli&nbsp;uložené";
                    } 
                    
                    else {
                        $message = "Pri úprave vášho účtu došlo k chybe.";
                    }

                    if(!empty($current_password) and !empty($_POST["new_password"])) {
                        if($user_object->passwordAuthenticationById($connection, $id, $current_password)) {
                            $user_object->updatePassword($connection, $id, $new_password);
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
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"> <!-- Inter & Plus Jakarta Sans Font Styles -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
</head>

<body class="<?php echo $theme; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

    <?php require "../assets/partials/flash_messages.php"; ?>
    <?php require "../assets/partials/cookie_policy.php"; ?>

    <header class="site_header">
        <?php require "../assets/partials/navigation.php"; ?>
    </header>

    <main>
        <section class="edit_account">
            <a href="./domov" class="back_icon" title="Späť na domovskú stránku" aria-label="Späť na domovskú stránku">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Späť <!-- https://fontawesome.com/icons/arrow-left -->
            </a>
            
            <h2><a href="./moj-ucet">Môj&nbsp;účet</a></h2>

            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                <script src="/src/ts/dist/pages/edit_account/components/edit_account_form.js" type="module"></script>
                
                <form class="edit_account_form" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="profile_picture_container">
                        <h3>Profilový obrázok</h3>

                        <?php if(!empty($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "profile_picture_name")) or !empty($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'))): ?>
                            <label for="profile_picture">
                                <img 
                                    class="profile_picture" 
                                    title="Zmeniť profilový obrázok" 
                                    src="
                                        <?php if(!empty($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url')) and empty($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'))): ?>
                                            <?php echo $user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'); ?>

                                        <?php else: ?>
                                            /content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo $user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'); ?>

                                        <?php endif; ?>
                                    "
                                    alt="Zmeniť profilový obrázok"
                                >

                                <?php if(!empty($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "google_id"))): ?>
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
                                <img class="profile_picture" title="Zmeniť profilový obrázok" src="/content/images/avatar.png" alt="Zmeniť profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->
                            </label>
                            
                            <input id="profile_picture" type="file" name="profile_picture">

                        <?php endif; ?>
                    </div>

                    <h3>Osobné údaje</h3>

                    <div class="inputs">
                        <div class="full_name_container">
                            <input
                                type="text"
                                name="first_name"
                                placeholder="Zadajte vaše meno"
                                autocomplete="given-name"
                                value="<?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'first_name'), ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <input
                                type="text"
                                name="last_name"
                                placeholder="Zadajte vaše priezvisko"
                                autocomplete="family-name"
                                value="<?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'last_name'), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>

                        <div class="email_address_container">
                            <!-- <h3>E-mailová adresa</h3> -->

                            <input
                                type="email"
                                name="email_address"
                                placeholder="Zadajte váš e-mail"
                                autocomplete="email"
                                value="<?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'email_address'), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>

                        <div class="phone_number_container">
                            <!-- <h3>Telefónne číslo</h3> -->

                            <input
                                type="text"
                                name="phone_number"
                                placeholder="Zadajte vaše telefónne číslo"
                                autocomplete="tel"
                                value="<?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'phone_number'), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                        </div>

                        <?php if(empty($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "google_id"))): ?>
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
                    </div>

                    <p class="form_report valid"></p>

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

    <footer>
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>