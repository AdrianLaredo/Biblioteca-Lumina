<?php
require_once('../maestras/Includes/auth.php');
requiereAlgunRol(['Docente', 'Estudiante']);

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/prestamo.php');

$db = new Database();
$cnn = $db->getConnection();

// Obtener ID del usuario actual
$usuario_id = obtenerIdUsuario();

// Obtener historial de préstamos (todos los estados)
$query = "SELECT p.*, l.titulo, l.autor, l.portada, l.editorial
          FROM prestamos p
          INNER JOIN libros l ON p.libro_id = l.libro_id
          WHERE p.usuario_id = :usuario_id
          ORDER BY p.fecha_prestamo DESC";

$stmt = $cnn->prepare($query);
$stmt->execute(['usuario_id' => $usuario_id]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas
$totalPrestamos = count($historial);
$prestamosDevueltos = count(array_filter($historial, fn($p) => $p['estado'] === 'Devuelto'));
$prestamosActivos = count(array_filter($historial, fn($p) => $p['estado'] === 'Activo'));
$prestamosAtrasados = count(array_filter($historial, fn($p) => $p['estado'] === 'Atrasado'));

// Ruta de portada predeterminada
$portada_predeterminada = 'portada/no portada.png';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Préstamos - Biblioteca</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/usuarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .historial-container { padding: 20px; max-width: 1400px; margin: 0 auto; }

        .estadisticas { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }

        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card i { font-size: 36px; margin-bottom: 10px; }

        .stat-card.total { border-top: 4px solid #007bff; color: #007bff; }
        .stat-card.activos { border-top: 4px solid #ffc107; color: #ffc107; }
        .stat-card.devueltos { border-top: 4px solid #28a745; color: #28a745; }
        .stat-card.atrasados { border-top: 4px solid #dc3545; color: #dc3545; }

        .stat-numero { font-size: 32px; font-weight: bold; margin: 10px 0; }
        .stat-label { color: #666; font-size: 14px; }

        .tabla-container { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #007bff; color: white; }
        thead th { padding: 15px; text-align: left; font-weight: 600; }
        tbody tr { border-bottom: 1px solid #eee; }
        tbody tr:hover { background: #f8f9fa; }
        tbody td { padding: 15px; }

        .libro-miniatura { display: flex; align-items: center; gap: 15px; }
        .libro-miniatura img { width: 50px; height: 70px; object-fit: cover; border-radius: 5px; }
        .libro-titulo { font-weight: bold; color: #333; }
        .libro-autor { color: #666; font-size: 13px; }

        .badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge.activo { background: #fff3cd; color: #856404; }
        .badge.devuelto { background: #d4edda; color: #155724; }
        .badge.atrasado { background: #f8d7da; color: #721c24; }

        .no-historial { text-align: center; padding: 60px 20px; }
        .no-historial i { font-size: 64px; color: #ddd; margin-bottom: 20px; }
        .no-historial h3 { color: #666; }

        @media (max-width: 768px) {
            table { font-size: 12px; }
            tbody td { padding: 10px 5px; }
            .libro-miniatura { flex-direction: column; align-items: flex-start; }
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

<div class="historial-container">
    <h1>Historial de Préstamos</h1>

    <?php if ($totalPrestamos > 0): ?>
        <!-- Estadísticas -->
        <div class="estadisticas">
            <div class="stat-card total"><i class="fas fa-book"></i><div class="stat-numero"><?php echo $totalPrestamos; ?></div><div class="stat-label">Total Préstamos</div></div>
            <div class="stat-card activos"><i class="fas fa-bookmark"></i><div class="stat-numero"><?php echo $prestamosActivos; ?></div><div class="stat-label">Activos</div></div>
            <div class="stat-card devueltos"><i class="fas fa-check-circle"></i><div class="stat-numero"><?php echo $prestamosDevueltos; ?></div><div class="stat-label">Devueltos</div></div>
            <div class="stat-card atrasados"><i class="fas fa-exclamation-triangle"></i><div class="stat-numero"><?php echo $prestamosAtrasados; ?></div><div class="stat-label">Atrasados</div></div>
        </div>

        <!-- Tabla de historial -->
        <div class="tabla-container">
            <table>
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Devolución Esperada</th>
                        <th>Fecha Devolución Real</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $prestamo): ?>
                        <tr>
                            <td>
                                <div class="libro-miniatura">
                                    <img src="<?php echo htmlspecialchars($prestamo['portada'] ?: $portada_predeterminada); ?>"
                                         alt="<?php echo htmlspecialchars($prestamo['titulo']); ?>"
                                         onerror="this.src='<?php echo $portada_predeterminada; ?>'">
                                    <div>
                                        <div class="libro-titulo"><?php echo htmlspecialchars($prestamo['titulo']); ?></div>
                                        <div class="libro-autor"><?php echo htmlspecialchars($prestamo['autor']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])); ?></td>
                            <td><?php echo $prestamo['fecha_devolucion_real'] ? date('d/m/Y', strtotime($prestamo['fecha_devolucion_real'])) : '<span style="color:#999;">-</span>'; ?></td>
                            <td><span class="badge <?php echo strtolower($prestamo['estado']); ?>"><?php echo $prestamo['estado']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-historial">
            <i class="fas fa-history"></i>
            <h3>No tienes historial de préstamos</h3>
            <p>Cuando solicites libros, aparecerán aquí</p>
        </div>
    <?php endif; ?>
</div>

<?php include("../maestras/Includes/footer.php"); ?>
</body>
</html>
