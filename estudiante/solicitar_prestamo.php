<?php
require_once('../maestras/Includes/auth.php');
requiereAlgunRol(['Docente', 'Estudiante']);

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');
require_once('../orm/prestamo.php');

$db = new Database();
$cnn = $db->getConnection();
$libroModel = new libro($cnn);
$prestamoModel = new prestamo($cnn);

$mensaje = '';
$tipoMensaje = '';
$libroSeleccionado = null;

// Obtener libro si se pasa ID por GET
if (isset($_GET['libro_id'])) {
    $libroSeleccionado = $libroModel->getById($_GET['libro_id']);
}

// Procesar solicitud de préstamo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_prestamo'])) {
    $libro_id = $_POST['libro_id'];
    $dias_prestamo = isset($_POST['dias_prestamo']) ? (int)$_POST['dias_prestamo'] : 7;
    $usuario_id = obtenerIdUsuario();

    // Validar que el libro esté disponible
    $libro = $libroModel->getById($libro_id);

    if (!$libro) {
        $mensaje = 'El libro no existe.';
        $tipoMensaje = 'error';
    } elseif (!$libro['disponible']) {
        $mensaje = 'El libro no está disponible en este momento.';
        $tipoMensaje = 'error';
    } else {
        // Verificar que el usuario no tenga más de 3 préstamos activos
        $query = "SELECT COUNT(*) as total FROM prestamos WHERE usuario_id = :usuario_id AND estado = 'Activo'";
        $stmt = $cnn->prepare($query);
        $stmt->execute(['usuario_id' => $usuario_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado['total'] >= 3) {
            $mensaje = 'No puedes tener más de 3 préstamos activos simultáneamente.';
            $tipoMensaje = 'error';
        } else {
            // Crear el préstamo
            $fechaPrestamo = date('Y-m-d H:i:s');
            $fechaDevolucion = date('Y-m-d H:i:s', strtotime("+$dias_prestamo days"));

            $dataPrestamo = [
                'libro_id' => $libro_id,
                'usuario_id' => $usuario_id,
                'fecha_prestamo' => $fechaPrestamo,
                'fecha_devolucion_esperada' => $fechaDevolucion,
                'estado' => 'Activo',
                'notificado' => 0
            ];

            $prestamoModel->insert($dataPrestamo);

            // Marcar libro como no disponible
            $libroModel->update($libro_id, ['disponible' => 0]);

            $mensaje = '¡Préstamo registrado exitosamente! Tienes ' . $dias_prestamo . ' días para devolver el libro.';
            $tipoMensaje = 'exito';

            // Limpiar variable para no mostrar el formulario
            $libroSeleccionado = null;
        }
    }
}

// Obtener libros disponibles
$librosDisponibles = array_filter($libroModel->getAll(), function($libro) {
    return $libro['disponible'];
});
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Préstamo - Biblioteca</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/agregar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .mensaje { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 16px; }
        .mensaje.exito { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .mensaje.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .libro-preview { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; display: flex; gap: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .libro-preview img { width: 150px; height: 200px; object-fit: cover; border-radius: 5px; }
        .libro-preview-info { flex: 1; }
        .libro-preview-info h2 { margin: 0 0 10px 0; color: #333; }
        .libro-preview-info p { margin: 5px 0; color: #666; }
        .formulario { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); margin-top: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        .form-group select, .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group select:focus, .form-group input:focus { outline: none; border-color: #007bff; }
        .btn-group { display: flex; gap: 10px; margin-top: 25px; }
        .btn { flex: 1; padding: 12px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; }
        .btn-primary { background: #28a745; color: white; }
        .btn-primary:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .info-box { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; border-radius: 5px; margin-bottom: 20px; }
        .info-box p { margin: 5px 0; color: #004085; }
    </style>
</head>

<body>
<?php
if ($_SESSION['rol'] === 'Docente') {
    include("../maestras/Includes/nav_docente.php");
} else {
    include("../maestras/Includes/nav_estudiante.php");
}
include("../maestras/Includes/header.php");
?>

<div class="container">
    <h1>Solicitar Préstamo</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <i class="fas fa-<?php echo $tipoMensaje === 'exito' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo $mensaje; ?>
        </div>

        <?php if ($tipoMensaje === 'exito'): ?>
            <div style="text-align: center;">
                <a href="mis_prestamos.php" class="btn btn-primary">
                    <i class="fas fa-bookmark"></i> Ver Mis Préstamos
                </a>
                <a href="catalogo.php" class="btn btn-secondary">
                    <i class="fas fa-book"></i> Ir al Catálogo
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($libroSeleccionado && !$mensaje): ?>
        <div class="libro-preview">
            <img src="<?php echo htmlspecialchars($libroSeleccionado['portada'] ?: 'portada/no portada.png'); ?>"
                 alt="<?php echo htmlspecialchars($libroSeleccionado['titulo']); ?>"
                 onerror="this.src='portada/no portada.png'">
            <div class="libro-preview-info">
                <h2><?php echo htmlspecialchars($libroSeleccionado['titulo']); ?></h2>
                <p><strong>Autor:</strong> <?php echo htmlspecialchars($libroSeleccionado['autor']); ?></p>
                <p><strong>Editorial:</strong> <?php echo htmlspecialchars($libroSeleccionado['editorial']); ?></p>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($libroSeleccionado['categoria']); ?></p>
                <p><strong>Año:</strong> <?php echo $libroSeleccionado['anio_publicacion']; ?></p>
            </div>
        </div>

        <div class="formulario">
            <h3>Detalles del Préstamo</h3>
            <div class="info-box">
                <p><strong>Información importante:</strong></p>
                <p>• Puedes tener un máximo de 3 préstamos activos</p>
                <p>• Recibirás notificaciones antes del vencimiento</p>
                <p>• Los préstamos vencidos generan penalizaciones</p>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="libro_id" value="<?php echo $libroSeleccionado['libro_id']; ?>">

                <div class="form-group">
                    <label for="dias_prestamo">
                        <i class="fas fa-calendar-alt"></i> Duración del Préstamo
                    </label>
                    <select name="dias_prestamo" id="dias_prestamo" required>
                        <option value="7">7 días</option>
                        <option value="14" selected>14 días</option>
                        <option value="21">21 días</option>
                        <option value="30">30 días</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" name="solicitar_prestamo" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirmar Préstamo
                    </button>
                    <a href="catalogo.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

    <?php elseif (!$mensaje): ?>
        <div class="formulario">
            <h3>Selecciona un libro</h3>

            <div class="form-group">
                <label for="libro_select">
                    <i class="fas fa-book"></i> Libro Disponible
                </label>
                <select id="libro_select" onchange="if(this.value) window.location.href='solicitar_prestamo.php?libro_id=' + this.value">
                    <option value="">-- Selecciona un libro --</option>
                    <?php foreach ($librosDisponibles as $libro): ?>
                        <option value="<?php echo $libro['libro_id']; ?>">
                            <?php echo htmlspecialchars($libro['titulo']); ?> - <?php echo htmlspecialchars($libro['autor']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <p style="text-align: center; color: #666; margin-top: 20px;">
                O explora el catálogo completo
            </p>

            <div style="text-align: center; margin-top: 15px;">
                <a href="catalogo.php" class="btn btn-primary">
                    <i class="fas fa-book"></i> Ir al Catálogo
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include("../maestras/Includes/footer.php"); ?>
</body>
</html>
