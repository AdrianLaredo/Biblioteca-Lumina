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
$libroEditar = null;

// Procesar POST de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosActualizar = [
        'titulo' => $_POST['titulo'],
        'autor' => $_POST['autor'],
        'editorial' => $_POST['editorial'],
        'anio_publicacion' => !empty($_POST['anio_publicacion']) ? $_POST['anio_publicacion'] : null,
        'categoria' => $_POST['categoria'],
        'descripcion' => !empty($_POST['descripcion']) ? $_POST['descripcion'] : null,
        'portada' => !empty($_POST['portada']) ? $_POST['portada'] : null,
        'disponible' => isset($_POST['disponible']) ? 1 : 0
    ];

    if (isset($_POST['actualizar'])) {
        $id = $_POST['libro_id'];
        if ($libroModel->update($id, $datosActualizar)) {
            $mensaje = '<div class="mensaje exito"><i class="fas fa-check-circle"></i> Libro actualizado correctamente</div>';
        } else {
            $mensaje = '<div class="mensaje error"><i class="fas fa-exclamation-circle"></i> Error al actualizar el libro</div>';
        }
    }
}

// Si hay un libro a editar
if (isset($_GET['id'])) {
    $libroEditar = $libroModel->getById($_GET['id']);
    if (!$libroEditar) {
        $mensaje = '<div class="mensaje error"><i class="fas fa-exclamation-circle"></i> Libro no encontrado</div>';
    }
}

// Siempre obtengo todos los libros para listarlos
$libros = $libroModel->getAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Modificar Libro</title>
<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="../css/agregar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body {
    background-color: #f5f6fa;
    font-family: Arial, sans-serif;
}
.container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
h1, h2 {
    color: #333;
}
.mensaje {
    padding: 15px 20px;
    margin: 15px 0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
}
.mensaje.exito {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}
.mensaje.error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Grid de libros */
.libros-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 20px;
}
.libro-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.libro-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.libro-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.libro-card-body {
    padding: 15px;
}
.libro-card-body h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #333;
}
.libro-card-body p {
    margin: 5px 0;
    font-size: 14px;
    color: #555;
}
.acciones a {
    display: inline-block;
    margin-top: 10px;
    padding: 6px 10px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
    font-size: 13px;
    margin-right: 5px;
}
.btn-editar { background-color: #28a745; }
.btn-editar:hover { background-color: #218838; }
.btn-eliminar { background-color: #dc3545; }
.btn-eliminar:hover { background-color: #c82333; }

/* Responsivo */
@media (max-width: 1024px) { .libros-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px)  { .libros-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .libros-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<?php include '../maestras/Includes/header.php'; ?>
<?php include '../maestras/Includes/nav_admin.php'; ?>

<div class="container">
    <h1><i class="fas fa-book"></i> Modificar Libro</h1>
    <?php echo $mensaje; ?>

    <?php if ($libroEditar): ?>
    <div class="form-section">
        <!-- Previsualización de portada -->
        <div class="preview-portada">
            <img src="<?= htmlspecialchars($libroEditar['portada'] ?: 'portada/no portada.png') ?>" alt="Portada" onerror="this.src='portada/no portada.png'">
        </div>

        <form method="POST" action="">
            <input type="hidden" name="libro_id" value="<?= $libroEditar['libro_id'] ?>">
            <input type="hidden" name="actualizar" value="1">

            <div class="form-group">
                <label>Título</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($libroEditar['titulo']) ?>" required>
            </div>
            <div class="form-group">
                <label>Autor</label>
                <input type="text" name="autor" value="<?= htmlspecialchars($libroEditar['autor']) ?>" required>
            </div>
            <div class="form-group">
                <label>Editorial</label>
                <input type="text" name="editorial" value="<?= htmlspecialchars($libroEditar['editorial']) ?>" required>
            </div>
            <div class="form-group">
                <label>Año de publicación</label>
                <input type="number" name="anio_publicacion" value="<?= $libroEditar['anio_publicacion'] ?>">
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria" required>
                    <?php
                    $categorias = ['Ficción','No Ficción','Ciencia','Tecnología','Biografía','Historia','Autoayuda'];
                    foreach ($categorias as $cat) {
                        $selected = ($libroEditar['categoria'] === $cat) ? 'selected' : '';
                        echo "<option value='$cat' $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion"><?= htmlspecialchars($libroEditar['descripcion']) ?></textarea>
            </div>
            <div class="form-group">
                <label>URL de la portada</label>
                <input type="text" name="portada" value="<?= htmlspecialchars($libroEditar['portada']) ?>">
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" name="disponible" <?= $libroEditar['disponible'] ? 'checked' : '' ?>>
                <label>Disponible para préstamo</label>
            </div>

            <div class="botones-accion">
                <button type="submit" class="btn-actualizar"><i class="fas fa-save"></i> Actualizar</button>
                <a href="modificar.php" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <h2>Listado de Libros</h2>
    <div class="libros-grid">
        <?php foreach ($libros as $libro): ?>
        <div class="libro-card">
            <img src="<?= htmlspecialchars($libro['portada'] ?: 'portada/no portada.png') ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>" onerror="this.src='portada/no portada.png'">
            <div class="libro-card-body">
                <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
                <p><strong>Autor:</strong> <?= htmlspecialchars($libro['autor']) ?></p>
                <p><strong>Editorial:</strong> <?= htmlspecialchars($libro['editorial']) ?></p>
                <p><strong>Categoría:</strong> <?= htmlspecialchars($libro['categoria']) ?></p>
                <div class="acciones">
                    <a href="modificar.php?id=<?= $libro['libro_id'] ?>" class="btn-editar"><i class="fas fa-edit"></i> Editar</a>
                    <a href="libros_admin.php?eliminar=<?= $libro['libro_id'] ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este libro?')">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php include '../maestras/Includes/footer.php'; ?>
</body>
</html>
