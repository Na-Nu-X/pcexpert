<?php
    // PHP Code

    require "../assets/functions.php";

    session_start();
    
    $connection = connectionDB();
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
        <!-- HTML Code -->

    <?php else: ?>
        <p>K obsahu tejto stránky nemáte povolený prístup.</p>
        <a href="./index.php" title="Domov" target="_self">Späť na úvodnú stránku.</a>

    <?php endif; ?>
</body>
</html>