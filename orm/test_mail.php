<?php
require_once('../orm/email.php');

if (enviarCorreo('wackyleo89@gmail.com', 'Prueba PHPMailer', 'Este es un mensaje de prueba')) {
    echo "Correo enviado correctamente.";
} else {
    echo "Error al enviar correo.";
}
