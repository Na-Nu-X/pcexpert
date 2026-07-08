<?php
    /**
     * Function For Redirect URL
     * 
     * @param string $path - Path
     * 
     * @return void
    **/

    function redirectURL(string $path):void {
        if(isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"] != "off") {
            $url_protocol = "https";
        }
        
        else {
            $url_protocol = "http";
        }

        header("location: $url_protocol://" . $_SERVER["HTTP_HOST"] . $path);
        exit;
    }

    /**
     * Function For Check If User Is Logged In
     * 
     * @return bool
    **/

    function isLoggedIn():bool {
        return isset($_SESSION["is_logged_in"]) and $_SESSION["is_logged_in"];
    }

    /**
     * Function For Delete Current Logged In Session
     * 
     * @return void
    **/

    function deleteSession():void {
        session_start();

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Function For Save Error
     * 
     * @param PDO $connection - Connection To The Database
     * @param string $message - Message
     * @param string $file_directory - File Directory
     * 
     * @return bool
    **/

    function errorLogSave(PDO $connection, string $message, string $file_directory):bool {
        // Data

        $error_time = date("Y-m-d H:i:s");
        $user_id = $_SESSION["logged_in_user_id"] ?? "unknown";
        $ip_address = $_SERVER["REMOTE_ADDR"] ?? "unknown";
        $referer = $_SERVER["HTTP_REFERER"] ?? "unknown";
        $request_method = $_SERVER["REQUEST_METHOD"] ?? "unknown";
        $user_agent = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";
        $server_ip = $_SERVER["SERVER_ADDR"] ?? "unknown";
        $post_data = json_encode($_POST) ?? "unknown";

        // Error Log Database

        $sql = "INSERT INTO errors (error_time, message, user_id, ip_address, referer, request_method, user_agent, server_ip, post_data)
                VALUES (CURRENT_TIMESTAMP, :message, :user_id, :ip_address, :referer, :request_method, :user_agent, :server_ip, :post_data)";

        try {
            $stmt = $connection->prepare($sql);

            $stmt->bindValue(":message", $message, PDO::PARAM_STR);

            if($user_id === "unknown") {
                $stmt->bindValue(":user_id", null, PDO::PARAM_STR);
            } 
            
            else {
                $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            }

            $stmt->bindValue(":ip_address", $ip_address, PDO::PARAM_STR);
            $stmt->bindValue(":referer", $referer, PDO::PARAM_STR);
            $stmt->bindValue(":request_method", $request_method, PDO::PARAM_STR);
            $stmt->bindValue(":user_agent", $user_agent, PDO::PARAM_STR);
            $stmt->bindValue(":server_ip", $server_ip, PDO::PARAM_STR);
            $stmt->bindValue(":post_data", $post_data, PDO::PARAM_STR);

            if($stmt->execute()) {
                // Error Log File

                $generated_message = 
                    "Chyba:\n" .
                    "[" . $error_time . "] - " .
                    $message . "\n" .
                    "Podrobnosti:\n" .
                    "User ID: " . $user_id . "\n" .
                    "IP Address: " . $ip_address . "\n" .
                    "Referer: " . $referer . "\n" .
                    "Request Method: " . $request_method ."\n" .
                    "User Agent: " . $user_agent . "\n" .
                    "Server IP: " . $server_ip . "\n" .
                    "Post Data: " . $post_data . "\n\n";

                error_log($generated_message, 3, $file_directory);

                return true;
            }
        } 

        catch(PDOException $e) {
            error_log("Chyba pri ukladaní chyby: " . $e->getMessage() . "\n", 3, "../errors/error.log");
            return false;
        }

        return false;
    }
?>