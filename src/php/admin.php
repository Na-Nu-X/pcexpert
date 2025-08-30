<?php
    // PHP Code

    require "../assets/functions.php";

    session_start();
    
    $connection = connectionDB();

    function allUsers($connection) {
        $sql = "SELECT * FROM users";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            if(mysqli_stmt_execute($stmt)) {
                return mysqli_stmt_get_result($stmt);
            }
        }
        
        else {
            echo mysqli_error($connection);
        }

        return false;
    }

    $all_users = mysqli_fetch_all(allUsers($connection));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PC Expert - Administrácia</title>

    <link rel="stylesheet" href="/src/css/admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins"> <!-- Poppins Font Style -->

    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>
<body>
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

        <h1><a href="./admin">Administrácia</a></h1>
    </header>

    <main>
        <?php if(isset($_SESSION["logged_in_user_id"]) and getUserRole($connection, $_SESSION["logged_in_user_id"]) === "admin"): ?>
            <div class="users">
                <?php foreach($all_users as $one_user): ?>
                    <div class="user">
                        <h2><?php echo $one_user[1] . " " . $one_user[2]; ?></h2>
                        <p><strong>ID používateľa: </strong><?php echo $one_user[0]; ?></p>
                        <p><strong>E-mailová adresa: </strong><?php echo $one_user[3]; ?></p>
                        <p><strong>Telefónne číslo: </strong><?php echo $one_user[4]; ?></p>
                        <p><strong>Rola: </strong><?php echo $one_user[7]; ?></p>
                        <p><strong>Čas vytvorenia účtu: </strong><?php echo $one_user[8]; ?></p>
                    </div>

                <?php endforeach; ?>
            </div>

            <br>

            <div class="error_log">
                <?php if(file_exists("../errors/error.log")): ?>
                    <?php echo nl2br((file_get_contents("../errors/error.log"))); ?>

                <?php endif; ?>
            </div>

        <?php else: ?>
            <p>K obsahu tejto stránky nemáte povolený prístup.</p>
            <a href="./index" title="Domov" target="_self">Späť na úvodnú stránku.</a>

        <?php endif; ?>
    </main>

    <footer>
        <?php require "../assets/footer.php"; ?>
    </footer>
</body>
</html>