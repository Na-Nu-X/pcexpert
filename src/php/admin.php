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

    // Logged In User ID From Session

    $user_id = $_SESSION["logged_in_user_id"] ?? null;

    // Admin Form After Submitting

    if($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST["admin_form_submit"])) {
        // Delete Error

        foreach(getErrorLog($connection) as $one_error) {
            if(isset($_POST["delete_error_log_checkbox_" . $one_error["error_id"]]) and $_POST["delete_error_log_checkbox_" . $one_error["error_id"]] === "yes") {
                deleteErrorLogById($connection, $one_error["error_id"]);
            }
        }
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
                <li class="navigation_bar_item">
                    <a href="../index">DOMOV</a>
                </li>

                <li class="navigation_bar_item scroll_users">
                    <a href="#">POUŽÍVATELIA</a>
                </li>

                <li class="navigation_bar_item scroll_error_log">
                    <a href="#">PROTOKOLY CHÝB</a>
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
            <form class="admin_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <section class="users">
                    <h2>Používatelia</h2>

                    <div class="all_users">
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
                    </div>

                    <?php if(countUsers($connection, $user_id) > 5): ?>
                        <button class="show_more show_more_users" type="button" title="Zobraziť viac">Zobraziť viac</button>
                        <button class="show_less show_less_users" type="button" title="Zobraziť menej">Zobraziť menej</button>

                    <?php endif; ?>
                </section>

                <section class="error_log">
                    <h2>Protokoly&nbsp;chýb</h2>
                    
                    <div class="all_errors">
                        <?php foreach(getErrorLog($connection) as $one_error): ?>
                            <div class="error" data-error_id="<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <p>
                                    <strong>[<?php echo htmlspecialchars($one_error["error_time"], ENT_QUOTES, "UTF-8"); ?>]</strong> - 
                                    <?php echo htmlspecialchars($one_error["message"], ENT_QUOTES, "UTF-8"); ?>
                                </p>

                                <div class="error_details"></div>

                                <button class="show_error_details" type="button" title="Podrobnosti">Podrobnosti</button>
                                <button class="hide_error_details" type="button" title="Skryť">Skryť</button>

                                <label class="delete_error_log" for="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>">Vymazať</label>
                                <label class="cancel_delete_error_log" for="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>">Zrušiť</label>

                                <input 
                                    class="delete_error_log_checkbox"
                                    type="checkbox" 
                                    name="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    id="delete_error_log_checkbox_<?php echo htmlspecialchars($one_error['error_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    value="yes"
                                >
                            </div>

                        <?php endforeach; ?>
                    </div>

                    <?php if(countErrors($connection) > 5): ?>
                        <button class="show_more show_more_errors" type="button" title="Zobraziť viac">Zobraziť viac</button>
                        <button class="show_less show_less_errors" type="button" title="Zobraziť menej">Zobraziť menej</button>

                    <?php endif; ?>
                </section>

                <button class="admin_form_submit" type="submit" name="admin_form_submit">Uložiť</button>
            </form>

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

        // Show More / Show Less Users

        let offset = 0
        const limit = 5

        document.addEventListener("DOMContentLoaded", function(event) {
            const user_id = <?php echo json_encode($user_id); ?>

            const all_users = document.querySelector(".all_users")
            const show_more_users = document.querySelector(".show_more_users")
            const show_less_users = document.querySelector(".show_less_users")

            show_more_users.addEventListener("click", function(event) {
                offset += limit

                fetch("all_users.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `offset=${offset}&user_id=${encodeURIComponent(user_id)}`
                })
                .then(response => response.text())
                .then(data => {
                    all_users.insertAdjacentHTML("beforeend", data)
                    show_more_users.style.display = "none"
                    show_less_users.style.display = "inline-block"
                })
            })

            show_less_users.addEventListener("click", function(event) {
                offset = 0

                fetch("all_users.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `offset=${offset}&user_id=${encodeURIComponent(user_id)}`
                })
                .then(response => response.text())
                .then(data => {
                    all_users.innerHTML = data
                    show_less_users.style.display = "none"
                    show_more_users.style.display = "inline-block"
                })
            })
        })

        // Show / Hide Error Details

        document.addEventListener("DOMContentLoaded", function(event) {
            const all_errors = document.querySelector(".all_errors")

            all_errors.addEventListener("click", async function(event) {
                const error = event.target.closest(".error")
                const error_id = error?.dataset.error_id
                const error_details = error?.querySelector(".error_details")

                const show_error_details = event.target.closest(".show_error_details")
                const hide_error_details = event.target.closest(".hide_error_details")

                if(show_error_details) {
                    const body = new URLSearchParams({ error_id: error_id }).toString()
                    const res = await fetch("error_details.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body
                    })

                    const html = await res.text()
                    error_details.innerHTML = html

                    show_error_details.style.display = "none"
                    error.querySelector(".hide_error_details").style.display = "inline-block"
                }

                if(hide_error_details) {
                    error_details.innerHTML = ""

                    hide_error_details.style.display = "none"
                    error.querySelector(".show_error_details").style.display = "inline-block"
                }
            })
        })

        // Delete Error

        document.addEventListener("DOMContentLoaded", function(event) {
            const all_errors = document.querySelector(".all_errors")

            all_errors.addEventListener("click", async function(event) {
                const error = event.target.closest(".error")
                const error_id = error?.dataset.error_id

                const delete_error_log = event.target.closest(".delete_error_log")
                const cancel_delete_error_log = event.target.closest(".cancel_delete_error_log")

                if(delete_error_log) {
                    delete_error_log.style.display = "none"
                    error.querySelector(".cancel_delete_error_log").style.display = "inline-block"

                    // console.log(error_id)
                }

                if(cancel_delete_error_log) {
                    cancel_delete_error_log.style.display = "none"
                    error.querySelector(".delete_error_log").style.display = "inline-block"

                    // console.log(error_id)
                }
            })
        })

        // Show More / Show Less Errors

        document.addEventListener("DOMContentLoaded", function(event) {
            const all_errors = document.querySelector(".all_errors")
            const show_more_errors = document.querySelector(".show_more_errors")
            const show_less_errors = document.querySelector(".show_less_errors")

            show_more_errors.addEventListener("click", function(event) {
                offset += limit

                fetch("all_errors.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `offset=${offset}`
                })
                .then(response => response.text())
                .then(data => {
                    all_errors.insertAdjacentHTML("beforeend", data)
                    show_more_errors.style.display = "none"
                    show_less_errors.style.display = "inline-block"
                })
            })

            show_less_errors.addEventListener("click", function(event) {
                offset = 0

                fetch("all_errors.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `offset=${offset}`
                })
                .then(response => response.text())
                .then(data => {
                    all_errors.innerHTML = data
                    show_less_errors.style.display = "none"
                    show_more_errors.style.display = "inline-block"
                })
            })
        })

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