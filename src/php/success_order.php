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
    $order = $order_object->getOrderInformation($connection, $session_id); // Gets The Order Information

    $attachments = json_decode($order["attachments"] ?? "[]", true); // Gets The Order Attachments
    $image_extensions = ["jpg", "jpeg", "png", "gif", "webp", "bmp", "tiff", "tif"]; // Defines The Image Extensions
    $video_extensions = ["mp4", "mov", "avi", "mkv", "webm", "hevc"]; // Defines The Video Extensions
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
    <link rel="stylesheet" href="/src/css/success_order.css">
    
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
                <?php if($order): ?>
                    <h2>Ďakujeme za objednávku <?php echo htmlspecialchars($order["first_name"], ENT_QUOTES, "UTF-8") ?>!</h2>

                    <?php if($order["status"] === "PENDING"): ?>
                        <h3 class="status">Čaká sa na platbu.</h3>

                    <?php elseif($order["status"] === "PAID"): ?>
                        <h3 class="status">Platba prebehla úspešne!</h3>

                    <?php elseif($order["status"] === "PREPARING"): ?>
                        <h3 class="status">Spracovávame objednávku.</h3>

                    <?php elseif($order["status"] === "DELIVERING"): ?>
                        <h3 class="status">Objednávka je na ceste!</h3>

                    <?php elseif($order["status"] === "COMPLETED"): ?>
                        <h3 class="status">Objednávka bola doručená!</h3>

                    <?php elseif($order["status"] === "CANCELLED"): ?>
                        <h3 class="status" style="color: #df3535;">Objednávka bola zrušená.</h3>

                    <?php endif; ?>

                    <h3 class="service"><?php if($order["service"] === "computer_building"): ?>Stavba počítača<?php elseif($order["service"] === "computer_maintenance"): ?>Údržba počítača<?php elseif($order["service"] === "computer_cleaning"): ?>Čistenie počítača<?php endif; ?> - <?php echo htmlspecialchars($order["price_at_purchase"], ENT_QUOTES, "UTF-8") / 100; ?><span>€</span></h3>

                    <p class="title">Podrobnosti:</p>

                    <div class="service_details">
                        <?php if($order["service"] === "computer_building"): ?>
                            <p><strong>Maximálna cena zoztavy:</strong> <?php echo htmlspecialchars($order["max_price"], ENT_QUOTES, "UTF-8"); ?><span>€</span></p>
                            <p><strong>Výrobca procesora:</strong> <?php if($order["cpu"] === "amd"): ?>AMD<?php elseif($order["cpu"] === "intel"): ?>Intel<?php endif; ?></p>
                            <p><strong>Výrobca grafickej karty:</strong> <?php if($order["gpu"] === "nvidia"): ?>NVIDIA<?php elseif($order["gpu"] === "amd"): ?>AMD<?php endif; ?></p>
                            <p><strong>Kapacita operačnej pamäte RAM:</strong> <?php echo htmlspecialchars($order["ram"], ENT_QUOTES, "UTF-8"); ?>GB</p>
                            <p><strong>Kapacita disku:</strong> <?php echo htmlspecialchars($order["space"], ENT_QUOTES, "UTF-8"); ?>GB</p>
                            <p><strong>Typ disku:</strong> <?php if($order["disk_type"] === "m2"): ?>M.2<?php elseif($order["disk_type"] === "ssd"): ?>SSD<?php elseif($order["disk_type"] === "hdd"): ?>HDD<?php endif; ?></p>
                            <p><strong>Dodatočná správa:</strong> <?php if($order["message"]): ?><?php echo htmlspecialchars($order["message"], ENT_QUOTES, "UTF-8"); ?><?php else: ?>Žiadna.<?php endif; ?></p>

                            <?php if(!empty($attachments)): ?>
                                <?php foreach($attachments as $one_attachment): ?>
                                    <div class="attachment">
                                        <?php if(in_array(strtolower(pathinfo($one_attachment["file_name"], PATHINFO_EXTENSION)), $image_extensions)): ?>
                                            <img 
                                                src="<?php echo '/content/uploads/attachments/' . htmlspecialchars($one_attachment['file_name'], ENT_QUOTES, "UTF-8"); ?>" 
                                                title="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                                alt="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                            >

                                        <?php elseif(in_array(strtolower(pathinfo($one_attachment["file_name"], PATHINFO_EXTENSION)), $video_extensions)): ?>
                                            <video 
                                                src="<?php echo '/content/uploads/attachments/' . htmlspecialchars($one_attachment['file_name'], ENT_QUOTES, "UTF-8"); ?>" 
                                                title="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                                alt="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                                controls
                                                muted
                                            >
                                            </video>

                                        <?php else: ?>
                                            <a 
                                                href="<?php echo '/content/uploads/attachments/' . htmlspecialchars($one_attachment['file_name'], ENT_QUOTES, "UTF-8"); ?>" 
                                                title="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                                alt="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                                target="_blank"
                                            >

                                        <?php endif; ?>
                                    </div>

                                <?php endforeach; ?>
                            <?php endif; ?>
    
                        <?php elseif($order["service"] === "computer_maintenance"): ?>
                            <?php
                                // Stores The Problems Labels
                                $problems_labels = [
                                    "overheating" => "Prehrievanie",
                                    "noise" => "Nadmerný hluk",
                                    "shutdowns" => "Samovoľné vypínanie"
                                ];
                                
                                // Stores The Software Services Labels
                                $software_services_labels = [
                                    "optimization" => "Optimalizácia",
                                    "reinstall" => "Reinštalácia operačného systému"
                                ];
    
                                // Translates The Problems
                                $problems_list = [];
    
                                if(!empty($order["problems"])) {
                                    $raw_problems = explode(", ", $order["problems"]);
    
                                    foreach($raw_problems as $key) {
                                        $problems_list[] = $problems_labels[$key] ?? $key;
                                    }
                                }
    
                                // Translates The Software Services
                                $software_services_list = [];
    
                                if(!empty($order["software_services"])) {
                                    $raw_software_services = explode(", ", $order["software_services"]);
    
                                    foreach($raw_software_services as $key) {
                                        $software_services_list[] = $software_services_labels[$key] ?? $key;
                                    }
                                }
                            ?>
    
                            <p><strong>Zariadenie:</strong> <?php if($order["computer_maintenance_device_type"] === "desktop_pc" || $order["computer_maintenance_device_type"] === "desktop_pc_2"): ?>Stolný počítač<?php elseif($order["computer_maintenance_device_type"] === "notebook"): ?>Notebook<?php endif; ?></p>
                            <p><strong>Problémy:</strong> <?php if($problems_list): ?><?php echo htmlspecialchars(implode(", ", $problems_list), ENT_QUOTES, "UTF-8"); ?><?php else: ?>Neboli uvedené.<?php endif; ?></p>
                            <p><strong>Softvérové služby:</strong> <?php if($software_services_list): ?><?php echo htmlspecialchars(implode(", ", $software_services_list), ENT_QUOTES, "UTF-8"); ?><?php else: ?>Neboli uvedené.<?php endif; ?></p>
                            <p><strong>Popis problému:</strong> <?php echo htmlspecialchars($order["message"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php if(!empty($attachments)): ?>
                                <?php foreach($attachments as $one_attachment): ?>
                                    <img 
                                        class="attachment"
                                        src="<?php echo '/content/uploads/attachments/' . htmlspecialchars($one_attachment['file_name'], ENT_QUOTES, "UTF-8"); ?>" 
                                        title="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                        alt="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                    >

                                <?php endforeach; ?>
                            <?php endif; ?>
    
                        <?php elseif($order["service"] === "computer_cleaning"): ?>
                            <p><strong>Zariadenie:</strong> <?php if($order["computer_cleaning_device_type"] === "desktop_pc" || $order["computer_cleaning_device_type"] === "desktop_pc_2"): ?>Stolný počítač<?php elseif($order["computer_cleaning_device_type"] === "notebook"): ?>Notebook<?php endif; ?></p>
                            <p><strong>Posledné čistenie:</strong> <?php if($order["last_cleaning"] === "less_than_year"): ?>Maximálne pred 1 rokom<?php elseif($order["last_cleaning"] === "two_years_ago"): ?>Maximálne pred 2 rokomi<?php elseif($order["last_cleaning"] === "never"): ?>Viac ako 2 roky alebo nikdy<?php endif; ?></p>
                            <p><strong>Prepastovať grafickú kartu:</strong> <?php if($order["thermal_paste_reapply"] === true): ?>Áno<?php else: ?>Nie<?php endif; ?></p>
                            <p><strong>Viac informácií:</strong> <?php echo htmlspecialchars($order["message"], ENT_QUOTES, "UTF-8"); ?></p>

                            <?php if(!empty($attachments)): ?>
                                <?php foreach($attachments as $one_attachment): ?>
                                    <img 
                                        class="attachment"
                                        src="<?php echo '/content/uploads/attachments/' . htmlspecialchars($one_attachment['file_name'], ENT_QUOTES, "UTF-8"); ?>" 
                                        title="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                        alt="<?php echo 'Príloha ' . htmlspecialchars($one_attachment['original_name'], ENT_QUOTES, "UTF-8"); ?>"
                                    >

                                <?php endforeach; ?>
                            <?php endif; ?>
    
                        <?php endif; ?>
                    </div>

                    <div class="delivery_details">
                        <?php if($order["shipping_method"] === "packeta"): ?>
                            <p><strong>Doručenie do:</strong> <?php echo htmlspecialchars($order["branch_name"], ENT_QUOTES, "UTF-8"); ?></p>

                        <?php endif; ?>
                        
                        <p><?php echo htmlspecialchars($order["address"], ENT_QUOTES, "UTF-8"); ?></p>
                        <p><?php echo htmlspecialchars($order["postal_code"], ENT_QUOTES, "UTF-8"); ?> <?php echo htmlspecialchars($order["city"], ENT_QUOTES, "UTF-8"); ?></p>
                    </div>

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