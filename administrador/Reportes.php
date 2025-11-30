<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Administrador');

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');

// Conexión a la base de datos
$db = new Database();
$encontrado = $db->verificarDriver();

if (!$encontrado) {
    die("Error de conexión a la base de datos");
}

$cnn = $db->getConnection();
$libroModelo = new libro($cnn);

// Obtener estadísticas de libros
$totalLibros = $libroModelo->getCount();
$librosPorCategoria = $libroModelo->getCountByCategory();
$ultimosLibros = $libroModelo->getLastAdded(5);
$librosMasAntiguos = $libroModelo->getOldest(5);
$librosPorEditorial = $libroModelo->getCountByEditorial();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Libros</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .report-section {
            margin: 20px 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .report-table th {
            background-color: #00b0ff;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .report-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .report-table tr:hover {
            background-color: #f5f9fa;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .stat-card h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #00b0ff;
            padding-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #00b0ff;
            margin: 10px 0;
        }

        .chart-container {
            height: 300px;
            margin: 20px 0;
        }

        .btn-pdf {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-pdf:hover {
            background-color: #c0392b;
        }

        .btn-pdf i {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <?php include '../Maestras/Includes/header.php'; ?>
    <?php include '../Maestras/Includes/nav_admin.php'; ?>

    <main>
        <section class="form-section">
            <h2>Reportes de Libros</h2>

            <!-- Estadísticas generales -->
            <div class="report-section">
                <h3>Estadísticas Generales</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="stat-card">
                        <h3>Total de Libros</h3>
                        <div class="stat-value"><?= $totalLibros ?></div>
                        <p>En la colección de la biblioteca</p>
                    </div>
                    <div class="stat-card">
                        <h3>Categorías</h3>
                        <div class="stat-value"><?= count($librosPorCategoria) ?></div>
                        <p>Diferentes categorías</p>
                    </div>
                    <div class="stat-card">
                        <h3>Editoriales</h3>
                        <div class="stat-value"><?= count($librosPorEditorial) ?></div>
                        <p>Diferentes editoriales</p>
                    </div>
                </div>
            </div>

            <!-- Libros por categoría -->
            <div class="report-section">
                <h3>Libros por Categoría</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($librosPorCategoria as $categoria): ?>
                        <tr>
                            <td><?= htmlspecialchars($categoria['categoria']) ?></td>
                            <td><?= $categoria['total'] ?></td>
                            <td><?= round(($categoria['total'] / $totalLibros) * 100, 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Últimos libros añadidos -->
            <div class="report-section">
                <h3>Últimos Libros Añadidos</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Editorial</th>
                            <th>Año</th>
                            <th>Fecha Ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimosLibros as $libro): ?>
                        <tr>
                            <td><?= htmlspecialchars($libro['titulo']) ?></td>
                            <td><?= htmlspecialchars($libro['autor']) ?></td>
                            <td><?= htmlspecialchars($libro['editorial']) ?></td>
                            <td><?= $libro['anio_publicacion'] ?? 'N/A' ?></td>
                            <td><?= date('d/m/Y', strtotime($libro['fecha_ingreso'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Libros más antiguos -->
            <div class="report-section">
                <h3>Libros Más Antiguos</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Año Publicación</th>
                            <th>Editorial</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($librosMasAntiguos as $libro): ?>
                        <tr>
                            <td><?= htmlspecialchars($libro['titulo']) ?></td>
                            <td><?= htmlspecialchars($libro['autor']) ?></td>
                            <td><?= $libro['anio_publicacion'] ?? 'N/A' ?></td>
                            <td><?= htmlspecialchars($libro['editorial']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Libros por editorial -->
            <div class="report-section">
                <h3>Libros por Editorial</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Editorial</th>
                            <th>Cantidad</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($librosPorEditorial as $editorial): ?>
                        <tr>
                            <td><?= htmlspecialchars($editorial['editorial']) ?></td>
                            <td><?= $editorial['total'] ?></td>
                            <td><?= round(($editorial['total'] / $totalLibros) * 100, 2) ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            
        </section>
    </main>

    <?php include '../Maestras/Includes/footer.php'; ?>
</body>

</html>