<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Administrador');

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');
require_once('../orm/usuario.php'); // si manejas usuarios

$db = new Database();
$cnn = $db->getConnection();

$libroModel = new libro($cnn);
$usuarioModel = new usuario($cnn); // si tienes modelo de usuarios

// Estadísticas
$totalLibros = count($libroModel->getAll());
$librosDisponibles = count(array_filter($libroModel->getAll(), fn($l) => $l['disponible']));
$librosPrestados = $totalLibros - $librosDisponibles;

$categorias = array_unique(array_map(fn($l) => $l['categoria'], $libroModel->getAll()));
$totalCategorias = count($categorias);

$totalUsuarios = method_exists($usuarioModel, 'getAll') ? count($usuarioModel->getAll()) : 0;

// Libros recientes (últimos 5 insertados)
$librosRecientes = array_slice(array_reverse($libroModel->getAll()), 0, 5);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Administrador</title>
<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
.main-content { max-width: 1200px; margin: 30px auto; padding: 20px; }
.dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
.card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: center; align-items: center; transition: transform 0.2s, box-shadow 0.2s; }
.card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
.card i { font-size: 30px; margin-bottom: 10px; color: #007bff; }
.card h3 { margin: 5px 0; font-size: 22px; color: #333; }
.card p { margin: 0; font-size: 14px; color: #555; text-align: center; }
.recientes { margin-top: 30px; }
.recientes h2 { margin-bottom: 15px; }
.recientes ul { list-style: none; padding: 0; }
.recientes li { background: #fff; margin-bottom: 10px; padding: 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; }
</style>
</head>
<body>
<?php include '../Maestras/Includes/header.php'; ?>
<?php include '../Maestras/Includes/nav_admin.php'; ?>

<main class="main-content">
    <h1>Panel de Administración - Biblioteca Lumina</h1>

    <div class="dashboard-cards">
        <div class="card">
            <i class="fas fa-book"></i>
            <h3><?= $totalLibros ?></h3>
            <p>Total de libros</p>
        </div>
        <div class="card">
            <i class="fas fa-check-circle"></i>
            <h3><?= $librosDisponibles ?></h3>
            <p>Libros disponibles</p>
        </div>
        <div class="card">
            <i class="fas fa-times-circle"></i>
            <h3><?= $librosPrestados ?></h3>
            <p>Libros prestados</p>
        </div>
        <div class="card">
            <i class="fas fa-th-large"></i>
            <h3><?= $totalCategorias ?></h3>
            <p>Categorías</p>
        </div>
        <?php if($totalUsuarios > 0): ?>
        <div class="card">
            <i class="fas fa-users"></i>
            <h3><?= $totalUsuarios ?></h3>
            <p>Usuarios registrados</p>
        </div>
        <?php endif; ?>
    </div>


</main>

<?php include '../Maestras/Includes/footer.php'; ?>
</body>
</html>
