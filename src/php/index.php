<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
    
    // PHP Code

    require "../assets/functions.php";
    require "../assets/reviews.php";

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

    // Auto Log In To Account If remember_me_token Cookie Is Setted And User Is Not Logged In Yet

    if(isset($_COOKIE["remember_me_token"]) and !isset($_SESSION["is_logged_in"])) {
        $id = getUserIdByRememberMeToken($connection, $_COOKIE["remember_me_token"]);

        if($id !== false) {
            session_regenerate_id(true); // Protection For Fixation Attack

            $_SESSION["is_logged_in"] = true;
            $_SESSION["logged_in_user_id"] = $id;
            $_SESSION["green_report_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars(getUserName($connection, $id), ENT_QUOTES, "UTF-8");
        }
    }

    $user_id = $_SESSION["logged_in_user_id"] ?? null;

    // Contact Form

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require "../phpmailer/vendor/autoload.php";

    $first_name = "";
    $last_name = "";
    $email_address = "";
    $subject = "";
    $message = "";

    // Write Review

    $review = "";

    $attachment_message = "Nie je vybraný žiaden súbor.";

    // Google reCAPTCHA

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["recaptcha_contact_form_submit"])) {
        $secret_key = "6LfbXMYrAAAAAM8u-89OhIKngVukDKxWDTOWWnrk";
        $token = $_POST["g-recaptcha-response"] ?? "";

        if(empty($token)) {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "reCAPTCHA Token Not Found In Contact Form.", "../errors/error.log");
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

            errorLogSave($connection, "reCAPTCHA cURL Error In Contact Form. (" . htmlspecialchars($curlErr) . ")", "../errors/error.log");
            exit;
        }

        curl_close($ch);

        $responseData = json_decode($response);

        if($responseData === null) {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "Invalid reCAPTCHA Server Response In Contact Form. (" . htmlspecialchars($response) . ")", "../errors/error.log");
            exit;
        }

        // reCAPTCHA V3

        $minScore = 0.5;
        if(!empty($responseData->success) and isset($responseData->score) and $responseData->score >= $minScore and isset($responseData->action) and $responseData->action === 'submit') {
            // Contact Form

            if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                errorLogSave($connection, "CSRF Token Verification Failed While Sending An E-mail With Contact Form.", "../errors/error.log");
                die("CSRF Token nie je platný.");
            }
        
            $first_name = $_POST["first_name"];
            $last_name = $_POST["last_name"];
            $email_address = $_POST["email_address"];
            $subject = $_POST["subject"];
            $message = $_POST["message"];

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

                $mail->setFrom("behulpatrik@gmail.com", "PC Expert");
                $mail->addAddress("behulpatrik@gmail.com");

                // Content

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = "
                    <strong>$first_name $last_name</strong><br>
                    $email_address<br><br>
                    $message<br><br>
                    <strong>IP Address:</strong> {$_SERVER['REMOTE_ADDR']}
                ";

                if(isset($_FILES["attachment"])) {
                    for($i = 0; $i <= 4; $i++) { // Maximum of 5 Files.
                        $attachment_name = $_FILES["attachment"]["name"][$i];
                        $attachment_full_path = $_FILES["attachment"]["full_path"][$i];
                        $attachment_type = $_FILES["attachment"]["type"][$i];
                        $attachment_tmp_name = $_FILES["attachment"]["tmp_name"][$i];
                        $attachment_size = $_FILES["attachment"]["size"][$i];
                        $attachment_error = $_FILES["attachment"]["error"][$i];

                        if(empty($_FILES["attachment"]["name"][$i])) {
                            continue;
                        }

                        if($attachment_error === 0) {
                            $supported_extensions = ["jpg", "jpeg", "png", "gif", "webp", "bmp", "tiff", "tif", "mp4", "mov", "avi", "mkv", "webm", "hevc", "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "rtf", "odt", "ods", "odp"];
                            $attachment_extension = strtolower(pathinfo($attachment_name, PATHINFO_EXTENSION));

                            if(in_array($attachment_extension, $supported_extensions)) {
                                $new_attachment_name = uniqid("FILE-", true) . "." . $attachment_extension;

                                if(!is_dir("../../content/uploads/attachments")) {
                                    mkdir("../../content/uploads/attachments", 0777, true);
                                }

                                $attachment_upload_path = "../../content/uploads/attachments/" . $new_attachment_name;
                                move_uploaded_file($attachment_tmp_name, $attachment_upload_path);

                                $mail->addAttachment($attachment_upload_path, $attachment_name);
                            }

                            else {
                                $attachment_message = "Prípona súboru nie je podporovaná.";
                                break;
                            }
                        }

                        else {
                            $attachment_message = "Pri nahrávaní súborov došlo k chybe.";
                        }
                    }
                }

                if($attachment_message === "Nie je vybraný žiaden súbor.") {
                    $mail->send();

                    $_SESSION["green_report_message"] = "Správa&nbsp;bola&nbsp;odoslaná";

                    redirectURL("/domov");
                }

                throw new Exception();
            }
            
            catch(Exception $e) {
                $_SESSION["red_report_message"] = "Správu&nbsp;sa&nbsp;nepodarilo&nbsp;odoslať";

                errorLogSave($connection, "Error While Sending An E-mail With Contact Form. (" . $e->getMessage() . ")", "../errors/error.log");
                exit;
            }
        }
        
        else {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "reCAPTCHA Failed In Contact Form. (" . htmlspecialchars($response) . ")", "../errors/error.log");
            exit;
        }
    }

    // Google reCAPTCHA

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["recaptcha_write_review_submit"])) {
        $secret_key = "6LfbXMYrAAAAAM8u-89OhIKngVukDKxWDTOWWnrk";
        $token = $_POST["g-recaptcha-response"] ?? "";

        if(empty($token)) {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "reCAPTCHA Token Not Found In Write Review Form.", "../errors/error.log");
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

            errorLogSave($connection, "reCAPTCHA cURL Error In Write Review Form. (" . htmlspecialchars($curlErr) . ")", "../errors/error.log");
            exit;
        }

        curl_close($ch);

        $responseData = json_decode($response);

        if($responseData === null) {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "Invalid reCAPTCHA Server Response In Write Review Form. (" . htmlspecialchars($response) . ")", "../errors/error.log");
            exit;
        }

        // reCAPTCHA V3

        $minScore = 0.5;
        if(!empty($responseData->success) and isset($responseData->score) and $responseData->score >= $minScore and isset($responseData->action) and $responseData->action === 'submit') {
            // Write Review

            if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                errorLogSave($connection, "CSRF Token Verification Failed While Posting A Review With Write Review Form.", "../errors/error.log");
                die("CSRF Token nie je platný.");
            }

            $rating = $_POST["rating"];
            $review = $_POST["review"];

            if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true) {
                sendReview($connection, $_SESSION["logged_in_user_id"], $rating, $review);
            }

            else {
                sendReview($connection, null, $rating, $review);
            }

            $_SESSION["green_report_message"] = "Ďakujeme&nbsp;za&nbsp;spätnú&nbsp;väzbu";
            $review = "";

            redirectURL("/domov");
        }
        
        else {
            $_SESSION["red_report_message"] = "Nepodarilo&nbsp;sa&nbsp;overenie&nbsp;reCAPTCHA";

            errorLogSave($connection, "reCAPTCHA Failed In Write Review Form. (" . htmlspecialchars($response) . ")", "../errors/error.log");
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
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-L62G1B08VS');
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Profesionálna stavba, údržba a čistenie počítačov. Kontaktujte nás a prenechajte Váš počítač profesionálom.">
    <meta name="keywords" content="PC, Stavba počítačov, Údržba počítačov, Čistenie počítačov, Servis, IT, Kontakt">

    <title>PC Expert - Stavba, údržba a čistenie počítačov</title>
    <!-- <link rel="icon" type="image/png" href=""> -->
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/lightbox.min.css"> <!-- https://github.com/lokesh/lightbox2/tree/dev -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->
    <link href="https://fonts.googleapis.com/css2?family=Bruno+Ace&display=swap" rel="stylesheet"> <!-- Bruno Ace Font Style -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script> <!-- https://releases.jquery.com/ -->
    <script src="/src/js/lightbox-plus-jquery.min.js"></script> <!-- https://github.com/lokesh/lightbox2/tree/dev -->
    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="/src/ts/dist/pages/homepage/components/reviews.js" type="module"></script>
    <script src="/src/ts/dist/pages/homepage/components/contact_form.js" type="module"></script>

    <script defer>
        // JavaScript Code

        "use strict"
        
        document.addEventListener("DOMContentLoaded", function(event) {
            // Secret Message

            console.log("%cPC Expert by Patrik Behul", "color: rgb(170, 255, 50); background-color: rgb(0, 0, 0)")
        })
    </script>
    
    <script src="/src/js/preloader.js" defer></script>
    <script src="/src/js/navigation_bar.js" defer></script>
    <script src="/src/js/move_up_icon.js" defer></script>
    <script src="/src/js/cookie_policy.js" defer></script>
    <script src="/src/js/theme_selection.js" defer></script>
    <script src="/src/js/photo_gallery.js" defer></script>
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

        <nav class="navigation_bar
            <?php if(isset($_SESSION['logged_in_user_id']) and getUserRole($connection, $_SESSION['logged_in_user_id']) === 'admin'): ?>
                admin

            <?php else: ?>
                classic

            <?php endif; ?>
        ">
            <ul>
                <li class="navigation_bar_item scroll_about_us">
                    <a href="#">O NÁS</a>
                </li>

                <li class="navigation_bar_item scroll_our_services">
                    <a href="#">NAŠE SLUŽBY</a>
                </li>

                <li class="navigation_bar_item scroll_contact">
                    <a href="#">KONTAKT</a>
                </li>

                <?php if(isset($_SESSION["logged_in_user_id"]) and getUserRole($connection, $_SESSION["logged_in_user_id"]) === "admin"): ?>
                    <li style="border: 1px solid rgb(250, 50, 50);" class="navigation_bar_item">
                        <a href="./administracia">ADMINISTRÁCIA</a>
                    </li>

                <?php endif; ?>
                
                <div class="navigation_icon">
                    <i class="fa-solid fa-bars"></i>
                    <!-- <i class="fa-solid fa-xmark"></i> -->
                </div>
            </ul>
        </nav>

        <div class="move_up_icon">
            <img src="/content/images/up-arrow.png" title="Posunúť nahor" alt="Posunúť nahor"> <!-- https://www.flaticon.com/free-icon/up-arrow_271239 -->
        </div>

        <div class="cookie_policy">
            <img src="/content/images/cookie.png" alt="Cookie"> <!-- https://www.flaticon.com/free-icon/cookie_1047711 -->
            <h2>Cookie Policy</h2>
            <p>Táto&nbsp;stránka&nbsp;používa&nbsp;súbory&nbsp;cookies.</p>
            <button class="cookie_policy_button">ROZUMIEM</button>
        </div>

        <div class="theme_selection">
            <img class="light_theme" src="/content/images/sun.png" title="Svetlé zobrazenie" alt="Svetlé zobrazenie"> <!-- https://www.flaticon.com/free-icon/day-mode_11457488 -->
            <img class="dark_theme" src="/content/images/moon.png" title="Tmavé zobrazenie" alt="Tmavé zobrazenie"> <!-- https://www.flaticon.com/free-icon/moon_11735139 -->
        </div>
        
        <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
            <div class="account">
                <ul>
                    <li>
                        <a href="./moj-ucet" title="Môj účet">
                            <?php if(!empty(getProfilePictureName($connection, $_SESSION["logged_in_user_id"])) or !empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'))): ?>
                                <img 
                                    class="profile_picture" 
                                    src="
                                        <?php if(!empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url')) and empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'))): ?>
                                            <?php echo getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'); ?>

                                        <?php else: ?>
                                            /content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>

                                        <?php endif; ?>
                                    "
                                    alt="Môj účet"
                                >

                            <?php else: ?>
                                <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                            <?php endif; ?>
                        </a>

                        <a href="./moj-ucet" title="Môj účet">
                            <?php echo htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8"); ?>
                        </a>
                    </li>

                    <li>
                        <a href="./logout.php" title="Odhlásiť sa z účtu <?php echo htmlspecialchars(getUserName($connection, $_SESSION["logged_in_user_id"]), ENT_QUOTES, "UTF-8"); ?>">Odhlásiť</a>
                    </li>
                </ul>
            </div>

        <?php else: ?>
            <div class="account">
                <ul>
                    <li>
                        <a href="./prihlasenie" title="Prihlásiť sa">
                            <?php echo "Neprihlásený" ?>
                        </a>
                    </li>
                </ul>
            </div>

        <?php endif; ?>

        <div class="header_box">
            <!-- <div class="logo">
                <img src="/content/images/test_logo.png" alt="Logo">
            </div> -->

            <h1><a href="./domov">PC&nbsp;Expert</a></h1>

            <div class="buttons"> <!-- https://codepen.io/hilwat/pen/BeemVX -->
                <a class="blob-btn login_button" href="./prihlasenie" target="_self">
                    Prihlásiť
                    <span class="blob-btn__inner">
                    <span class="blob-btn__blobs">
                        <span class="blob-btn__blob"></span>
                        <span class="blob-btn__blob"></span>
                        <span class="blob-btn__blob"></span>
                        <span class="blob-btn__blob"></span>
                    </span>
                    </span>
                </a>

                <a class="blob-btn registration_button" href="./registracia" target="_self">
                    Zaregistrovať
                    <span class="blob-btn__inner">
                    <span class="blob-btn__blobs">
                        <span class="blob-btn__blob"></span>
                        <span class="blob-btn__blob"></span>
                        <span class="blob-btn__blob"></span>
                        <span class="blob-btn__blob"></span>
                    </span>
                    </span>
                </a>

                <br/>

                <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
                    <defs>
                        <filter id="goo">
                        <feGaussianBlur in="SourceGraphic" result="blur" stdDeviation="10"></feGaussianBlur>
                        <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 21 -7" result="goo"></feColorMatrix>
                        <feBlend in2="goo" in="SourceGraphic" result="mix"></feBlend>
                        </filter>
                    </defs>
                </svg>
            </div>
        </div>
        
        <div class="header_text">
            <div class="user_rating" title="Na základe <?php echo htmlspecialchars(countReviews($connection), ENT_QUOTES, 'UTF-8'); ?> recenzií.">
                <?php if(averageRating($connection)): ?>
                    <p><?php echo htmlspecialchars(averageRating($connection), ENT_QUOTES, "UTF-8"); ?> / 5</p>

                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= round(averageRating($connection))): ?>
                            <img src="/content/images/star.png" alt="Hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                        <?php else: ?>
                            <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                        <?php endif; ?>

                    <?php endfor; ?>

                <?php else: ?>
                    <p>0 / 5</p>

                    <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->
                    <img src="/content/images/empty_star.png" alt="Prázdna "> <!-- https://www.flaticon.com/free-icon/star_1828970 -->
                    <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->
                    <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->
                    <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                <?php endif; ?>
            </div>

            <p>Vážime si, že využívate naše služby. Ďakujeme!</p>
        </div>
    </header>

    <section class="photo_gallery">
        <div class="image image_1 active <?php echo $theme; ?>">
            <h2>Procesor</h2>
        </div>

        <div class="image image_2 <?php echo $theme; ?>">
            <h2>Základná doska</h2>
        </div>

        <div class="image image_3 <?php echo $theme; ?>">
            <h2>Grafická karta</h2>
        </div>

        <div class="image image_4 <?php echo $theme; ?>">
            <h2>2. Grafická karta</h2>
        </div>

        <div class="image image_5 <?php echo $theme; ?>">
            <h2>RAM</h2>
        </div>
    </section>

    <main class="<?php echo $theme; ?>">
        <section class="about_us <?php echo $theme; ?>">
            <h2>O&nbsp;nás</h2>
            <p>Sme tím nadšencov a odborníkov na počítačový hardvér, ktorí sa venujú profesionálnej stavbe, servisu a upgradu počítačov. Našim cieľom je poskytovať spoľahlivé a kvalitné služby, vďaka ktorým si môžete užívať bezproblémový a výkonný počítač, či už na prácu, hranie hier alebo kreatívnu tvorbu.<br>
            Či už potrebujete nový počítač na mieru, odbornú diagnostiku, výmenu komponentov, čistenie alebo zrýchlenie vášho zariadenia, sme tu pre vás. Ku každému zákazníkovi pristupujeme individuálne a hľadáme riešenia, ktoré najlepšie vyhovujú jeho potrebám a rozpočtu.<br>
            Dôverujte odborníkom a nechajte si poradiť od ľudí, ktorí počítačom skutočne rozumejú. S nami získate nielen profesionálnu službu, ale aj cenné rady, ktoré vám pomôžu naplno využiť potenciál vášho zariadenia.<br>
            Kontaktujte nás ešte dnes a nechajte svoj počítač v rukách profesionálov!</p>
        </section>
        
        <section class="main_content <?php echo $theme; ?>">
            <section class="our_services">
                <h2><img class="discount_icon" src="/content/images/free.png" alt="Zadarmo">Stavba&nbsp;počítača</h2> <!-- https://www.flaticon.com/free-icon/free_3706334 -->

                <p>Nechajte si u nás zostaviť a nakonfigurovať počítač na mieru – rýchlo, profesionálne a za výhodnú cenu. Ak už máte vhodné komponenty a hľadáte spoľahlivých odborníkov na ich správnu montáž, sme tu pre vás. A ak si ešte nie ste istí výberom, radi vám pomôžeme vybrať tie najlepšie komponenty podľa vašich potrieb a zabezpečíme ich odborné zostavenie. S nami získate počítač, ktorý bude pripravený na použitie bez starostí!<br>
                len za: <span class="price"><s>30€</s> ZADARMO</span><br><a class="contact_us_button build_computer_button" href="#" target="_self">Mám záujem</a></p>
            </section>
    
            <section>
                <h2><img class="discount_icon" src="/content/images/free.png" alt="Zadarmo">Údržba&nbsp;počítača</h2> <!-- https://www.flaticon.com/free-icon/free_3706334 -->

                <p>Ak vás trápi hardvérový problém, nefunkčné alebo poškodené komponenty, sme tu, aby sme vám pomohli. Diagnostikujeme váš počítač, navrhneme najlepšie riešenie a postaráme sa o opravu alebo výmenu nefunkčných komponentov. Okrem toho vám radi poradíme s upgradom – či už ide o zrýchlenie systému, rozšírenie kapacity alebo zvýšenie výkonu staršieho alebo nového počítača, pomôžeme vám z neho vyťažiť maximum!<br>
                len za: <span class="price"><s>15€</s> ZADARMO</span><br><a class="contact_us_button computer_maintenance_button" href="#" target="_self">Mám záujem</a></p>
            </section>
    
            <section>
                <h2><img class="discount_icon" src="/content/images/free.png" alt="Zadarmo">Čistenie&nbsp;počítača</h2> <!-- https://www.flaticon.com/free-icon/free_3706334 -->

                <p>Ak váš počítač alebo notebook spomaľuje nahromadený prach a nečistoty, postaráme sa o jeho dôkladné vyčistenie. Odborne vyčistíme vnútro zariadenia, odstránime prach a nečistoty a zabezpečíme optimálne chladenie výmenou teplovodivej pasty na procesore či grafickej karte, čím výrazne znížime prevádzkové teploty. S nami váš počítač opäť získa maximálny výkon, stabilitu a dlhšiu životnosť!<br>
                len za: <span class="price"><s>10€</s> ZADARMO</span><br><a class="contact_us_button computer_cleaning_button" href="#" target="_self">Mám záujem</a></p>
            </section>
    
            <section>
                <div class="slider <?php echo $theme; ?>">
                    <div class="slides">
                        <input type="radio" name="r" id="r1" checked>
                        <input type="radio" name="r" id="r2">
                        <input type="radio" name="r" id="r3">
                        <input type="radio" name="r" id="r4">
                        <input type="radio" name="r" id="r5">
    
                        <div class="slide s1">
                            <a href="/content/images/processor.jpg" title="Klikni pre zobrazenie" data-lightbox="mygallery" data-title="Procesor">
                                <img src="/content/images/processor.jpg" alt="Procesor">
                            </a>
                        </div>

                        <div class="slide">
                            <a href="/content/images/motherboard.jpg" title="Klikni pre zobrazenie" data-lightbox="mygallery" data-title="Základná doska">
                                <img src="/content/images/motherboard.jpg" alt="Základná doska">
                            </a>
                        </div>

                        <div class="slide">
                            <a href="/content/images/graphics-card.jpg" title="Klikni pre zobrazenie" data-lightbox="mygallery" data-title="Grafická karta">
                                <img src="/content/images/graphics-card.jpg" alt="Grafická karta">
                            </a>
                        </div>

                        <div class="slide">
                            <a href="/content/images/graphics-card-2.jpg" title="Klikni pre zobrazenie" data-lightbox="mygallery" data-title="2. Grafická karta">
                                <img src="/content/images/graphics-card-2.jpg" alt="2. Grafická karta">
                            </a>
                        </div>
                        
                        <div class="slide">
                            <a href="/content/images/ram.jpg" title="Klikni pre zobrazenie" data-lightbox="mygallery" data-title="RAM">
                                <img src="/content/images/ram.jpg" alt="RAM">
                            </a>
                        </div>
                    </div>
    
                    <div class="navigation">
                        <label for="r1" class="bar"></label>
                        <label for="r2" class="bar"></label>
                        <label for="r3" class="bar"></label>
                        <label for="r4" class="bar"></label>
                        <label for="r5" class="bar"></label>
                    </div>
                </div>
            </section>
    
            <section>
                <div class="map <?php echo $theme; ?>">
                    <iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2606.766288777082!2d18.764998876951566!3d49.20499297138131!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x471459414e15928d%3A0x565bca562c7ff67c!2zMSwgS2FycGF0c2vDoSAzMTA2LCAwMTAgMDggxb1pbGluYS1WbMSNaW5jZQ!5e0!3m2!1ssk!2ssk!4v1740590369995!5m2!1ssk!2ssk" allowfullscreen="yes" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </section>

            <!-- <section>
                <h2>Obchod</h2>
                <div>
                    <h3>GeForce GTX 1660 Ti VENTUS XS 6G OC - 100€</h3>
                    <img src="/content/images/GTX-1660-Ti.png" alt="GTX 1660 Ti VENTUS XS 6G OC">
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Saepe unde ipsa voluptatum! Esse, provident debitis est placeat suscipit, reiciendis, ratione iste laborum odio inventore similique exercitationem minima quas delectus fugit.</p>
                </div>
            </section> -->
    
            <section class="faq">
                <h2>Časté&nbsp;otázky&nbsp;(FAQ)</h2>
                <h3>Ako dlho trvá zostavenie počítača?</h3>
                <p>Závisí to podľa dostupnosti komponentov a zložitosti zostavy. Ak máte všetky komponenty pripravené, zostavenie zvyčajne trvá 1 – 2 dni. Ak vám pomáhame aj s výberom komponentov, môže to trvať niekoľko dní až týždeň.</p>
                <h3>Ako to je s dopravou?</h3>
                <p>Po vašom schválení a objednaní potrebných komponentov pre vaše zariadenie, alebo pri odoslaní vašich komponentov či zariadenia, cenu dopravy dopravnou spoločnosťou do 5 € hradíme my. Ak bude cena dopravy vyššia, rozdiel hradíte vy. O všetkých nákladoch vás budeme vopred informovať.</p>
                <h3>Ako to je s detailnou dohodou ohľadom danej služby?</h3>
                <p>Stačí nás kontaktovať, ideálne telefonicky, aby sme mohli rýchlo a efektívne prediskutovať vaše požiadavky. Či už ide o výber komponentov, údržbu, čistenie alebo inú službu, radi vám poradíme a spoločne nájdeme najlepšie riešenie. Po dohode zabezpečíme objednanie potrebných komponentov, dohodneme sa na termíne realizácie a spôsobe prevzatia vášho zariadenia.</p>
                <h3>Ako to je s platbou?</h3>
                <p>Pred objednaním komponentov na stavbu počítača alebo pred odovzdaním vášho zariadenia na údržbu či čistenie je platba za službu a zakúpené komponenty potrebná vopred. Po dokončení práce vám odovzdáme všetky faktúry, doklady o kúpe a originálne balenia komponentov.</p>
            </section>

            <section class="reviews <?php echo $theme; ?>" data-logged_in_user_id="<?php echo json_encode($user_id); ?>">
                <h2>Recenzie</h2>
                
                <div class="user_review">
                    <h3>Moja recenzia</h3>
                    
                    <?php if(userReview($connection, $user_id) !== null and userReview($connection, $user_id) !== false): ?>
                        <div class="review">
                            <?php if(userReview($connection, $user_id)["status"] === "pending"): ?>
                                <p class="pending_review">Vaša recenzia čaká na kontrolu</p>

                            <?php endif; ?>

                            <div class="reviewer">
                                <img
                                    class="profile_picture"
                                    src="
                                        <?php if(!empty(getUserInformations($connection, userReview($connection, $user_id)['user_id'], 'google_profile_picture_url')) and empty(getUserInformations($connection, userReview($connection, $user_id)['user_id'], 'profile_picture_name'))): ?>
                                            <?php echo getUserInformations($connection, userReview($connection, $user_id)['user_id'], 'google_profile_picture_url'); ?>

                                        <?php else: ?>
                                            /content/uploads/<?php echo userReview($connection, $user_id)['user_id']; ?>/<?php echo getProfilePictureName($connection, userReview($connection, $user_id)['user_id']); ?>

                                        <?php endif; ?>
                                    "
                                    alt="Profilový obrázok"
                                >

                                <p class="user_name">
                                    <?php echo htmlspecialchars(getUserInformations($connection, userReview($connection, $user_id)["user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>

                                    <?php echo htmlspecialchars(getUserInformations($connection, userReview($connection, $user_id)["user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>

                                    <span class="date">
                                        -

                                        <?php echo htmlspecialchars(date("j.n.Y", strtotime(userReview($connection, $user_id)["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                                        <?php if(userReview($connection, $_SESSION["logged_in_user_id"])["edited"] === "yes"): ?>
                                            (upravená)

                                        <?php endif; ?>
                                    </span>
                                </p>
                            </div>

                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= userReview($connection, $user_id)["rating"]): ?>
                                    <img src="/content/images/star.png" alt="Hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                                <?php else: ?>
                                    <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                                <?php endif; ?>

                            <?php endfor; ?>
                            
                            <br>

                            <a class="edit_review" href="./upravit-recenziu" title="Upraviť recenziu">Upraviť recenziu</a>

                            <p><?php echo htmlspecialchars(userReview($connection, $user_id)["review"], ENT_QUOTES, "UTF-8"); ?></p>
                        </div>

                    <?php else: ?>
                        <script src="/src/ts/dist/pages/homepage/components/write_review.js" type="module"></script>

                        <!-- reCAPTCHA V3 -->
                        <script src="https://www.google.com/recaptcha/api.js?render=6LfbXMYrAAAAAJfSCq3aCjeiFxnhly7K2sgFjiEz&badge=inline"></script>
                        
                        <script>
                            grecaptcha.ready(function() {
                                const grecaptcha_badge = document.querySelectorAll(".grecaptcha-badge")[0]
                                const grecaptcha_badge_container = document.querySelectorAll(".grecaptcha_badge_container")[0]

                                grecaptcha_badge_container.appendChild(grecaptcha_badge)
                                grecaptcha_badge.style.marginBottom = "7px"
                                grecaptcha_badge.style.background = "#1e1e1e"
                                grecaptcha_badge.style.opacity = "0.8"

                                document.querySelector(".write_review").addEventListener("submit", function(event) {
                                    event.preventDefault()
                                    
                                    grecaptcha.execute("6LfbXMYrAAAAAJfSCq3aCjeiFxnhly7K2sgFjiEz", {action: "submit"}).then(function(token) {
                                        const g_recaptcha_response = document.querySelectorAll("#g-recaptcha-response")[0]

                                        g_recaptcha_response.value = token
                                        event.target.submit()
                                    })
                                })
                            })
                        </script>

                        <form class="write_review" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                                <div class="reviewer">
                                    <img 
                                        class="profile_picture" 
                                        src="
                                            <?php if(!empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url')) and empty(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'))): ?>
                                                <?php echo getUserInformations($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'); ?>

                                            <?php else: ?>
                                                /content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>

                                            <?php endif; ?>
                                        "
                                        alt="Profilový obrázok"
                                    >

                                    <p class="user_name">
                                        <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>

                                        <?php echo htmlspecialchars(getUserInformations($connection, $_SESSION["logged_in_user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>
                                    </p>
                                </div>

                            <?php else: ?>
                                <div class="reviewer">
                                    <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                                    <p class="user_name">Anonymný zákazník</p>
                                </div>

                            <?php endif; ?>

                            <div class="stars">
                                <img class="star" src="/content/images/empty_star.png" data-rating="1" alt="Prázdna hviezda">
                                <img class="star" src="/content/images/empty_star.png" data-rating="2" alt="Prázdna hviezda">
                                <img class="star" src="/content/images/empty_star.png" data-rating="3" alt="Prázdna hviezda">
                                <img class="star" src="/content/images/empty_star.png" data-rating="4" alt="Prázdna hviezda">
                                <img class="star" src="/content/images/empty_star.png" data-rating="5" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828884 https://www.flaticon.com/free-icon/star_1828970 -->

                                <input class="rating" type="hidden" name="rating" value="">
                            </div>

                            <p class="form_report"></p>

                            <textarea type="textarea" name="review" placeholder="Zanechajte nám vaše hodnotenie"><?php echo htmlspecialchars($review, ENT_QUOTES, "UTF-8"); ?></textarea>

                            <div class="grecaptcha_badge_container"></div>

                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                            <input type="hidden" name="recaptcha_write_review_submit">
                            <button class="write_review_submit" type="submit" name="write_review_submit">Zverejniť recenziu</button>
                        </form>

                    <?php endif; ?>
                </div>

                <h3 class="all_reviews_title">Ostatné recenzie</h3>

                <div class="all_reviews">
                    <?php foreach(allReviews($connection, $user_id) as $one_review): ?>
                        <div class="review">
                            <?php if($one_review["user_id"] !== null): ?>
                                <div class="reviewer">
                                    <img 
                                        class="profile_picture"
                                        src="
                                            <?php if(!empty(getUserInformations($connection, $one_review['user_id'], 'google_profile_picture_url')) and empty(getUserInformations($connection, $one_review['user_id'], 'profile_picture_name'))): ?>
                                                <?php echo getUserInformations($connection, $one_review['user_id'], 'google_profile_picture_url'); ?>

                                            <?php else: ?>
                                                /content/uploads/<?php echo $one_review['user_id']; ?>/<?php echo getProfilePictureName($connection, $one_review['user_id']); ?>

                                            <?php endif; ?>
                                        "
                                    >

                                    <p class="user_name">
                                        <?php echo htmlspecialchars(getUserInformations($connection, $one_review["user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>

                                        <?php echo htmlspecialchars(getUserInformations($connection, $one_review["user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>

                                        <span class="date">
                                            -

                                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                                            <?php if($one_review["edited"] === "yes"): ?>
                                                (upravená)

                                            <?php endif; ?>
                                        </span>
                                    </p>
                                </div>

                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $one_review["rating"]): ?>
                                        <img src="/content/images/star.png" alt="Hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                                    <?php else: ?>
                                        <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                                    <?php endif; ?>
                                    
                                <?php endfor; ?>

                                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php else: ?>
                                <div class="reviewer">
                                    <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                                    <p class="user_name">
                                        Anonymný zákazník

                                        <span class="date">
                                            -
                                            
                                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>
                                        </span>
                                    </p>
                                </div>

                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $one_review["rating"]): ?>
                                        <img src="/content/images/star.png" alt="Hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828884 -->

                                    <?php else: ?>
                                        <img src="/content/images/empty_star.png" alt="Prázdna hviezda"> <!-- https://www.flaticon.com/free-icon/star_1828970 -->

                                    <?php endif; ?>
                                    
                                <?php endfor; ?>

                                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>
                </div>

                <?php if(countReviews($connection, $user_id) > 5): ?>
                    <button class="show_more show_more_reviews" title="Zobraziť viac">Zobraziť viac</button>
                    <button class="show_less show_less_reviews" title="Zobraziť menej">Zobraziť menej</button>

                <?php endif; ?>
            </section>
        </section>

        <section class="contact <?php echo $theme; ?>">
            <h2>Kontakt</h2>
            <p>
                V prípade akýchkoľvek otázok, či upozornení nás kontaktujte a my sa vám pokúsime v krátkom čase odpovedať. Budeme radi za akúkoľvek spätnú väzbu od Vás.
                <?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?>
                    <?php echo "Údaje vo formulári boli automaticky vyplnené na základe údajov vášho účtu."; ?>

                <?php endif; ?>
            </p>

            <!-- reCAPTCHA V3 -->
            <script src="https://www.google.com/recaptcha/api.js?render=6LfbXMYrAAAAAJfSCq3aCjeiFxnhly7K2sgFjiEz&badge=inline"></script>
            
            <script>
                grecaptcha.ready(function() {
                    const grecaptcha_badge = document.querySelectorAll(".grecaptcha-badge")[1] ?? document.querySelectorAll(".grecaptcha-badge")[0]
                    const grecaptcha_badge_container = document.querySelectorAll(".grecaptcha_badge_container")[1] ?? document.querySelectorAll(".grecaptcha_badge_container")[0]

                    grecaptcha_badge_container.appendChild(grecaptcha_badge)
                    grecaptcha_badge.style.margin = "0px auto 10px"
                    grecaptcha_badge.style.background = "#1e1e1e"
                    grecaptcha_badge.style.opacity = "0.8"

                    document.querySelector(".contact_form").addEventListener("submit", function(event) {
                        event.preventDefault()
                        
                        grecaptcha.execute("6LfbXMYrAAAAAJfSCq3aCjeiFxnhly7K2sgFjiEz", {action: "submit"}).then(function(token) {
                            const g_recaptcha_response = document.querySelectorAll("#g-recaptcha-response")[1] ?? document.querySelectorAll("#g-recaptcha-response")[0]

                            g_recaptcha_response.value = token
                            event.target.submit()
                        })
                    })
                })
            </script>

            <form class="contact_form <?php echo $theme; ?>" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                <input 
                    type="text"
                    name="first_name"
                    placeholder="Zadajte vaše meno"
                    autocomplete="given-name"
                    value="<?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?><?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'first_name'), ENT_QUOTES, 'UTF-8'); ?><?php else: ?><?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>"
                    required
                >

                <input 
                    type="text"
                    name="last_name"
                    placeholder="Zadajte vaše priezvisko"
                    autocomplete="family-name"
                    value="<?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?><?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'last_name'), ENT_QUOTES, 'UTF-8'); ?><?php else: ?><?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>"
                    required
                >

                <input 
                    type="email"
                    name="email_address"
                    placeholder="Zadajte váš e-mail"
                    autocomplete="email"
                    value="<?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?><?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'email_address'), ENT_QUOTES, 'UTF-8'); ?><?php else: ?><?php echo htmlspecialchars($email_address, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>"
                    required
                >

                <select class="select_menu" name="subject">
                    <option value="">Zadajte predmet vašej správy</option>

                    <optgroup label="Naše služby">
                        <option>Stavba počítača</option>
                        <option>Údržba počítača</option>
                        <option>Čistenie počítača</option>
                    </optgroup>
                    
                    <optgroup label="Ostatné">
                        <option>Otázka</option>
                        <option>Návrh na zlepšenie</option>
                        <option>Nahlásenie chyby</option>
                    </optgroup>
                </select>
                
                <div class="attachment_container">
                    <p class="attachment_report <?php echo $theme; ?>"><?php echo htmlspecialchars($attachment_message, ENT_QUOTES, "UTF-8"); ?></p>

                    <label class="attachment" for="attachment"><i class="fa-solid fa-paperclip" title="priložiť prílohu..."></i></label>

                    <input id="attachment" type="file" name="attachment[]" accept=".jpg, .jpeg, .png, .gif, .webp, .bmp, .tiff, .tif, .mp4, .mov, .avi, .mkv, .webm, .hevc, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .txt, .rtf, .odt, .ods, .odp" multiple>
                </div>

                <textarea type="textarea" name="message" placeholder="Zadajte vašu správu" required><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></textarea>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                
                <input type="hidden" name="recaptcha_contact_form_submit">
                <button class="contact_form_submit" type="submit" name="contact_form_submit">Poslať správu</button>

                <div class="grecaptcha_badge_container"></div>
            </form>
        </section>
    </main>
    
    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>
