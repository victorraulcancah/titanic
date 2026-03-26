<?php

class Compra
{
    private $id_compra;
    private $fecha;
    private $fechaVenc;
    private $id_tipo_pago;
    private $dias_pagos;
    private $id_tido;
    private $serie;
    private $numero;
    private $id_cliente;
    private $total;
    private $estado;
    private $enviado_sunat;
    private $id_empresa;
    private $direccion;
    private $conectar;

    /**
     * Venta constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getFechaVenc()
    {
        return $this->fechaVenc;
    }

    /**
     * @param mixed $fechaVenc
     */
    public function setFechaVenc($fechaVenc): void
    {
        $this->fechaVenc = $fechaVenc;
    }

    /**
     * @return mixed
     */
    public function getIdTipoPago()
    {
        return $this->id_tipo_pago;
    }

    /**
     * @param mixed $id_tipo_pago
     */
    public function setIdTipoPago($id_tipo_pago): void
    {
        $this->id_tipo_pago = $id_tipo_pago;
    }

    /**
     * @return mixed
     */
    public function getDiasPagos()
    {
        return $this->dias_pagos;
    }

    /**
     * @param mixed $dias_pagos
     */
    public function setDiasPagos($dias_pagos): void
    {
        $this->dias_pagos = $dias_pagos;
    }

    /**
     * @return mixed
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * @param mixed $direccion
     */
    public function setDireccion($direccion): void
    {
        $this->direccion = $direccion;
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
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param mixed $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
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
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * @param mixed $serie
     */
    public function setSerie($serie)
    {
        $this->serie = $serie;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getIdCliente()
    {
        return $this->id_cliente;
    }

    /**
     * @param mixed $id_cliente
     */
    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param mixed $estado
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    /**
     * @return mixed
     */
    public function getEnviadoSunat()
    {
        return $this->enviado_sunat;
    }

    /**
     * @param mixed $enviado_sunat
     */
    public function setEnviadoSunat($enviado_sunat)
    {
        $this->enviado_sunat = $enviado_sunat;
    }

    /**
     * @return mixed
     */
    public function getIdEmpresa()
    {
        return $this->id_empresa;
    }

    /**
     * @param mixed $id_empresa
     */
    public function setIdEmpresa($id_empresa)
    {
        $this->id_empresa = $id_empresa;
    }
    public function exeSQL($sql)
    {
        return $this->conectar->query($sql);
    }
    public function insertar()
    {
        $sql = "insert into compras set id_tido='$this->id_tido',id_tipo_pago='$this->id_tipo_pago',fecha_emision='$this->fecha',
        fecha_vencimiento='$this->fechaVenc',dias_pagos='$this->dias_pagos',direccion='$this->direccion',
        serie='$this->serie',numero='$this->numero',id_cliente='$this->id_cliente',total='$this->total', estado='1',
        enviado_sunat='0',id_empresa='$this->id_empresa'";
        //echo $sql;
        $result = $this->conectar->query($sql);
        if ($result) {
            $this->id_compra = $this->conectar->insert_id;
        } else {
            //echo $this->conectar->error;
        }
        return $result;
    }

    public function anular()
    {
        $sql = "update compras 
        set estado = '2'   
        where id_compra = '$this->id_compra'";
        return $this->conectar->query($sql);
    }

    public function obtenerId()
    {
        $sql = "select ifnull(max(id_compra) + 1, 1) as codigo 
            from compras";
        $this->id_compra = $this->conectar->get_valor_query($sql, 'codigo');
    }

    public function verDetalle()
    {
        $sql = "select compras.*, c.documento,c.datos
        from compras  join clientes c on c.id_cliente = compras.id_cliente
        where id_compra = '$this->id_compra'";
        $respuesta = ["res" => false];
        if ($row = $this->conectar->query($sql)->fetch_assoc()) {
            $totalVenta = 0;
            $row["detalles"] = [];
            $sql = "SELECT productos_compras.*,p.descripcion FROM productos_compras join productos p on p.id_producto = productos_compras.id_producto WHERE id_compra=" . $this->id_compra;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro) {
                $totalVenta += $depro['cantidad'] * $depro['precio'];
                $row["detalles"][] = $depro;
            }
            $sql = "SELECT * FROM compras_servicios WHERE id_compra=" . $this->id_compra;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro) {
                $depro['precio'] = $depro['monto'];
                $totalVenta += $depro['cantidad'] * $depro['monto'];
                $row["detalles"][] = $depro;
            }
            $row["montoTotal"] = number_format($totalVenta, 2, '.', '');
            $respuesta['res'] = true;
            $respuesta['data'] = $row;
        }
        return json_encode($respuesta);
    }
    public function verDetalle2()
    {
        $sql = "select compras.*, c.documento,c.datos
        from compras  join clientes c on c.id_cliente = compras.id_cliente
        where id_compra = '$this->id_compra'";
        $respuesta = ["res" => false];
        if ($row = $this->conectar->query($sql)->fetch_assoc()) {
            $totalVenta = 0;
            $row["detalles"] = [];
            $sql = "SELECT productos_compras.*,p.descripcion FROM productos_compras join productos p on p.id_producto = productos_compras.id_producto WHERE id_compra=" . $this->id_compra;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro) {
                $totalVenta += $depro['cantidad'] * $depro['precio'];
                $row["detalles"][] = $depro;
            }
            $sql = "SELECT * FROM compras_servicios WHERE id_compra=" . $this->id_compra;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro) {
                $depro['precio'] = $depro['monto'];
                $totalVenta += $depro['cantidad'] * $depro['monto'];
                $row["detalles"][] = $depro;
            }
            $row["montoTotal"] = number_format($totalVenta, 2, '.', '');
            $respuesta['res'] = true;
            $respuesta['data'] = $row;
        }
        return $respuesta;
    }

    public function obtenerDatos()
    {
        $sql = "select * 
        from compras 
        where id_compra = '$this->id_compra'";
        $fila = $this->conectar->query($sql)->fetch_assoc();
        $this->fecha = $fila['fecha_emision'];
        $this->id_tido = $fila['id_tido'];
        $this->serie = $fila['serie'];
        $this->numero = $fila['numero'];
        $this->id_cliente = $fila['id_cliente'];
        $this->total = $fila['total'];
        $this->estado = $fila['estado'];
        $this->enviado_sunat = $fila['enviado_sunat'];
        $this->id_empresa = $fila['id_empresa'];
    }

    public function actualizar_envio()
    {
        $query = "update compras 
        set enviado_sunat = 1 
        where id_compra = '$this->id_compra'";
        return $this->conectar->ejecutar_idu($query);
    }

    public function validarVenta()
    {
        $sql = "select id_compra as codigo  
            from compras
            where id_tido = '$this->id_tido' and serie = '$this->serie' and numero = '$this->numero'";
        //echo $sql;
        if ($row = $this->conectar->query($sql)->fetch_assoc()) {
            $this->id_compra = $row['codigo'];
        } else {
            $this->id_compra = null;
        }
    }

    public function verFilas($periodo)
    {
        $sql = "select v.id_compra, v.fecha_emision, ds.abreviatura,
       v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from compras as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN compras_sunat vs on v.id_compra = vs.id_compra
        where v.id_empresa = '$this->id_empresa' 
        order by v.fecha_emision asc, v.numero asc";

        /*$sql = "select v.id_compra, v.fecha, ds.abreviatura,
       v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from compras as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN compras_sunat vs on v.id_compra = vs.id_compra
        where v.id_empresa = '$this->id_empresa' and concat(year(fecha), LPAD(month(fecha), 2, 0)) = '$periodo'
        order by v.fecha asc, v.numero asc";*/

        /*$sql = "select v.id_compra, v.fecha, ds.abreviatura, v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from compras as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
            inner join compras_sunat vs on v.id_compra = vs.id_compra
        where v.id_empresa = '$this->id_empresa'
        order by v.fecha asc, v.numero asc";*/


        //echo $sql;
        $rest = $this->conectar->query($sql);
        $lista = [];
        foreach ($rest as $row) {
            $row['cod_v'] = $row['id_compra'];
            $row['id_compra'] = $row['id_compra'] . '--' . $row['nombre_xml'];
            $lista[] = $row;
        }
        return $lista;
    }

    public function verDocumentosResumen()
    {
        $sql = "select v.id_compra, v.fecha, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from compras as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '$this->id_empresa' and v.fecha = '$this->fecha' and v.id_tido in (1,3)";
        return $this->conectar->get_Cursor($sql);
    }

    public function verFacturasResumen()
    {
        $sql = "select v.id_compra, v.fecha, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from compras as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '$this->id_empresa' and v.fecha = '$this->fecha' and v.id_tido = 2 ";
        return $this->conectar->get_Cursor($sql);
    }

    public function verPeriodos()
    {
        $sql = "select DISTINCT(concat(year(fecha), LPAD(month(fecha), 2, 0))) as periodo 
        from compras 
        where id_empresa = '$this->id_empresa'
        order by concat(year(fecha), LPAD(month(fecha), 2, 0)) desc";
        return $this->conectar->get_Cursor($sql);
    }
    public function insertarCompra($id_tido, $id_tipo_pago, $id_proveedor, $fecha_emision, $fecha_vencimiento, $direccion, $serie, $numero, $total, $id_empresa, $moneda)
    {
        $sql = "INSERT INTO compras(id_tido,id_tipo_pago,id_proveedor,fecha_emision,fecha_vencimiento,direccion,serie,numero,total,id_empresa,moneda,sucursal)
        VALUES ($id_tido,$id_tipo_pago,$id_proveedor,'$fecha_emision','$fecha_vencimiento','$direccion','$serie','$numero',$total,$id_empresa,'$moneda','{$_SESSION['id_empresa']}')";
        $result = $this->conectar->query($sql);
        /*    $sql = "update productos set cantidad = cantidad+$cantidad where id_producto=$idProducto"; */
        if ($result) {
            return $this->conectar->insert_id;
        }
    }
    public function updateStock($cantidad, $idProducto)
    {
        $sql = "update productos set cantidad = cantidad+$cantidad where id_producto=$idProducto";
        $result = $this->conectar->query($sql);
        if ($result) {
            return $result;
        }
    }
    public function insertProductosCompras($id_producto, $id_compra, $cantidad, $precio)
    {
        $sql = "INSERT INTO productos_compras(id_producto,id_compra,cantidad,precio)
        VALUES ($id_producto,$id_compra,$cantidad,'$precio')";
        $result = $this->conectar->query($sql);
        /*    $sql = "update productos set cantidad = cantidad+$cantidad where id_producto=$idProducto"; */
        if ($result) {
            return $result;
        }
    }
    public function insertDiasCompras($id_compra, $monto, $fecha)
    {
        $sql = "INSERT INTO dias_compras (id_compra,monto,fecha,estado) VALUES($id_compra,'$monto','$fecha',0)";
        /*  return $sql; */
        $result = $this->conectar->query($sql);

        if ($result) {
            return $result;
        }
    }
}
