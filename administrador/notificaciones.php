<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Administrador');

require_once('../orm/dataBase.php');
require_once('../orm/prestamo.php');
require_once('../orm/email.php'); // Nuevo archivo con función enviarCorreo()

$db = new Database();
$cnn = $db->getConnection();
$prestamoModel = new prestamo($cnn);

// Obtener préstamos próximos a vencer
$prestamos = $prestamoModel->getLoansAboutToExpire();

foreach ($prestamos as $prestamo) {
    $to = $prestamo['email'];
    $subject = "Recordatorio de devolución - Biblioteca Universitaria";

    $message = "Estimado/a {$prestamo['nombres']} {$prestamo['apellidos']},\n\n";
    $message .= "Le recordamos que el libro '{$prestamo['titulo']}' debe ser devuelto ";
    $message .= "antes del " . date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) . ".\n\n";
    $message .= "Por favor, acérquese a la biblioteca para renovar o devolver el libro.\n\n";
    $message .= "Atentamente,\nBiblioteca Universitaria";

    if (enviarCorreo($to, $subject, $message)) {
        $prestamoModel->markAsNotified($prestamo['prestamo_id']);
        echo "✅ Notificación enviada a {$to}<br>";
    } else {
        echo "❌ Error al enviar notificación a {$to}<br>";
    }
}

echo "<br>Proceso de notificación completado. Total: " . count($prestamos);
?>
