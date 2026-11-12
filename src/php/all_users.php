<?php
    // PHP Code

    require "../assets/functions.php";
    require "../classes/Database.php";
    require "../classes/Admin.php";

    $database_object = new Database(); // Initializes The Database Methods
    $admin_object = new Admin(); // Initializes The Admin Methods
    
    session_start();

    $connection = $database_object->connectionDB(); // Connects To The DB

    requireAdmin($connection, "Fragment all_users.php"); // Only Administrators Can Load This Fragment

    $offset = intval($_POST["offset"] ?? 0);
    $user_id = (int)$_SESSION["logged_in_user_id"]; // Excludes The Logged In Administrator
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach($admin_object->getAllUsers($connection, $user_id, $offset) ?? [] as $one_user): ?>
        <?php require "../assets/partials/admin_user_row.php"; ?>

    <?php endforeach; ?>
<?php endif; ?>
