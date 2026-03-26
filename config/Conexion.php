<?php

class Conexion
{
    private $servername = HOST_SS;
    private $username = USER_SS;
    private $password = PASSWORD_SS;
    private $bd=DATABASE_SS;
    private $conn;

    function getConexion()
    {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->bd);
        $this->conn->set_charset("utf8");
        return $this->conn;
    }

    function closeConexion()
    {
        $this->conn->close();
    }
}
