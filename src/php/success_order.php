<?php
    // Debug
        
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    // PHP Code

    require "../assets/functions.php";
    require "../classes/Database.php";
    require "../classes/Order.php";

    $database_object = new Database(); // Initializes The Database Methods
    $order_object = new Order(); // Initializes The User Methods

    session_start();

    $connection = $database_object->connectionDB(); // Connects To The DB

    $session_id = $_GET["session_id"] ?? null; // Gets The Order Session ID
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Úspešná objednávka</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/forms.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="/src/ts/dist/components/cookie_policy.js" type="module"></script>
    <script src="/src/ts/dist/components/navigation_bar.js" type="module"></script>
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
            <div class="navigation_container">
                <button class="hamburger" (click)="toggleMenu()">
                    <i class="fa-solid" [class.fa-bars]="!is_menu_open" [class.fa-xmark]="is_menu_open"></i> <!-- https://fontawesome.com/icons/bars / https://fontawesome.com/icons/magnifying-glass -->
                </button>
            
                <ul [class.open]="is_menu_open">
                    <li class="home"><a href="./domov">DOMOV</a></li>
                </ul>
            </div>
        </nav>

        <div class="cookie_policy">
            <img src="/content/images/cookie.png" alt="Cookie"> <!-- https://www.flaticon.com/free-icon/cookie_1047711 -->
            <h2>Cookie Policy</h2>
            <p>Táto&nbsp;stránka&nbsp;používa&nbsp;súbory&nbsp;cookies.</p>
            <button class="accept_all">ROZUMIEM</button>
        </div>
    </header>

    <main>
        <section class="success_order">
            <?php if($session_id): ?>
                <?php if($order_object->getOrderInformation($connection, $session_id, "stripe_intent_id")): ?>
                    <h2>Ďakujeme za objednávku <?php echo htmlspecialchars($order_object->getOrderInformation($connection, $session_id, "first_name"), ENT_QUOTES, "UTF-8") ?>!</h2>

                    <?php if($order_object->getOrderInformation($connection, $session_id, "status") === "PENDING"): ?>
                        <p>Čaká sa na platbu.</p>

                    <?php elseif($order_object->getOrderInformation($connection, $session_id, "status") === "PAID"): ?>
                        <p>Platba prebehla úspešne!</p>

                    <?php elseif($order_object->getOrderInformation($connection, $session_id, "status") === "PREPARING"): ?>
                        <p>Spracovávame objednávku.</p>

                    <?php elseif($order_object->getOrderInformation($connection, $session_id, "status") === "DELIVERING"): ?>
                        <p>Objednávka je na ceste!</p>

                    <?php elseif($order_object->getOrderInformation($connection, $session_id, "status") === "COMPLETED"): ?>
                        <p>Objednávka bola doručená!</p>

                    <?php elseif($order_object->getOrderInformation($connection, $session_id, "status") === "CANCELLED"): ?>
                        <p style="color: #df3535;">Objednávka bola zrušená.</p>

                    <?php endif; ?>

                    <p><?php echo $order_object->getOrderInformation($connection, $session_id, "service"); ?> - <?php echo $order_object->getOrderInformation($connection, $session_id, "price_at_purchase") / 100; ?><span>€</span></p>

                <?php else: ?>
                    <h2>Objednávka sa nenašla</h2>

                <?php endif; ?>
            
            <?php else: ?>
                <h2>Objednávka sa nenašla</h2>

            <?php endif; ?>

            <a class="back" href="/domov">Späť domov</a>
        </section>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>