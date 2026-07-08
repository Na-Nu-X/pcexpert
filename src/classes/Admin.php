<?php
    class Admin {
        /**
         * Function For Get All Users
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $user_id - User ID
         * @param int|null $offset - Offset
         * @param int|null $limit - Limit
         * 
         * @return array|null
        **/

        public function getAllUsers(PDO $connection, int $user_id, ?int $offset = 0, ?int $limit = 5):?array {
            $sql = "SELECT * FROM users
                    WHERE id <> :user_id
                    ORDER BY creation_time DESC
                    LIMIT :offset, :limit";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
                $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

                $stmt->execute();
    
                $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                if(!empty($all_users)) {
                    return $all_users;
                }
            }

            catch(PDOException $e) {
                error_log("Chyba pri získavaní užívateľov: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return null;
        }

        /**
         * Function For Count The Users
         * 
         * @param PDO $connection - Connection To The Database
         * @param int|null $user_id - User ID
         * 
         * @return int
        **/

        public function countUsers(PDO $connection, ?int $user_id = null):int {
            try {
                if($user_id === null) {
                    $sql = "SELECT COUNT(*) 
                            AS users_amount 
                            FROM users";
        
                    $stmt = $connection->prepare($sql);
                } 
                
                else {
                    $sql = "SELECT COUNT(*) 
                            AS users_amount
                            FROM users
                            WHERE id != :user_id";
        
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
                }

                $stmt->execute();

                $users_amount = $stmt->fetchColumn();

                if($users_amount !== null && $users_amount !== false) {
                    return (int)$users_amount;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní počtu užívateľov: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return 0;
        }

        /**
         * Function For Change The User Role
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * @param string $role - Role
         * 
         * @return bool
        **/

        public function changeUserRole(PDO $connection, int $id, string $role):bool {
            $sql = "UPDATE users
                    SET role = :role
                    WHERE id = :id";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":role", $role, PDO::PARAM_STR);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);

                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri aktualizácii role užívateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Get All Pending Reviews
         * 
         * @param PDO $connection - Connection To The Database
         * @param int|null $offset - Offset
         * @param int|null $limit - Limit
         * 
         * @return array|null
        **/

        public function getPendingReviews(PDO $connection, ?int $offset = 0, ?int $limit = 5):?array {
            $sql = "SELECT * FROM reviews
                    WHERE status = 'pending'
                    ORDER BY review_time DESC
                    LIMIT :offset, :limit";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
                $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

                $stmt->execute();

                $all_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if(!empty($all_reviews)) {
                    return $all_reviews;
                }
            }

            catch(PDOException $e) {
                error_log("Chyba pri získavaní recenzií na potvrdenie: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return null;
        }

        /**
         * Function For Count The Pending Reviews
         * 
         * @param PDO $connection - Connection To The Database
         * 
         * @return int
        **/

        public function countPendingReviews(PDO $connection):int {
            $sql = "SELECT COUNT(*) 
                    AS reviews_amount
                    FROM reviews
                    WHERE status = 'pending'";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $reviews_amount = $stmt->fetchColumn();

                if($reviews_amount !== null && $reviews_amount !== false) {
                    return (int)$reviews_amount;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní počtu recenzií na potvrdenie: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return 0;
        }

        /**
         * Function For Approve The Review
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/

        public function approveReview(PDO $connection, int $id):bool {
            $sql = "UPDATE reviews
                    SET status = 'approved'
                    WHERE review_id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri potvrdzovaní recenzie: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Reject The Review
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/
    
        public function rejectReview(PDO $connection, int $id):bool {
            $sql = "UPDATE reviews
                    SET status = 'rejected'
                    WHERE review_id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri zamietnutí recenzie: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Get The Error Log
         * 
         * @param PDO $connection - Connection To The Database
         * @param int|null $offset - Offset
         * @param int|null $limit - Limit
         * 
         * @return array|null
        **/

        public function getErrorLog(PDO $connection, ?int $offset = 0, ?int $limit = 5):?array {
            $sql = "SELECT * FROM errors
                    ORDER BY error_time DESC
                    LIMIT :offset, :limit";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
                $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

                $stmt->execute();

                $all_errors = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if(!empty($all_errors)) {
                    return $all_errors;
                }
            }

            catch(PDOException $e) {
                error_log("Chyba pri získavaní protokolu chýb: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return null;
        }

        /**
         * Function For Count The Errors
         * 
         * @param PDO $connection - Connection To The Database
         * 
         * @return int
        **/

        public function countErrors(PDO $connection):int {
            $sql = "SELECT COUNT(*) 
                    AS errors_amount
                    FROM errors";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $errors_amount = $stmt->fetchColumn();

                if($errors_amount !== null && $errors_amount !== false) {
                    return (int)$errors_amount;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní počtu záznamov chýb: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return 0;
        }

        /**
         * Function For Get The Error Log By ID
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return array|null
        **/
    
        public function getErrorLogById(PDO $connection, int $id):?array {
            $sql = "SELECT * FROM errors
                    WHERE error_id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                $stmt->execute();

                $error_log = $stmt->fetch(PDO::FETCH_ASSOC);

                if($error_log) {
                    return $error_log;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní záznamu chyby: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }

            return null;
        }

        /**
         * Function For Delete The Error Log By ID
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/
    
        public function deleteErrorLogById(PDO $connection, int $id):bool {
            $sql = "DELETE FROM errors
                    WHERE error_id = :id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                return $stmt->execute();
            }

            catch(PDOException $e) {
                error_log("Chyba pri odstraňovaní záznamu chyby: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }
    }
?>