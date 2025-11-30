<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Administrador');

require_once('../orm/orm.php');
require_once('../orm/dataBase.php');
require_once('../orm/usuario.php');


if (isset($_GET['id'])) {
    try {
        $db = new Database();
        $cnn = $db->getConnection();
        $usuarioModelo = new Usuario($cnn);

        $success = $usuarioModelo->deleteById($_GET['id']);

        if ($success) {
            $_SESSION['mensaje'] = "Usuario eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar el usuario";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header('Location: usuarios.php');
exit();
?>