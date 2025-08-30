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

    function get_error_log($connection, $offset = 0, $limit = 5) {
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
    }
?>