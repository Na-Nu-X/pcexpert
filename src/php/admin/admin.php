<?php
    // PHP Code

    require "../../assets/functions.php";

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
</head>
<body>
    <?php if(isset($_SESSION["logged_in_user_id"]) and getUserRole($connection, $_SESSION["logged_in_user_id"]) === "admin"): ?>
        <?php foreach($all_users as $one_user): ?>
            <a href="?id=<?php echo $one_user[0]; ?>" title="Zobraziť viac" target="_self"><?php echo $one_user[1] . " " . $one_user[2]; ?></a>
            <br>

        <?php endforeach; ?>

    <?php else: ?>
        <p>K obsahu tejto stránky nemáte povolený prístup.</p>
        <a href="./index.php" title="Domov" target="_self">Späť na úvodnú stránku.</a>

    <?php endif; ?>
</body>
</html>