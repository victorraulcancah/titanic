<?php

class RutaVendedor
{

    private $id;
    private $id_ruta;
    private $id_usuario;

    /**
     * RutaVendedor constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getIdRuta()
    {
        return $this->id_ruta;
    }

    /**
     * @param mixed $id_ruta
     */
    public function setIdRuta($id_ruta)
    {
        $this->id_ruta = $id_ruta;
    }

    /**
     * @return mixed
     */
    public function getIdUsuario()
    {
        return $this->id_usuario;
    }

    /**
     * @param mixed $id_usuario
     */
    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }
    
    public function obtenerDatos()
    {
        $sql = "select * from rutas_vendedor group by id_ruta;";
        $result = $this->conectar->query($sql);
        $resultSet = array();
        while ($row = $result->fetch_assoc()) {
            $resultSet[] = $row['id_ruta'];
        }
        return $resultSet;
    }

}