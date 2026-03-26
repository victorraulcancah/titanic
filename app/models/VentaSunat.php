<?php
class VentaSunat
{
    private $id_venta;
    private $hash;
    private $nombre_xml;
    private $conectar;
    private $qr_data;

    /**
     * VentaSunat constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getIdVenta()
    {
        return $this->id_venta;
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

    /**
     * @param mixed $id_venta
     */
    public function setIdVenta($id_venta)
    {
        $this->id_venta = $id_venta;
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
    public function setHash($hash)
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
    public function setNombreXml($nombre_xml)
    {
        $this->nombre_xml = $nombre_xml;
    }

    public function insertar()
    {
        $sql = "insert into ventas_sunat 
        values ('$this->id_venta', '$this->hash', '$this->nombre_xml', '$this->qr_data')";
        return $this->conectar->query($sql);
    }

    public function obtenerDatos()
    {
        $sql = "select * 
        from ventas_sunat 
        where id_venta = '$this->id_venta'";
        $fila = $this->conectar->get_Row($sql);
        $this->hash = $fila['hash'];
        $this->nombre_xml = $fila['nombre_xml'];
    }
}