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
    <?php foreach($review_object->getAllReviews($connection, $user_id, $offset) as $one_review): ?>
        <div class="review">
            <?php if($one_review["user_id"] !== null): ?>
                <div class="reviewer">
                    <?php if(!empty($user_object->getUserInformation($connection, $one_review['user_id'], 'profile_picture_name')) or !empty($user_object->getUserInformation($connection, $one_review['user_id'], 'google_profile_picture_url'))): ?>
                        <img 
                            class="profile_picture" 
                            src="
                                <?php if(!empty($user_object->getUserInformation($connection, $one_review['user_id'], 'google_profile_picture_url')) and empty($user_object->getUserInformation($connection, $one_review['user_id'], 'profile_picture_name'))): ?>
                                    <?php echo $user_object->getUserInformation($connection, $user_id, 'google_profile_picture_url'); ?>

                                <?php else: ?>
                                    /content/uploads/<?php echo $user_id; ?>/<?php echo $user_object->getUserInformation($connection, $user_id, 'profile_picture_name'); ?>

                                <?php endif; ?>
                            "
                            alt="Môj účet"
                        >

                    <?php else: ?>
                        <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->

                    <?php endif; ?>

                    <div class="user_info">
                        <p class="name">
                            <?php echo htmlspecialchars($user_object->getUserInformation($connection, $one_review["user_id"], "first_name"), ENT_QUOTES, "UTF-8"); ?>
                            <?php echo htmlspecialchars($user_object->getUserInformation($connection, $one_review["user_id"], "last_name"), ENT_QUOTES, "UTF-8"); ?>
                        </p>

                        <p class="date">
                            -

                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>

                            <?php if($one_review["edited"] === "yes"): ?>
                                (upravená)

                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= $one_review["rating"]): ?>
                            <div class="star">
                                <i class="fa-solid fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                            </div>
    
                        <?php else: ?>
                            <div class="star">
                                <i class="fa-regular fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                            </div>
    
                        <?php endif; ?>
                        
                    <?php endfor; ?>
                </div>

                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

            <?php else: ?>
                <div class="reviewer">
                    <img class="profile_picture" src="/content/images/avatar.png" alt="Profilový obrázok"> <!-- https://www.flaticon.com/free-icon/user_847969 -->

                    <div class="user_info">
                        <p class="name">Anonymný zákazník</p>

                        <p class="date">
                            -
                            
                            <?php echo htmlspecialchars(date("j.n.Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>
                        </p>
                    </div>
                </div>

                <div class="stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= $one_review["rating"]): ?>
                            <div class="star">
                                <i class="fa-solid fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                            </div>
    
                        <?php else: ?>
                            <div class="star">
                                <i class="fa-regular fa-star"></i> <!-- https://fontawesome.com/icons/star -->
                            </div>
    
                        <?php endif; ?>
                        
                    <?php endfor; ?>
                </div>

                <p><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

            <?php endif; ?>
        </div>

    <?php endforeach; ?>
<?php endif; ?>