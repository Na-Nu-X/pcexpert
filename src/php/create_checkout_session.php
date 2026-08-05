<?php
    // PHP Code

    require_once "../../vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->load();

    require "../classes/Database.php";
    require "../classes/Order.php";

    $database_object = new Database(); // Initializes The Database Methods
    $order_object = new Order(); // Initializes The Order Methods

    $connection = $database_object->connectionDB(); // Connects To The DB

    // Create Checkout Session

    if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pay"])) {
        $service = $_POST["service"]; // Gets The Service

        if($service === "computer_building") {
            $product_name = "Stavba počítača";
            $product_description = "Stavba počítača na mieru.";
            $product_price = 5000; // 50.00 EUR
        } 
        
        elseif($service === "computer_maintenance") {
            $product_name = "Údržba počítača";
            $product_description = "Údržba a oprava počítača.";
            $product_price = 3000; // 30.00 EUR
        } 

        elseif($service === "computer_cleaning") {
            $product_name = "Čistenie počítača";
            $product_description = "Kompletné čistenie počítača.";
            $product_price = 2000; // 20.00 EUR
        } 
        
        else {
            die("Neznáma služba!");
        }

        $first_name = $_POST["first_name"]; // Gets The Customer's First Name
        $last_name = $_POST["last_name"]; // Gets The Customer's Last Name
        $email_address = $_POST["email_address"]; // Gets The Customer's E-mail Address
        $phone_number = $_POST["phone_number"]; // Gets The Customer's Phone Number
        $message = $_POST["message"] ?? null; // Gets The Customer's Additional Message

        // Checks If The Customer's Required Data Are Filled Out
        if(
            empty($first_name) || 
            empty($last_name) || 
            empty($email_address) || 
            empty($phone_number)
        ) {
            die("Prosím, vyplňte všetky povinné kontaktné údaje pre pokračovanie.");
        }

        \Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET_KEY"]);

        try {
            $checkout_session = \Stripe\Checkout\Session::create([
                "line_items" => [[
                    "price_data" => [
                        "currency" => "eur",

                        "product_data" => [
                            "name" => $product_name,
                            "description" => $product_description
                        ],

                        "unit_amount" => $product_price,
                    ],

                    "quantity" => 1
                ]],

                "mode" => "payment",

                "success_url" => "http://pcexpert.local/objednavka-uspesna?session_id={CHECKOUT_SESSION_ID}",
                "cancel_url" => "http://pcexpert.local/domov"
            ]);

            // Stores The New Order

            $stripe_intent_id = $checkout_session->id; // Gets The Stripe Intent ID

            if($service === "computer_building") $service_text = "Stavba počítača"; // Sets The Service Text
            elseif($service === "computer_maintenance") $service_text = "Údržba počítača"; // Sets The Service Text
            elseif($service === "computer_cleaning") $service_text = "Čistenie počítača"; // Sets The Service Text

            if(!$order_object->createOrder($connection, $service_text, $product_price, $first_name, $last_name, $email_address, $phone_number, $message, $stripe_intent_id)) {
                die("Pri vytváraní objednávky došlo k chybe.");
            }

            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
            exit;
        }
        
        catch(Exception $e) {
            die("Chyba pri spracovávaní platby: " . $e->getMessage());
        }
    }
?>