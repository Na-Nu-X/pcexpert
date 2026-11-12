<?php
    // PHP Code

    require "../assets/functions.php";
    require "../classes/Database.php";
    require "../classes/Admin.php";

    $database_object = new Database(); // Initializes The Database Methods
    $admin_object = new Admin(); // Initializes The Admin Methods
    
    session_start();

    $connection = $database_object->connectionDB(); // Connects To The DB

    requireAdmin($connection, "Fragment all_errors.php"); // Only Administrators Can Load This Fragment

    $offset = intval($_POST["offset"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach($admin_object->getErrorLog($connection, $offset) ?? [] as $one_error): ?>
        <?php require "../assets/partials/admin_error_row.php"; ?>

    <?php endforeach; ?>
<?php endif; ?>
