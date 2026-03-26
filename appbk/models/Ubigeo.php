<?php

class Ubigeo
{
    private $id_ubigeo;
    private $departamento;
    private $provincia;
    private $distrito;
    private $nombre;
    private $conectar;

    /**
     * Ubigeo constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getIdUbigeo()
    {
        return $this->id_ubigeo;
    }

    /**
     * @param mixed $id_ubigeo
     */
    public function setIdUbigeo($id_ubigeo)
    {
        $this->id_ubigeo = $id_ubigeo;
    }

    /**
     * @return mixed
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * @param mixed $departamento
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;
    }

    /**
     * @return mixed
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * @param mixed $provincia
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
    }

    /**
     * @return mixed
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * @param mixed $distrito
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
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

    public function verDepartamentos()
    {
        $sql = "select departamento, nombre 
        from ubigeo_inei 
        where provincia = '00'  and distrito = '00' 
        order by nombre asc ";
        return $this->conectar->query($sql);
    }

    public function verProvincias()
    {
        $sql = "select provincia, nombre 
        from ubigeo_inei 
        where departamento = '$this->departamento'  and provincia != '00' and distrito = '00'
        order by nombre asc";
        $result =  $this->conectar->query($sql);
        $listaProv=[];
        foreach ($result as $row){
            $listaProv[]=$row;
        }
        return json_encode($listaProv);
    }

    public function verDistritos()
    {
        $sql = "select concat(departamento, provincia, distrito) as ubigeo, nombre 
        from ubigeo_inei 
        where departamento = '$this->departamento'  and provincia = '$this->provincia' and distrito != '00'
        order by nombre asc";

        $result =  $this->conectar->query($sql);
        $listaProv=[];
        foreach ($result as $row){
            $listaProv[]=$row;
        }
        return json_encode($listaProv);
    }
}