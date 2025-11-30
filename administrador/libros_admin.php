<?php
require_once('../maestras/Includes/auth.php');
require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');

// Verificar que el usuario es Administrador
requiereRol('Administrador');

$db = new Database();
$cnn = $db->getConnection();
$libroModel = new libro($cnn);

// Inicializar mensajes y libro a editar
$mensaje = '';
$libroEditar = null;

// Procesar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosLibro = [
        'titulo' => $_POST['titulo'],
        'autor' => $_POST['autor'],
        'editorial' => $_POST['editorial'],
        'anio_publicacion' => !empty($_POST['anio_publicacion']) ? $_POST['anio_publicacion'] : null,
        'categoria' => $_POST['categoria'],
        'descripcion' => !empty($_POST['descripcion']) ? $_POST['descripcion'] : null,
        'portada' => !empty($_POST['portada']) ? $_POST['portada'] : null
    ];

    // Crear
    if (isset($_POST['crear'])) {
        if ($libroModel->insert($datosLibro)) {
            $mensaje = '<div class="mensaje-exito"><i class="fas fa-check-circle"></i> Libro creado exitosamente</div>';
            $_POST = [];
        } else {
            $mensaje = '<div class="mensaje-error"><i class="fas fa-exclamation-circle"></i> Error al crear el libro</div>';
        }
    }

    // Actualizar
    if (isset($_POST['actualizar'])) {
        $id = $_POST['libro_id'];
        if ($libroModel->update($id, $datosLibro)) {
            $mensaje = '<div class="mensaje-exito"><i class="fas fa-check-circle"></i> Libro actualizado exitosamente</div>';
        } else {
            $mensaje = '<div class="mensaje-error"><i class="fas fa-exclamation-circle"></i> Error al actualizar el libro</div>';
        }
    }
}

// Procesar edición
if (isset($_GET['editar'])) {
    $libroEditar = $libroModel->getById($_GET['editar']);
    if (!$libroEditar) {
        $mensaje = '<div class="mensaje-error"><i class="fas fa-exclamation-circle"></i> Libro no encontrado</div>';
    }
}

// Procesar eliminación
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    if ($libroModel->delete($idEliminar)) {
        $mensaje = '<div class="mensaje-exito"><i class="fas fa-check-circle"></i> Libro eliminado exitosamente</div>';
    } else {
        $mensaje = '<div class="mensaje-error"><i class="fas fa-exclamation-circle"></i> No se puede eliminar el libro porque tiene historial de préstamos</div>';
    }
}

$libros = $libroModel->getAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administración de Libros</title>
<link rel="stylesheet" href="../css/index.css">
<link rel="stylesheet" href="../css/agregar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Estilos generales */
body { font-family: Arial, sans-serif; background-color: #f5f6fa; margin: 0; padding: 0; }
main { max-width: 1200px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 10px; }

/* Formulario */
.form-section { margin-bottom: 40px; }
input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box; }
textarea { min-height: 80px; }
.form-button { margin-top: 10px; }
button { padding: 8px 14px; border: none; border-radius: 4px; background-color: #007bff; color: white; cursor: pointer; }
button:hover { background-color: #0056b3; }
.btn-cancelar { margin-left: 10px; background-color: #777; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; }
.btn-cancelar:hover { background-color: #555; }

/* Mensajes */
.mensaje-exito { background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px; display: flex; align-items: center; gap: 8px; }
.mensaje-error { background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px; display: flex; align-items: center; gap: 8px; }

/* Grid de libros */
.libros-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px; }
.libro-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
.libro-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
.libro-card img { width: 100%; height: 180px; object-fit: cover; }
.libro-card-body { padding: 10px; }
.libro-card-body h3 { margin: 0 0 8px 0; font-size: 16px; color: #333; }
.libro-card-body p { margin: 3px 0; font-size: 14px; color: #555; }
.acciones { margin-top: 10px; display: flex; gap: 5px; }
.btn-editar { background-color: #28a745; color: white; padding: 4px 8px; font-size: 13px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 3px; }
.btn-editar:hover { background-color: #218838; }
.btn-eliminar { background-color: #dc3545; color: white; padding: 4px 8px; font-size: 13px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 3px; }
.btn-eliminar:hover { background-color: #c82333; }

/* Responsivo */
@media (max-width: 1024px) { .libros-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px)  { .libros-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .libros-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<?php include '../Maestras/Includes/header.php'; ?>
<?php include '../Maestras/Includes/nav_admin.php'; ?>

<main>
    <section class="form-section">
        <h2><?= isset($libroEditar) ? 'Editar' : 'Agregar' ?> Libro</h2>
        <?php echo $mensaje; ?>

        <form action="libros_admin.php" method="POST">
            <?php if (isset($libroEditar)): ?>
                <input type="hidden" name="libro_id" value="<?= $libroEditar['libro_id'] ?>">
                <input type="hidden" name="actualizar" value="1">
            <?php else: ?>
                <input type="hidden" name="crear" value="1">
            <?php endif; ?>

            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($libroEditar['titulo'] ?? $_POST['titulo'] ?? '') ?>" required>

            <label for="autor">Autor:</label>
            <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($libroEditar['autor'] ?? $_POST['autor'] ?? '') ?>" required>

            <label for="editorial">Editorial:</label>
            <input type="text" id="editorial" name="editorial" value="<?= htmlspecialchars($libroEditar['editorial'] ?? $_POST['editorial'] ?? '') ?>" required>

            <label for="anio_publicacion">Año de publicación:</label>
            <input type="number" id="anio_publicacion" name="anio_publicacion" value="<?= htmlspecialchars($libroEditar['anio_publicacion'] ?? $_POST['anio_publicacion'] ?? '') ?>">

            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria" required>
                <option value="">Selecciona una categoría</option>
                <?php
                $categorias = ['Ficción', 'No Ficción', 'Ciencia', 'Tecnología', 'Biografía', 'Historia', 'Autoayuda'];
                $categoriaSeleccionada = $libroEditar['categoria'] ?? $_POST['categoria'] ?? '';
                foreach ($categorias as $categoria) {
                    $selected = ($categoria === $categoriaSeleccionada) ? 'selected' : '';
                    echo "<option value=\"$categoria\" $selected>$categoria</option>";
                }
                ?>
            </select>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($libroEditar['descripcion'] ?? $_POST['descripcion'] ?? '') ?></textarea>

            <label for="portada">URL de la portada:</label>
            <input type="text" id="portada" name="portada" value="<?= htmlspecialchars($libroEditar['portada'] ?? $_POST['portada'] ?? '') ?>">

            <div class="form-button">
                <button type="submit"><?= isset($libroEditar) ? 'Actualizar' : 'Insertar' ?> Libro</button>
                <?php if (isset($libroEditar)): ?>
                    <a href="libros_admin.php" class="btn-cancelar">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section>
        <h2 style="margin-top: 40px;">Listado de Libros</h2>
        <div class="libros-grid">
            <?php foreach ($libros as $libro): ?>
            <div class="libro-card">
                <img src="<?= htmlspecialchars($libro['portada'] ?? 'portada/no portada.png') ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>" onerror="this.src='portada/no portada.png'">
                <div class="libro-card-body">
                    <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
                    <p><strong>Autor:</strong> <?= htmlspecialchars($libro['autor']) ?></p>
                    <p><strong>Editorial:</strong> <?= htmlspecialchars($libro['editorial']) ?></p>
                    <p><strong>Categoría:</strong> <?= htmlspecialchars($libro['categoria']) ?></p>
                    <div class="acciones">
                        <a href="libros_admin.php?editar=<?= $libro['libro_id'] ?>" class="btn-editar"><i class="fas fa-edit"></i> Editar</a>
                        <a href="libros_admin.php?eliminar=<?= $libro['libro_id'] ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este libro?')"><i class="fas fa-trash"></i> Eliminar</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include '../Maestras/Includes/footer.php'; ?>
</body>
</html>
