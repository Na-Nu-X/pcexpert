<?php
    class Database {
        public function connectionDB() {
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

            catch(PDOException $e) {
                errorLogSave($connection, "Error While Connecting To Database. (" . $e->getMessage() . ")", "../errors/error.log");
                
                return false;
            }
        }
    }
?>