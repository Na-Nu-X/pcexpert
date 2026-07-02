<?php
    class User {
        public function createUser($connection, $first_name, $last_name, $email_address, $phone_number, $password, $news) {
            $sql = "INSERT INTO users (first_name, last_name, email_address, phone_number, password, news, creation_time) 
                    VALUES (:first_name, :last_name, :email_address, :phone_number, :password, :news, CURRENT_TIMESTAMP)";

            try {
                $stmt = $connection->prepare($sql);

                $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
                $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
                $stmt->bindValue(":email_address", $email_address, PDO::PARAM_STR);
                $stmt->bindValue(":phone_number", $phone_number, PDO::PARAM_STR);
                $stmt->bindValue(":password", $password, PDO::PARAM_STR);
                $stmt->bindValue(":news", $news, PDO::PARAM_STR);

                $stmt->execute();
                $id = $connection->lastInsertId();

                if (!empty($id)) {
                    return (int)$id;
                }
            } 
            
            catch(Exception $e) {
                error_log("Chyba v createUser: " . $e->getMessage() . "\n", 3, "../errors/error.log");
                echo "Typ chyby: " . $e->getMessage();
            }

            return false;
        }
    }
?>