<?php
    // PHP Code

    require "../assets/functions.php";

    // Delete remember_me_token Cookie If Is Setted

    if(isset($_COOKIE["remember_me_token"])) {
        unset($_COOKIE["remember_me_token"]);
        setcookie("remember_me_token", "", -1, "/");
    }

    deleteSession();

    session_start();

    $_SESSION["log_out_successful_message"] = "Úspešne odhlásený.";

    session_write_close();

    redirectURL("/src/html/index.php");
?>