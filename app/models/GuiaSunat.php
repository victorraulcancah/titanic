<?php

class GuiaSunat
{
    private $id_guia;
    private $hash;
    private $nombre_xml;
    private $qr_data;

    private $conectar;

    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getIdGuia()
    {
        return $this->id_guia;
    }

    /**
     * @param mixed $id_guia
     */
    public function setIdGuia($id_guia): void
    {
        $this->id_guia = $id_guia;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return mixed
     */
    public function getNombreXml()
    {
        return $this->nombre_xml;
    }

    /**
     * @param mixed $nombre_xml
     */
    public function setNombreXml($nombre_xml): void
    {
        $this->nombre_xml = $nombre_xml;
    }

    /**
     * @return mixed
     */
    public function getQrData()
    {
        return $this->qr_data;
    }

    /**
     * @param mixed $qr_data
     */
    public function setQrData($qr_data): void
    {
        $this->qr_data = $qr_data;
    }

    public function insertar(){
        $sql = "INSERT INTO guia_sunat set  id_guia='$this->id_guia',
                           hash='$this->hash',nombre_xml='$this->nombre_xml',
                           qr_data='$this->qr_data'";
        return $this->conectar->query($sql);
    }
}