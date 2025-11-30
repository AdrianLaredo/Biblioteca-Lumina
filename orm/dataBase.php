<?php

class Database
{
    private $connection;

    public function __construct() {}

    public function verificarDriver()
    {
        /*Buscar - Driver*/
        $miArray = (PDO::getAvailableDrivers());
        $encontrado = false;
        foreach ($miArray as $n) {
            if ($n == 'mysql') {
                $encontrado = true;
                break;
            }
        }

        return $encontrado;
    }

    public function getConnection()
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];



        //$dns = "mysql:host=localhost;dbname=pw";         //PUERTO = 3306
        $dns = "mysql:host=localhost;port=3306;dbname=pw"; //PUERTO = 3307
        $user = "root";
        $password = "";
        $this->connection = new PDO($dns, $user, $password, $options);
        $this->connection->exec("SET CHARACTER SET UTF8");
        return $this->connection;
    }
}
