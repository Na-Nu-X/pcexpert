<?php
require "../assets/functions.php";

session_start();

$connection = connectionDB();

if(deleteUser($connection, $_SESSION["logged_in_user_id"])) {
    deleteSession();

    redirectURL("/src/php/index.php");
}

else {
    echo "Nepodarilo sa vymazať účet.";
}
?>