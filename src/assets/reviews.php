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

    function allReviews($connection, $user_id = null, $offset = 0, $limit = 5) {
        if($user_id === null) {
            $sql = "SELECT * FROM reviews
                    WHERE status = 'approved'
                    ORDER BY review_time DESC
                    LIMIT ?, ?";

            $stmt = mysqli_prepare($connection, $sql);

            if($stmt) {
                mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
            }
        }

        else {
            $sql = "SELECT * FROM reviews
                    WHERE user_id IS NULL
                    AND status = 'approved'
                    OR user_id != ?
                    AND status = 'approved'
                    ORDER BY review_time DESC
                    LIMIT ?, ?";

            $stmt = mysqli_prepare($connection, $sql);

            if($stmt) {
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $offset, $limit);
            }
        }

        if(mysqli_stmt_execute($stmt)) {
            return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        }
    }

    function countReviews($connection, $user_id = null) {
        if($user_id === null) {
            $sql = "SELECT COUNT(*) 
                    AS reviews_amount 
                    FROM reviews
                    WHERE status = 'approved'";

            $stmt = mysqli_prepare($connection, $sql);
        } 
        
        else {
            $sql = "SELECT COUNT(*) 
                    AS reviews_amount
                    FROM reviews
                    WHERE user_id IS NULL 
                    AND status = 'approved'
                    OR user_id != ?
                    AND status = 'approved'";

            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        return $row["reviews_amount"];
    }

    function deleteReview($connection, $user_id) {
        $sql = "DELETE
                FROM reviews
                WHERE user_id = ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);

            if(mysqli_stmt_execute($stmt)) {
                return true;
            }

            else {
                return false;
            }
        }

        else {
            echo mysqli_error($connection);
        }
    }
?>