<?php

class ProductoVenta
{
    private $id_producto;
    private $id_venta;
    private $cantidad;
    private $precio;
    private $costo;
    private $conectar;
    private $precio_usado;
    private $medida;
    private $presenta;
    private $presenta_cnt;

    /**
     * @return mixed
     */
    public function getMedida()
    {
        return $this->medida;
    }

    /**
     * @param mixed $medida
     */
    public function setMedida($medida): void
    {
        $this->medida = $medida;
    }

    /**
     * @return mixed
     */
    public function getPresenta()
    {
        return $this->presenta;
    }

    /**
     * @param mixed $presenta
     */
    public function setPresenta($presenta): void
    {
        $this->presenta = $presenta;
    }

    /**
     * @return mixed
     */
    public function getPresentaCnt()
    {
        return $this->presenta_cnt;
    }

    /**
     * @param mixed $presenta_cnt
     */
    public function setPresentaCnt($presenta_cnt): void
    {
        $this->presenta_cnt = $presenta_cnt;
    }


    private $sql;
    private $sql_error;
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
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @param mixed $sql
     */
    public function setSql($sql): void
    {
        $this->sql = $sql;
    }

    /**
     * @return mixed
     */
    public function getSqlError()
    {
        return $this->sql_error;
    }

    /**
     * @param mixed $sql_error
     */
    public function setSqlError($sql_error): void
    {
        $this->sql_error = $sql_error;
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
    public function getIdVenta()
    {
        return $this->id_venta;
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
    public function getPrecioUsado()
    {
        return $this->precio_usado;
    }

    /**
     * @param mixed $precio
     */
    public function setPrecioUsado($precio_usado)
    {
        $this->precio_usado = $precio_usado;
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
        $sql = "insert into productos_ventas 
        values ('$this->id_producto', '$this->id_venta', '$this->cantidad', '$this->precio', '$this->costo', '$this->precio_usado', '$this->medida', '$this->presenta', '$this->presenta_cnt')";
        //echo $sql;
        $this->sql=$sql;
        $result = $this->conectar->query($sql);

        if (!$result){
            $this->sql_error= $this->conectar->error;
        }

        $cntRestante= $this->cantidad * $this->presenta_cnt;
 
        $sql = "update productos set cantidad = cantidad - $cntRestante where id_producto='$this->id_producto'";
        $this->conectar->query($sql);

        return $result;
    }

    public function eliminar($id_venta)
    {
        $sql = "delete from productos_ventas 
        where id_venta =  '$id_venta'";
        return $this->conectar->query($sql);
    }

    public function verFilas()
    {
        $sql = "select pv.id_producto, p.descripcion, p.iscbp, pv.precio, pv.cantidad, pv.costo, p.codsunat 
        from productos_ventas as pv 
        inner join productos p on pv.id_producto = p.id_producto 
        where pv.id_venta = '$this->id_venta'";
        //echo $sql;
        return $this->conectar->query($sql);
    }
}
