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
         * @param string|null $stripe_intent_id - Stripe Intent ID
         * @param string $address - Address
         * @param string $city - City
         * @param string $postal_code - Postal Code
         * @param string $shipping_method - Shipping Method
         * @param int|null $packeta_branch_id - Packeta Branch ID
         * @param string|null $packeta_branch_name - Packeta Branch Name
         * @param int|null $max_price - Max Price
         * @param string|null $cpu - CPU
         * @param string|null $gpu - GPU
         * @param int|null $ram - RAM
         * @param int|null $space - Space
         * @param string|null $disk_type - Disk Type
         * @param string|null $message - Message
         * 
         * @return bool
        **/

        public function createOrder(PDO $connection, string $service, int $price_at_purchase, string $first_name, string $last_name, string $email_address, string $phone_number, ?string $stripe_intent_id, string $address, string $city, string $postal_code, string $shipping_method, ?int $packeta_branch_id, ?string $packeta_branch_name, ?int $max_price, ?string $cpu, ?string $gpu, ?int $ram, ?int $space, ?string $disk_type, ?string $message):bool {
            try {
                $sql = "INSERT INTO orders (service, price_at_purchase, first_name, last_name, email_address, phone_number, status, stripe_intent_id, address, city, postal_code, shipping_method, packeta_branch_id, packeta_branch_name, max_price, cpu, gpu, ram, space, disk_type, message, creation_time) 
                        VALUES (:service, :price_at_purchase, :first_name, :last_name, :email_address, :phone_number, 'PENDING', :stripe_intent_id, :address, :city, :postal_code, :shipping_method, :packeta_branch_id, :packeta_branch_name, :max_price, :cpu, :gpu, :ram, :space, :disk_type, :message, CURRENT_TIMESTAMP)";

                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":service", $service, PDO::PARAM_STR);
                $stmt->bindValue(":price_at_purchase", $price_at_purchase, PDO::PARAM_INT);
                $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->bindValue(":phone_number", $phone_number, PDO::PARAM_STR);
                $stmt->bindValue(":stripe_intent_id", $stripe_intent_id, PDO::PARAM_STR);
                $stmt->bindValue(":address", $address, PDO::PARAM_STR);
                $stmt->bindValue(":city", $city, PDO::PARAM_STR);
                $stmt->bindValue(":postal_code", $postal_code, PDO::PARAM_STR);
                $stmt->bindValue(":shipping_method", $shipping_method, PDO::PARAM_STR);
                $stmt->bindValue(":packeta_branch_id", $packeta_branch_id, PDO::PARAM_INT);
                $stmt->bindValue(":packeta_branch_name", $packeta_branch_name, PDO::PARAM_STR);
                $stmt->bindValue(":max_price", $max_price, PDO::PARAM_INT);
                $stmt->bindValue(":cpu", $cpu, PDO::PARAM_STR);
                $stmt->bindValue(":gpu", $gpu, PDO::PARAM_STR);
                $stmt->bindValue(":ram", $ram, PDO::PARAM_INT);
                $stmt->bindValue(":space", $space, PDO::PARAM_INT);
                $stmt->bindValue(":disk_type", $disk_type, PDO::PARAM_STR);
                $stmt->bindValue(":message", $message, PDO::PARAM_STR);

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

        /**
         * Function For Get The User Information
         * 
         * @param PDO $connection - Connection To The Database
         * @param string|null $session_id - Session ID
         * @param string $database_field - Database Field
         * 
         * @return int|string|null
        **/

        public function getOrderInformation(PDO $connection, ?string $session_id, string $database_field):int|string|null {
            // Defines The Allowed Fields
            $allowed_fields = [
                "service", 
                "price_at_purchase", 
                "first_name", 
                "last_name", 
                "status",
                "stripe_intent_id"
            ];
    
            if(!in_array($database_field, $allowed_fields)) {
                error_log("Nepovolený prístup k stĺpcu: " . $database_field, 3, "../errors/error.log");
                return null;
            }

            if($session_id === null) {
                return null;
            }

            $sql = "SELECT $database_field
                    FROM orders
                    WHERE stripe_intent_id = :session_id";

            try {
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(":session_id", $session_id, PDO::PARAM_STR);
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