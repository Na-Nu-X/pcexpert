<?php
    class Database {
        /**
         * Function For Load The Environment Variables From The .env File (Only Once)
         *
         * @return void
        **/

        private function loadEnvironment():void {
            if(isset($_ENV["POSTGRES_DB"])) return; // Already Loaded By The Page

            require_once __DIR__ . "/../../vendor/autoload.php";

            Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
        }

        /**
         * Function For Connect To Database
         *
         * Credentials Are Read From The .env File (See .env.example)
         *
         * @return PDO|false
        **/

        public function connectionDB() {
            $this->loadEnvironment();

            // PostgreSQL

            $host = $_ENV["POSTGRES_HOST"] ?? "127.0.0.1";
            $port = $_ENV["POSTGRES_PORT"] ?? "5432";
            $dbname = $_ENV["POSTGRES_DB"] ?? "";
            $user = $_ENV["POSTGRES_USER"] ?? "";
            $password = $_ENV["POSTGRES_PASSWORD"] ?? "";

            if($dbname === "" || $user === "") {
                error_log("Chyba pri pripojovaní k databázy: Chýbajú premenné POSTGRES_DB / POSTGRES_USER v súbore .env.\n", 3, __DIR__ . "/../errors/error.log");

                return false;
            }

            $dsn = "pgsql:host=" . $host . ";port=" . $port . ";dbname=" . $dbname;

            try {
                $db = new PDO($dsn, $user, $password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                return $db;
            }

            catch(PDOException $error) {
                error_log("Chyba pri pripojovaní k databázy: " . $error->getMessage() . "\n", 3, __DIR__ . "/../errors/error.log");

                return false;
            }
        }
    }
?>
