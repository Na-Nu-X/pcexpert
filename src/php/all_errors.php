<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/admin_functions.php";
    
    $connection = connectionDB();

    $offset = intval($_POST["offset"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach(get_error_log($connection, $offset) as $one_error): ?>
        <p>
            <strong>Chyba:</strong>
            <br>
            [<?php echo htmlspecialchars($one_error["error_time"], ENT_QUOTES, "UTF-8"); ?>] - 
            <?php echo htmlspecialchars($one_error["message"], ENT_QUOTES, "UTF-8"); ?>
            <br><br>
            <strong>Podrobnosti:</strong>
            <br>
            <strong>User ID: </strong><?php echo htmlspecialchars($one_error["user_id"], ENT_QUOTES, "UTF-8"); ?>
            <br>
            <strong>IP Address: </strong><?php echo htmlspecialchars($one_error["ip_address"], ENT_QUOTES, "UTF-8"); ?>
            <br>
            <strong>Referer: </strong><?php echo htmlspecialchars($one_error["referer"], ENT_QUOTES, "UTF-8"); ?>
            <br>
            <strong>Request Method: </strong><?php echo htmlspecialchars($one_error["request_method"], ENT_QUOTES, "UTF-8"); ?>
            <br>
            <strong>User Agent: </strong><?php echo htmlspecialchars($one_error["user_agent"], ENT_QUOTES, "UTF-8"); ?>
            <br>
            <strong>Server IP: </strong><?php echo htmlspecialchars($one_error["server_ip"], ENT_QUOTES, "UTF-8"); ?>
            <br>
            <strong>Post Data: </strong><?php var_dump(json_decode($one_error["post_data"])); ?>
            <hr></hr>
        </p>

    <?php endforeach; ?>
<?php endif; ?>