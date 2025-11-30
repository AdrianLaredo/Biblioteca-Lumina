<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="library-header">
            <i class="fas fa-book-open library-icon"></i>
            <h1 class="library-title">Biblioteca Lumina</h1>
            <h2 class="login-subtitle">Registro de Usuario</h2>
        </div>

        <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="message error" style="display: block;">
                El nombre de usuario/email ya está en uso
            </div>
        <?php endif; ?>
        
        <form action="registro_usuario.php" method="POST">
            <div class="input-group">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres" placeholder="Ingresa tus nombres" required>
            </div>
            
            <div class="input-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" placeholder="Ingresa tus apellidos" required>
            </div>
            
            <div class="input-group">
                <label for="login">Nombre de Usuario/Email</label>
                <input type="text" id="login" name="login" placeholder="Ingresa tu nombre de usuario o email" required>
            </div>
            
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Crea una contraseña segura" required>
            </div>
            
            <button type="submit" class="btn" name="btnregistrar">Registrarse</button>
        </form>
    </div>
</body>

</html>
