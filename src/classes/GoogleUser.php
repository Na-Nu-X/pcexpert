<?php
    class GoogleUser {
        /**
         * Function For Create A Google User
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $first_name - First Name
         * @param string $last_name - Last Name
         * @param string $email_address - Email Address
         * @param string $google_id - Google ID
         * @param string $google_profile_picture_url - Google Profile Picture URL
         * 
         * @return int|false
        **/

        public function createGoogleUser(PDO $connection, string $first_name, string $last_name, string $email_address, string $google_id, string $google_profile_picture_url):int|false {
            $sql = "INSERT INTO users (first_name, last_name, email_address, google_id, google_profile_picture_url, creation_time) 
                    VALUES (:first_name, :last_name, :email_address, :google_id, :google_profile_picture_url, CURRENT_TIMESTAMP)";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->bindValue(":google_id", $google_id, PDO::PARAM_STR);
                $stmt->bindValue(":google_profile_picture_url", $google_profile_picture_url, PDO::PARAM_STR);

                $stmt->execute();
                $id = $connection->lastInsertId();

                if (!empty($id)) {
                    return (int)$id;
                }
            } 
            
            catch(PDOException $e) {
                error_log("Chyba pri vytváraní Google používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return false;
        }

        /**
         * Function For Get The Google User Information
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $google_id - Google ID
         * @param string $database_field - Database Field
         * 
         * @return int|string|null
        **/
    
        public function getGoogleUserInformation(PDO $connection, string $google_id, string $database_field):int|string|null {
            // Defines The Allowed Fields
            $allowed_fields = [
                "first_name", 
                "last_name", 
                "email_address", 
                "phone_number", 
                "role",
                "creation_time",
                "profile_picture_name",
                "password_reset_code",
                "google_id",
                "google_profile_picture_url"
            ];
    
            if(!in_array($database_field, $allowed_fields)) {
                error_log("Nepovolený prístup k stĺpcu: " . $database_field, 3, "../errors/error.log");
                return null;
            }

            $sql = "SELECT $database_field
                    FROM users
                    WHERE google_id = :google_id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":google_id", $google_id, PDO::PARAM_STR);
                $stmt->execute();

                $result = $stmt->fetchColumn();

                if($result !== false) {
                    return $result;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní informácií používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return null;
        }
    }
?>