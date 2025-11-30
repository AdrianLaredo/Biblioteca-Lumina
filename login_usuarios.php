<?php
session_start();
require_once('orm/dataBase.php');
require_once('orm/orm.php');
require_once('orm/usuario.php');

$db = new Database();
$encontrado = $db->verificarDriver();

if ($encontrado) {
    $cnn = $db->getConnection();
    $usuarioModelo = new usuario($cnn);

    if (isset($_POST["btningresar"])) {
        $login = $_POST["usrName"];
        $password = sha1($_POST["usrPwd"]);

        $data = "login = '" . $login . "' AND password = '" . $password . "'";
        $usuario = $usuarioModelo->validalogin($data);

        if ($usuario) {
            $usr = $usuario['nombres'] . ' ' . $usuario['apellidos'];
            $_SESSION["usr"] = $usr;

            // Guardar el rol y el ID de usuario en sesión
            $_SESSION["rol"] = $usuario['rol'];
            $_SESSION["usuario_id"] = $usuario['usuario_id'];

            $rol = $usuario['rol'];

            if ($rol == 'Administrador') {
                header("Location: administrador/index_admin.php");
                exit();
            } else if ($rol == 'Docente') {
                header("Location: docente/index_docente.php");
                exit();
            } else {
                header("Location: estudiante/index_estudiante.php");
                exit();
            }
        } else {
            header("Location: login.php?error=1");
            exit();
        }
    }
} else {
    header("Location: login.php?error=2");
    exit();
}
