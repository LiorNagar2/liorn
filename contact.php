<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';

define('SECRET_KEY', '6Lfh8sMUAAAAAOTQNjIaH0mmE7c75-gKuos0La-p');
function getCaptcha($secret_key, $token)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => $secret_key, 'response' => $token)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $arrResponse = json_decode($response, true);
    return $arrResponse;
}

// Instantiation and passing `true` enables exceptions
$mail = new PHPMailer(true);

// subject of the email
$subject = 'New message from liorn.sourceset.co.il website';

// form field names and their translations.
// array variable name => Text to appear in the email
$fields = array('name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'subject' => 'Subject', 'message' => 'Message');

// message that will be displayed when everything is OK :)
$okMessage = 'Contact form successfully submitted. Thank you, I will get back to you soon!';

// If something goes wrong, we will display this message.
$errorMessage = 'There was an error while submitting the form. Please try again later';

/*
 *  LET'S DO THE SENDING
 */

// if you are not debugging and don't need error reporting, turn this off by error_reporting(0);
error_reporting(E_ALL & ~E_NOTICE);

try {

    if (count($_POST) == 0) throw new \Exception('Form is empty');

    // validate captcha
    $g_recaptcha_response = getCaptcha(SECRET_KEY, $_POST['g-recaptcha-response']);
    if ($g_recaptcha_response["success"] == '1' && $g_recaptcha_response["score"] >= 0.5) {
        // valid submission
        // go ahead and do necessary stuff
    } else {
        // spam submission
        // show error message
        throw new \Exception('You are a spam bot.');
    }

    $emailText = "You have a new message from your contact form\n=============================\n";

    foreach ($_POST as $key => $value) {
        // If the field exists in the $fields array, include it in the email
        if (isset($fields[$key])) {
            $emailText .= "<p> $fields[$key]: $value\n</p>";
        }
    }

    //Mail settings
    $mail->SMTPDebug = 0;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host = 'smtp.gmail.com';                    // Set the SMTP server to send through
    $mail->SMTPAuth = true;                                   // Enable SMTP authentication
    $mail->Username = 'liorn.sourceset@gmail.com';                     // SMTP username
    $mail->Password = 'kovenant20@';                               // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('liorn.sourceset@gmail.com', 'Lior Nagar - Web developer');
    $mail->addAddress('liorn.sourceset@gmail.com', 'Lior Nagar');
    $mail->addReplyTo($_POST['email']);

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body = $emailText;
    $mail->AltBody = strip_tags($emailText);

    $mail->send();

    $responseArray = array('type' => 'success', 'message' => $okMessage);
} catch (\Exception $e) {
    $responseArray = array('type' => 'danger', 'message' => $errorMessage);
}


// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
} // else just display the message
else {
    echo $responseArray['message'];
}