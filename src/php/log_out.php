<?php
    // PHP Code

    require "../assets/functions.php";

    // Delete remember_me_token Cookie If Is Setted

    if(isset($_COOKIE["remember_me_token"])) {
        unset($_COOKIE["remember_me_token"]);
        setcookie("remember_me_token", "", -1, "/");
    }

    deleteSession();

    // Create Session For Log Out Message

    session_start();
    session_regenerate_id(true); // Protection For Fixation Attack
    $_SESSION["log_out_successful_message"] = "Úspešne odhlásený";

    redirectURL("/src/php/index");
?>