<?php
    // PHP Code

    require "../assets/functions.php";
    
    session_start();
    
    $connection = connectionDB();

    if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true and isset($_SESSION["logged_in_user_id"])) {
        if($_POST) {
            $user_id = $_SESSION["logged_in_user_id"];

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

                        if(!file_exists("../../content/uploads/" . $user_id)) {
                            mkdir("../../content/uploads/" . $user_id, 0777, true);
                        }

                        $image_upload_path = "../../content/uploads/" . $user_id . "/" . $new_image_name;
                        move_uploaded_file($image_tmp_name, $image_upload_path);

                        if(insertProfilePicture($connection, $user_id, $new_image_name)) {
                            redirectURL("/src/php/index.php");
                        };
                    }

                    else {
                        echo "Koncovka vybraného súboru nie je podporovaná. Skúste pridať obrázok s bežne používanými koncovkami ako png, jpg, jpeg.";
                    }
                }
            }
        }
    }

    else {
        echo "Pre zmenu profilového obrázka sa musíte najskôr prihlásiť pod nejakým účtom.";
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
        <section class="edit_profile_picture">
            <?php if(!empty(getProfilePictureName($connection, $_SESSION["logged_in_user_id"]))): ?>
                <img class="profile_picture" src="/content/uploads/<?php echo $_SESSION['logged_in_user_id']; ?>/<?php echo getProfilePictureName($connection, $_SESSION['logged_in_user_id']); ?>">

            <?php else: ?>
                <img class="profile_picture" src="/content/images/user.png"> <!-- https://www.flaticon.com/free-icon/user_747376?term=user -->

            <?php endif; ?>

            <form action="profile_picture.php" method="POST" enctype="multipart/form-data">
                <label for="profile_picture">Vybrať obrázok</label>
                <input id="profile_picture" type="file" name="profile_picture" require>

                <button type="submit" name="submit">Hotovo</button>
            </form>
        </section>
    </main>

    <footer class="<?php echo $theme; ?>">
        <?php require "../assets/footer.php"; ?>
    </footer>

    <script src="/src/js/navigation_bar.js"></script>
</body>
</html>