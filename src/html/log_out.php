<?php
    // PHP Code

    require "../assets/functions.php";

    // Delete remember_me_token Cookie If Is Setted

    if(isset($_COOKIE["remember_me_token"])) {
        unset($_COOKIE["remember_me_token"]);
        setcookie("remember_me_token", "", -1, "/");
    }

    deleteSession();

    redirectURL("/src/html/index.php");
?>