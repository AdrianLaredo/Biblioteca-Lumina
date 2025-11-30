<?php
// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('orm/dataBase.php');
    require_once('orm/orm.php');
    require_once('orm/usuario.php');

    $db = new Database();
    $encontrado = $db->verificarDriver();

    if ($encontrado) {
        $cnn = $db->getConnection();
        $usuarioModelo = new usuario($cnn);

        $insertar = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'login' => $_POST['login'],
            'password' => sha1($_POST['password']),
            'rol' => 'Estudiante'
        ];

        if ($usuarioModelo->insert($insertar)) {
            header('Location: login.php?registro=exitoso');
            exit();
        } else {
            header("Location: registro.php?error=1");
            exit();
        }
    }
}
?>
