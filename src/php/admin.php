<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/admin_functions.php";

    session_start();
    
    $connection = connectionDB();

    // Theme

    $theme = "light"; // Default Theme

    if(isset($_COOKIE["theme"])) {
        $theme = $_COOKIE["theme"];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Administrácia</title>

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo $theme; ?>">
    <header>
        <nav class="navigation_bar">
            <ul>
                <li class="navigation_bar_item home">
                    <a href="../index">DOMOV</a>
                </li>

                <div class="navigation_icon">
                    <i class="fa-solid fa-bars"></i>
                    <!-- <i class="fa-solid fa-xmark"></i> -->
                </div>
            </ul>
        </nav>

        <!-- <div class="header_box">
            <h1><a href="./admin">Administrácia</a></h1>
        </div> -->
    </header>

    <main class="<?php echo $theme; ?>" style="padding: 10px 20px;">
        <?php if(isset($_SESSION["logged_in_user_id"]) and getUserRole($connection, $_SESSION["logged_in_user_id"]) === "admin"): ?>
            <section class="users">
                <h2>Používatelia</h2>

                <?php foreach(allUsers($connection, $_SESSION["logged_in_user_id"]) as $one_user):  ?>
                    <div class="user <?php echo $theme; ?>">
                        <div class="basic_info">
                            <?php if(!empty($one_user["profile_picture_name"])): ?>
                                <img
                                    class="profile_picture"
                                    src="/content/uploads/<?php echo htmlspecialchars($one_user['id'], ENT_QUOTES, 'UTF-8'); ?>/<?php echo htmlspecialchars($one_user['profile_picture_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                >

                            <?php else: ?>
                                <img class="profile_picture" src="/content/images/user.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

                            <?php endif; ?>

                            <p class="user_name">
                                <?php echo htmlspecialchars($one_user["first_name"], ENT_QUOTES, "UTF-8"); ?>

                                <?php echo htmlspecialchars($one_user["last_name"], ENT_QUOTES, "UTF-8"); ?>

                                <span class="user_role"
                                    <?php if($one_user["role"] === "admin"): ?>
                                        style="color: rgb(250, 50, 50);"

                                    <?php endif; ?>
                                >
                                    -

                                    <?php echo htmlspecialchars($one_user["role"], ENT_QUOTES, "UTF-8"); ?>
                                </span>
                            </p>
                        </div>

                        <div class="other_info">
                            <div>
                                <h3>Kontaktné údaje</h3>

                                <p><strong>E-mail: </strong><?php echo htmlspecialchars($one_user["email_address"], ENT_QUOTES, "UTF-8"); ?></p>
                                <p><strong>Telefónne číslo: </strong><?php echo htmlspecialchars($one_user["phone_number"], ENT_QUOTES, "UTF-8"); ?></p>
                            </div>

                            <div>
                                <h3>Ostatné údaje</h3>

                                <p><strong>ID používateľa: </strong><?php echo htmlspecialchars($one_user["id"], ENT_QUOTES, "UTF-8"); ?></p>

                                <p><strong>Vytvorenie účtu: </strong><?php echo htmlspecialchars(date("j.n.Y", strtotime($one_user["creation_time"])), ENT_QUOTES, "UTF-8"); ?></p>
                            </div>
                        </div>
                        
                        <p class="form_report_paragraph"></p>

                        <div class="delete_user_container">
                            <p>Chcete nenávratne odstrániť tento účet?</p>
                            
                            <label for="delete_user">
                                <img class="delete_user" title="Odstrániť účet" src="/content/images/delete_account.png" alt="Odstrániť účet"> <!-- https://www.flaticon.com/free-icon/delete_11502978 -->
                            </label>

                            <input type="checkbox" name="delete_user" id="delete_user" value="yes">
                        </div>
                    </div>

                <?php endforeach; ?>
            </section>

            <br>

            <div class="error_log">
                <h2>Výpis&nbsp;chýb</h2>

                <?php foreach(get_error_log($connection) as $one_error): ?>
                    <p>
                        <strong>Chyba:</strong>
                        <br>
                        [<?php echo htmlspecialchars($one_error["error_time"], ENT_QUOTES, "UTF-8"); ?>] - 
                        <?php echo htmlspecialchars($one_error["message"], ENT_QUOTES, "UTF-8"); ?>
                        <br><br>
                        <strong>Podrobnosti:</strong>
                        <br>
                        <strong>User ID: </strong><?php echo htmlspecialchars($one_error["user_id"], ENT_QUOTES, "UTF-8"); ?>
                        <br>
                        <strong>IP Address: </strong><?php echo htmlspecialchars($one_error["ip_address"], ENT_QUOTES, "UTF-8"); ?>
                        <br>
                        <strong>Referer: </strong><?php echo htmlspecialchars($one_error["referer"], ENT_QUOTES, "UTF-8"); ?>
                        <br>
                        <strong>Request Method: </strong><?php echo htmlspecialchars($one_error["request_method"], ENT_QUOTES, "UTF-8"); ?>
                        <br>
                        <strong>User Agent: </strong><?php echo htmlspecialchars($one_error["user_agent"], ENT_QUOTES, "UTF-8"); ?>
                        <br>
                        <strong>Server IP: </strong><?php echo htmlspecialchars($one_error["server_ip"], ENT_QUOTES, "UTF-8"); ?>
                        <br>
                        <strong>Post Data: </strong><?php var_dump(json_decode($one_error["post_data"])); ?>
                        <hr></hr>
                    </p>

                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p>K obsahu tejto stránky nemáte povolený prístup.</p>
            <a href="./index" title="Domov" target="_self">Späť na úvodnú stránku.</a>

        <?php endif; ?>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>

    <script>
        // JavaScript Code

        "use strict"

        // Delete Account Warning

        const delete_user_input = document.querySelector("#delete_user")
        const delete_user_image = document.querySelector(".delete_user")
        const form_report_paragraph = document.querySelector(".form_report_paragraph")

        delete_user_input.addEventListener("click", function(event) {
            if(delete_user_input.checked) {
                delete_user_image.style.opacity = 1

                form_report_paragraph.textContent = "Účet bude odstránený."
                form_report_paragraph.classList.add("invalid")
            }

            else {
                delete_user_image.style.opacity = 0.8

                form_report_paragraph.textContent = ""
            }
        })
    </script>

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>