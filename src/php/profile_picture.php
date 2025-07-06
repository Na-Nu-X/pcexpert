<?php
    // PHP Code 261

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

            // echo $image_name . " " . $image_full_path . " " . $image_type . " " . $image_tmp_name . " " . $image_size . " " . $image_error;

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
</head>
<body>
    <header>
    </header>

    <main>
        <section>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" require>

                <button type="submit" name="submit">Nahrať obrázok</button>
            </form>
        </section>
    </main>

    <footer>
    </footer>
</body>
</html>