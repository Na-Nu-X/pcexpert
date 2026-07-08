<?php
    class User {
        /**
         * Function For Create A User
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $first_name - First Name
         * @param string $last_name - Last Name
         * @param string $email_address - Email Address
         * @param string $phone_number - Phone Number
         * @param string $password - Password
         * @param bool $news - Subscribe For News
         * 
         * @return int|false
        **/

        public function createUser(PDO $connection, string $first_name, string $last_name, string $email_address, string $phone_number, string $password, bool $news):int|false {
            $sql = "INSERT INTO users (first_name, last_name, email_address, phone_number, password, news, creation_time) 
                    VALUES (:first_name, :last_name, :email_address, :phone_number, :password, :news, CURRENT_TIMESTAMP)";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->bindValue(":phone_number", $phone_number, PDO::PARAM_STR);
                $stmt->bindValue(":password", $password, PDO::PARAM_STR);
                $stmt->bindValue(":news", $news, PDO::PARAM_BOOL);

                $stmt->execute();
                $id = $connection->lastInsertId();

                if (!empty($id)) {
                    return (int)$id;
                }
            } 
            
            catch(PDOException $e) {
                error_log("Chyba pri vytváraní používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return false;
        }

        /**
         * Function For Update A User
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * @param string $first_name - First Name
         * @param string $last_name - Last Name
         * @param string $email_address - Email Address
         * @param string $phone_number - Phone Number
         * @param string $profile_picture_name - Profile Picture Name
         * 
         * @return bool
        **/

        public function updateUser(PDO $connection, int $id, string $first_name, string $last_name, string $email_address, string $phone_number, string $profile_picture_name):bool {
            $sql = "UPDATE users 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        email_address = :email_address, 
                        phone_number = :phone_number, 
                        profile_picture_name = :profile_picture_name, 
                        last_edit = NOW()
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->bindValue(":phone_number", $phone_number, PDO::PARAM_STR);
                $stmt->bindValue(":profile_picture_name", $profile_picture_name, PDO::PARAM_STR);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);

                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri aktualizácii používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Delete A User
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/

        public function deleteUser(PDO $connection, int $id):bool {
            $sql = "DELETE
                    FROM users
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                return $stmt->execute();
            }

            catch(PDOException $e) {
                error_log("Chyba pri odstraňovaní používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Authenticate The User
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $email_address - Email Address
         * @param string $password - Password
         * 
         * @return bool
        **/

        public function authentication(PDO $connection, string $email_address, string $password):bool {
            $sql = "SELECT password
                    FROM users
                    WHERE email_address = :email_address";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->execute();

                $hashed_password = $stmt->fetchColumn();

                if($hashed_password !== false) {
                    return password_verify($password, $hashed_password);
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri autentifikácii používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return false;
        }

        /**
         * Function For Authenticate The User By ID
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * @param string $password - Password
         * 
         * @return bool
        **/

        public function passwordAuthenticationById(PDO $connection, int $id, string $password):bool {
            $sql = "SELECT password
                    FROM users
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                $stmt->execute();

                $hashed_password = $stmt->fetchColumn();

                if($hashed_password !== false) {
                    return password_verify($password, $hashed_password);
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri autentifikácii používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return false;
        }

        /**
         * Function For Authenticate The Email
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $email_address - Email Address
         * 
         * @return bool
        **/

        public function emailAuthentication(PDO $connection, string $email_address):bool {
            $sql = "SELECT email_address
                    FROM users
                    WHERE email_address = :email_address";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->execute(); 

                $result = $stmt->fetchColumn();

                if($result !== false) {
                    return true;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri overovaní emailovej adresy: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return false;
        }

        /**
         * Function For Get The User ID
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $email_address - Email Address
         * 
         * @return int|null
        **/

        public function getUserId(PDO $connection, string $email_address):?int {
            $sql = "SELECT id 
                    FROM users
                    WHERE email_address = :email_address";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->execute();

                $id = $stmt->fetchColumn();

                if($id !== false) {
                    return (int)$id;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní ID používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return null;
        }

        /**
         * Function For Get The User ID By Remember Me Token
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $remember_me_token_cookie - Remember Me Token From Cookie
         * 
         * @return int|null
        **/

        public function getUserIdByRememberMeToken(PDO $connection, string $remember_me_token_cookie):?int {
            $sql = "SELECT id, remember_me_token
                    FROM users
                    WHERE remember_me_token IS NOT NULL";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->execute();

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if(password_verify($remember_me_token_cookie, $row["remember_me_token"])) {
                        return (int)$row["id"];
                    }
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní ID používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return null;
        }

        // /**
        //  * Function For Get The User Name
        //  * 
        //  * @param PDO $connection - Connection To The Database
        //  * @param int $id - ID
        //  * 
        //  * @return string|null
        // **/

        // public function getUserName(PDO $connection, int $id):?string {
        //     $sql = "SELECT first_name, last_name
        //             FROM users
        //             WHERE id = :id";

        //     try {
        //         $stmt = $connection->prepare($sql);
        //         $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        //         $stmt->execute();

        //         $user = $stmt->fetch(PDO::FETCH_ASSOC);

        //         if($user) {
        //             return $user["first_name"] . " " . $user["last_name"];
        //         }
        //     } 

        //     catch(PDOException $e) {
        //         error_log("Chyba pri získavaní mena používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
        //     }

        //     return null;
        // }

        /**
         * Function For Get The User Information
         * 
         * @param PDO $connection - Connection To The Database
         * @param int|null $id - ID
         * @param string $database_field - Database Field
         * 
         * @return int|string|null
        **/

        public function getUserInformation(PDO $connection, ?int $id, string $database_field):int|string|null {
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

            if($id === null) {
                return null;
            }

            $sql = "SELECT $database_field
                    FROM users
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
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

        // /**
        //  * Function For Get The User Role
        //  * 
        //  * @param PDO $connection - Connection To The Database
        //  * @param int $id - ID
        //  * 
        //  * @return string|null
        // **/

        // public function getUserRole(PDO $connection, int $id):?string {
        //     $sql = "SELECT role
        //             FROM users
        //             WHERE id = :id";

        //     try {
        //         $stmt = $connection->prepare($sql);
        //         $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        //         $stmt->execute();

        //         $role = $stmt->fetchColumn();

        //         if($role !== false) {
        //             return (string)$role;
        //         }
        //     } 

        //     catch(PDOException $e) {
        //         error_log("Chyba pri získavaní role používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
        //     }

        //     return null;
        // }

        // /**
        //  * Function For Get The Profile Picture Name
        //  * 
        //  * @param PDO $connection - Connection To The Database
        //  * @param int $id - ID
        //  * 
        //  * @return string|null
        // **/

        // public function getProfilePictureName(PDO $connection, int $id):?string {
        //     $sql = "SELECT profile_picture_name
        //             FROM users
        //             WHERE id = :id";

        //     try {
        //         $stmt = $connection->prepare($sql);
        //         $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        //         $stmt->execute();

        //         $profile_picture_name = $stmt->fetchColumn();

        //         if($profile_picture_name !== false) {
        //             return (string)$profile_picture_name;
        //         }
        //     } 

        //     catch(PDOException $e) {
        //         error_log("Chyba pri získavaní profilového obrázka používateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
        //     }

        //     return null;
        // }

        /**
         * Function For Update The Password
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * @param string $password - Password
         * 
         * @return bool
        **/

        public function updatePassword(PDO $connection, int $id, string $password):bool {
            $sql = "UPDATE users 
                    SET password = :password
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":password", $password, PDO::PARAM_STR);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);

                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri aktualizácii hesla: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Set The Remember Me Token
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * @param string $remember_me_token - Remember Me Token
         * 
         * @return bool
        **/

        public function setRememberMeToken(PDO $connection, int $id, string $remember_me_token):bool {
            $sql = "UPDATE users 
                    SET remember_me_token = :remember_me_token 
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":remember_me_token", $remember_me_token, PDO::PARAM_STR);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);

                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri aktualizácii prihlasovacieho tokenu: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Set The Profile Picture Name
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * @param string $profile_picture_name - Profile Picture Name
         * 
         * @return bool
        **/

        public function setProfilePicture(PDO $connection, int $id, string $profile_picture_name):bool {
            $sql = "UPDATE users
                    SET profile_picture_name = :profile_picture_name
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":profile_picture_name", $profile_picture_name, PDO::PARAM_STR);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);

                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri aktualizácii profilového obrázka: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Delete The Profile Picture Name
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/

        public function deleteProfilePicture(PDO $connection, int $id):bool {
            $sql = "UPDATE users
                    SET profile_picture_name = NULL
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri odstraňovaní profilového obrázka: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Set The Password Reset Code
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * @param string $password_reset_code - Password Reset Code
         * 
         * @return bool
        **/

        public function setPasswordResetCode(PDO $connection, int $id, string $password_reset_code):bool {
            $sql = "UPDATE users 
                    SET password_reset_code = :password_reset_code 
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":password_reset_code", $password_reset_code, PDO::PARAM_STR);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);

                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri aktualizácii kódu pre obnovu hesla: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Delete The Password Reset Code
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/

        public function deletePasswordResetCode(PDO $connection, int $id):bool {
            $sql = "UPDATE users
                    SET password_reset_code = null
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri odstraňovaní kódu pre obnovu hesla: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }
    }
?>