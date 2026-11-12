<?php
    // Debug
    
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);
    
    // PHP Code

    require "../assets/functions.php";
    require "../classes/Database.php";
    require "../classes/Admin.php";

    $database_object = new Database(); // Initializes The Database Methods
    $admin_object = new Admin(); // Initializes The Admin Methods
    
    session_start();

    $connection = $database_object->connectionDB(); // Connects To The DB

    requireAdmin($connection, "Fragment error_details.php"); // Only Administrators Can Load This Fragment

    $error_id = intval($_POST["error_id"] ?? 0);
?>

<?php $error_details = $_SERVER["REQUEST_METHOD"] === "POST" ? $admin_object->getErrorLogById($connection, $error_id) : null; ?>

<?php if($error_details): ?>
    <dl class="details_list">
        <?php foreach(["user_id" => "User ID", "ip_address" => "IP Address", "referer" => "Referer", "request_method" => "Request Method", "user_agent" => "User Agent", "server_ip" => "Server IP"] as $field => $label): ?>
            <div>
                <dt><?php echo $label; ?></dt>
                <dd><?php echo htmlspecialchars((string)($error_details[$field] ?? "-"), ENT_QUOTES, "UTF-8"); ?></dd>
            </div>

        <?php endforeach; ?>

        <div class="full">
            <dt>Post Data</dt>
            <dd><pre><?php echo htmlspecialchars(json_encode(json_decode((string)$error_details["post_data"], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: "-", ENT_QUOTES, "UTF-8"); ?></pre></dd>
        </div>
    </dl>

<?php endif; ?>
