<?php

class Venta
{
    private $id_venta;
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
    private $sucursal;
    private $observaciones;

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
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param mixed $observaciones
     */
    public function setObservaciones($observaciones): void
    {
        $this->observaciones = $observaciones;
    }



    /**
     * @return mixed
     */
    public function getSucursal()
    {
        return $this->sucursal;
    }

    /**
     * @param mixed $sucursal
     */
    public function setSucursal($sucursal): void
    {
        $this->sucursal = $sucursal;
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
    public function exeSQL($sql){
        return $this->conectar->query($sql);
    }
    public function insertar()
    {
        $sql="insert into ventas set id_tido='$this->id_tido',id_tipo_pago='$this->id_tipo_pago',fecha_emision='$this->fecha',
        fecha_vencimiento='$this->fechaVenc',dias_pagos='$this->dias_pagos',direccion='$this->direccion',
        serie='$this->serie',numero='$this->numero',id_cliente='$this->id_cliente',total='$this->total', estado='1',
        enviado_sunat='0',id_empresa='$this->id_empresa',sucursal='{$_SESSION['sucursal']}', observacion=?";
        //echo $sql;

        $stmt = $this->conectar->prepare($sql);
        $observacion = $this->observaciones;
        $stmt->bind_param("s", $observacion);
        $result = $stmt->execute();
        if ($result){
            $this->id_venta=$stmt->insert_id;
        }else{
            //echo $this->conectar->error;
        }
        return $result;
    }

    public function anular()
    {
        $sql = "update ventas 
        set estado = '2'   
        where id_venta = '$this->id_venta'";
        return $this->conectar->query($sql);
    }

    public function obtenerId()
    {
        $sql = "select ifnull(max(id_venta) + 1, 1) as codigo 
            from ventas";
        $this->id_venta = $this->conectar->get_valor_query($sql, 'codigo');
    }

    public function verDetalle(){
        $sql = "select ventas.*, c.documento,c.datos
        from ventas  join clientes c on c.id_cliente = ventas.id_cliente
        where id_venta = '$this->id_venta'";
        $respuesta=["res"=>false];
        if ($row = $this->conectar->query($sql)->fetch_assoc()){
            $totalVenta=0;
            $row["detalles"]=[];
            $sql = "SELECT productos_ventas.*,p.descripcion FROM productos_ventas join productos p on p.id_producto = productos_ventas.id_producto WHERE id_venta=".$this->id_venta;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro){
                $totalVenta+=$depro['cantidad']*$depro['precio'];
                $row["detalles"][]=$depro;
            }
            $sql = "SELECT * FROM ventas_servicios WHERE id_venta=".$this->id_venta;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro){
                $depro['precio']= $depro['monto'];
                $totalVenta+=$depro['cantidad']*$depro['monto'];
                $row["detalles"][]=$depro;
            }
            $row["montoTotal"]=number_format($totalVenta, 2, '.', '');
            $respuesta['res']=true;
            $respuesta['data']=$row;
        }
        return json_encode($respuesta);
    }
    public function verDetalle2(){
        $sql = "select ventas.*, c.documento,c.datos
        from ventas  join clientes c on c.id_cliente = ventas.id_cliente
        where id_venta = '$this->id_venta'";
        $respuesta=["res"=>false];
        if ($row = $this->conectar->query($sql)->fetch_assoc()){
            $totalVenta=0;
            $row["detalles"]=[];
            $sql = "SELECT productos_ventas.*,p.descripcion FROM productos_ventas join productos p on p.id_producto = productos_ventas.id_producto WHERE id_venta=".$this->id_venta;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro){
                $totalVenta+=$depro['cantidad']*$depro['precio'];
                $row["detalles"][]=$depro;
            }
            $sql = "SELECT * FROM ventas_servicios WHERE id_venta=".$this->id_venta;
            $result = $this->conectar->query($sql);
            foreach ($result as $depro){
                $depro['precio']= $depro['monto'];
                $totalVenta+=$depro['cantidad']*$depro['monto'];
                $row["detalles"][]=$depro;
            }
            $row["montoTotal"]=number_format($totalVenta, 2, '.', '');
            $respuesta['res']=true;
            $respuesta['data']=$row;
        }
        return $respuesta;
    }

    public function obtenerDatos()
    {
        $sql = "select * 
        from ventas 
        where id_venta = '$this->id_venta'";
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
        $this->sucursal = $fila['sucursal'];
    }

    public function actualizar_envio()
    {
        $query = "update ventas 
        set enviado_sunat = 1 
        where id_venta = '$this->id_venta'";
        return $this->conectar->ejecutar_idu($query);
    }

    public function validarVenta()
    {
        $sql = "select id_venta as codigo  
            from ventas
            where id_tido = '$this->id_tido' and 
                  serie = '$this->serie' and numero = '$this->numero' and id_empresa='{$_SESSION['id_empresa']}'";
        //echo $sql;
        if ($row = $this->conectar->query($sql)->fetch_assoc()){
           $this->id_venta = $row['codigo'];
       }else{
           $this->id_venta =null;
       }

    }

    public function verFilasPeriodo($periodo)
    {
       $sql = "select v.id_venta, v.fecha_emision, ds.abreviatura,
       v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from ventas as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN ventas_sunat vs on v.id_venta = vs.id_venta
        where v.id_empresa = '$this->id_empresa' and v.sucursal='{$_SESSION['sucursal']}' 
          and concat(year(v.fecha_emision), LPAD(month(v.fecha_emision), 2, 0)) = '$periodo' 
        order by v.fecha_emision asc, v.numero asc";

       //echo $sql;

        /*$sql = "select v.id_venta, v.fecha, ds.abreviatura,
       v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from ventas as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN ventas_sunat vs on v.id_venta = vs.id_venta
        where v.id_empresa = '$this->id_empresa' and concat(year(fecha), LPAD(month(fecha), 2, 0)) = '$periodo'
        order by v.fecha asc, v.numero asc";*/

         /*$sql = "select v.id_venta, v.fecha, ds.abreviatura, v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from ventas as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
            inner join ventas_sunat vs on v.id_venta = vs.id_venta
        where v.id_empresa = '$this->id_empresa'
        order by v.fecha asc, v.numero asc";*/


        //echo $sql;
        $rest = $this->conectar->query($sql);
        $lista=[];
        foreach ($rest as $row){
            $row['cod_v']=$row['id_venta'];
            $row['id_venta']=$row['id_venta'].'--'.$row['nombre_xml'];
            $lista[]=$row;
        }
        return $lista;
    }
    public function verFilasPorEmpresas($empresa,$sucuarsal)
    {
        $sql = "select v.id_venta, v.fecha_emision, ds.abreviatura,
       v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from ventas as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN ventas_sunat vs on v.id_venta = vs.id_venta
        where v.id_empresa = '$empresa' and v.sucursal='$sucuarsal'
        order by v.fecha_emision asc, v.numero asc";

        $rest = $this->conectar->query($sql);
        $lista=[];
        foreach ($rest as $row){
            $row['cod_v']=$row['id_venta'];
            $row['id_venta']=$row['id_venta'].'--'.$row['nombre_xml'];
            $lista[]=$row;
        }
        return $lista;
    }
    public function verFilas($periodo)
    {
       $sql = "select v.id_venta, v.fecha_emision, ds.abreviatura,
       v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from ventas as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN ventas_sunat vs on v.id_venta = vs.id_venta
        where v.id_empresa = '$this->id_empresa' and v.sucursal='{$_SESSION['sucursal']}'
        order by v.fecha_emision asc, v.numero asc";

        /*$sql = "select v.id_venta, v.fecha, ds.abreviatura,
       v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from ventas as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN ventas_sunat vs on v.id_venta = vs.id_venta
        where v.id_empresa = '$this->id_empresa' and concat(year(fecha), LPAD(month(fecha), 2, 0)) = '$periodo'
        order by v.fecha asc, v.numero asc";*/

         /*$sql = "select v.id_venta, v.fecha, ds.abreviatura, v.id_tido, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.enviado_sunat, vs.nombre_xml
        from ventas as v
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente
            inner join ventas_sunat vs on v.id_venta = vs.id_venta
        where v.id_empresa = '$this->id_empresa'
        order by v.fecha asc, v.numero asc";*/


        //echo $sql;
        $rest = $this->conectar->query($sql);
        $lista=[];
        foreach ($rest as $row){
            $row['cod_v']=$row['id_venta'];
            $row['id_venta']=$row['id_venta'].'--'.$row['nombre_xml'];
            $lista[]=$row;
        }
        return $lista;
    }

    public function verDocumentosResumen()
    {
        $sql = "select v.id_venta, v.fecha, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '$this->id_empresa' and v.fecha = '$this->fecha' and v.id_tido in (1,3)";
        return $this->conectar->get_Cursor($sql);
    }

    public function verFacturasResumen()
    {
        $sql = "select v.id_venta, v.fecha, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '$this->id_empresa' and v.fecha = '$this->fecha' and v.id_tido = 2 ";
        return $this->conectar->get_Cursor($sql);
    }

    public function verPeriodos()
    {
        $sql = "select DISTINCT(concat(year(fecha), LPAD(month(fecha), 2, 0))) as periodo 
        from ventas 
        where id_empresa = '$this->id_empresa'
        order by concat(year(fecha), LPAD(month(fecha), 2, 0)) desc";
        return $this->conectar->get_Cursor($sql);
    }
}