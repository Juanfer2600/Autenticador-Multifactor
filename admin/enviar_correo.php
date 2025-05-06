<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require '../libs/vendor/autoload.php';

// Default SMTP configuration
if (!isset($host_mail)) $host_mail = 'mail.rhinotechgt.com';
if (!isset($username_mail)) $username_mail = 'no-reply@rhinotechgt.com';
if (!isset($password_mail)) $password_mail = 'Xu9PdBkVdsUSFGh';
if (!isset($port)) $port = 465;
if (!isset($username_name_mail)) $username_name_mail = 'No reply';

function enviarCorreo($destinatarios, $asunto, $cuerpo)
{
    global $host_mail, $username_mail, $password_mail, $port, $username_name_mail;

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear any previous error messages
    unset($_SESSION['error']);
    $_SESSION['debug_info'] = [];

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; // Set the character set to UTF-8

    try {
        // Debug the configuration values
        $_SESSION['debug_info']['config'] = [
            'host' => $host_mail,
            'username' => $username_mail,
            'port' => $port,
            'username_name' => $username_name_mail
        ];

        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                   //Enable verbose debug output for troubleshooting
        $mail->Debugoutput = function($str, $level) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['smtp_debug'][] = $str;
        };
        $mail->isSMTP();                                         //Send using SMTP
        $mail->Host       = $host_mail;                          //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                //Enable SMTP authentication
        $mail->Username   = $username_mail;                      //SMTP username
        $mail->Password   = $password_mail;                      //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         //Enable implicit TLS encryption
        $mail->Port       = $port;                               //TCP port to connect to

        // Validate the From address before setting it
        if (empty($username_mail)) {
            throw new Exception('From email address is empty. Please configure a valid email address.');
        }
        
        if (!filter_var($username_mail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('From email address "' . $username_mail . '" is not valid.');
        }
        
        // Set the sender information
        if (!$mail->setFrom($username_mail, $username_name_mail)) {
            throw new Exception('Failed to set sender: ' . $mail->ErrorInfo);
        }
        
        // Make sure $destinatarios is always an array
        if (!is_array($destinatarios)) {
            $destinatarios = [$destinatarios];
        }
        
        // Validate we have at least one recipient
        if (empty($destinatarios) || (count($destinatarios) === 1 && empty($destinatarios[0]))) {
            throw new Exception('No recipients specified');
        }
        
        // Add each recipient
        foreach ($destinatarios as $destinatario) {
            if (!empty($destinatario)) {
                // Validate email addresses
                if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Recipient email address "' . $destinatario . '" is not valid.');
                }
                $mail->addAddress($destinatario);
            }
        }

        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;

        // Add a plain text alternative for non-HTML mail clients
        $mail->AltBody = strip_tags($cuerpo);

        // Attempt to send the email
        $sent = $mail->send();
        $_SESSION['debug_info']['success'] = true;
        return $sent;
    } catch (Exception $e) {
        // En caso de error, registramos el error en una variable de sesión
        $_SESSION['error'] = 'Error al enviar el mensaje: ' . $mail->ErrorInfo;
        $_SESSION['debug_info']['success'] = false;
        $_SESSION['debug_info']['error'] = $e->getMessage();
        
        // Para debugging
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        
        // Retornamos false para indicar que ocurrió un error
        return false;
    }
}
