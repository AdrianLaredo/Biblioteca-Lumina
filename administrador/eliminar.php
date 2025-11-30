<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Administrador');

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');

$db = new Database();
$cnn = $db->getConnection();
$libroModel = new libro($cnn);

$mensaje = '';
$tipoMensaje = '';

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {
    $libro_id = $_POST['libro_id'];

    // Verificar si tiene algún préstamo (activo o histórico)
    $stmt = $cnn->prepare("SELECT COUNT(*) FROM prestamos WHERE libro_id = :libro_id");
    $stmt->execute(['libro_id' => $libro_id]);
    $tienePrestamos = $stmt->fetchColumn() > 0;

    if ($tienePrestamos) {
        $mensaje = 'No se puede eliminar el libro porque tiene historial de préstamos';
        $tipoMensaje = 'error';
    } else {
        if ($libroModel->deleteById($libro_id)) {
            $mensaje = 'Libro eliminado correctamente';
            $tipoMensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el libro';
            $tipoMensaje = 'error';
        }
    }
}

// Obtener todos los libros
$libros = $libroModel->getAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Libros</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f4f4; font-family: Arial, sans-serif; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .mensaje {
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
        }
        .mensaje.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .mensaje.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }

.libros-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 libros por fila */
    gap: 20px;
    margin-top: 20px;
}

/* Responsivo: en pantallas medianas */
@media (max-width: 1024px) {
    .libros-grid {
        grid-template-columns: repeat(3, 1fr); /* 3 libros por fila */
    }
}

/* Responsivo: en pantallas pequeñas */
@media (max-width: 768px) {
    .libros-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 libros por fila */
    }
}

/* Responsivo: en móviles muy pequeños */
@media (max-width: 480px) {
    .libros-grid {
        grid-template-columns: 1fr; /* 1 libro por fila */
    }
}


        .libro-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .libro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .libro-card img {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .libro-info h3 {
            font-size: 16px;
            margin: 5px 0;
            color: #333;
        }

        .libro-info p {
            font-size: 13px;
            color: #555;
            margin: 2px 0;
        }

        .btn-eliminar {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-eliminar:hover:not(:disabled) { background: #c82333; }
        .btn-eliminar:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
<?php include '../maestras/Includes/header.php'; ?>
<?php include '../maestras/Includes/nav_admin.php'; ?>

<div class="container">
    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <i class="fas fa-info-circle"></i>
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <div class="libros-grid">
        <?php foreach ($libros as $libro):
            // Verificar si tiene préstamos
            $stmt = $cnn->prepare("SELECT COUNT(*) FROM prestamos WHERE libro_id = :libro_id");
            $stmt->execute(['libro_id' => $libro['libro_id']]);
            $tienePrestamos = $stmt->fetchColumn() > 0;
        ?>
        <div class="libro-card">
            <img src="<?php echo htmlspecialchars($libro['portada'] ?: 'portada/no portada.png'); ?>"
                 alt="<?php echo htmlspecialchars($libro['titulo']); ?>"
                 onerror="this.src='portada/no portada.png'">

            <div class="libro-info">
                <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                <p><strong>Autor:</strong> <?php echo htmlspecialchars($libro['autor']); ?></p>
                <p><strong>Editorial:</strong> <?php echo htmlspecialchars($libro['editorial']); ?></p>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($libro['categoria']); ?></p>
                <p><strong>Año:</strong> <?php echo $libro['anio_publicacion']; ?></p>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="libro_id" value="<?php echo $libro['libro_id']; ?>">
                <button type="submit" name="confirmar_eliminar" class="btn-eliminar"
                        <?php echo $tienePrestamos ? 'disabled title="No se puede eliminar, tiene historial"' : ''; ?>>
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../maestras/Includes/footer.php'; ?>
</body>
</html>
