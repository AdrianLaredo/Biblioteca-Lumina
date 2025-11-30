<?php
/**
 * Middleware de Autenticación y Control de Acceso
 * Sistema centralizado para gestionar sesiones y permisos por rol
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function estaAutenticado() {
    return isset($_SESSION['usr']) && isset($_SESSION['rol']);
}

/**
 * Redirige al login si el usuario no está autenticado
 */
function requiereAutenticacion() {
    if (!estaAutenticado()) {
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param string $rol El rol requerido ('Administrador', 'Docente', 'Estudiante')
 * @return bool
 */
function tieneRol($rol) {
    return estaAutenticado() && $_SESSION['rol'] === $rol;
}

/**
 * Verifica si el usuario tiene alguno de los roles permitidos
 * @param array $rolesPermitidos Array de roles permitidos
 * @return bool
 */
function tieneAlgunRol($rolesPermitidos) {
    if (!estaAutenticado()) {
        return false;
    }
    return in_array($_SESSION['rol'], $rolesPermitidos);
}

/**
 * Requiere que el usuario tenga un rol específico, de lo contrario redirige
 * @param string $rol El rol requerido
 */
function requiereRol($rol) {
    requiereAutenticacion();

    if (!tieneRol($rol)) {
        // Redirigir a su página correspondiente según su rol
        redirigirSegunRol();
        exit();
    }
}

/**
 * Requiere que el usuario tenga alguno de los roles permitidos
 * @param array $rolesPermitidos Array de roles permitidos
 */
function requiereAlgunRol($rolesPermitidos) {
    requiereAutenticacion();

    if (!tieneAlgunRol($rolesPermitidos)) {
        // Redirigir a su página correspondiente según su rol
        redirigirSegunRol();
        exit();
    }
}

/**
 * Redirige al usuario a su página de inicio según su rol
 */
function redirigirSegunRol() {
    if (!estaAutenticado()) {
        header('Location: ../login.php');
        exit();
    }

    switch ($_SESSION['rol']) {
        case 'Administrador':
            header('Location: ../administrador/index_admin.php');
            break;
        case 'Docente':
            header('Location: ../docente/index_docente.php');
            break;
        case 'Estudiante':
            header('Location: ../estudiante/index_estudiante.php');
            break;
        default:
            header('Location: ../login.php');
            break;
    }
    exit();
}

/**
 * Obtiene el nombre del usuario actual
 * @return string|null
 */
function obtenerNombreUsuario() {
    return $_SESSION['usr'] ?? null;
}

/**
 * Obtiene el rol del usuario actual
 * @return string|null
 */
function obtenerRol() {
    return $_SESSION['rol'] ?? null;
}

/**
 * Obtiene el ID del usuario actual
 * @return int|null
 */
function obtenerIdUsuario() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Cierra la sesión del usuario
 */
function cerrarSesion() {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

/**
 * Verifica permisos específicos para operaciones
 * @param string $operacion Nombre de la operación (ej: 'gestionar_usuarios', 'prestar_libros', etc.)
 * @return bool
 */
function tienePermiso($operacion) {
    if (!estaAutenticado()) {
        return false;
    }

    $rol = $_SESSION['rol'];

    // Definir permisos por rol
    $permisos = [
        'Administrador' => [
            'gestionar_usuarios',
            'gestionar_libros',
            'gestionar_prestamos',
            'ver_reportes',
            'enviar_notificaciones',
            'ver_todos_prestamos',
            'eliminar_usuarios',
            'eliminar_libros'
        ],
        'Docente' => [
            'ver_libros',
            'solicitar_prestamo',
            'ver_mis_prestamos',
            'renovar_prestamo',
            'ver_catalogo'
        ],
        'Estudiante' => [
            'ver_libros',
            'solicitar_prestamo',
            'ver_mis_prestamos',
            'ver_catalogo'
        ]
    ];

    // Verificar si el rol tiene el permiso
    if (!isset($permisos[$rol])) {
        return false;
    }

    return in_array($operacion, $permisos[$rol]);
}

/**
 * Registra actividad del usuario (opcional, para auditoría)
 * @param string $accion Descripción de la acción realizada
 */
function registrarActividad($accion) {
    // Esta función puede implementarse para guardar logs en BD o archivos
    // Por ahora, solo es un placeholder
    $usuario = obtenerNombreUsuario();
    $rol = obtenerRol();
    $fecha = date('Y-m-d H:i:s');

    // TODO: Implementar guardado en tabla de auditoría
    // Por ejemplo: INSERT INTO auditoria (usuario, rol, accion, fecha) VALUES (...)
}
?>
