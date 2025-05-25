<?php
    // PHP Code

    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Expert - Napísať recenziu</title>
</head>
<body>
    <?php if(isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true): ?>


    <?php else: ?>
        <!-- <p>Pre napísanie recenzie musíte byť prihlásený.</p> -->

    <?php endif; ?>
</body>
</html>