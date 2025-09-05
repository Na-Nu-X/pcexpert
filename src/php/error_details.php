<?php
    // PHP Code

    require "../assets/functions.php";
    require "../assets/admin_functions.php";
    
    $connection = connectionDB();

    $error_id = intval($_POST["error_id"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <p>
        <strong>User ID:</strong>
        <?php echo htmlspecialchars(getErrorLogById($connection, $error_id)["user_id"], ENT_QUOTES, "UTF-8"); ?><br>
        <strong>IP Address:</strong>
        <?php echo htmlspecialchars(getErrorLogById($connection, $error_id)["ip_address"], ENT_QUOTES, "UTF-8"); ?><br>
        <strong>Referer:</strong>
        <?php echo htmlspecialchars(getErrorLogById($connection, $error_id)["referer"], ENT_QUOTES, "UTF-8"); ?><br>
        <strong>Request Method:</strong>
        <?php echo htmlspecialchars(getErrorLogById($connection, $error_id)["request_method"], ENT_QUOTES, "UTF-8"); ?><br>
        <strong>User Agent:</strong>
        <?php echo htmlspecialchars(getErrorLogById($connection, $error_id)["user_agent"], ENT_QUOTES, "UTF-8"); ?><br>
        <strong>Server IP:</strong>
        <?php echo htmlspecialchars(getErrorLogById($connection, $error_id)["server_ip"], ENT_QUOTES, "UTF-8"); ?><br>
        <strong>Post Data:</strong>

        <?php foreach(json_decode(getErrorLogById($connection, $error_id)["post_data"]) as $key => $value): ?>
            <?php echo $key . ": " . "<i>" . $value . "</i>" . ","; ?>

        <?php endforeach; ?>
    </p>

<?php endif; ?>