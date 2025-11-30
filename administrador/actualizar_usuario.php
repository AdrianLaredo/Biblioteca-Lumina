<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Administrador');

require_once('../orm/orm.php');
require_once('../orm/dataBase.php');
require_once('../orm/usuario.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        $cnn = $db->getConnection();
        $usuarioModelo = new Usuario($cnn);

        $data = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'login' => $_POST['login'],
            'rol' => $_POST['rol']
        ];

        $success = $usuarioModelo->updateById($_POST['usuario_id'], $data);

        if ($success) {
            $_SESSION['mensaje'] = "Usuario actualizado correctamente";
        } else {
            $_SESSION['error'] = "Error al actualizar el usuario";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header('Location: usuarios.php');
    exit();
}
?>