<?php
    // Debug
    
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);
    
    // PHP Code

    require_once "../../vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->load();

    require "../assets/functions.php";
    require "../classes/Database.php";
    require "../classes/User.php";
    require "../classes/Review.php";

    $database_object = new Database(); // Initializes The Database Methods
    $user_object = new User(); // Initializes The User Methods
    $review_object = new Review(); // Initializes The Review Methods

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

    // Auto Log In To Account If remember_me_token Cookie Is Setted And User Is Not Logged In Yet

    if(isset($_COOKIE["remember_me_token"]) and !isset($_SESSION["is_logged_in"])) {
        $id = $user_object->getUserIdByRememberMeToken($connection, $_COOKIE["remember_me_token"]);

        if($id !== false) {
            session_regenerate_id(true); // Protection For Fixation Attack

            $_SESSION["is_logged_in"] = true;
            $_SESSION["logged_in_user_id"] = $id;
            $_SESSION["green_report_message"] = "Prihlásený&nbsp;ako " . htmlspecialchars($user_object->getUserInformation($connection, $id, "first_name"), ENT_QUOTES, "UTF-8") . " " . htmlspecialchars($user_object->getUserInformation($connection, $id, "last_name"), ENT_QUOTES, "UTF-8");
        }
    }

    $user_id = $_SESSION["logged_in_user_id"] ?? null; // Gets The Logged In User ID

    // Contact Form

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $first_name = "";
    $last_name = "";
    $email_address = "";
    $phone_number = "";
    $subject = "";
    $message = "";

    // Write Review

    $review = "";

    $attachment_message = "Nie je vybraný žiaden súbor.";

    // Google reCAPTCHA

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["recaptcha_contact_form_submit"])) {
        $secret_key = $_ENV["RECAPTCHA_SECRET_KEY"];
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
        $secret_key = $_ENV["RECAPTCHA_SECRET_KEY"];
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
                $review_object->sendReview($connection, $rating, $review, $_SESSION["logged_in_user_id"]);
            }

            else {
                $review_object->sendReview($connection, $rating, $review, null);
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

    // All Services (Card + Checkout Dialog Configuration)

    $all_services = [
        [
            "service" => "computer_building",
            "title" => "Stavba počítača",
            "icon" => "fa-solid fa-microchip",
            "image" => "/content/images/computer_building.jpg", // https://unsplash.com/photos/a-close-up-of-a-computer-motherboard-0Sdxy9Ev6oQ
            "price" => 50,
            "description" => "Nechajte si zostaviť a nakonfigurovať počítač na mieru – rýchlo, profesionálne a za výhodnú cenu. Máte už nakúpené vlastné komponenty a hľadáte niekoho na odbornú montáž? Radi sa o to postaráme. Ak si s výberom zatiaľ neviete rady, poradíme vám tie najlepšie diely presne podľa vášho rozpočtu a požiadaviek. Získate tak vyladený stroj pripravený na okamžité použitie, úplne bez starostí.",
            "summary" => "Zostavíme vám výkonný počítač presne podľa vášho rozpočtu a požiadaviek.",
            "features" => [
                "Výber komponentov podľa rozpočtu",
                "Odborná montáž aj vlastných dielov",
                "Pripravený na okamžité použitie"
            ],
            "price_note" => "za prácu, bez komponentov",
            "badge" => "Na mieru", // Optional Card Badge (null Hides It)
            "details_title" => "O počítači",
            "details_description" => "Priblížte nám vašu predstavu. Po spracovaní objednávky vás budeme kontaktovať s konkrétnym návrhom zostavy.",
            "details_file" => "checkout_details_computer_building.php",
            "message_placeholder" => "Napr. už mám zakúpenú grafickú kartu RTX 4070...",
            "message_required" => false
        ],
        [
            "service" => "computer_maintenance",
            "title" => "Údržba počítača",
            "icon" => "fa-solid fa-screwdriver-wrench",
            "image" => "/content/images/computer_maintenance.jpg", // https://unsplash.com/photos/person-holding-black-and-silver-dj-controller-sMKUYIasyDM
            "price" => 30,
            "description" => "Trápi vás pomalý systém, podozrivý hluk alebo úplne nefunkčný počítač? Sme tu pre vás. Zariadenie odborne diagnostikujeme, navrhneme najlepšie riešenie a postaráme sa o opravu či výmenu chybných dielov. Radi vám poradíme aj s prípadným upgradom – či už ide o zrýchlenie, zväčšenie úložiska alebo zvýšenie herného výkonu. Pomôžeme vám z vášho stroja opäť vyťažiť maximum!",
            "summary" => "Diagnostika, oprava a upgrade pomalého alebo nefunkčného počítača.",
            "features" => [
                "Diagnostika a odstránenie porúch",
                "Výmena chybných dielov",
                "Poradenstvo pri upgrade"
            ],
            "price_note" => "za diagnostiku a prácu",
            "badge" => null,
            "details_title" => "Detaily údržby",
            "details_description" => "Popíšte nám aktuálny stav zariadenia, aby sme problém diagnostikovali rýchlejšie.",
            "details_file" => "checkout_details_computer_maintenance.php",
            "message_placeholder" => "Popíšte problém, kedy nastáva a čo ste už skúšali...",
            "message_required" => true
        ],
        [
            "service" => "computer_cleaning",
            "title" => "Čistenie počítača",
            "icon" => "fa-solid fa-fan",
            "image" => "/content/images/computer_cleaning.jpg", // https://unsplash.com/photos/a-compact-computer-case-with-internal-components-visible-AZcrU0-8QzU
            "price" => 20,
            "description" => "Trápi váš počítač alebo notebook vysoký hluk ventilátorov a prehrievanie? Postaráme sa o jeho profesionálne hĺbkové vyčistenie. Odborne zbavíme vnútro zariadenia nahromadeného prachu a v prípade záujmu vymeníme teplovodivú pastu na procesore aj grafickej karte. Tým radikálne znížime prevádzkové teploty, vďaka čomu váš stroj opäť získa maximálny výkon, tichý chod a dlhšiu životnosť!",
            "summary" => "Hĺbkové vyčistenie pre nižšie teploty, tichší chod a dlhšiu životnosť.",
            "features" => [
                "Odstránenie prachu z celého zariadenia",
                "Výmena teplovodivej pasty",
                "Nižšie teploty a tichší chod"
            ],
            "price_note" => "za kompletné čistenie",
            "badge" => null,
            "details_title" => "Detaily čistenia",
            "details_description" => "Pár otázok, aby sme odhadli stav zariadenia a zvolili správny postup.",
            "details_file" => "checkout_details_computer_cleaning.php",
            "message_placeholder" => "Napr. notebook sa prehrieva pri hraní hier...",
            "message_required" => true
        ]
    ];

    // Rating (Used In The Hero)

    $average_rating = (float)$review_object->getAverageRating($connection);
    $reviews_count = (int)$review_object->countReviews($connection);

    // Checkout Prefill (Logged In User Data Or Previously Submitted Values)

    $checkout_prefill = [
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email_address" => $email_address,
        "phone_number" => $phone_number
    ];

    if(isLoggedIn()) {
        foreach(array_keys($checkout_prefill) as $field) {
            $checkout_prefill[$field] = (string)($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], $field) ?? "");
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
    <link rel="stylesheet" href="/src/css/index.css">

    <link rel="stylesheet" href="/src/css/lightbox.min.css"> <!-- https://github.com/lokesh/lightbox2/tree/dev -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"> <!-- Inter & Plus Jakarta Sans Font Styles -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script> <!-- https://releases.jquery.com/ -->
    <script src="/src/js/lightbox-plus-jquery.min.js"></script> <!-- https://github.com/lokesh/lightbox2/tree/dev -->
    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="https://widget.packeta.com/v6/www/js/library.js"></script> <!-- Packeta -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/move_up.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
    <script src="/src/ts/dist/pages/homepage/components/all_services.js" type="module"></script>
    <script src="/src/ts/dist/pages/homepage/components/reviews.js" type="module"></script>
    <script src="/src/ts/dist/pages/homepage/components/contact_form.js" type="module"></script>

    <script defer>
        // JavaScript Code

        "use strict"
        
        document.addEventListener("DOMContentLoaded", function() {
            // Secret Message

            console.log("%cPC Expert by Patrik Behul", "color: rgb(170, 255, 50); background-color: rgb(0, 0, 0)")
        })
    </script>
</head>

<body class="<?php echo $theme; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

    <?php require "../assets/partials/flash_messages.php"; ?>
    <?php require "../assets/partials/cookie_policy.php"; ?>

    <button class="move_up" type="button" title="Posunúť nahor" aria-label="Posunúť nahor"><i class="fa-solid fa-arrow-up"></i></button> <!-- https://fontawesome.com/icons/arrow-up -->

    <header class="site_header">
        <?php $is_homepage = true; ?>
        <?php require "../assets/partials/navigation.php"; ?>
    </header>

    <main>
        <section class="hero" id="top">
            <div class="hero_container">
                <div class="hero_content">
                    <a class="hero_rating" href="#reviews" title="Na základe <?php echo $reviews_count; ?> recenzií">
                        <span class="stars" aria-hidden="true">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="<?php echo $i <= round($average_rating) ? "fa-solid" : "fa-regular"; ?> fa-star"></i> <!-- https://fontawesome.com/icons/star -->

                            <?php endfor; ?>
                        </span>

                        <span><strong><?php echo number_format($average_rating, 1, ",", ""); ?></strong>&nbsp;/&nbsp;5 · <?php echo $reviews_count; ?>&nbsp;recenzií</span>
                    </a>

                    <h1>Váš počítač <span class="highlight">v&nbsp;rukách expertov</span></h1>

                    <p class="hero_lead">Stavba počítačov na mieru, odborná údržba a hĺbkové čistenie. Objednajte si službu online za pár minút a o zvyšok sa postaráme my.</p>

                    <div class="hero_actions">
                        <a class="btn btn_lg" href="#all_services">Vybrať službu <i class="fa-solid fa-arrow-right"></i></a> <!-- https://fontawesome.com/icons/arrow-right -->
                        <a class="btn btn_lg btn_secondary" href="#contact"><i class="fa-regular fa-comment"></i> Kontaktovať nás</a> <!-- https://fontawesome.com/icons/comment -->
                    </div>

                    <ul class="hero_highlights">
                        <li><i class="fa-solid fa-lock"></i> Bezpečná platba kartou</li> <!-- https://fontawesome.com/icons/lock -->
                        <li><i class="fa-solid fa-box"></i> Doručenie cez Packeta</li> <!-- https://fontawesome.com/icons/box -->
                        <li><i class="fa-solid fa-location-dot"></i> Bratislava</li> <!-- https://fontawesome.com/icons/location-dot -->
                    </ul>
                </div>

                <div class="hero_visual" aria-hidden="true">
                    <div class="hero_image">
                        <img src="/content/images/background.jpg" alt=""> <!-- https://pixabay.com/sk/photos/cpu-procesor-%C4%8Dip-z%C3%A1kladn%C3%A9-dosky-4393376/ -->
                    </div>

                    <div class="hero_card hero_card_price">
                        <span class="hero_card_icon"><i class="fa-solid fa-tag"></i></span> <!-- https://fontawesome.com/icons/tag -->

                        <div>
                            <p class="hero_card_label">Služby už od</p>
                            <p class="hero_card_value"><?php echo min(array_column($all_services, "price")); ?>&nbsp;€</p>
                        </div>
                    </div>

                    <div class="hero_card hero_card_services">
                        <?php foreach($all_services as $one_service): ?>
                            <p><i class="<?php echo $one_service["icon"]; ?>"></i> <?php echo $one_service["title"]; ?></p>

                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <section class="all_services section" id="all_services">
            <div class="container">
                <div class="section_heading">
                    <p class="eyebrow"><i class="fa-solid fa-screwdriver-wrench"></i> Naše služby</p> <!-- https://fontawesome.com/icons/screwdriver-wrench -->
                    <h2>S čím vám môžeme pomôcť?</h2>
                    <p>Jasné ceny, objednávka online za pár minút a bezpečná platba kartou.</p>
                </div>

                <div class="service_grid">
                    <?php foreach($all_services as $one_service): ?>
                        <form class="service_card <?php echo $one_service["service"]; ?>" action="create_checkout_session.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="service" value="<?php echo $one_service["service"]; ?>">

                            <article class="service_card_inner" aria-labelledby="<?php echo $one_service["service"]; ?>_card_title">
                                <div class="service_media">
                                    <img src="<?php echo $one_service["image"]; ?>" alt="<?php echo $one_service["title"]; ?>" loading="lazy">

                                    <?php if(!empty($one_service["badge"])): ?>
                                        <span class="badge service_badge"><i class="fa-solid fa-star"></i> <?php echo $one_service["badge"]; ?></span> <!-- https://fontawesome.com/icons/star -->

                                    <?php endif; ?>
                                </div>

                                <div class="service_body">
                                    <div class="service_heading">
                                        <span class="service_icon" aria-hidden="true"><i class="<?php echo $one_service["icon"]; ?>"></i></span>
                                        <h3 id="<?php echo $one_service["service"]; ?>_card_title"><?php echo $one_service["title"]; ?></h3>
                                    </div>

                                    <p class="service_summary"><?php echo $one_service["summary"]; ?></p>

                                    <ul class="service_features">
                                        <?php foreach($one_service["features"] as $one_feature): ?>
                                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> <?php echo $one_feature; ?></li> <!-- https://fontawesome.com/icons/check -->

                                        <?php endforeach; ?>
                                    </ul>

                                    <details class="service_more">
                                        <summary>Viac o službe <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary> <!-- https://fontawesome.com/icons/chevron-down -->
                                        <p><?php echo $one_service["description"]; ?></p>
                                    </details>
                                </div>

                                <div class="service_footer">
                                    <p class="service_price">
                                        <strong><?php echo $one_service["price"]; ?>&nbsp;€</strong>
                                        <span><?php echo $one_service["price_note"]; ?></span>
                                    </p>

                                    <button class="btn" type="button" aria-label="Objednať službu <?php echo $one_service["title"]; ?>" data-checkout-open>Objednať <i class="fa-solid fa-arrow-right"></i></button> <!-- https://fontawesome.com/icons/arrow-right -->
                                </div>
                            </article>

                            <?php $checkout = $one_service; ?>
                            <?php require "../assets/partials/checkout_dialog.php"; ?>
                        </form>

                    <?php endforeach; ?>
                </div>

                <ol class="order_process">
                    <li>
                        <span class="order_process_icon"><i class="fa-solid fa-hand-pointer"></i></span> <!-- https://fontawesome.com/icons/hand-pointer -->
                        <div>
                            <h3>Vyberte službu</h3>
                            <p>Zvoľte, s čím potrebujete pomôcť.</p>
                        </div>
                    </li>

                    <li>
                        <span class="order_process_icon"><i class="fa-solid fa-pen-to-square"></i></span> <!-- https://fontawesome.com/icons/pen-to-square -->
                        <div>
                            <h3>Vyplňte detaily</h3>
                            <p>Kontakt, doručenie a pár otázok.</p>
                        </div>
                    </li>

                    <li>
                        <span class="order_process_icon"><i class="fa-solid fa-credit-card"></i></span> <!-- https://fontawesome.com/icons/credit-card -->
                        <div>
                            <h3>Zaplaťte online</h3>
                            <p>Bezpečne kartou cez Stripe.</p>
                        </div>
                    </li>

                    <li>
                        <span class="order_process_icon"><i class="fa-solid fa-circle-check"></i></span> <!-- https://fontawesome.com/icons/circle-check -->
                        <div>
                            <h3>O zvyšok sa postaráme</h3>
                            <p>Ozveme sa vám s ďalším postupom.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section class="main <?php echo $theme; ?>">
            <div class="about_us <?php echo $theme; ?>" id="about_us">
                <h2>O&nbsp;nás</h2>
                <p>Sme tím nadšencov a odborníkov na počítačový hardvér, ktorí sa venujú profesionálnej stavbe, servisu a upgradu počítačov. Našim cieľom je poskytovať spoľahlivé a kvalitné služby, vďaka ktorým si môžete užívať bezproblémový a výkonný počítač, či už na prácu, hranie hier alebo kreatívnu tvorbu.<br>
                Či už potrebujete nový počítač na mieru, odbornú diagnostiku, výmenu komponentov, čistenie alebo zrýchlenie vášho zariadenia, sme tu pre vás. Ku každému zákazníkovi pristupujeme individuálne a hľadáme riešenia, ktoré najlepšie vyhovujú jeho potrebám a rozpočtu.<br>
                Dôverujte odborníkom a nechajte si poradiť od ľudí, ktorí počítačom skutočne rozumejú. S nami získate nielen profesionálnu službu, ale aj cenné rady, ktoré vám pomôžu naplno využiť potenciál vášho zariadenia.<br>
                Kontaktujte nás ešte dnes a nechajte svoj počítač v rukách profesionálov!</p>
            </div>
            
            <div class="faq">
                <h2>Časté&nbsp;otázky&nbsp;(FAQ)</h2>
                <h3>Ako dlho trvá zostavenie počítača?</h3>
                <p>Závisí to podľa dostupnosti komponentov a zložitosti zostavy. Ak máte všetky komponenty pripravené, zostavenie zvyčajne trvá 1 – 2 dni. Ak vám pomáhame aj s výberom komponentov, môže to trvať niekoľko dní až týždeň.</p>
                <h3>Ako to je s dopravou?</h3>
                <p>Po vašom schválení a objednaní potrebných komponentov pre vaše zariadenie, alebo pri odoslaní vašich komponentov či zariadenia, cenu dopravy dopravnou spoločnosťou do 5 € hradíme my. Ak bude cena dopravy vyššia, rozdiel hradíte vy. O všetkých nákladoch vás budeme vopred informovať.</p>
                <h3>Ako to je s detailnou dohodou ohľadom danej služby?</h3>
                <p>Stačí nás kontaktovať, ideálne telefonicky, aby sme mohli rýchlo a efektívne prediskutovať vaše požiadavky. Či už ide o výber komponentov, údržbu, čistenie alebo inú službu, radi vám poradíme a spoločne nájdeme najlepšie riešenie. Po dohode zabezpečíme objednanie potrebných komponentov, dohodneme sa na termíne realizácie a spôsobe prevzatia vášho zariadenia.</p>
                <h3>Ako to je s platbou?</h3>
                <p>Pred objednaním komponentov na stavbu počítača alebo pred odovzdaním vášho zariadenia na údržbu či čistenie je platba za službu a zakúpené komponenty potrebná vopred. Po dokončení práce vám odovzdáme všetky faktúry, doklady o kúpe a originálne balenia komponentov.</p>
            </div>
        </section>

        <section class="reviews <?php echo $theme; ?>" id="reviews" data-logged_in_user_id="<?php echo json_encode($user_id); ?>">
            <div class="user_review">
                <?php if($review_object->getUserReview($connection, $user_id) !== null and $review_object->getUserReview($connection, $user_id) !== false): ?>
                    <div class="review">
                        <?php if($review_object->getUserReview($connection, $user_id)["status"] === "pending"): ?>
                            <p class="pending_review">Vaša recenzia čaká na kontrolu</p>

                        <?php endif; ?>

                        <div class="reviewer">
                            <?php if(!empty($user_object->getUserInformation($connection, $review_object->getUserReview($connection, $user_id)['user_id'], 'profile_picture_name')) or !empty($user_object->getUserInformation($connection, $review_object->getUserReview($connection, $user_id)['user_id'], 'google_profile_picture_url'))): ?>
                                <img 
                                    class="profile_picture" 
                                    src="
                                        <?php if(!empty($user_object->getUserInformation($connection, $review_object->getUserReview($connection, $user_id)['user_id'], 'google_profile_picture_url')) and empty($user_object->getUserInformation($connection, $review_object->getUserReview($connection, $user_id)['user_id'], 'profile_picture_name'))): ?>
                                            <?php echo $user_object->getUserInformation($connection, $user_id, 'google_profile_picture_url'); ?>

                                        <?php else: ?>
                                            /content/uploads/<?php echo $user_id; ?>/<?php echo $user_object->getUserInformation($connection, $user_id, 'profile_picture_name'); ?>

                                        <?php endif; ?>
                                    "
                                    alt="Môj účet"
                                >

                            <?php else: ?>
                                <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->

                            <?php endif; ?>

                            <div class="user_info">
                                <p class="name">
                                    <?php echo htmlspecialchars($user_object->getUserInformation($connection, $review_object->getUserReview($connection, $user_id)["user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>
                                    <?php echo htmlspecialchars($user_object->getUserInformation($connection, $review_object->getUserReview($connection, $user_id)["user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>
                                </p>

                                <p class="date">
                                    -

                                    <?php echo htmlspecialchars(date("j.n.Y", strtotime($review_object->getUserReview($connection, $user_id)["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                                    <?php if($review_object->getUserReview($connection, $_SESSION["logged_in_user_id"])["edited"] === "yes"): ?>
                                        (upravená)

                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= $review_object->getUserReview($connection, $user_id)["rating"]): ?>
                                    <div class="star">
                                        <i class="fa-solid fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                    </div>

                                <?php else: ?>
                                    <div class="star">
                                        <i class="fa-regular fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                    </div>

                                <?php endif; ?>

                            <?php endfor; ?>
                        </div>

                        <a class="edit_review" href="./upravit-recenziu" title="Upraviť recenziu">Upraviť recenziu</a>

                        <p><?php echo htmlspecialchars($review_object->getUserReview($connection, $user_id)["review"], ENT_QUOTES, "UTF-8"); ?></p>
                    </div>

                <?php else: ?>
                    <script src="/src/ts/dist/pages/homepage/components/write_review.js" type="module"></script>

                    <!-- reCAPTCHA V3 -->
                    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($_ENV["RECAPTCHA_SITE_KEY"]); ?>&badge=inline"></script>
                    
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

                                grecaptcha.execute("<?php echo htmlspecialchars($_ENV["RECAPTCHA_SITE_KEY"]); ?>", {action: "submit"}).then(function(token) {
                                    const g_recaptcha_response = document.querySelectorAll("#g-recaptcha-response")[0]

                                    g_recaptcha_response.value = token
                                    event.target.submit()
                                })
                            })
                        })
                    </script>

                    <form class="write_review" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <h2>Moja&nbsp;recenzia</h2>

                        <div class="header">
                            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                                <div class="reviewer">
                                    <?php if(!empty($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "profile_picture_name")) or !empty($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'))): ?>
                                        <img 
                                            class="profile_picture" 
                                            src="
                                                <?php if(!empty($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url')) and empty($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'))): ?>
                                                    <?php echo $user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'google_profile_picture_url'); ?>
    
                                                <?php else: ?>
                                                    /content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo $user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'profile_picture_name'); ?>
    
                                                <?php endif; ?>
                                            "
                                            alt="Môj účet"
                                        >
    
                                    <?php else: ?>
                                        <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->
    
                                    <?php endif; ?>
    
                                    <p class="name">
                                        <?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>
                                        <?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION["logged_in_user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>
                                    </p>
                                </div>
    
                            <?php else: ?>
                                <div class="reviewer">
                                    <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->
                                    <p class="name">Anonymný zákazník</p>
                                </div>
    
                            <?php endif; ?>
    
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <div class="star" data-rating="<?php echo $i; ?>">
                                        <i class="fa-regular fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                    </div>
    
                                <?php endfor; ?>
    
                                <input class="rating" type="hidden" name="rating" value="">
                            </div>
                        </div>

                        <p class="form_report valid"></p>

                        <textarea type="textarea" name="review" placeholder="Zanechajte nám vaše hodnotenie" max-length="250"><?php echo htmlspecialchars($review, ENT_QUOTES, "UTF-8"); ?></textarea>

                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                        
                        <input type="hidden" name="recaptcha_write_review_submit">
                        <button class="write_review_submit" type="submit" name="write_review_submit">Zverejniť recenziu</button>

                        <div class="grecaptcha_badge_container"></div>
                    </form>

                <?php endif; ?>
            </div>

            <?php if($review_object->countReviews($connection, $user_id) > 0): ?>
                <div class="all_reviews">
                    <?php foreach($review_object->getAllReviews($connection, $user_id) as $one_review): ?>
                        <div class="review">
                            <?php if($one_review["user_id"] !== null): ?>
                                <div class="reviewer">
                                    <?php if(!empty($user_object->getUserInformation($connection, $one_review['user_id'], 'profile_picture_name')) or !empty($user_object->getUserInformation($connection, $one_review['user_id'], 'google_profile_picture_url'))): ?>
                                        <img 
                                            class="profile_picture" 
                                            src="
                                                <?php if(!empty($user_object->getUserInformation($connection, $one_review['user_id'], 'google_profile_picture_url')) and empty($user_object->getUserInformation($connection, $one_review['user_id'], 'profile_picture_name'))): ?>
                                                    <?php echo $user_object->getUserInformation($connection, $user_id, 'google_profile_picture_url'); ?>

                                                <?php else: ?>
                                                    /content/uploads/<?php echo $user_id; ?>/<?php echo $user_object->getUserInformation($connection, $user_id, 'profile_picture_name'); ?>

                                                <?php endif; ?>
                                            "
                                            alt="Môj účet"
                                        >

                                    <?php else: ?>
                                        <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->

                                    <?php endif; ?>

                                    <div class="user_info">
                                        <p class="name">
                                            <?php echo htmlspecialchars($user_object->getUserInformation($connection, $one_review["user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>
                                            <?php echo htmlspecialchars($user_object->getUserInformation($connection, $one_review["user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>
                                        </p>

                                        <p class="date">
                                            -

                                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                                            <?php if($one_review["edited"] === "yes"): ?>
                                                (upravená)

                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= $one_review["rating"]): ?>
                                            <div class="star">
                                                <i class="fa-solid fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                            </div>
    
                                        <?php else: ?>
                                            <div class="star">
                                                <i class="fa-regular fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                            </div>
    
                                        <?php endif; ?>
                                        
                                    <?php endfor; ?>
                                </div>

                                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php else: ?>
                                <div class="reviewer">
                                    <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->

                                    <div class="user_info">
                                        <p class="name">Anonymný zákazník</p>

                                        <p class="date">
                                            -
                                            
                                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= $one_review["rating"]): ?>
                                            <div class="star">
                                                <i class="fa-solid fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                            </div>
    
                                        <?php else: ?>
                                            <div class="star">
                                                <i class="fa-regular fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                                            </div>
    
                                        <?php endif; ?>
                                        
                                    <?php endfor; ?>
                                </div>

                                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

            <?php if($review_object->countReviews($connection, $user_id) > 5): ?>
                <div class="buttons">
                    <button class="show_more" title="Zobraziť viac">Zobraziť viac</button>
                    <button class="show_less hidden" title="Zobraziť menej">Zobraziť menej</button>
                </div>

            <?php endif; ?>
        </section>

        <section class="contact <?php echo $theme; ?>" id="contact">
            <!-- reCAPTCHA V3 -->
            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($_ENV["RECAPTCHA_SITE_KEY"]); ?>&badge=inline"></script>
            
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
                        
                        grecaptcha.execute("<?php echo htmlspecialchars($_ENV["RECAPTCHA_SITE_KEY"]); ?>", {action: "submit"}).then(function(token) {
                            const g_recaptcha_response = document.querySelectorAll("#g-recaptcha-response")[1] ?? document.querySelectorAll("#g-recaptcha-response")[0]

                            g_recaptcha_response.value = token
                            event.target.submit()
                        })
                    })
                })
            </script>

            <form class="contact_form <?php echo $theme; ?>" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                <h2>Kontakt</h2>

                <!-- <p>
                    V prípade akýchkoľvek otázok, či upozornení nás kontaktujte a my sa vám pokúsime v krátkom čase odpovedať. Budeme radi za akúkoľvek spätnú väzbu od Vás.
                    <?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?>
                        <?php echo "Údaje vo formulári boli automaticky vyplnené na základe údajov vášho účtu."; ?>
    
                    <?php endif; ?>
                </p> -->

                <div class="name_container">
                    <input 
                        type="text"
                        name="first_name"
                        placeholder="Zadajte vaše meno"
                        autocomplete="given-name"
                        value="<?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?><?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'first_name'), ENT_QUOTES, 'UTF-8'); ?><?php else: ?><?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>"
                        required
                    >
    
                    <input 
                        type="text"
                        name="last_name"
                        placeholder="Zadajte vaše priezvisko"
                        autocomplete="family-name"
                        value="<?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?><?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'last_name'), ENT_QUOTES, 'UTF-8'); ?><?php else: ?><?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>"
                        required
                    >
                </div>

                <input 
                    type="email"
                    name="email_address"
                    placeholder="Zadajte váš e-mail"
                    autocomplete="email"
                    value="<?php if(isset($_SESSION['is_logged_in']) and $_SESSION['is_logged_in'] === true): ?><?php echo htmlspecialchars($user_object->getUserInformation($connection, $_SESSION['logged_in_user_id'], 'email_address'), ENT_QUOTES, 'UTF-8'); ?><?php else: ?><?php echo htmlspecialchars($email_address, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>"
                    required
                >

                <select class="select_menu" name="subject">
                    <option value="">Zadajte predmet vašej správy</option>

                    <optgroup label="Naše služby">
                        <option value="build_computer">Stavba počítača</option>
                        <option value="computer_maintenance">Údržba počítača</option>
                        <option value="computer_cleaning">Čistenie počítača</option>
                    </optgroup>
                    
                    <optgroup label="Ostatné">
                        <option value="question">Otázka</option>
                        <option value="suggestion">Návrh na zlepšenie</option>
                        <option value="bug">Nahlásenie chyby</option>
                    </optgroup>
                </select>
                
                <div class="textarea_container">
                    <textarea type="textarea" name="message" placeholder="Zadajte vašu správu" max-length="250" required><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></textarea>

                    <div class="attachment_container">
                        <p class="attachment_report <?php echo $theme; ?>"><?php echo htmlspecialchars($attachment_message, ENT_QUOTES, "UTF-8"); ?></p>
    
                        <label class="attachment" for="attachment"><i class="fa-solid fa-paperclip" title="priložiť prílohu..."></i></label>
    
                        <input class="attachment_input" id="attachment" type="file" name="attachment[]" accept=".jpg, .jpeg, .png, .gif, .webp, .bmp, .tiff, .tif, .mp4, .mov, .avi, .mkv, .webm, .hevc, .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .txt, .rtf, .odt, .ods, .odp" multiple>
                    </div>
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                
                <input type="hidden" name="recaptcha_contact_form_submit">
                <button class="contact_form_submit" type="submit" name="contact_form_submit">Poslať správu</button>

                <div class="grecaptcha_badge_container"></div>
            </form>
        </section>

        <div class="map">
            <iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d170411.37760859914!2d16.950700206516654!3d48.135737106169756!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x476c89360aca6197%3A0x631f9b82fd884368!2sBratislava!5e0!3m2!1sen!2ssk!4v1784274678048!5m2!1sen!2ssk" allowfullscreen="yes" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </main>
    
    <footer>
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>
