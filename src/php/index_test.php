<?php
    $db_host = "127.0.0.1";
    $db_user = "pc_expert_admin";
    $db_password = "Ac7}l|szo98=pK-5-g?R";
    $db_name = "pc_expert";

    $connection = mysqli_connect($db_host, $db_user, $db_password, $db_name);

    if (mysqli_connect_error()){
        echo mysqli_connect_error();
        exit;
    }

    echo "Úspešne prihlásenie do databáze.";

    $sql = "SELECT * FROM processors";
    echo "<br>";

    $result = mysqli_query($connection, $sql);
    // var_dump($result);
    echo "<br>";
    echo "<br>";
    $processors = mysqli_fetch_all($result, MYSQLI_ASSOC);
    // var_dump($processors[0]['name']);
    // echo $processors[0]['name'];

    $processors_length = count($processors);
    // echo $processors_length;

    function currentUrl() {
        if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1) || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https") {
            $protocol = "https";
        }

        else {
            $protocol = "http";
        }

        $host = $_SERVER["HTTP_HOST"];
        $script = $_SERVER["SCRIPT_NAME"];
        // $params = $_SERVER["QUERY_STRING"];
    
        return $protocol . "://" . $host . $script;
    }

    for($i = 0; $i < $processors_length; $i++) {
        echo "<strong>ID: </strong>" . $processors[$i]['id'] . " " . "<strong>Name: </strong>" . "<a href='" . currentUrl() . "?id=" . ($i+1) . "'>" . $processors[$i]['name'] . "</a>";
        echo "<br>";
    }

    echo currentURL() . "?" . $_SERVER["QUERY_STRING"];

    // var_dump(count($processors));

    // 79. časť
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>

