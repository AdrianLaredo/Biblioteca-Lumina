<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="library-header">
            <i class="fas fa-book-open library-icon"></i>
            <h1 class="library-title">Biblioteca Lumina</h1>
            <h2 class="login-subtitle">Iniciar Sesión</h2>
        </div>

        <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="message error" style="display: block;">
                Usuario o contraseña incorrectos
            </div>
        <?php endif; ?>

        <form action="login_usuarios.php" method="POST">
            <div class="input-group">
                <label for="usrName">Usuario</label>
                <input type="text" id="usrName" name="usrName" placeholder="Ingresa tu usuario" required>
            </div>

            <div class="input-group">
                <label for="usrPwd">Contraseña</label>
                <input type="password" id="usrPwd" name="usrPwd" placeholder="Ingresa tu contraseña" required>
            </div>

            <button type="submit" class="btn" name="btningresar">Ingresar</button>

            <a href="registro.php" class="register-link">¿No tienes cuenta? Regístrate</a>
        </form>
    </div>
</body>

</html>
