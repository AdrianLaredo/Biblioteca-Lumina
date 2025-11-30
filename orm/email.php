<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreo($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'luminabiblioteca@gmail.com'; // tu correo remitente
        $mail->Password   = 'jhugskwpcfwsspqu'; // contraseña de aplicación aquí
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('luminabiblioteca@gmail.com', 'Biblioteca Universitaria');
        $mail->addAddress($to);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}
