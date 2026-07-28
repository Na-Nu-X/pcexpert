<?php
    class Order {
        /**
         * Function For Create An Order
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $service - Service
         * @param int $price_at_purchase - Price At Purchase
         * @param string $first_name - First Name
         * @param string $last_name - Last Name
         * @param string $email_address - Email Address
         * @param string $phone_number - Phone Number
         * @param string|null $message - Message
         * @param string|null $stripe_intent_id - Stripe Intent ID
         * 
         * @return bool
        **/

        public function createOrder(PDO $connection, string $service, int $price_at_purchase, string $first_name, string $last_name, string $email_address, string $phone_number, ?string $message, ?string $stripe_intent_id):bool {
            try {
                if($message === null) {
                    $sql = "INSERT INTO orders (service, price_at_purchase, first_name, last_name, email_address, phone_number, status, stripe_intent_id, creation_time) 
                            VALUES (:service, :price_at_purchase, :first_name, :last_name, :email_address, :phone_number, 'PENDING', :stripe_intent_id, CURRENT_TIMESTAMP)";

                    $stmt = $connection->prepare($sql);
                }

                else {
                    $sql = "INSERT INTO orders (service, price_at_purchase, first_name, last_name, email_address, phone_number, message, status, stripe_intent_id, creation_time) 
                            VALUES (:service, :price_at_purchase, :first_name, :last_name, :email_address, :phone_number, :message, 'PENDING', :stripe_intent_id, CURRENT_TIMESTAMP)";

                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue(":message", $message, PDO::PARAM_STR);
                }

                $stmt->bindValue(":service", $service, PDO::PARAM_STR);
                $stmt->bindValue(":price_at_purchase", $price_at_purchase, PDO::PARAM_INT);
                $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->bindValue(":phone_number", $phone_number, PDO::PARAM_STR);
                $stmt->bindValue(":stripe_intent_id", $stripe_intent_id, PDO::PARAM_STR);

                return $stmt->execute();
            } 
            
            catch(PDOException $error) {
                error_log("Chyba pri vytváraní objednávky: " . $error->getMessage() . "\n", 3, "../errors/error.log");
            }

            return false;
        }

        /**
         * Function For Mark The Order As Paid
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/

        public function markAsPaid(PDO $connection, string $session_id):bool {
            try {
                $sql = "UPDATE orders 
                        SET status = 'PAID' 
                        WHERE stripe_intent_id = :session_id";

                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":session_id", $session_id, PDO::PARAM_STR);

                return $stmt->execute();
            } 
            
            catch(PDOException $error) {
                error_log("Chyba pri označovaní objednávky za zaplatenú: " . $error->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }

        /**
         * Function For Mark The Order As Cancelled
         * 
         * @param PDO $connection - Connection To The Database
         * @param int $id - ID
         * 
         * @return bool
        **/

        public function markAsCancelled(PDO $connection, string $session_id):bool {
            try {
                $sql = "UPDATE orders 
                        SET status = 'CANCELLED' 
                        WHERE stripe_intent_id = :session_id";

                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":session_id", $session_id, PDO::PARAM_STR);

                return $stmt->execute();
            } 
            
            catch(PDOException $error) {
                error_log("Chyba pri označovaní objednávky za zrušenú: " . $error->getMessage() . "\n", 3, "../errors/error.log");
                return false;
            }
        }
    }
?>