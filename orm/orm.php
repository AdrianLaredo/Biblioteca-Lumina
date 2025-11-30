<?php
class Orm
{
    protected $id;
    protected $table;
    protected $db;

    function __construct($id, $table, PDO $conn)
    {
        $this->id = $id;
        $this->table = $table;
        $this->db = $conn;
    }

    function getAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stm = $this->db->prepare($sql);
        $stm->execute();
        return $stm->fetchAll();
    }

    function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->id} = :id";
        $stm = $this->db->prepare($sql);
        $stm->bindParam(':id', $id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetch();
    }

    function deleteById($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->id} = :id";
        $stm = $this->db->prepare($sql);
        $stm->bindParam(':id', $id, PDO::PARAM_INT);
        return $stm->execute();
    }

    // Cambié updateById a update para que coincida con tu llamada en prestamo.php
    function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET ";
        foreach ($data as $key => $value) {
            $sql .= "{$key} = :{$key}, ";
        }

        $sql = rtrim($sql, ', ');
        $sql .= " WHERE {$this->id} = :id";

        // Añadimos el id al array de parámetros
        $data['id'] = $id;

        $stm = $this->db->prepare($sql);

        try {
            return $stm->execute($data);
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }

    function insert($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stm = $this->db->prepare($sql);

        try {
            return $stm->execute($data);
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            return false;
        }
    }

    // Método específico para usuarios u otras búsquedas
    function getByField($field, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value";
        $stm = $this->db->prepare($sql);
        $stm->bindParam(':value', $value);
        $stm->execute();
        return $stm->fetch();
    }
}
?>
