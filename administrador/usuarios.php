<?php
require_once('../maestras/Includes/auth.php');
requiereRol('Administrador');

require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/usuario.php');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Usuarios</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/agregar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/usuarios.css">
</head>

<body>

    <?php include '../Maestras/Includes/header.php'; ?>
    <?php include '../Maestras/Includes/nav_admin.php'; ?>

    <main>
        <div class="table-container">
            <h3>Usuarios</h3>

            <?php
            try {
                $db = new Database();
                $encontrado = $db->verificarDriver();

                if ($encontrado) {
                    $cnn = $db->getConnection();
                    $usuarioModelo = new Usuario($cnn);
                    $usuarios = $usuarioModelo->getAll();
                } else {
                    throw new Exception("Driver MySQL no encontrado");
                }
            } catch (Exception $e) {
                echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
                $usuarios = [];
            }
            ?>

            <?php if (!empty($usuarios)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Login</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['usuario_id']) ?></td>
                                <td><?= htmlspecialchars($usuario['nombres']) ?></td>
                                <td><?= htmlspecialchars($usuario['apellidos']) ?></td>
                                <td><?= htmlspecialchars($usuario['login']) ?></td>
                                <td><?= htmlspecialchars($usuario['rol']) ?></td>
                                <td class="action-buttons">
                                    <button class="btn btn-edit" onclick="openEditModal(
                                        '<?= $usuario['usuario_id'] ?>',
                                        '<?= htmlspecialchars($usuario['nombres'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($usuario['apellidos'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($usuario['login'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($usuario['rol'], ENT_QUOTES) ?>'
                                    )">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-delete" onclick="confirmDelete(<?= $usuario['usuario_id'] ?>)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">No se encontraron usuarios registrados.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal de Edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Editar Usuario</h2>
            <form id="editForm" action="actualizar_usuario.php" method="POST">
                <input type="hidden" id="editUsuarioId" name="usuario_id">

                <div class="form-group">
                    <label for="editNombres">Nombres:</label>
                    <input type="text" id="editNombres" name="nombres" required>
                </div>

                <div class="form-group">
                    <label for="editApellidos">Apellidos:</label>
                    <input type="text" id="editApellidos" name="apellidos" required>
                </div>

                <div class="form-group">
                    <label for="editLogin">Login:</label>
                    <input type="text" id="editLogin" name="login" required>
                </div>

                <div class="form-group">
                    <label for="editRol">Rol:</label>
                    <select id="editRol" name="rol" required>
                        <option value="Estudiante">Estudiante</option>
                        <option value="Docente">Docente</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" onclick="closeEditModal()">Cancelar</button>

                    <button type="button" class="btn btn-save" onclick="submitEditForm()">Guardar</button>
                </div>

            </form>
        </div>
    </div>

    <?php include '../Maestras/Includes/footer.php'; ?>

    <script>
        // Función para abrir el modal de edición
        function openEditModal(id, nombres, apellidos, login, rol) {
            document.getElementById('editUsuarioId').value = id;
            document.getElementById('editNombres').value = nombres;
            document.getElementById('editApellidos').value = apellidos;
            document.getElementById('editLogin').value = login;
            document.getElementById('editRol').value = rol;

            document.getElementById('editModal').style.display = 'block';
        }

        // Función para cerrar el modal de edición
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Función para enviar el formulario de edición
        function submitEditForm() {
            document.getElementById('editForm').submit();
        }

// Asegúrate de declarar la variable globalmente
let userIdToDelete = null;

function confirmDelete(id) {
    userIdToDelete = id;
    document.getElementById('confirmModal').style.display = 'block';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    userIdToDelete = null;
}

// Espera a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (userIdToDelete !== null) {
            window.location.href = 'eliminar_usuario.php?id=' + userIdToDelete;
        }
    });
});

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('confirmModal');
    if (event.target == modal) {
        closeConfirmModal();
    }
}
    </script>

    
</body>

<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h2>Confirmar Eliminación</h2>
        <p>¿Estás seguro de que deseas eliminar este usuario?</p>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeConfirmModal()">No</button>
            <button type="button" class="btn btn-save" id="confirmDeleteBtn">Sí</button>
        </div>
    </div>
</div>


</html>