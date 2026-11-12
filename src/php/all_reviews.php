<?php
    // Debug
        
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    // PHP Code

    require "../classes/Database.php";
    require "../classes/User.php";
    require "../classes/Review.php";

    $database_object = new Database(); // Initializes The Database Methods
    $user_object = new User(); // Initializes The User Methods
    $review_object = new Review(); // Initializes The Review Methods

    $connection = $database_object->connectionDB(); // Connects To The DB

    $offset = intval($_POST["offset"] ?? 0);
    $user_id = intval($_POST["user_id"] ?? 0);
?>

<?php if($_SERVER["REQUEST_METHOD"] === "POST"): ?>
    <?php foreach($review_object->getAllReviews($connection, $user_id, $offset) ?? [] as $one_review): ?>
        <?php require "../assets/partials/review_card.php"; ?>

    <?php endforeach; ?>
<?php endif; ?>
