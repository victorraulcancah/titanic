<?php

class Cliente
{
    private $id_cliente;
    private $documento;
    private $datos;
    private $direccion;
    private $distrito;
    private $id_empresa;
    private $telefono;
    private $diasVisitas;
    private $email;
    private $total_venta;
    private $ultima_venta;
    private $id_ruta;
    private $mercado;
    private $conectar;

    /**
     * Cliente constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getDiasVisitas()
    {
        return $this->diasVisitas;
    }

    /**
     * @param mixed $direccion2
     */
    public function setDiasVisitas($diasVisitas): void
    {
        $this->diasVisitas = $diasVisitas;
    }

    /**
     * @return mixed
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * @param mixed $telefono2
     */
    public function setDistrito($distrito): void
    {
        $this->distrito = $distrito;
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
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * @param mixed $documento
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    }

    /**
     * @return mixed
     */
    public function getDatos()
    {
        return $this->datos;
    }

    /**
     * @param mixed $datos
     */
    public function setTelefono($telefono)
    {
        $this->telefono = strtoupper($telefono);
    }
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param mixed $datos
     */
    public function setEmail($email)
    {
        $this->email = strtoupper($email);
    }
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $datos
     */
    public function setDatos($datos)
    {
        $this->datos = strtoupper($datos);
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
    public function setDireccion($direccion)
    {
        $this->direccion = strtoupper($direccion);
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

    /**
     * @return mixed
     */
    public function getTotalVenta()
    {
        return $this->total_venta;
    }

    /**
     * @param mixed $total_venta
     */
    public function setTotalVenta($total_venta)
    {
        $this->total_venta = $total_venta;
    }

    /**
     * @return mixed
     */
    public function getUltimaVenta()
    {
        return $this->ultima_venta;
    }

    /**
     * @param mixed $ultima_venta
     */
    public function setUltimaVenta($ultima_venta)
    {
        $this->ultima_venta = $ultima_venta;
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
    public function getMercado()
    {
        return $this->mercado;
    }

    /**
     * @param mixed $id_ruta
     */
    public function setMercado($mercado)
    {
        $this->mercado = $mercado;
    }

    public function insertar()
    {
        $sql = "insert into clientes values (NULL, '$this->documento', '$this->datos', '$this->direccion','$this->distrito','$this->telefono','$this->diasVisitas','$this->email', {$_SESSION['id_empresa']}, '1000-01-01', '0','$this->id_ruta','$this->mercado')";
        $result =  $this->conectar->query($sql);

        if ($result) {
            $this->id_cliente = $this->conectar->insert_id;
        }
        return $result;
    }

    public function modificar($documento, $datos, $id_cliente)
    {

        $sql = "update clientes 
        set documento = '$documento', datos = '$datos' 
        where id_cliente = '$id_cliente'";
        $result =  $this->conectar->query($sql);
        if ($result) {
            $this->id_cliente = $this->conectar->insert_id;
        }
        return $result;
    }

    public function obtenerId()
    {
        $sql = "select ifnull(max(id_cliente) + 1, 1) as codigo from clientes";
        $this->id_cliente = $this->conectar->get_valor_query($sql, 'codigo');
    }

    public function obtenerDatos()
    {
        $sql = "select * 
        from clientes 
        where id_cliente = '$this->id_cliente'";
        $fila = $this->conectar->query($sql)->fetch_assoc();
        $this->documento = $fila['documento'];
        $this->datos = $fila['datos'];
        $this->direccion = $fila['direccion'];
        $this->id_empresa = $fila['id_empresa'];
        $this->ultima_venta = $fila['ultima_venta'];
        $this->total_venta = $fila['total_venta'];
    }

    public function verificarDocumento()
    {
        $sql = "select *
        from clientes 
        where documento = '$this->documento' and id_empresa = '$this->id_empresa'";
        $result = $this->conectar->query($sql);
        if ($row = $result->fetch_assoc()) {
            $this->id_cliente = $row['id_cliente'];
            $this->datos = $row['datos'];
            $this->documento = $row['documento'];
            $this->email = $row['email'];
            $this->telefono = $row['telefono'];
            return true;
        }
        return false;
    }

    public function verFilas()
    {
        $sql = "select * from clientes where id_empresa = '$this->id_empresa'";
        return $this->conectar->query($sql);
    }

    public function buscarClientes($termino)
    {
        $sql = "select * from clientes 
        where id_empresa = '$this->id_empresa' and (datos like '%$termino%' or documento like '%$termino%') 
        order by datos asc";
        return $this->conectar->query($sql);
    }
    public function idLast()
    {

        try {
            $sql = "SELECT id_cliente,documento,datos,direccion,telefono,email,ultima_venta,total_venta FROM clientes  ORDER BY id_cliente DESC LIMIT 1";
            $fila = $this->conectar->query($sql)->fetch_object();
            return $fila;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function getAllData()
    {
        try {
            if(isset($_SESSION['rutas']) && sizeof($_SESSION['rutas'])>0){
                $rutas = implode(',',$_SESSION['rutas']);
                $sql = "SELECT * FROM clientes where id_empresa='{$_SESSION['id_empresa']}' AND id_ruta IN ({$rutas}) ";
            }else{
                $sql = "SELECT * FROM clientes where id_empresa='{$_SESSION['id_empresa']}'";
            }
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function getOne($id)
    {
        try {
            $sql = "SELECT * FROM clientes WHERE id_cliente = '$id' ";
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function cuentasCobrar()
    {
        try {
            $sql = "SELECT ventas.id_venta,ventas.fecha_emision,ventas.fecha_vencimiento,c.datos,dv.estado,dv.dias_venta_id FROM ventas LEFT JOIN dias_ventas AS dv ON
            ventas.id_venta=dv.id_venta 
            LEFT JOIN clientes AS c ON 
            ventas.id_cliente = c.id_cliente 
            WHERE ventas.id_tipo_pago = 2";
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function cuentasCobrarEstado($id)
    {
        try {
            $sql = "UPDATE dias_ventas set estado = 0 WHERE dias_venta_id = $id";
            $result =  $this->conectar->query($sql);
            return $result;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function editar($id)
    {
        $sql = "UPDATE clientes SET datos ='$this->datos',documento ='$this->documento',direccion ='$this->direccion',distrito ='$this->distrito',telefono ='$this->telefono',dias_visitas ='$this->diasVisitas',id_ruta='$this->id_ruta',mercado='$this->mercado' WHERE id_cliente = $id";
        $result =  $this->conectar->query($sql);
        return $result;
    }
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM clientes WHERE  id_cliente = '$id' ";
            $fila = mysqli_query($this->conectar, $sql);
            return $fila;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
}
