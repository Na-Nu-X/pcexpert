<?php
    // PHP Code

    class Functions {
        /**
         * Function For Connect To Database.
         * 
         * @return
        **/

        public static function connectionDB() {
            $db_host = "127.0.0.1";
            $db_name = "pc_expert";
            $db_user = "pc_expert_admin";
            $db_password = "Ac7}l|szo98=pK-5-g?R";
            
            $connection = "mysql:host=" . $db_host . ";dbname=" . $db_name . ";charset=utf8";
            
            try {
                $db = new PDO($connection, $db_user, $db_password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                return $db;
            }

            catch (PDOException $e) {
                errorLogSave($connection, "Error While Connecting To Database. (" . $e->getMessage() . ")", "../errors/error.log");
                
                return false;
            }
        }

        /**
         * Function For Redirect To Setted URL Address.
         * 
         * @param $path - New URL Address To Redirect.
        **/

        public static function redirectURL($path) {
            try {
                if (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"] != "off") {
                    $url_protocol = "https";
                }
                
                else {
                    $url_protocol = "http";
                }
            
                header("location: $url_protocol://" . $_SERVER["HTTP_HOST"] . $path);
            }
            
            catch (PDOException $e) {
                errorLogSave($connection, "Error While Redirecting URL Address. (" . $e->getMessage() . ")", "../errors/error.log");
                
                return false;
            }
        }

        /**
         * Function For Check If User Is Logged In.
         * 
         * @return boolean true - If User Is Logged In, Else False.
        **/

        public static function isLoggedIn() {
            return isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"] === true;
        }

        /**
         * Function For Add / Create New User In Database.
         * 
         * @param object $connection - Connection To Database.
         * @param string $first_name - First Name Of The User.
         * @param string $last_name - Last Name Of The User.
         * @param string $email_address - Email Address Of The User.
         * @param string $phone_number - Phone Number Of The User.
         * @param string $password - Password Of The User.
         * @param string $news - Information Whether The User Agrees To Receive News.
         * 
         * @return integer|null $id - ID Of User Or Null On Failure.
        **/

        public static function createUser($connection, $first_name, $last_name, $email_address, $phone_number, $password, $news) {
            $sql = "INSERT INTO users (first_name, last_name, email_address, phone_number, password, news) 
                    VALUES (:first_name, :last_name, :email_address, :phone_number, :password, :news)";
            
            $stmt = $connection->prepare($sql);

            $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
            $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
            $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
            $stmt->bindValue(":phone_number", $phone_number, PDO::PARAM_STR);
            $stmt->bindValue(":password", $password, PDO::PARAM_STR);
            $stmt->bindValue(":news", $news, PDO::PARAM_STR);

            try {
                if ($stmt->execute()) {
                    $id = $connection->lastInsertId();

                    if (!empty($id)) {
                        return (int)$id;
                    } 
                    
                    else {
                        throw new Exception("Nepodarilo sa získať ID nového používateľa.");
                    }
                } 
                
                else {
                    throw new Exception("Dotaz na vloženie používateľa zlyhal.");
                }
            } 
            
            catch (Exception $e) {
                error_log("Chyba v createUser: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
                return null;
            }
        }

        /**
         * Function For Delete User In Database.
        **/

        public static function deleteUser($connection, $id) {
            $sql = "DELETE
                    FROM users
                    WHERE id = :id";

            $stmt = $connection->prepare($sql);

            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            try {
                if ($stmt->execute()) {
                    return true;
                }

                else {
                    throw new Exception("Nepodarilo sa vymazať používateľa.");
                }
            }

            catch (Exception $e) {
                error_log("Exception in deleteUser Function: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
                return null;
            }
        }

        /**
         * 
        **/

        public static function getUserInformations($connection, $id, $database_field) {
            $allowed_fields = ["email_address", "phone_number"];
        
            if(!in_array($database_field, $allowed_fields)) {
                echo "K týmto informáciam nie je možný prístup.";
            }
        
            else {
                $sql = "SELECT $database_field
                FROM users
                WHERE id = :id";
        
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
                try {
                    if($stmt->execute()) {
                        return mysqli_fetch_column(mysqli_stmt_get_result($stmt));
                    }
            
                    else {
                        throw new Exception("Nepodarilo sa získať informácie o používateľovi.");
                    }
                }
                
                catch(Exception $e) {
                    error_log("Exception in getUserInformations Function: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                    echo "Typ chyby: " . $e->getMessage();
                    return null;
                }
            }
        }

        /**
         * 
        **/

        public static function emailAuthentication($connection, $email_address) {
            $sql = "SELECT email_address
                    FROM users
                    WHERE email_address = :email_address";
        
            $stmt = $connection->prepare($sql);
        
            $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
            
            try {
                if($stmt->execute()) {
                    return mysqli_fetch_column(mysqli_stmt_get_result($stmt));
                }

                else {
                    throw new Exception("Nepodarilo sa overiť email používateľa.");
                }
            }

            catch(Exception $e) {
                error_log("Exception in emailAuthentication Function: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
                return null;
            }
        }

        /**
         * 
        **/

        public static function authentication($connection, $email_address, $password) {
            $sql = "SELECT password
                    FROM users
                    WHERE email_address = :email_address";
           
            $stmt = $connection->prepare($sql);
            
            $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
    
            try {
                if($stmt->execute()) {
                    $result = mysqli_stmt_get_result($stmt);
        
                    if($result->num_rows != 0) {
                        $password_database = mysqli_fetch_row($result);
                        $user_password_database = $password_database[0];
        
        
                        if($user_password_database) {
                            return password_verify($password, $user_password_database);
                        }
                    }
                }

                else {
                    throw new Exception("Nepodarilo sa overiť používateľa.");
                }
            }
            
            catch(Exception $e) {
                error_log("Exception in authentication Function: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
                return null;
            }
        }

        /**
         * 
        **/

        public static function passwordAuthentication($connection, $password) {
            $sql = "SELECT password
                    FROM users
                    WHERE password = :password";
            
            $stmt = $connection->prepare($sql);

            $stmt->bindValue(":password", $password, PDO::PARAM_STR);
            
            try {
                if($stmt->execute()) {
                    return password_verify($password, mysqli_fetch_column(mysqli_stmt_get_result($stmt)));
                }

                else {
                    throw new Exception("Nepodarilo sa overiť heslo.");
                }
            }

            catch(Exception $e) {
                error_log("Exception in passwordAuthentication Function: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
                return null;
            }
        }

        /**
         * 
        **/

        public static function deleteSession() {
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

        /**
         * 
        **/

        public static function getUserName($connection, $id) {
            $sql = "SELECT first_name, last_name
                    FROM users
                    WHERE id = :id";
        
            $stmt = $connection->prepare($sql);
            
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            
            try {
                if($stmt->execute()) {
                    $result = mysqli_stmt_get_result($stmt);
                    $name_database = mysqli_fetch_row($result);
        
                    if($name_database) {
                        return $name_database[0] . " " . $name_database[1];
                    }
        
                    else {
                        return null;
                    }
                }

                else {
                    throw new Exception("Nepodarilo sa získať meno používateľa.");
                }
            }
            
            catch(Exception $e) {
                error_log("Exception in getUserName Function: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
                return null;
            }
        }

        /**
         * 
        **/

        public static function getUserId($connection, $email_address) {
            $sql = "SELECT id 
                    FROM users
                    WHERE email_address = :email_address";
        
            $stmt = $connection->prepare($sql);
        
            $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
    
            try {
                if($stmt->execute()) {
                    $result = mysqli_stmt_get_result($stmt);
                    $id_database = mysqli_fetch_row($result); // pole
                    $user_id = $id_database[0];
        
                    return $user_id;
                }

                else {
                    throw new Exception("Nepodarilo sa získať ID používateľa.");
                }
            }
            
            catch(Exception $e) {
                error_log("Exception in getUserId Function: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
                return null;
            }
        }
    }
?>