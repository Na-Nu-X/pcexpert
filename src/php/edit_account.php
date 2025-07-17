<?php
    // PHP Code

    require "../assets/functions.php";
    
    session_start();
    
    $connection = connectionDB();

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        // Profile Picture

        $id = $_SESSION["logged_in_user_id"];

        $image_name = $_FILES["profile_picture"]["name"];
        $image_full_path = $_FILES["profile_picture"]["full_path"];
        $image_type = $_FILES["profile_picture"]["type"];
        $image_tmp_name = $_FILES["profile_picture"]["tmp_name"];
        $image_size = $_FILES["profile_picture"]["size"];
        $image_error = $_FILES["profile_picture"]["error"];

        if($image_error === 0) {
            if($image_size > 8000000) { // 8MB
                echo "Vybraný súbor je príliš veľký. Maximálna veľkosť súboru je 8MB.";
            }

            else {
                $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                $supported_extensions = ["png", "jpg", "jpeg"];

                if(in_array($image_extension, $supported_extensions)) {
                    $new_image_name = uniqid("IMG-", true) . "." . $image_extension;

                    if(!file_exists("../../content/uploads/" . $id)) {
                        mkdir("../../content/uploads/" . $id, 0777, true);
                    }

                    $image_upload_path = "../../content/uploads/" . $id . "/" . $new_image_name;
                    move_uploaded_file($image_tmp_name, $image_upload_path);

                    if(insertProfilePicture($connection, $id, $new_image_name)) {
                        redirectURL("/src/php/index.php");
                    };
                }

                else {
                    echo "Koncovka vybraného súboru nie je podporovaná. Skúste pridať obrázok s bežne používanými koncovkami ako png, jpg, jpeg.";
                }
            }
        }

        // Other Post Data

        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $email_address = $_POST["email_address"];
        $phone_number = str_replace(" ", "", $_POST["phone_number"]);
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
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
<body>
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
            <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>
                <h2>Môj účet</h2>

                <form class="edit_account_form" action="edit_account.php" method="POST" enctype="multipart/form-data">
                    <div class="profile_picture_container">
                        <p>Profilový obrázok:</p>

                        <?php if(!empty(getProfilePictureName($connection, $_SESSION["logged_in_user_id"]))): ?>
                            <label for="profile_picture">
                                <img class="profile_picture" src="/content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>">
                            </label>

                            <input id="profile_picture" type="file" name="profile_picture">

                        <?php else: ?>
                            <label for="profile_picture">
                                <img class="profile_picture" src="/content/images/user.png"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->
                            </label>
                            
                            <input id="profile_picture" type="file" name="profile_picture">

                        <?php endif; ?>
                    </div>

                    <div class="full_name_container">
                        <p>Meno:</p>

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
                        <p>E-mailová adresa:</p>

                        <input
                            type="email"
                            name="email_address"
                            placeholder="Zadajte váš e-mail"
                            autocomplete="email"
                            value="<?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'email_address'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="phone_number_container">
                        <p>Telefónne číslo:</p>

                        <input
                            type="text"
                            name="phone_number"
                            placeholder="Zadajte vaše telefónne číslo"
                            autocomplete="tel"
                            value="<?php echo htmlspecialchars(getUserInformations($connection, $_SESSION['logged_in_user_id'], 'phone_number'), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="password_container">
                        <p>Heslo:</p>
                    </div>

                    <button type="submit" name="submit">Hotovo</button>
                </form>

            <?php else: ?>
                <h2>Môj účet</h2>

                <p>Pre zobrazenie podrobností o vašom účte sa musíte najprv <a href="./log_in.php" title="Prihlásiť sa">prihlásiť</a> alebo <a href="./registration.php" title="Vytvoriť účet">zaregistrovať.</a></p>

            <?php endif; ?>
        </section>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>