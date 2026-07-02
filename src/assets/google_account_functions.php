<?php
    function createGoogleUser($connection, $first_name, $last_name, $email_address, $google_id, $google_profile_picture_url) {
        $sql = "INSERT INTO users (first_name, last_name, email_address, google_id, google_profile_picture_url, creation_time) 
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $statement = mysqli_prepare($connection, $sql);

        if($statement === false) {
            echo mysqli_error($connection);
        }

        else {
            mysqli_stmt_bind_param($statement, "sssss", mysqli_real_escape_string($connection, $first_name), mysqli_real_escape_string($connection, $last_name), mysqli_real_escape_string($connection, $email_address), mysqli_real_escape_string($connection, $google_id), mysqli_real_escape_string($connection, $google_profile_picture_url));

            mysqli_stmt_execute($statement);
            $id = mysqli_insert_id($connection);
            return $id;
        }
    }

    function getGoogleUserInformations($connection, $google_id, $database_field) {
        $not_allowed_fields = ["password", "news", "remember_me_token"];

        if(in_array($database_field, $not_allowed_fields)) {
            echo "K týmto informáciam nie je možný prístup.";
        }

        else {
            $sql = "SELECT $database_field
            FROM users
            WHERE google_id = ?";

            $stmt = mysqli_prepare($connection, $sql);

            if($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $google_id);

                if(mysqli_stmt_execute($stmt)) {
                    return mysqli_fetch_column(mysqli_stmt_get_result($stmt));
                }
            }

            return null;
        }
    }
?>