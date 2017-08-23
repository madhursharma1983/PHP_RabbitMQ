<?php
require_once __DIR__ . '/vendor/autoload.php';
include("/var/www/html/FE1/includes/function_master.php");
$objulife = new ulife();
use PhpAmqpLib\Connection\AMQPConnection;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('email_queue', false, false, false, false);

echo ' * Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg){

    echo " * Message received", "\n";
    $data = json_decode($msg->body, true);
	print_r($data);

    $from = $data['from'];
    $from_email = $data['from_email'];
    $to_email = $data['to_email'];
    $subject = $data['subject'];
    $message = $data['message'];
    
	$headers = 'MIME-Version: 1.0' . PHP_EOL;
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . PHP_EOL;
	$headers .= "From: $mail_from" . PHP_EOL;
	if (!empty($mail_bcc)) {
		$headers .= 'BCC: ' . $mail_bcc . PHP_EOL;
	}
	mail($to_email, $subject, $message, $headers, "-f $from_email");
    
    echo " * Message was sent", "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('email_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}
?>
