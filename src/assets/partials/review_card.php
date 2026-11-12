<?php
    /**
     * Public Review Card (Used By index.php And all_reviews.php)
     *
     * @var array $one_review - Review Row From Review::getAllReviews()
     * @var User $user_object - User Methods
     * @var PDO $connection - Connection To The Database
    **/

    $card_author = "Anonymný zákazník";
    $card_avatar = "/content/images/avatar.png"; // https://www.flaticon.com/free-icon/user_847969

    if($one_review["user_id"] !== null) {
        $card_user_id = (int)$one_review["user_id"];
        $card_profile_picture = $user_object->getUserInformation($connection, $card_user_id, "profile_picture_name");
        $card_google_picture = $user_object->getUserInformation($connection, $card_user_id, "google_profile_picture_url");

        $card_author = $user_object->getUserInformation($connection, $card_user_id, "first_name") . " " . $user_object->getUserInformation($connection, $card_user_id, "last_name");

        if(!empty($card_profile_picture)) $card_avatar = "/content/uploads/" . $card_user_id . "/" . $card_profile_picture;
        elseif(!empty($card_google_picture)) $card_avatar = $card_google_picture;
    }
?>

<article class="review">
    <div class="stars" aria-label="Hodnotenie <?php echo (int)$one_review["rating"]; ?> z 5">
        <?php for($i = 1; $i <= 5; $i++): ?>
            <i class="<?php echo $i <= $one_review["rating"] ? "fa-solid" : "fa-regular"; ?> fa-star" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/star -->

        <?php endfor; ?>
    </div>

    <p class="review_text"><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

    <div class="reviewer">
        <img class="profile_picture" src="<?php echo htmlspecialchars($card_avatar, ENT_QUOTES, "UTF-8"); ?>" alt="" referrerpolicy="no-referrer" loading="lazy">

        <div class="user_info">
            <p class="name"><?php echo htmlspecialchars($card_author, ENT_QUOTES, "UTF-8"); ?></p>

            <p class="date">
                <?php echo htmlspecialchars(date("j. n. Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>
                <?php if(($one_review["edited"] ?? "") === "yes"): ?> · upravená<?php endif; ?>
            </p>
        </div>
    </div>
</article>
