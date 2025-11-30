<?php
require_once('../maestras/Includes/auth.php');
requiereAlgunRol(['Docente', 'Estudiante']);

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/prestamo.php');

$db = new Database();
$cnn = $db->getConnection();
$prestamoModel = new prestamo($cnn);

// Obtener ID del usuario actual
$usuario_id = obtenerIdUsuario();

// Obtener préstamos activos del usuario
$query = "SELECT p.*, l.titulo, l.autor, l.portada, l.editorial
          FROM prestamos p
          INNER JOIN libros l ON p.libro_id = l.libro_id
          WHERE p.usuario_id = :usuario_id AND p.estado = 'Activo'
          ORDER BY p.fecha_devolucion_esperada ASC";

$stmt = $cnn->prepare($query);
$stmt->execute(['usuario_id' => $usuario_id]);
$prestamosActivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular días restantes y estado
foreach ($prestamosActivos as &$prestamo) {
    $fechaActual = new DateTime();
    $fechaDevolucion = new DateTime($prestamo['fecha_devolucion_esperada']);
    $diferencia = $fechaActual->diff($fechaDevolucion);

    if ($fechaActual > $fechaDevolucion) {
        $prestamo['dias_restantes'] = -$diferencia->days;
        $prestamo['estado_visual'] = 'atrasado';
    } elseif ($diferencia->days <= 2) {
        $prestamo['dias_restantes'] = $diferencia->days;
        $prestamo['estado_visual'] = 'proximo';
    } else {
        $prestamo['dias_restantes'] = $diferencia->days;
        $prestamo['estado_visual'] = 'activo';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Préstamos - Biblioteca</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/usuarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .prestamos-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .prestamos-grid {
            display: grid;
            gap: 20px;
        }

        .prestamo-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 20px;
            border-left: 5px solid #007bff;
        }

        .prestamo-card.atrasado {
            border-left-color: #dc3545;
        }

        .prestamo-card.proximo {
            border-left-color: #ffc107;
        }

        .prestamo-portada {
            flex-shrink: 0;
        }

        .prestamo-portada img {
            width: 120px;
            height: 160px;
            object-fit: cover;
            border-radius: 5px;
            background: #f0f0f0;
        }

        .prestamo-info {
            flex: 1;
        }

        .prestamo-titulo {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .prestamo-detalles {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .prestamo-detalles div {
            margin-bottom: 8px;
        }

        .prestamo-fechas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .fecha-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .fecha-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .fecha-valor {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .alerta-dias {
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
        }

        .alerta-dias.activo {
            background: #d4edda;
            color: #155724;
        }

        .alerta-dias.proximo {
            background: #fff3cd;
            color: #856404;
        }

        .alerta-dias.atrasado {
            background: #f8d7da;
            color: #721c24;
        }

        .no-prestamos {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }

        .no-prestamos i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-prestamos h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .no-prestamos a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .no-prestamos a:hover {
            background: #0056b3;
        }
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

    <div class="prestamos-container">
        <h1>Mis Préstamos Activos</h1>

        <?php if (count($prestamosActivos) > 0): ?>
            <p style="margin-bottom: 20px; color: #666;">
                Tienes <?php echo count($prestamosActivos); ?> libro(s) en préstamo
            </p>

<div class="prestamos-grid">
    <?php foreach ($prestamosActivos as $prestamo): ?>
        <div class="prestamo-card <?php echo htmlspecialchars($prestamo['estado_visual']); ?>">
            <div class="prestamo-portada">
                <img src="<?php echo htmlspecialchars($prestamo['portada'] ?: 'portada/no portada.png'); ?>"
                     alt="<?php echo htmlspecialchars($prestamo['titulo']); ?>"
                     onerror="this.src='portada/no portada.png'">
            </div>
        </div>
</div>
                        <div class="prestamo-info">
                            <div class="prestamo-titulo">
                                <?php echo htmlspecialchars($prestamo['titulo']); ?>
                            </div>

                            <div class="prestamo-detalles">
                                <div><i class="fas fa-user"></i> <strong>Autor:</strong> <?php echo htmlspecialchars($prestamo['autor']); ?></div>
                                <div><i class="fas fa-building"></i> <strong>Editorial:</strong> <?php echo htmlspecialchars($prestamo['editorial']); ?></div>
                            </div>

                            <div class="prestamo-fechas">
                                <div class="fecha-box">
                                    <div class="fecha-label">Fecha de Préstamo</div>
                                    <div class="fecha-valor">
                                        <?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?>
                                    </div>
                                </div>

                                <div class="fecha-box">
                                    <div class="fecha-label">Fecha de Devolución</div>
                                    <div class="fecha-valor">
                                        <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])); ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($prestamo['estado_visual'] === 'atrasado'): ?>
                                <div class="alerta-dias atrasado">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    ¡ATRASADO! <?php echo abs($prestamo['dias_restantes']); ?> día(s) de retraso
                                </div>
                            <?php elseif ($prestamo['estado_visual'] === 'proximo'): ?>
                                <div class="alerta-dias proximo">
                                    <i class="fas fa-clock"></i>
                                    Vence en <?php echo $prestamo['dias_restantes']; ?> día(s)
                                </div>
                            <?php else: ?>
                                <div class="alerta-dias activo">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $prestamo['dias_restantes']; ?> día(s) restantes
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-prestamos">
                <i class="fas fa-bookmark"></i>
                <h3>No tienes préstamos activos</h3>
                <p>Explora nuestro catálogo y solicita un libro</p>
                <a href="catalogo.php">
                    <i class="fas fa-book"></i> Ir al Catálogo
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include("../maestras/Includes/footer.php"); ?>
</body>

</html>
