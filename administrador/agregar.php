<?php
session_start();
require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');

// Inicializar la variable $mensaje al principio
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? null;
    $autor = $_POST['autor'] ?? null;
    $editorial = $_POST['editorial'] ?? null;
    $anio_publicacion = $_POST['anio_publicacion'] ?? null;
    $categoria = $_POST['categoria'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $portada = $_POST['portada'] ?? null;

    if ($titulo && $autor && $editorial && $categoria) {
        $db = new Database();
        $encontrado = $db->verificarDriver();

        if ($encontrado) {
            $cnn = $db->getConnection();
            $libroModelo = new libro($cnn);

            $insertar = [
                'titulo' => $titulo,
                'autor' => $autor,
                'editorial' => $editorial,
                'anio_publicacion' => $anio_publicacion,
                'categoria' => $categoria,
                'descripcion' => $descripcion,
                'portada' => $portada
            ];

            if ($libroModelo->insert($insertar)) {
                $mensaje = '<div class="mensaje-exito">Libro insertado correctamente</div>';
                $_POST = [];
            } else {
                $mensaje = '<div class="mensaje-error">Error al insertar el libro</div>';
            }
        } else {
            $mensaje = '<div class="mensaje-error">No se encontró un driver de base de datos válido</div>';
        }
    } else {
        $mensaje = '<div class="mensaje-advertencia">Por favor llena los campos obligatorios (Título, Autor, Editorial, Categoría)</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Libro</title>
    <link rel="stylesheet" href="../css/index.css">
     <link rel="stylesheet" href="../css/agregar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>

<body>

    <?php include '../Maestras/Includes/header.php'; ?>
    <?php include '../Maestras/Includes/nav_admin.php'; ?>


    <main>
        <section class="form-section">
            <h2>Insertar Libro</h2>
            <?php echo $mensaje; ?>
            <form action="agregar.php" method="POST">
                <label for="titulo" class="required-field">Título:</label>
                <input type="text" id="titulo" name="titulo" 
                value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" required>

                <label for="autor" class="required-field">Autor:</label>
                <input type="text" id="autor" name="autor" 
                value="<?= htmlspecialchars($_POST['autor'] ?? '') ?>" required>

                <label for="editorial" class="required-field">Editorial:</label>
                <input type="text" id="editorial" name="editorial" 
                value="<?= htmlspecialchars($_POST['editorial'] ?? '') ?>" required>

                <label for="anio_publicacion">Año de publicación:</label>
                <input type="number" id="anio_publicacion" name="anio_publicacion" 
                value="<?= htmlspecialchars($_POST['anio_publicacion'] ?? '') ?>">

                <label for="categoria" class="required-field">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecciona una categoría</option>
                    <option value="Ficción" <?= (isset($_POST['categoria']) && $_POST['categoria'] == 'Ficción') ? 'selected' : '' ?>>Ficción</option>
                    <option value="No Ficción" <?= (isset($_POST['categoria']) && $_POST['categoria'] == 'No Ficción') ? 'selected' : '' ?>>No Ficción</option>
                    <option value="Ciencia" <?= (isset($_POST['categoria']) && $_POST['categoria'] == 'Ciencia') ? 'selected' : '' ?>>Ciencia</option>
                    <option value="Tecnología" <?= (isset($_POST['categoria']) && $_POST['categoria'] == 'Tecnología') ? 'selected' : '' ?>>Tecnología</option>
                    <option value="Biografía" <?= (isset($_POST['categoria']) && $_POST['categoria'] == 'Biografía') ? 'selected' : '' ?>>Biografía</option>
                    <option value="Historia" <?= (isset($_POST['categoria']) && $_POST['categoria'] == 'Historia') ? 'selected' : '' ?>>Historia</option>
                    <option value="Autoayuda" <?= (isset($_POST['categoria']) && $_POST['categoria'] == 'Autoayuda') ? 'selected' : '' ?>>Autoayuda</option>
                </select>

                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>

                <label for="portada">URL de la portada:</label>
                <input type="text" id="portada" name="portada" 
                value="<?= htmlspecialchars($_POST['portada'] ?? '') ?>">

                <div class="form-button">
                    <button type="submit">Insertar Libro</button>
                </div>
            </form>
        </section>
    </main>

    <?php include '../Maestras/Includes/footer.php'; ?>

</body>

</html>