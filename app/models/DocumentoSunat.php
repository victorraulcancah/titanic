<?php

class DocumentoSunat
{

    private $id_tido;
    private $nombre;
    private $cod_sunat;
    private $abreviatura;
    private $conectar;

    /**
     * DocumentoSunat constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getIdTido()
    {
        return $this->id_tido;
    }

    /**
     * @param mixed $id_tido
     */
    public function setIdTido($id_tido)
    {
        $this->id_tido = $id_tido;
    }

    /**
     * @return mixed
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param mixed $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * @return mixed
     */
    public function getCodSunat()
    {
        return $this->cod_sunat;
    }

    /**
     * @param mixed $cod_sunat
     */
    public function setCodSunat($cod_sunat)
    {
        $this->cod_sunat = $cod_sunat;
    }

    /**
     * @return mixed
     */
    public function getAbreviatura()
    {
        return $this->abreviatura;
    }

    /**
     * @param mixed $abreviatura
     */
    public function setAbreviatura($abreviatura)
    {
        $this->abreviatura = $abreviatura;
    }

    public function insertar()
    {
        $sql = "insert into documentos_sunat 
        values ('$this->id_tido', '$this->nombre', '$this->cod_sunat', '$this->abreviatura')";
        return $this->conectar->ejecutar_idu($sql);
    }

    public function modificar()
    {
        $sql = "update documentos_sunat 
        set nombre = '$this->nombre', cod_sunat = '$this->cod_sunat', abreviatura = '$this->abreviatura'
        where id_tido = '$this->id_tido'";
        return $this->conectar->ejecutar_idu($sql);
    }

    public function obtenerId()
    {
        $sql = "select ifnull(max(id_tido) + 1, 1) as codigo 
            from documentos_sunat";
        $this->id_tido = $this->conectar->get_valor_query($sql, 'codigo');
    }

    public function obtenerDatos()
    {
        $sql = "select * 
        from documentos_sunat 
        where id_tido = '$this->id_tido'";
        $fila = $this->conectar->query($sql)->fetch_assoc();
        $this->nombre = $fila['nombre'];
        $this->cod_sunat = $fila['cod_sunat'];
        $this->abreviatura = $fila['abreviatura'];
    }

    public function verFilas()
    {
        $sql = "select * from documentos_empresas as de 
        inner join documentos_sunat ds on de.id_tido = ds.id_tido 
        where id_empresa = '$this->id_empresa'";
        return $this->conectar->get_Cursor($sql);
    }


}