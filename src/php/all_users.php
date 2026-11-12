<?php
    // PHP Code

    require "../classes/Database.php";
    require "../classes/Admin.php";

    $database_object = new Database(); // Initializes The Database Methods
    $admin_object = new Admin(); // Initializes The Admin Methods
    
    $connection = $database_object->connectionDB(); // Connects To The DB

    $offset = intval($_POST["offset"] ?? 0);
    $user_id = intval($_POST["user_id"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach($admin_object->getAllUsers($connection, $user_id, $offset) ?? [] as $one_user): ?>
        <?php require "../assets/partials/admin_user_row.php"; ?>

    <?php endforeach; ?>
<?php endif; ?>
