<?php

class GuiaRemision
{
    private $id_guia;
    private $id_venta;
    private $fecha;
    private $serie;
    private $numero;
    private $dir_llegada;
    private $ubigeo;
    private $tipo_transporte;
    private $ruc_transporte;
    private $raz_transporte;
    private $vehiculo;
    private $chofer;
    private $enviado_sunat;
    private $hash;
    private $nombre_xml;
    private $peso;
    private $nro_bultos;
    private $estado;
    private $id_empresa;
    private $conectar;

    /**
     * GuiaRemision constructor.
     */
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
    public function setIdGuia($id_guia)
    {
        $this->id_guia = $id_guia;
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
    public function getDirLlegada()
    {
        return $this->dir_llegada;
    }

    /**
     * @param mixed $dir_llegada
     */
    public function setDirLlegada($dir_llegada)
    {
        $this->dir_llegada = $dir_llegada;
    }

    /**
     * @return mixed
     */
    public function getUbigeo()
    {
        return $this->ubigeo;
    }

    /**
     * @param mixed $ubigeo
     */
    public function setUbigeo($ubigeo)
    {
        $this->ubigeo = $ubigeo;
    }

    /**
     * @return mixed
     */
    public function getTipoTransporte()
    {
        return $this->tipo_transporte;
    }

    /**
     * @param mixed $tipo_transporte
     */
    public function setTipoTransporte($tipo_transporte)
    {
        $this->tipo_transporte = $tipo_transporte;
    }

    /**
     * @return mixed
     */
    public function getRucTransporte()
    {
        return $this->ruc_transporte;
    }

    /**
     * @param mixed $ruc_transporte
     */
    public function setRucTransporte($ruc_transporte)
    {
        $this->ruc_transporte = $ruc_transporte;
    }

    /**
     * @return mixed
     */
    public function getRazTransporte()
    {
        return $this->raz_transporte;
    }

    /**
     * @param mixed $raz_transporte
     */
    public function setRazTransporte($raz_transporte)
    {
        $this->raz_transporte = $raz_transporte;
    }

    /**
     * @return mixed
     */
    public function getVehiculo()
    {
        return $this->vehiculo;
    }

    /**
     * @param mixed $vehiculo
     */
    public function setVehiculo($vehiculo)
    {
        $this->vehiculo = $vehiculo;
    }

    /**
     * @return mixed
     */
    public function getChofer()
    {
        return $this->chofer;
    }

    /**
     * @param mixed $chofer
     */
    public function setChofer($chofer)
    {
        $this->chofer = $chofer;
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

    /**
     * @return mixed
     */
    public function getPeso()
    {
        return $this->peso;
    }

    /**
     * @param mixed $peso
     */
    public function setPeso($peso)
    {
        $this->peso = $peso;
    }

    /**
     * @return mixed
     */
    public function getNroBultos()
    {
        return $this->nro_bultos;
    }

    /**
     * @param mixed $nro_bultos
     */
    public function setNroBultos($nro_bultos)
    {
        $this->nro_bultos = $nro_bultos;
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

    public function obtenerId()
    {
        $sql = "select ifnull(max(id_guia_remision) + 1, 1) as codigo 
            from guia_remision";
        $this->id_guia = $this->conectar->get_valor_query($sql, 'codigo');
    }

    public function obtenerDatos()
    {
        $sql = "select * 
        from guia_remision 
        where id_guia_remision = '$this->id_guia'";
        $fila = $this->conectar->get_Row($sql);
        $this->fecha = $fila['fecha_emision'];
        $this->id_venta = $fila['id_venta'];
        $this->dir_llegada = $fila['dir_llegada'];
        $this->ubigeo = $fila['ubigeo'];
        $this->tipo_transporte = $fila['tipo_transporte'];
        $this->ruc_transporte = $fila['ruc_transporte'];
        $this->raz_transporte = $fila['razon_transporte'];
        $this->vehiculo = $fila['vehiculo'];
        $this->chofer = $fila['chofer_brevete'];
        $this->enviado_sunat = $fila['enviado_sunat'];
        $this->hash = $fila['hash'];
        $this->nombre_xml = $fila['nombre_xml'];
        $this->serie = $fila['serie'];
        $this->numero = $fila['numero'];
        $this->peso = $fila['peso'];
        $this->nro_bultos = $fila['nro_bultos'];
        $this->estado = $fila['estado'];
    }

    public function exeSQL($sql){
        return $this->conectar->query($sql);
    }
    public function insertar()
    {
        $sql = "insert into guia_remision 
        values (null,
                '$this->id_venta',
                '$this->fecha',
                '$this->dir_llegada',
                '$this->ubigeo',
                '$this->tipo_transporte',
                '$this->ruc_transporte',
                '$this->raz_transporte',
                '$this->vehiculo',
                '$this->chofer',
                '0',
                '',
                '',
                '$this->serie',
                '$this->numero',
                '$this->peso',
                '$this->nro_bultos',
                '1',
                '$this->id_empresa',
                '{$_SESSION['sucursal']}'
                )";
        $reselt= $this->conectar->query($sql);
        if ($reselt){$this->id_guia=$this->conectar->insert_id;}
        //else{echo $this->conectar->error;}

        return $reselt;
    }
    public function actualizarHash () {
        $sql = "update guia_remision 
        set hash = '$this->hash', nombre_xml = '$this->nombre_xml', enviado_sunat = 1 
        where id_guia_remision = '$this->id_guia' ";
        return $this->conectar->ejecutar_idu($sql);
    }

    public function anular()
    {
        $sql = "update guia_remision 
        set estado = '2'   
        where id_guia_remision = '$this->id_guia'";
        return $this->conectar->ejecutar_idu($sql);
    }

    public function verFilas()
    {
        $sql = "select gr.fecha_emision, gr.id_guia_remision, 
       gr.dir_llegada, gr.enviado_sunat, gr.serie, gr.numero, 
       gr.estado, c.datos, v.serie as serie_venta, 
       e.ruc as ruc_empresa,
       v.numero as numero_venta, ds.abreviatura as doc_venta,
       gs.nombre_xml as nom_guia_xml
        from guia_remision as gr 
        inner join ventas v on gr.id_venta = v.id_venta 
        inner join documentos_sunat ds on v.id_tido = ds.id_tido            
        inner join clientes c on v.id_cliente = c.id_cliente 
            join empresas e on e.id_empresa = gr.id_empresa
        inner join guia_sunat gs on gr.id_guia_remision = gs.id_guia
        where gr.id_empresa = '$this->id_empresa' and gr.sucursal='{$_SESSION['sucursal']}' ";
        return $this->conectar->query($sql);
    }
}