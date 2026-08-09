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

    if($_SERVER["REQUEST_METHOD"] === "POST") {
        if(isset($_POST["pay"])) {
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
            $address = $_POST["address"]; // Gets The Customer's Address
            $city = $_POST["city"]; // Gets The Customer's City
            $postal_code = $_POST["postal_code"]; // Gets The Customer's Postal Code

            // Stores The Customer Data
            $customer = [
                "first_name" => $first_name,
                "last_name" => $last_name,
                "email_address" => $email_address,
                "phone_number" => $phone_number,
                "address" => $address,
                "city" => $city,
                "postal_code" => $postal_code
            ];

            $shipping_method = $_POST["shipping_method"]; // Gets The Delivery Shipping Method

            if($shipping_method === "packeta") {
                $packeta_branch_id = $_POST["packeta_branch_id"]; // Gets The Packeta Branch ID
                $packeta_branch_name = $_POST["packeta_branch_name"]; // Gets The Packeta Branch Name

                // Stores The Packeta Data
                $packeta = [
                    "branch_id" => $packeta_branch_id,
                    "branch_name" => $packeta_branch_name
                ];
            }

            if($service === "computer_building") {
                $max_price = $_POST["max_price"]; // Gets The Computer Max Price
                $cpu = $_POST["cpu"]; // Gets The Computer CPU
                $gpu = $_POST["gpu"]; // Gets The Computer GPU
                $ram = $_POST["ram"]; // Gets The Computer RAM
                $space = $_POST["space"]; // Gets The Computer Space
                $disk_type = $_POST["disk_type"]; // Gets The Computer Disk Type

                // Stores The Computer Building Data
                $computer_building = [
                    "max_price" => $_POST["max_price"],
                    "cpu" => $cpu,
                    "gpu" => $gpu,
                    "ram" => $ram,
                    "space" => $space,
                    "disk_type" => $disk_type
                ];
            }

            if($service === "computer_maintenance") {
                $device_type = $_POST["device_type"]; // Gets The Device Type
                $problems[] = $_POST["problems"]; // Gets All Problems
                $software_services[] = $_POST["software_services"]; // Gets All Software Services

                // Stores The Computer Maintenance Data
                $computer_maintenance = [
                    "device_type" => $device_type,
                    "problems" => $problems,
                    "software_services" => $software_services
                ];
            }

            if($service === "computer_cleaning") {
                $device_type = $_POST["device_type"]; // Gets The Device Type
                $last_cleaning = $_POST["last_cleaning"]; // Gets The Last Cleaning Time
                $thermal_paste_reapply = $_POST["thermal_paste_re-apply"]; // Gets The Information About Thermal Paste Re-Apply

                // Stores The Computer Cleaning Data
                $computer_cleaning = [
                    "device_type" => $device_type,
                    "last_cleaning" => $last_cleaning,
                    "thermal_paste_reapply" => $thermal_paste_reapply
                ];
            }

            $message = $_POST["message"]; // Gets The Customer's Additional Message

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

                $order_id = $order_object->createOrder(
                    $connection, 
                    $service, 
                    $product_price, 
                    $customer,
                    $stripe_intent_id,
                    $shipping_method,
                    $packeta ?? null,
                    $message ?? null,
                    $computer_building ?? null,
                    $computer_maintenance ?? null,
                    $computer_cleaning ?? null
                );

                if(!$order_id) {
                    die("Pri vytváraní objednávky došlo k chybe.");
                }

                $uploaded_attachments = $_FILES["computer_building_attachment"] ?? $_FILES["computer_maintenance_attachment"] ?? $_FILES["computer_cleaning_attachment"] ?? null;

                if($uploaded_attachments && isset($order_id)) {
                    $uploaded_attachments_amount = count($uploaded_attachments["name"]); // Gets The Amount Of Uploaded Attachments
                    $limit = min($uploaded_attachments_amount, 5); // Maximum Of 5 Files
                
                    for($i = 0; $i < $limit; $i++) {
                        if(empty($uploaded_attachments["name"][$i])) {
                            continue;
                        }
                        
                        $attachment_name = $uploaded_attachments["name"][$i];
                        $attachment_full_path = $uploaded_attachments["full_path"][$i];
                        $attachment_type = $uploaded_attachments["type"][$i];
                        $attachment_tmp_name = $uploaded_attachments["tmp_name"][$i];
                        $attachment_size = $uploaded_attachments["size"][$i];
                        $attachment_error = $uploaded_attachments["error"][$i];

                        if($attachment_error === 0) {
                            $supported_extensions = ["jpg", "jpeg", "png", "gif", "webp", "bmp", "tiff", "tif", "mp4", "mov", "avi", "mkv", "webm", "hevc", "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "rtf", "odt", "ods", "odp"]; // Defines The Supported Extensions
                            $attachment_extension = strtolower(pathinfo($attachment_name, PATHINFO_EXTENSION)); // Gets The Attachment Extension

                            if(in_array($attachment_extension, $supported_extensions)) {
                                $new_attachment_name = uniqid("FILE-", true) . "." . $attachment_extension; // Gets The New Attachment Name

                                // Saves The New Attachment File

                                if(!is_dir("../../content/uploads/attachments")) {
                                    mkdir("../../content/uploads/attachments", 0777, true);
                                }

                                $attachment_upload_path = "../../content/uploads/attachments/" . $new_attachment_name;

                                if(move_uploaded_file($attachment_tmp_name, $attachment_upload_path)) {
                                    $order_object->storeOrderAttachments($connection, (int)$order_id, $new_attachment_name, $attachment_name); // Stores The Attachments
                                }
                            }

                            else {
                                break;
                            }
                        }
                    }
                }

                header("HTTP/1.1 303 See Other");
                header("Location: " . $checkout_session->url);
                exit;
            }
            
            catch(Exception $e) {
                die("Chyba pri spracovávaní platby: " . $e->getMessage());
            }
        }
    }
?>