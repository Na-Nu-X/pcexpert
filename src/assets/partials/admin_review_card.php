<?php
    /**
     * Admin Pending Review Card (Used By admin.php)
     *
     * @var array $one_review - Review Row From Admin::getPendingReviews()
     * @var User $user_object - User Methods
     * @var PDO $connection - Connection To The Database
    **/

    $review_author = "Anonymný zákazník";
    $review_avatar = "/content/images/avatar.png"; // https://www.flaticon.com/free-icon/user_847969

    if($one_review["user_id"] !== null) {
        $review_user_id = (int)$one_review["user_id"];
        $review_profile_picture = $user_object->getUserInformation($connection, $review_user_id, "profile_picture_name");
        $review_google_picture = $user_object->getUserInformation($connection, $review_user_id, "google_profile_picture_url");

        $review_author = $user_object->getUserInformation($connection, $review_user_id, "first_name") . " " . $user_object->getUserInformation($connection, $review_user_id, "last_name");

        if(!empty($review_profile_picture)) $review_avatar = "/content/uploads/" . $review_user_id . "/" . $review_profile_picture;
        elseif(!empty($review_google_picture)) $review_avatar = $review_google_picture;
    }
?>

<article class="review_card">
    <div class="review_card_header">
        <img class="profile_picture" src="<?php echo htmlspecialchars($review_avatar, ENT_QUOTES, "UTF-8"); ?>" alt="" referrerpolicy="no-referrer" loading="lazy">

        <div class="cell_text">
            <p class="primary_text"><?php echo htmlspecialchars($review_author, ENT_QUOTES, "UTF-8"); ?></p>

            <p class="secondary_text">
                <?php echo htmlspecialchars(date("j. n. Y", strtotime($one_review["review_time"])), ENT_QUOTES, "UTF-8"); ?>
                <?php if(($one_review["edited"] ?? "") === "yes"): ?> · upravená<?php endif; ?>
            </p>
        </div>

        <span class="stars" title="<?php echo (int)$one_review["rating"]; ?> / 5" aria-label="Hodnotenie <?php echo (int)$one_review["rating"]; ?> z 5">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <i class="<?php echo $i <= $one_review["rating"] ? "fa-solid" : "fa-regular"; ?> fa-star" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/star -->

            <?php endfor; ?>
        </span>
    </div>

    <p class="review_text"><?php echo htmlspecialchars($one_review["review"], ENT_QUOTES, "UTF-8"); ?></p>

    <form class="review_confirmation" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "UTF-8"); ?>" method="POST">
        <input type="hidden" name="review_id" value="<?php echo (int)$one_review["review_id"]; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["csrf_token"]; ?>">

        <button class="btn btn_danger btn_sm" type="submit" name="reject_review"><i class="fa-solid fa-xmark"></i> Zamietnuť</button> <!-- https://fontawesome.com/icons/xmark -->
        <button class="btn btn_sm" type="submit" name="approve_review"><i class="fa-solid fa-check"></i> Schváliť</button> <!-- https://fontawesome.com/icons/check -->
    </form>
</article>
