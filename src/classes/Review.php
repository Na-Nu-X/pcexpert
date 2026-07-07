<?php
    class Review {
        /**
         * Function For Create A Review
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $user_id - User ID
         * @param int $rating - Rating
         * @param string $review - Review
         * 
         * @return bool
        **/

        public function sendReview(PDO $connection, int $user_id, int $rating, string $review):bool {
            $sql = "INSERT INTO reviews (user_id, rating, review, review_time)
                    VALUES (:user_id, :rating, :review, CURRENT_TIMESTAMP)";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindValue(":rating", $rating, PDO::PARAM_INT);
                $stmt->bindValue(":review", $review, PDO::PARAM_STR);

                return $stmt->execute();
            } 
            
            catch(PDOException $e) {
                error_log("Chyba pri pridávaní recenzie: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Update A Review
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $user_id - User ID
         * @param int $rating - Rating
         * @param string $review - Review
         * 
         * @return bool
        **/

        public function updateReview(PDO $connection, int $user_id, int $rating, string $review):bool {
            $sql = "UPDATE reviews 
                    SET rating = :rating, 
                        review = :review, 
                        edited = 'yes', 
                        status = 'pending', 
                        last_edit = NOW()
                    WHERE user_id = :user_id";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":rating", $rating, PDO::PARAM_INT);
                $stmt->bindValue(":review", $review, PDO::PARAM_STR);
                $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

                return $stmt->execute(); 
            } 

            catch(PDOException $e) {
                error_log("Chyba pri aktualizácii recenzie: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Get The Average Rating
         * 
         * @param PDO $connection - Connection To The Database
         * 
         * @return float
        **/

        public function getAverageRating(PDO $connection):float {
            $sql = "SELECT AVG(rating)
                    AS average_rating
                    FROM reviews
                    WHERE status = 'approved'";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->execute();

                $average_rating = $stmt->fetchColumn();

                if($average_rating !== null && $average_rating !== false) {
                    return (float)round($average_rating, 1);
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní priemerného hodnotenia: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return 0.0;
        }

        /**
         * Function For Count The Reviews
         * 
         * @param PDO $connection - Connection To The Database
         * @param int|null $user_id - User ID
         * 
         * @return int
        **/

        public function countReviews(PDO $connection, ?int $user_id = null):int {
            try {
                if($user_id === null) {
                    $sql = "SELECT COUNT(*) 
                            AS reviews_amount 
                            FROM reviews
                            WHERE status = 'approved'";
        
                    $stmt = $connection->prepare($sql);
                } 
                
                else {
                    $sql = "SELECT COUNT(*) 
                            AS reviews_amount
                            FROM reviews
                            WHERE (user_id IS NULL OR user_id != :user_id)
                            AND status = 'approved'";
        
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
                }

                $stmt->execute();

                $reviews_amount = $stmt->fetchColumn();

                if($reviews_amount !== null && $reviews_amount !== false) {
                    return (int)$reviews_amount;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní počtu recenzií: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return 0;
        }

        /**
         * Function For Get The User's Review
         * 
         * @param PDO $connection - Connection To The Database
         * @param int|null $user_id - User ID
         * 
         * @return array|null
        **/

        public function userReview(PDO $connection, ?int $user_id = null):?array {
            try {
                if($user_id === null) {
                    return null;
                }
                
                $sql = "SELECT * FROM reviews
                        WHERE user_id = :user_id";
    
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
                $stmt->execute();

                $review = $stmt->fetch(PDO::FETCH_ASSOC);

                if($review) {
                    return $review;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní recenzie užívateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return null;
        }

        /**
         * Function For Get All Reviews
         * 
         * @param PDO $connection - Connection To The Database
         * @param int|null $user_id - User ID
         * @param int|null $offset - Offset
         * @param int|null $limit - Limit
         * 
         * @return array|null
        **/

        public function allReviews($connection, ?int $user_id = null, ?int $offset = 0, ?int $limit = 5):?array {
            try {
                if($user_id === null) {
                    $sql = "SELECT * FROM reviews
                            WHERE status = 'approved'
                            ORDER BY review_time DESC
                            LIMIT :offset, :limit";

                    $stmt = $connection->prepare($sql);
                }
                
                else {
                    $sql = "SELECT * FROM reviews
                            WHERE (user_id IS NULL OR user_id != :user_id)
                            AND status = 'approved'
                            ORDER BY review_time DESC
                            LIMIT :offset, :limit";
        
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
                }

                $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
                $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);

                $stmt->execute();
    
                $all_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                if(!empty($all_reviews)) {
                    return $all_reviews;
                }
            } 

            catch(PDOException $e) {
                error_log("Chyba pri získavaní recenzie užívateľa: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            }
    
            return null;
        }
    }
?>