<?php
require_once('../orm/orm.php');

class prestamo extends Orm {
    function __construct(PDO $connection) {
        parent::__construct('prestamo_id', 'prestamos', $connection);
    }
    
    // Obtener préstamos activos
    public function getActiveLoans() {
        $sql = "SELECT p.*, l.titulo, u.nombres, u.apellidos, u.email 
                FROM {$this->table} p
                JOIN libros l ON p.libro_id = l.libro_id
                JOIN usuarios u ON p.usuario_id = u.usuario_id
                WHERE p.estado = 'Activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener préstamos próximos a vencer (3 días o menos)
    public function getLoansAboutToExpire() {
        $sql = "SELECT p.*, l.titulo, u.nombres, u.apellidos, u.email 
                FROM {$this->table} p
                JOIN libros l ON p.libro_id = l.libro_id
                JOIN usuarios u ON p.usuario_id = u.usuario_id
                WHERE p.estado = 'Activo' 
                AND p.fecha_devolucion_esperada BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
                AND p.notificado = FALSE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Marcar como notificado
    public function markAsNotified($prestamo_id) {
        return $this->update($prestamo_id, ['notificado' => true]);
    }
    
    // Obtener historial de préstamos
    public function getLoanHistory($limit = 50) {
        $sql = "SELECT p.*, l.titulo, u.nombres, u.apellidos 
                FROM {$this->table} p
                JOIN libros l ON p.libro_id = l.libro_id
                JOIN usuarios u ON p.usuario_id = u.usuario_id
                ORDER BY p.fecha_prestamo DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>