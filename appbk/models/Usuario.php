<?php

class Usuario
{
    private $usuario_id;
    private $id_empresa;
    private $num_doc;
    private $usuario;
    private $clave;
    private $email;
    private $nombres;
    private $apellidos;
    private $token_reset;
    private $estado;

    private $conectar;

    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getUsuarioId()
    {
        return $this->usuario_id;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setUsuarioId($usuario_id)
    {
        $this->usuario_id = $usuario_id;
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
    public function getNumDoc()
    {
        return $this->num_doc;
    }

    /**
     * @param mixed $num_doc
     */
    public function setNumDoc($num_doc)
    {
        $this->num_doc = $num_doc;
    }

    /**
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param mixed $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @return mixed
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * @param mixed $clave
     */
    public function setClave($clave)
    {
        $this->clave = $clave;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getNombres()
    {
        return $this->nombres;
    }

    /**
     * @param mixed $nombres
     */
    public function setNombres($nombres)
    {
        $this->nombres = $nombres;
    }

    /**
     * @return mixed
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }

    /**
     * @param mixed $apellidos
     */
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;
    }

    /**
     * @return mixed
     */
    public function getTokenReset()
    {
        return $this->token_reset;
    }

    /**
     * @param mixed $token_reset
     */
    public function setTokenReset($token_reset)
    {
        $this->token_reset = $token_reset;
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

    public function login(){
        $respuesta=["res"=>false];
        $sql="select * from usuarios where email='{$this->usuario}' or usuario='{$this->usuario}'";
        $resul = $this->conectar->query($sql);
        if ($row = $resul->fetch_assoc()){
            if ($row['clave']==sha1($this->clave)){
                if ($row["estado"]==1){
                    $sql = "select * 
                    from empresas 
                    where id_empresa = '{$row['id_empresa']}'";

                    $empr = $this->conectar->query($sql)->fetch_assoc();


                    /*$_SESSION["usuario_fac"]=$row['usuario_id'];
                    $_SESSION['rol'] = $row['id_rol'];
                    $_SESSION['id_empresa'] = $empr['id_empresa'];
                    $_SESSION['nombre_empresa'] = $empr['razon_social'];
                    $_SESSION['logo_empresa'] = $empr['logo'];
                    $_SESSION['ruc_empr']=$empr['ruc'];*/
                    $token_u=[
                        "usuario_fac"=>$row['usuario_id'],
                        'rol'=>$row['id_rol'],
                        'id_empresa'=>$empr['id_empresa'],
                        'nombre_empresa'=> $empr['razon_social'],
                        'logo_empresa'=>$empr['logo'],
                        'sucursal'=>$row['sucursal'],
                        'ruc_empr'=>$empr['ruc']
                    ];
                    $_SESSION =$token_u;
                    $respuesta['res']=true;
                    $respuesta['token']=Tools::encryptText(json_encode($token_u));

                    if($row['id_rol']==1){
                        $respuesta['ruta']="/";
                    }else{
                        $respuesta['ruta']="/";
                    }

                }else{
                    $respuesta['msg']="Usuario Bloqueado";
                }

            }else{
                $respuesta['msg']="Contraseña incorrecta";
            }
        }else{
            $respuesta['msg']="Usuario no encontrado";
        }
        return $respuesta;
    }

}