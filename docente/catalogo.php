<?php
require_once('../maestras/Includes/auth.php');
requiereAlgunRol(['Docente', 'Estudiante']);

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');

$db = new Database();
$cnn = $db->getConnection();
$libroModel = new libro($cnn);

// Obtener parámetros de búsqueda y filtrado
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$disponible = isset($_GET['disponible']) ? $_GET['disponible'] : '';

// Obtener libros según filtros
if ($busqueda) {
    $libros = $libroModel->search($busqueda);
} elseif ($categoria) {
    $libros = $libroModel->getByCategory($categoria);
} else {
    $libros = $libroModel->getAll();
}

// Filtrar por disponibilidad si se especifica
if ($disponible !== '') {
    $libros = array_filter($libros, function($libro) use ($disponible) {
        return $libro['disponible'] == $disponible;
    });
}

// Obtener categorías únicas para el filtro
$todasCategorias = $libroModel->getAll();
$categorias = array_unique(array_column($todasCategorias, 'categoria'));
sort($categorias);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Libros - Biblioteca</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/usuarios.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Contenedor principal */
        .catalogo-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Filtros */
        .filtros {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .filtros form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filtros input, .filtros select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .filtros input[type="text"] {
            flex: 1;
            min-width: 250px;
        }
        .filtros button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .filtros button:hover {
            background: #0056b3;
        }
        .btn-limpiar {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        /* Grid de libros */
        .libros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* Tarjeta del libro */
        .libro-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .libro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .libro-card img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            background: #f0f0f0;
        }

        /* Información del libro */
        .libro-info {
            padding: 15px;
        }
        .libro-titulo {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            min-height: 50px;
        }
        .libro-autor {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .libro-detalles {
            font-size: 13px;
            color: #888;
            margin-bottom: 10px;
        }

        /* Estado de disponibilidad */
        .libro-estado {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .disponible {
            background: #d4edda;
            color: #155724;
        }
        .no-disponible {
            background: #f8d7da;
            color: #721c24;
        }

        /* Botón de solicitud */
        .btn-solicitar {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-solicitar:hover {
            background: #218838;
        }
        .btn-solicitar:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* No resultados */
        .no-resultados {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #666;
        }

        /* Media queries */
        @media (max-width: 768px) {
            .libros-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
        @media (max-width: 480px) {
            .libros-grid {
                grid-template-columns: 1fr;
            }
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

    <div class="catalogo-container">
        <h1>Catálogo de Libros</h1>

        <!-- Filtros -->
        <div class="filtros">
            <form method="GET" action="">
                <input type="text" name="busqueda" placeholder="Buscar por título, autor o editorial..."
                       value="<?php echo htmlspecialchars($busqueda); ?>">

                <select name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoria === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="disponible">
                    <option value="">Todos los libros</option>
                    <option value="1" <?php echo $disponible === '1' ? 'selected' : ''; ?>>Solo disponibles</option>
                    <option value="0" <?php echo $disponible === '0' ? 'selected' : ''; ?>>No disponibles</option>
                </select>

                <button type="submit">
                    <i class="fas fa-search"></i> Buscar
                </button>

                <?php if ($busqueda || $categoria || $disponible !== ''): ?>
                    <a href="catalogo.php" class="btn-limpiar">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Resultados -->
        <?php if (!empty($libros)): ?>
            <p style="margin-bottom: 15px; color: #666;">
                Se encontraron <?php echo count($libros); ?> libro(s)
            </p>

            <div class="libros-grid">
                <?php foreach ($libros as $libro): ?>
                    <div class="libro-card">
                        <img src="<?php echo htmlspecialchars($libro['portada'] ?: 'portada/no portada.png'); ?>"
                             alt="<?php echo htmlspecialchars($libro['titulo']); ?>"
                             onerror="this.src='portada/no portada.png'">

                        <div class="libro-info">
                            <div class="libro-titulo"><?php echo htmlspecialchars($libro['titulo']); ?></div>
                            <div class="libro-autor"><i class="fas fa-user"></i> <?php echo htmlspecialchars($libro['autor']); ?></div>
                            <div class="libro-detalles">
                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($libro['editorial']); ?><br>
                                <i class="fas fa-calendar"></i> <?php echo $libro['anio_publicacion']; ?> |
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($libro['categoria']); ?>
                            </div>

                            <span class="libro-estado <?php echo $libro['disponible'] ? 'disponible' : 'no-disponible'; ?>">
                                <?php echo $libro['disponible'] ? 'Disponible' : 'No Disponible'; ?>
                            </span>

                            <?php if ($libro['disponible']): ?>
                                <a href="solicitar_prestamo.php?libro_id=<?php echo $libro['libro_id']; ?>" class="btn-solicitar">
                                    <i class="fas fa-bookmark"></i> Solicitar Préstamo
                                </a>
                            <?php else: ?>
                                <button class="btn-solicitar" disabled>
                                    <i class="fas fa-ban"></i> No Disponible
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-resultados">
                <i class="fas fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                <h3>No se encontraron libros</h3>
                <p>Intenta con otros criterios de búsqueda</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include("../maestras/Includes/footer.php"); ?>
</body>
</html>
