<?php
    function userReview($connection, $user_id = null) {
        if($user_id === null) {
            return null;
        }

        $sql = "SELECT * FROM reviews
                WHERE user_id = ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);

            if(mysqli_stmt_execute($stmt)) {
                return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            }
        }

        return false;
    }

    function allReviews($connection, $user_id = null, $limit = 5) {
        if($user_id === null) {
            $sql = "SELECT * FROM reviews
                    ORDER BY review_time DESC
                    LIMIT ?";

            $stmt = mysqli_prepare($connection, $sql);

            if($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $limit);
            }
        }

        else {
            $sql = "SELECT * FROM reviews
                    WHERE user_id IS NULL
                    OR user_id != ?
                    ORDER BY review_time DESC
                    LIMIT ?";

            $stmt = mysqli_prepare($connection, $sql);

            if($stmt) {
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $limit);
            }
        }

        if(mysqli_stmt_execute($stmt)) {
            return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        }
    }
?>