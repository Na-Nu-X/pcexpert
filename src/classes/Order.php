<?php
    class Order {
        /**
         * Function For Create An Order
         * 
         * @param PDO $connection - Connection To The Database
         * @param string $service - Service
         * @param int $price_at_purchase - Price At Purchase
         * @param array|null $customer - Customer ($first_name, $last_name, $email_address, $phone_number, $address, $city, $postal_code)
         * @param string|null $stripe_intent_id - Stripe Intent ID
         * @param string $shipping_method - Shipping Method
         * @param array|null $packeta - Packeta ($branch_id, $branch_name)
         * @param string|null $message - Message
         * @param array|null $computer_building - Computer Building ($max_price, $cpu, $gpu, $ram, $space, $disk_type)
         * @param array|null $computer_maintenance - Computer Maintenance ($device_type, $problems[], $software_services[])
         * @param array|null $computer_cleaning - Computer Cleaning ($device_type, $last_cleaning, $thermal_paste_reapply)
         * 
         * @return bool
        **/

        public function createOrder(
            PDO $connection, 
            string $service, 
            int $price_at_purchase, 
            array $customer,
            ?string $stripe_intent_id, 
            string $shipping_method, 
            ?array $packeta, 
            ?string $message,
            ?array $computer_building,
            ?array $computer_maintenance,
            ?array $computer_cleaning
        ):bool {
            try {
                $connection->beginTransaction();

                $sql = "INSERT INTO orders (service, price_at_purchase, status, stripe_intent_id, shipping_method, message, creation_time) 
                        VALUES (:service, :price_at_purchase, 'PENDING', :stripe_intent_id, :shipping_method, :message, CURRENT_TIMESTAMP)
                        RETURNING id";

                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":service", $service, PDO::PARAM_STR);
                $stmt->bindValue(":price_at_purchase", $price_at_purchase, PDO::PARAM_INT);
                $stmt->bindValue(":stripe_intent_id", $stripe_intent_id, $stripe_intent_id === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->bindValue(":shipping_method", $shipping_method, PDO::PARAM_STR);
                $stmt->bindValue(":message", $message, $message === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

                $stmt->execute();

                $order_id = (int)$stmt->fetchColumn(); // Gets The Order ID

                // Customer
                if(!empty($order_id)) {
                    $sql = "INSERT INTO customer (order_id, first_name, last_name, email_address, phone_number, address, city, postal_code)
                            VALUES (:order_id, :first_name, :last_name, :email_address, :phone_number, :address, :city, :postal_code)";

                    $stmt = $connection->prepare($sql);

                    $stmt->bindValue(":order_id", $order_id, PDO::PARAM_INT);
                    $stmt->bindValue(":first_name", $computer_building["first_name"], PDO::PARAM_STR);
                    $stmt->bindValue(":last_name", $computer_building["last_name"], PDO::PARAM_STR);
                    $stmt->bindValue(":email_address", $computer_building["email_address"], PDO::PARAM_STR);
                    $stmt->bindValue(":phone_number", $computer_building["phone_number"], PDO::PARAM_STR);
                    $stmt->bindValue(":address", $computer_building["address"], PDO::PARAM_STR);
                    $stmt->bindValue(":city", $computer_building["city"], PDO::PARAM_STR);
                    $stmt->bindValue(":postal_code", $computer_building["postal_code"], PDO::PARAM_INT);

                    $stmt->execute();
                }

                // Packeta
                if(!empty($order_id) && !empty($packeta)) {
                    $sql = "INSERT INTO packeta (order_id, branch_id, branch_name)
                            VALUES (:order_id, :branch_id, :branch_name)";

                    $stmt = $connection->prepare($sql);

                    $stmt->bindValue(":order_id", $order_id, PDO::PARAM_INT);
                    $stmt->bindValue(":branch_id", $computer_building["branch_id"], PDO::PARAM_INT);
                    $stmt->bindValue(":branch_name", $computer_building["branch_name"], PDO::PARAM_STR);

                    $stmt->execute();
                }

                // Computer Building
                if(!empty($order_id) && !empty($computer_building)) {
                    $sql = "INSERT INTO computer_building (order_id, max_price, cpu, gpu, ram, space, disk_type)
                            VALUES (:order_id, :max_price, :cpu, :gpu, :ram, :space, :disk_type)";

                    $stmt = $connection->prepare($sql);

                    $stmt->bindValue(":order_id", $order_id, PDO::PARAM_INT);
                    $stmt->bindValue(":max_price", $computer_building["max_price"], PDO::PARAM_INT);
                    $stmt->bindValue(":cpu", $computer_building["cpu"], PDO::PARAM_STR);
                    $stmt->bindValue(":gpu", $computer_building["gpu"], PDO::PARAM_STR);
                    $stmt->bindValue(":ram", $computer_building["ram"], PDO::PARAM_INT);
                    $stmt->bindValue(":space", $computer_building["space"], PDO::PARAM_INT);
                    $stmt->bindValue(":disk_type", $computer_building["disk_type"], PDO::PARAM_STR);

                    $stmt->execute();
                }

                // Computer Maintenance
                if(!empty($order_id) && !empty($computer_maintenance)) {
                    $sql = "INSERT INTO computer_maintenance (order_id, device_type)
                            VALUES (:order_id, :device_type)
                            RETURNING id";

                    $stmt = $connection->prepare($sql);

                    $stmt->bindValue(":order_id", $order_id, PDO::PARAM_INT);
                    $stmt->bindValue(":device_type", $computer_maintenance["device_type"], PDO::PARAM_STR);

                    $stmt->execute();

                    $computer_maintenance_id = (int)$stmt->fetchColumn(); // Gets The Computer Maintenance ID

                    if(!empty($computer_maintenance_id)) {
                        if(!empty($computer_maintenance["problems"])) {
                            foreach($computer_maintenance["problems"] as $one_problem) {
                                $sql = "INSERT INTO computer_maintenance_problems (computer_maintenance_id, problem)
                                        VALUES (:computer_maintenance_id, :problem)";
        
                                $stmt = $connection->prepare($sql);
        
                                $stmt->bindValue(":computer_maintenance_id", $computer_maintenance_id, PDO::PARAM_INT);
                                $stmt->bindValue(":problem", $one_problem, PDO::PARAM_STR);
        
                                $stmt->execute();
                            }
                        }

                        if(!empty($computer_maintenance["software_services"])) {
                            foreach($computer_maintenance["software_services"] as $one_software_service) {
                                $sql = "INSERT INTO computer_maintenance_software_services (computer_maintenance_id, software_service)
                                        VALUES (:computer_maintenance_id, :software_service)";

                                $stmt = $connection->prepare($sql);

                                $stmt->bindValue(":computer_maintenance_id", $computer_maintenance_id, PDO::PARAM_INT);
                                $stmt->bindValue(":software_service", $one_software_service, PDO::PARAM_STR);

                                $stmt->execute();
                            }
                        }
                    }
                }

                // Computer Cleaning
                if(!empty($order_id) && !empty($computer_cleaning)) {
                    $sql = "INSERT INTO computer_cleaning (order_id, device_type, last_cleaning, thermal_paste_reapply)
                            VALUES (:order_id, :device_type, :last_cleaning, :thermal_paste_reapply)";

                    $stmt = $connection->prepare($sql);

                    $stmt->bindValue(":order_id", $order_id, PDO::PARAM_INT);
                    $stmt->bindValue(":device_type", $computer_cleaning["device_type"], PDO::PARAM_STR);
                    $stmt->bindValue(":last_cleaning", $computer_cleaning["last_cleaning"], PDO::PARAM_STR);
                    $stmt->bindValue(":thermal_paste_reapply", $computer_cleaning["thermal_paste_reapply"], PDO::PARAM_BOOL);

                    $stmt->execute();
                }

                $connection->commit();

                return true;
            } 

            catch(PDOException $error) {
                if($connection->inTransaction()) {
                    $connection->rollBack();
                }

                error_log("Chyba pri vytváraní objednávky: " . $error->getMessage() . "\n", 3, "../errors/error.log");

                return false;
            }
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