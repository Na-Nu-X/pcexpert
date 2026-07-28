<?php
    // PHP Code

    require_once "../../../vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
    $dotenv->load();

    require "../../classes/Database.php";
    require "../../classes/Order.php";

    $database_object = new Database(); // Initializes The Database Methods
    $order_object = new Order(); // Initializes The Order Methods

    $connection = $database_object->connectionDB(); // Connects To The DB

    \Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET_KEY"]);
    $endpoint_secret = $_ENV["STRIPE_WEBHOOK_SECRET"] ?? ""; // Gets The Stripe Webhook Secret

    $payload = @file_get_contents("php://input");
    $sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"] ?? ""; // Gets The Stripe Signature
    $event = null; // Stores The Event

    try {
        if(!empty($endpoint_secret)) {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } 
        
        else {
            $event = \Stripe\Event::constructFrom(json_decode($payload, true));
        }
    } 
    
    catch(\UnexpectedValueException $error) {
        http_response_code(400);
        exit();
    } 
    
    catch(\Stripe\Exception\SignatureVerificationException $error) {
        http_response_code(400);
        exit();
    }

    // Checkout
    if($event->type === "checkout.session.completed") {
        $checkout_session = $event->data->object; // Gets The Checkout Session
        $stripe_intent_id = $checkout_session->id; // Gets The Stripe Intent ID
        $order_object->markAsPaid($connection, $stripe_intent_id); // Marks The Order As Paid
    }

    // Expired Checkout
    if($event->type === "checkout.session.expired") {
        $checkout_session = $event->data->object; // Gets The Checkout Session
        $stripe_intent_id = $checkout_session->id; // Gets The Stripe Intent ID
        $order_object->markAsCancelled($connection, $stripe_intent_id); // Marks The Order As Cancelled
    }

    http_response_code(200);
?>