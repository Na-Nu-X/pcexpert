<?php
    function allUsers($connection, $user_id, $offset = 0, $limit = 5) {
        $sql = "SELECT * FROM users
                WHERE id <> ?
                ORDER BY creation_time DESC
                LIMIT ?, ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "iii", $user_id, $offset, $limit);
        }
        
        if(mysqli_stmt_execute($stmt)) {
            return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        }
    }

    function getErrorLog($connection, $offset = 0, $limit = 5) {
        $sql = "SELECT * FROM errors
                ORDER BY error_time DESC
                LIMIT ?, ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
        }

        if(mysqli_stmt_execute($stmt)) {
            return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        }

        return false;
    }

    function getErrorLogById($connection, $error_id) {
        $sql = "SELECT * FROM errors
                WHERE error_id = ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $error_id);
        }

        if(mysqli_stmt_execute($stmt)) {
            return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        }

        return false;
    }

    function deleteErrorLogById($connection, $error_id) {
        $sql = "DELETE FROM errors
                WHERE error_id = ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $error_id);
        }

        if(mysqli_stmt_execute($stmt)) {
            return true;
        }

        return false;
    }

    function countUsers($connection, $user_id = null) {
        if($user_id === null) {
            $sql = "SELECT COUNT(*) 
                    AS users_amount 
                    FROM users";

            $stmt = mysqli_prepare($connection, $sql);
        } 
        
        else {
            $sql = "SELECT COUNT(*) 
                    AS users_amount
                    FROM users
                    WHERE id IS NULL 
                    OR id != ?";

            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        }

        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row["users_amount"];
        }
        
        return false;
    }

    function countErrors($connection) {
        $sql = "SELECT COUNT(*) 
                AS errors_amount
                FROM errors";

        $stmt = mysqli_prepare($connection, $sql);

        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row["errors_amount"];
        }
        
        return false;
    }

    function changeUserRole($connection, $id, $role) {
        $sql = "UPDATE users
                SET role = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt === false) {
            echo mysqli_error($connection);
        }

        else {
            mysqli_stmt_bind_param($stmt, "si", $role, $id);

            if(mysqli_stmt_execute($stmt)) {
                return true;
            }
        }
    }

    function getUncheckedReviews($connection, $offset = 0, $limit = 5) {
        $sql = "SELECT * FROM reviews
                WHERE checked = 'no'
                ORDER BY review_time DESC
                LIMIT ?, ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
        }

        if(mysqli_stmt_execute($stmt)) {
            return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        }

        return false;
    }
?>