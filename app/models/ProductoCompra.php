<?php

class ProductoCompra
{
    private $id_producto;
    private $id_compra;
    private $cantidad;
    private $precio;
    private $costo;
    private $conectar;

    /**
     * ProductoVenta constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
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
    public function setIdProducto($id_producto)
    {
        $this->id_producto = $id_producto;
    }

    /**
     * @return mixed
     */
    public function getIdCompra()
    {
        return $this->id_compra;
    }

    /**
     * @param mixed $id_compra
     */
    public function setIdCompra($id_compra)
    {
        $this->id_compra = $id_compra;
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
    public function setCantidad($cantidad)
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
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }

    /**
     * @return mixed
     */
    public function getCosto()
    {
        return $this->costo;
    }

    /**
     * @param mixed $costo
     */
    public function setCosto($costo)
    {
        $this->costo = $costo;
    }

    public function insertar()
    {
        $sql = "insert into productos_compras
        values (null,'$this->id_producto', '$this->id_compra', '$this->cantidad', '$this->precio', '$this->costo')";
        //echo $sql;
        $result = $this->conectar->query($sql);

        $sql = "update productos set cantidad = cantidad+$this->cantidad where id_producto='$this->id_producto'";
        //echo $sql;
        $this->conectar->query($sql);

        return $result;
    }

    public function eliminar()
    {
        $sql = "delete from productos_compras 
        where id_compra =  '$this->id_compra'";
        return $this->conectar->query($sql);
    }

    public function verFilas()
    {
        $sql = "select pv.id_producto, p.descripcion, p.iscbp, pv.precio, pv.cantidad, pv.costo, p.codsunat 
        from productos_compras as pv 
        inner join productos p on pv.id_producto = p.id_producto 
        where pv.id_compra = '$this->id_compra'";
        //echo $sql;
        return $this->conectar->query($sql);
    }
}
