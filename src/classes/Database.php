<?php
    class Database {
        /**
         * Function For Connect To Database
         * 
         * @return PDO|false
        **/

        public function connectionDB() {
            // MySQL

            // $db_host = "127.0.0.1";
            // $db_name = "pc_expert";
            // $db_user = "pc_expert_admin";
            // $db_password = "Ac7}l|szo98=pK-5-g?R";
            
            // $connection = "mysql:host=" . $db_host . ";dbname=" . $db_name . ";charset=utf8mb4";
            
            // try {
            //     $db = new PDO($connection, $db_user, $db_password);
            //     $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            //     return $db;
            // }

            // catch(PDOException $e) {
            //     errorLogSave($connection, "Error While Connecting To Database. (" . $e->getMessage() . ")", "../errors/error.log");
                
            //     return false;
            // }

            // PostgreSQL

            $host = "127.0.0.1";
            $port = "5433";
            $dbname = "pcexpert";
            $user = "postgres";
            $password = "Patrik2008";
            
            $dsn = "pgsql:host=" . $host . ";port=" . $port . ";dbname=" . $dbname;
            
            try {
                $db = new PDO($dsn, $user, $password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                return $db;
            }
            
            catch(PDOException $error) {
                error_log("Chyba pri pripojovaní k databázy: " . $error->getMessage() . "\n", 3, "../errors/error.log");
                
                return false;
            }
        }
    }
?>