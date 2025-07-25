<?php

// Function for Connecting to Database

function connectionDB() {
    $db_host = "127.0.0.1";
    $db_user = "pc_expert_admin";
    $db_password = "Ac7}l|szo98=pK-5-g?R";
    $db_name = "pc_expert";
    
    $connection = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    
    if (mysqli_connect_error()){
        echo mysqli_connect_error();
        exit;
    }
    
    // echo "Úspešne prihlásenie do databáze.";

    return $connection;
}

// Function for Changing Current URL Address

function redirectURL($path) {
    if (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"] != "off") {
        $url_protocol = "https";
    }
    
    else {
        $url_protocol = "http";
    }

    header("location: $url_protocol://" . $_SERVER["HTTP_HOST"] . $path);

    exit;
}


/**
 * Function For Add / Create New User In Database.
 * 
 * @param object $connection - Connection To Database.
 * @param string $first_name - First Name Of User.
 * @param string $last_name - Last Name Of User.
 * @param string $email_address - Email Address Of User.
 * @param string $password - Password Of User.
 * 
 * @return integer $id - ID Of User.
**/

function createUser($connection, $first_name, $last_name, $email_address, $phone_number, $password, $news, $role) {
    $sql = "INSERT INTO users (first_name, last_name, email_address, phone_number, password, news, role, creation_time) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

    $statement = mysqli_prepare($connection, $sql);

    if($statement === false) {
        echo mysqli_error($connection);
    }

    else {
        mysqli_stmt_bind_param($statement, "sssssss", $first_name, $last_name, $email_address, $phone_number, $password, $news, $role);

        mysqli_stmt_execute($statement);
        $id = mysqli_insert_id($connection);
        return $id;
    }
}

function updateUser($connection, $id, $first_name, $last_name, $email_address, $phone_number, $profile_picture_name) {
    $sql = "UPDATE users 
            SET first_name = ?, last_name = ?, email_address = ?, phone_number = ?, profile_picture_name = ? 
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $email_address, $phone_number, $profile_picture_name, $id);

        if(mysqli_stmt_execute($stmt)) {
            return true;
        }
    }

    else {
        echo mysqli_error($connection);
    }
}

/**
 * Function For Check If User Is Logged In.
 * 
 * @return boolean true - If User Is Logged In, Else False.
**/

function isLoggedIn() {
    return isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"];
}


/**
 * Ověření uživatele pomocí emailu a hesla
 *
 *
 * @param object $connection - připojení do databáze
 * @param string $log_email - email z formuláře pro přihlášení
 * @param string $log_password - heslo z formuláře pro přihlášení
 *
 * @return boolean true - pokud je přihlášení úspěšné, false - pokud je neúspěšné
 *
 */

function authentication($connection, $email_address, $password) {
    $sql = "SELECT password
            FROM users
            WHERE email_address = ?";
   
    $stmt = mysqli_prepare($connection, $sql);

    if($stmt){
        mysqli_stmt_bind_param($stmt, "s", $email_address);


        if (mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);

            if($result->num_rows != 0){
                $password_database = mysqli_fetch_row($result);
                $user_password_database = $password_database[0];


                if($user_password_database) {
                    return password_verify($password, $user_password_database);
                }
            }
        }
    }
    
    else {
        echo mysqli_error($connection);
    }
}

/**
 *
 * Získání ID uživatele
 *
 * @param object $connection - připojení do databáze
 * @param string $email - email uživatele
 *
 * @return int id uživatele
 *
 */

function getUserId($connection, $email_address) {
    $sql = "SELECT id 
            FROM users
            WHERE email_address = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email_address);

        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $id_database = mysqli_fetch_row($result); // pole
            $user_id = $id_database[0];

            return $user_id;
        }
    } 
    
    else {
        echo mysqli_error($connection);
    }
}

function getUserName($connection, $id) {
    $sql = "SELECT first_name, last_name
            FROM users
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $name_database = mysqli_fetch_row($result);

            if($name_database) {
                return $name_database[0] . " " . $name_database[1];
            }

            else {
                return null;
            }
        }
    }

    else {
        echo mysqli_error($connection);
    }
}

function getUserInformations($connection, $id, $database_field) {
    $not_allowed_fields = ["id", "password", "news", "remember_me_token", "profile_picture_name"];

    if(in_array($database_field, $not_allowed_fields)) {
        echo "K týmto informáciam nie je možný prístup.";
    }

    else {
        $sql = "SELECT $database_field
        FROM users
        WHERE id = ?";

        $stmt = mysqli_prepare($connection, $sql);

        if($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);

            if(mysqli_stmt_execute($stmt)) {
                return mysqli_fetch_column(mysqli_stmt_get_result($stmt));
            }
        }

        else {
            echo mysqli_error($connection);
        }
    }
}

function emailAuthentication($connection, $email_address) {
    $sql = "SELECT email_address
            FROM users
            WHERE email_address = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email_address);

        if(mysqli_stmt_execute($stmt)) {
            return mysqli_fetch_column(mysqli_stmt_get_result($stmt));
        }
    }

    else {
        echo mysqli_error($connection);
    }
}

function passwordAuthentication($connection, $password) {
    $sql = "SELECT password
            FROM users
            WHERE password = ?";
    
    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $password);

        if(mysqli_stmt_execute($stmt)) {

            return password_verify($password, mysqli_fetch_column(mysqli_stmt_get_result($stmt)));
            // return mysqli_fetch_column(mysqli_stmt_get_result($stmt));
        }
    }

    else {
        echo mysqli_error($connection);
    }
}

function deleteUser($connection, $id) {
    $sql = "DELETE
            FROM users
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if(mysqli_stmt_execute($stmt)) {
            return true;
        }
    }

    else {
        echo mysqli_error($connection);
    }
}

function deleteSession() {
    session_start();

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

function insertRememberMeToken($connection, $id, $remember_me_token) {
    $sql = "UPDATE users
            SET remember_me_token = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        $hashed_remember_me_token = password_hash($remember_me_token, PASSWORD_DEFAULT);

        mysqli_stmt_bind_param($stmt, "si", $hashed_remember_me_token, $id);
        mysqli_stmt_execute($stmt);
    }

    else {
        echo mysqli_error($connection);
    }
}

function getUserIdByRememberMeToken($connection, $remember_me_token_cookie) {
    $sql = "SELECT id, remember_me_token 
            FROM users 
            WHERE remember_me_token IS NOT NULL";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            while($row = mysqli_fetch_assoc($result)) {
                $id = $row["id"];
                $remember_me_token_database = $row["remember_me_token"];

                if(password_verify($remember_me_token_cookie, $remember_me_token_database)) {
                    return $id;
                }
            }
        }
    } 
    
    else {
        echo mysqli_error($connection);
    }

    return false;
}

function getUserRole($connection, $id) {
    $sql = "SELECT role
            FROM users
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $role_database = mysqli_fetch_row($result); // pole
            $user_role = $role_database[0];

            return $user_role;
        }
    }

    else {
        echo mysqli_error($connection);
    }
}

function insertProfilePicture($connection, $id, $image_name) {
    $sql = "UPDATE users
            SET profile_picture_name = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt === false) {
        echo mysqli_error($connection);
    }

    else {
        mysqli_stmt_bind_param($stmt, "si", $image_name, $id);

        if(mysqli_stmt_execute($stmt)) {
            return true;
        }
    }
}

function deleteProfilePicture($connection, $id) {
    $sql = "UPDATE users
            SET profile_picture_name = ''
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt === false) {
        echo mysqli_error($connection);
        return false;
    }

    else {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if(mysqli_stmt_execute($stmt)) {
            return true;
        }
    }
}

function getProfilePictureName($connection, $id) {
    $sql = "SELECT profile_picture_name
            FROM users
            WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    if($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $profile_picture_name_database = mysqli_fetch_row($result); // pole
            $user_profile_picture_name = $profile_picture_name_database[0];

            return $user_profile_picture_name;
        }
    }

    else {
        echo mysqli_error($connection);
    }
}
?>