<?php

class GuiaDetalle
{
    private $guia_detalle_id;
    private $id_guia;
    private $id_producto;
    private $detalles;
    private $unidad;
    private $cantidad;
    private $precio;

    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getGuiaDetalleId()
    {
        return $this->guia_detalle_id;
    }

    /**
     * @param mixed $guia_detalle_id
     */
    public function setGuiaDetalleId($guia_detalle_id): void
    {
        $this->guia_detalle_id = $guia_detalle_id;
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
    public function getIdProducto()
    {
        return $this->id_producto;
    }

    /**
     * @param mixed $id_producto
     */
    public function setIdProducto($id_producto): void
    {
        $this->id_producto = $id_producto;
    }

    /**
     * @return mixed
     */
    public function getDetalles()
    {
        return $this->detalles;
    }

    /**
     * @param mixed $detalles
     */
    public function setDetalles($detalles): void
    {
        $this->detalles = $detalles;
    }

    /**
     * @return mixed
     */
    public function getUnidad()
    {
        return $this->unidad;
    }

    /**
     * @param mixed $unidad
     */
    public function setUnidad($unidad): void
    {
        $this->unidad = $unidad;
    }

    /**
     * @return mixed
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * @param mixed $cantidad
     */
    public function setCantidad($cantidad): void
    {
        $this->cantidad = $cantidad;
    }

    /**
     * @return mixed
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * @param mixed $precio
     */
    public function setPrecio($precio): void
    {
        $this->precio = $precio;
    }

    public function insertar(){
        $sql="INSERT INTO guia_detalles set id_guia='$this->id_guia',
                              id_producto='$this->id_producto',detalles='$this->detalles',
                              unidad='$this->unidad',cantidad='$this->cantidad',precio='$this->precio'";
        $this->conectar->query($sql);
    }
}