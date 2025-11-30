<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Docente');

require_once('../orm/dataBase.php');
require_once('../orm/libro.php');
require_once('../orm/prestamo.php');

$db = new Database();
$cnn = $db->getConnection();
$libroModel = new libro($cnn);
$usuario_id = obtenerIdUsuario();

// Obtener estadísticas
$query = "SELECT COUNT(*) as total FROM prestamos WHERE usuario_id = :usuario_id AND estado = 'Activo'";
$stmt = $cnn->prepare($query);
$stmt->execute(['usuario_id' => $usuario_id]);
$prestamosActivos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$librosDisponibles = count(array_filter($libroModel->getAll(), function($l) { return $l['disponible']; }));

// Obtener últimos libros agregados
$ultimosLibros = $libroModel->getLastAdded(6);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Biblioteca</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-card i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #007bff;
        }

        .dashboard-card h3 {
            margin: 10px 0;
            color: #333;
        }

        .dashboard-card .numero {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
        }

        .dashboard-card a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .dashboard-card a:hover {
            background: #0056b3;
        }

        .libros-recientes {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

.libros-grid {
    display: grid;
    gap: 20px;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
}

@media (max-width: 768px) {
    .libros-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); /* más compacta */
    }
}

@media (max-width: 480px) {
    .libros-grid {
        grid-template-columns: 1fr; /* una tarjeta por fila */
        gap: 15px;
    }
}


        .libro-mini {
            text-align: center;
        }

.libro-mini img {
    width: 100%;
    height: auto; /* mantiene proporción y se adapta al ancho */
    border-radius: 5px;
}

.libro-mini h4 {
    font-size: 14px;
    color: #333;
    margin: 5px 0 0 0;
    word-wrap: break-word; /* evita que títulos largos se salgan */
}

@media (max-width: 480px) {
    .libro-mini h4 {
        font-size: 12px; /* más pequeño en móviles */
    }
}

    </style>
</head>

<body>
    <?php include '../maestras/Includes/header.php'; ?>
    <?php include '../maestras/Includes/nav_docente.php'; ?>

    <div class="dashboard">
        <h1>Bienvenido, <?php echo obtenerNombreUsuario(); ?></h1>
        <p style="color: #666; margin-bottom: 30px;">Panel de Docente - Biblioteca Universitaria</p>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <i class="fas fa-bookmark"></i>
                <div class="numero"><?php echo $prestamosActivos; ?></div>
                <h3>Préstamos Activos</h3>
                <a href="mis_prestamos.php">Ver Préstamos</a>
            </div>

            <div class="dashboard-card">
                <i class="fas fa-book"></i>
                <div class="numero"><?php echo $librosDisponibles; ?></div>
                <h3>Libros Disponibles</h3>
                <a href="catalogo.php">Ver Catálogo</a>
            </div>

            <div class="dashboard-card">
                <i class="fas fa-plus-circle"></i>
                <h3>Solicitar Préstamo</h3>
                <p>Hasta 3 libros simultáneos</p>
                <a href="solicitar_prestamo.php">Solicitar</a>
            </div>

            <div class="dashboard-card">
                <i class="fas fa-history"></i>
                <h3>Mi Historial</h3>
                <p>Ver todos tus préstamos</p>
                <a href="historial.php">Ver Historial</a>
            </div>
        </div>

<div class="libros-recientes">
    <h2>Últimos Libros Agregados</h2>
    <div class="libros-grid">
        <?php foreach ($ultimosLibros as $libro): ?>
            <div class="libro-mini">
<img src="<?php echo htmlspecialchars($libro['portada'] ?: 'portada/no portada.png'); ?>"
     alt="<?php echo htmlspecialchars($libro['titulo']); ?>"
     onerror="this.src='portada/no portada.png'">
                <h4><?php echo htmlspecialchars($libro['titulo']); ?></h4>
            </div>
        <?php endforeach; ?>
    </div>
</div>

    <?php include '../maestras/Includes/footer.php'; ?>

    <script>
        // Activar elementos del menú según la página actual
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            const currentPage = window.location.pathname.split('/').pop();
            
            navItems.forEach(item => {
                const link = item.querySelector('a');
                if (link && link.getAttribute('href') === currentPage) {
                    item.classList.add('active');
                }
                
                item.addEventListener('click', function() {
                    navItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>

</html>