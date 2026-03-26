<?php

    /*class models
    {
        public function __construct() {
            $this->conectar = (new Conexion())->getConexion();
        }
    }*/

   require_once '/home/magusqao/titanicsac.com/app/clases/funciones.php';


    class Database
    {
        private $host;
        private $db;
        private $user;
        private $password;
        private $charset;
        private $collation;

        public function __construct() {
            $this->host = HOST_SS;

            if (!isset($_POST['testing'])):
                $this->db = DATABASE_SS;
            else:
                $this->db = $_POST['testing'];
            endif;
            $this->user = USER_SS;
            $this->password = PASSWORD_SS;
            $this->charset = 'utf8';
            #$this->collation = 'utf8mb4_unicode_ci';
        }

        function connect() {
            try {
                $conexion = "mysql:host=" . $this->host . ";dbname=" . $this->db . ";charset=" . $this->charset;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    #PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = 'America/Santo_Domingo'",
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $pdo = new PDO($conexion, $this->user, $this->password, $options);
                return $pdo;
            } catch (Exception $e) {
                var_dump('Error conexion' . $e->getMessage());
            }
        }
    }


    class models
    {
        protected $table;
        protected $colums;
        protected $limit = '';
        protected $order = '';
        protected $orderDirection = '';
        protected $left_join = '';
        protected $on = '';
        protected $where = '';
        protected $campos = '';
        protected $inputs = '';
        protected $group = '';
        protected $test;
        protected $typescolum;
        protected $primaryKey;
        protected $dependencia;
        protected $datos_tabla;


        protected $dataBase = DATABASE_SS;

        function __construct($table = '', $test = false) {
            #$this->db = (new Conexion())->getConexion();

            $this->table = stringOnlyTrim($table);
            $this->test = $test;
            if (is_bool($test)):
                if ($test):
                    $_POST['testing'] = 'distribution';
                    $this->dataBase = $_POST['testing'];
                endif;
            endif;
            if (hasText($test, 'contable')):
                $_POST['testing'] = $test;
                $this->dataBase = $_POST['testing'];
            endif;
            $this->db = new Database();

        }

        /*function produccion($idproduccion=false) {
            $dataBase = DB;
            $this->db = new Database();
        }*/

        function reset() {
            $this->table = '';
            $this->colums = '';
            $this->limit = '';
            $this->order = '';
            $this->orderDirection = '';
            $this->left_join = '';
            $this->on = '';
            $this->where = '';
            $this->campos = '';
            $this->inputs = '';
            $this->group = '';
            $this->typescolum = '';
            $this->dependencia = '';
        }

        function existeTabla($table) {
            $table = onlyReduceToOneSpace($table);
            $table = stringToArray($table, ' ');
            $table = array_shift($table);
            $sql = "SELECT table_name 
							FROM information_schema . tables
								WHERE table_schema = '$this->dataBase'
                and table_name = '$table'";
            $sql = stringOnlyReduceToOneSpace($sql);
            if ($this->select($sql))
                return true;
            else
                return false;
        }

        function checkTable() {
            if ($this->table != ''):
                $table = stringOnlyReduceToOneSpace(rtrim($this->table));

                if (hasText($table, ' ')):
                    $table = substr($table, 0, -2);
                endif;
                $table = stringOnlyTrim($table);

                $sql = "SELECT table_name 
							FROM information_schema . tables
								WHERE table_schema = '$this->dataBase'
                and table_name = '$table'";
                #console($sql);
                $sql = stringOnlyReduceToOneSpace($sql);
                #console($sql);
                $this->console($sql);
                $result = $this->select($sql);
                if ($this->table == '')
                    die('Ingrese una tabla');
                if (!$result)
                    die('La tabla no existe ' . $table);

            endif;
        }

        function leftJoin($table) {
            if ($table != '')
                $this->left_join .= " LEFT JOIN $table ";
            return $this;
        }

        function on($on) {
            if ($on != '')
                $this->left_join .= "ON $on ";
            return $this;
        }


        function setTable($table = '') {
            $this->reset();
            $table = stringOnlyReduceToOneSpace(rtrim($table));
            $t = $table;

            if (hasText($table, ' ')):
                $table = substr($table, 0, -2);
            endif;
            $this->table = stringOnlyTrim($table);
            #console($this->table);
            $this->checkTable();
            $this->table = $t;
            return $this;
        }

        #function getLimit($limit = '') {
        function getTable() {
            return $this->table;
        }

        function setLimit($limit = '') {
            $this->limit = $limit == '' ? '' : getLimit($limit);

            return $this;
        }

        #function getLimit($limit = '') {
        function getLimit() {
            #$this->setLimit($limit);
            return $this->limit;
        }

        function setGroupBy($limit = '') {
            $group = '';
            if (is_array($limit))
                $group = convertArrayToString($limit, ',');
            else
                $group = $limit;

            if ($group != '')
                $this->group = "GROUP BY $group";

            return $this;
        }

        #function getLimit($limit = '') {
        function getGroupBy() {
            #$this->setLimit($limit);
            return $this->group;
        }

        function setOrderBy($orderBy = [], $orderDirection = 'DESC') {
            if ($this->orderDirection == '')
                $this->desc();
            else
                $this->orderDirection = $orderDirection;

            $orderBy = convertArrayToString($orderBy, ',');
            if ($orderBy <> '')
                $orderBy = "ORDER BY $orderBy $this->orderDirection";
            $this->order = $orderBy;

            return $this;
        }

        function getOrderBy() {
            return $this->order;
        }

        function desc() {
            $this->order = str_replace('ASC', 'DESC', $this->order);
            $this->orderDirection = 'DESC';
            return $this;
        }

        function asc() {
            $this->order = str_replace('DESC', 'ASC', $this->order);
            $this->orderDirection = 'ASC';
            return $this;
        }

        function rowWhere($where = '') {

            if (is_array($where)):
                if (count($where) > 0):
                    $where = array_find($where, '', '!=');
                    $where = arrayToString($where, ' AND ');
                    $where = "WHERE $where";
                else:
                    $where = '';
                endif;
            elseif (onlyTrim($where) != ''):
                $where = "WHERE $where";
            endif;
            $this->where = $where;

            return $this;
        }

        function setWhere($where = []) {
            $where = $this->where($where);


            if ($where != '')
                $where = "WHERE $where";
            $this->where = $where;

            return $this;
        }

        function getWhere() {
            return $this->where;
        }

        function setColums($campos = ['*']) {
            $campos = convertArrayToString($campos, ',');
            $this->campos = $campos;

            return $this;
        }

        function getColums() {
            return $this->campos;
        }

        function selectionById($tabla, $datos = [], $cantidad = 20) {
            $where = '';
            $colums = [];
            if (isset($datos['row - where'])):
                if (isset($datos['row - q'])):
                    $datos['row - where'] = $datos['row - where'] . " and " . $datos['row - q'];
                    unset($datos['row - q']);
                endif;
                $where = $datos['row - where'];
                unset($datos['row - where']);
            endif;

            if (isset($datos['colums'])):
                $colums = $datos['colums'];
                unset($datos['colums']);
            endif;


            $valores = $datos;
            #console($datos);
            reset($valores);
            #console('******************');
            #console($valores);
            if (count($datos) > 1) # RETIRAR ESTA CONDICION SI HAY ERROR
                array_shift($datos);
            #console('*******-----***********');
            #console($datos);
            $colum = array_keys($valores)[0] ?? '';
            $valor = array_values($valores)[0] ?? '';
            $valor = is_numeric($valor) ? $valor : 0;

            $desc = end($datos);

            if ($desc == 'desc' or $desc == 'asc'):
                array_pop($datos);
            endif;

            if ($desc == 'desc'):
                $desc = true;
            else:
                $desc = false;
            endif;

            $orderBy = arrayToString($datos, ',');
            #console('******************');
            #console("($colum != $valor)");
            #console($orderBy);
            if (stringOnlyTrim($orderBy) != '')
                $orderBy = ",$orderBy";

            /*console("($colum != $valor)  $orderBy");
            console($orderBy);
            die();*/
            $query = '';
            if ($where != ''):
                if ($valor > 0):# RETIRAR ESTA CONDICION SI HAY ERROR
                    $orderBy = "($colum != $valor) $orderBy";# RETIRAR ESTA CONDICION SI HAY ERROR
                else:# RETIRAR ESTA CONDICION SI HAY ERROR
                    $orderBy = str_replace(',', '', $orderBy);# RETIRAR ESTA CONDICION SI HAY ERROR
                endif;# RETIRAR ESTA CONDICION SI HAY ERROR

                $query = $this->setTable("$tabla")
                    ->rowWhere("$where")
                    ->setOrderBy([$orderBy])
                    ->setLimit($cantidad);
            else:
                $query = $this->setTable("$tabla")
                    ->rowWhere("$where")
                    ->setOrderBy(["($colum != $valor)  $orderBy"])
                    ->setLimit($cantidad);
            endif;

            if ($desc):
                $query->desc();
            else:
                $query->asc();
            endif;

            if ($colums):
                #console($colums);
                $query->setColums($colums);
            endif;
            return $query->selectAll();
        }

        function limpiarSql($sql) {
            $sql = onlyReduceToOneSpace($sql);
            $sql = preg_replace('/\b' . preg_quote('and and') . '\b/i', 'AND', $sql);
            $sql = preg_replace('/\b' . preg_quote('and order') . '\b/i', 'ORDER', $sql);
            $sql = preg_replace('/\b' . preg_quote('where where') . '\b/i', 'WHERE', $sql);
            $sql = preg_replace('/\b' . preg_quote('and limit') . '\b/i', 'LIMIT', $sql);
            $sql = preg_replace('/\b' . preg_quote('where and') . '\b/i', 'WHERE', $sql);
            #$sql = preg_replace('/\b' . preg_quote('or and') . '\b/i', 'OR', $sql);
            return $sql;
        }

        function selectAll($sqlOrWhere = '', $orderBy = [], $colum = ['*'], $limit = '') {

            $this->checkTable();

            if ($this->limit == ''):
                $this->setLimit($limit);
            endif;

            if ($this->order == ''):
                $this->setOrderBy($orderBy);
            endif;

            if ($this->campos == ''):
                $this->setColums($colum);
            endif;

            if (is_array($sqlOrWhere)):
                if ($this->where == ''):
                    $this->setWhere($sqlOrWhere);
                endif;
                $sql = "SELECT $this->campos FROM $this->table $this->left_join $this->where $this->group $this->order $this->limit";
                $this->console($sql);
            elseif ($sqlOrWhere == ''):
                $sql = "SELECT $this->campos FROM $this->table $this->left_join $this->where $this->group $this->order $this->limit";
            else:
                $sql = $sqlOrWhere;
            endif;

            #if (hasText($this->table, 'contabilidad_usuarios'))
            #    console3($sql);
            #console3($sql);
            $sql = $this->limpiarSql($sql);
            $result = $this->db->connect()->query($sql);
            $result = $result->fetchAll(PDO::FETCH_OBJ);

            $this->limit = '';
            $this->order = '';
            if (!isset($_GET['ignorar - this']))
                $this->where = '';

            return $result;

            #$result = $this->db->connect()->query($sql);
            #$resultSet = array();
            #while ($row = $result->fetchObject()) {
            #	$resultSet[] = $row;
            #}
            #$this->limit = '';
            #$this->order = '';
            #if (!isset($_GET['ignorar - this']))
            #	$this->where = '';
            #return $resultSet;
        }

        #function select($sql) {
        function select($sqlOrWhere = '', $colum = ['*']) {

            #$this->checkTable();

            if ($this->campos == ''):
                $this->setColums($colum);
            endif;

            if (is_array($sqlOrWhere)):
                if ($this->where == ''):
                    $this->setWhere($sqlOrWhere);
                endif;
                $sql = "SELECT $this->campos FROM $this->table $this->where";
            elseif ($sqlOrWhere == ''):
                $sql = "SELECT $this->campos FROM $this->table $this->where";
            else:
                $sql = $sqlOrWhere;
            endif;

            #if ($this->table == 'solicitudes_usuario')
            #    console($sql);

            #console($sql);
            $sql = $this->limpiarSql($sql);
            #console($sql);
            $result = $this->db->connect()->query($sql);
            $resultSet = array();
            while ($row = $result->fetchObject()) {
                $resultSet = $row;
            }
            return $resultSet;
        }

        function update($wherecampos = [], $inputs = true, $return = false) {
            $_GET['ignorar - this'] = true;

            $this->checkTable();
            /*if ($inputs == '')
                $inputs = $this->inputs;
            if ($inputs == '')
                die('No existe campos a actualizar');*/

            if ($this->where == ''):
                $this->setWhere($wherecampos);
            endif;
            if ($this->inputs == ''):
                $this->setInputs($inputs);
            endif;

            $update = "UPDATE $this->table SET $this->inputs $this->where";

            #if ($this->table == 'proveedores')
            #    console($update);
            #endif;
            #console($update);
            try {
                $this->set($update);
                $this->reset();
                $data = new stdClass();
                $data->estatus = true;

                if ($return === true):
                    $sql = "SELECT * FROM $this->table WHERE $this->primaryKey = $data->id";
                    $data = $this->select($sql);
                    $data->estatus = true;
                endif;

                if (isset($_POST['testing']))
                    unset($_POST['testing']);
                return $data;
            } catch (Exception $e) {
                $this->reset();
                $data = new stdClass();
                $data->sql = $update;
                $data->mensaje = $e->getMessage();
                $mensahe = str_replace(': ', ':', $data->mensaje);
                $mensahe = stringToArray($mensahe, ':');
                $data->codigoSql = $mensahe[0] ?? '';
                $data->tipo = $mensahe[1] ?? '';
                $data->datosOriginal = $mensahe[2] ?? '';
                #$data->datos = ltrim(substr($data->datos, 4));
                $data->estatus = false;
                if (isset($_POST['testing']))
                    unset($_POST['testing']);
                return $data;
            }
        }

        function insert($inputs = '', $return = false) {
            $this->checkTable();
            /*if ($inputs == '')
                $inputs = $this->inputs;
            if ($inputs == '')
                die('No existe campos a actualizar');*/

            #if (is_object($inputs) and !substr(onlyReduceToOneSpace(strtoupper($inputs)), 0, 11) === "INSERT INTO"):
            if (is_object($inputs) or is_array($inputs)):
                if ($this->inputs == '' and is_bool($inputs) == false):
                    $this->console($inputs);
                    $this->setInputs($inputs);
                endif;

                $update = "INSERT INTO $this->table SET $this->inputs";
                if (isset($_POST['imprime'])):
                    echo "<pre > " . var_dump($update) . "</pre > ";
                endif;
            else:
                $update = $inputs;
            endif;


            #die();
            $this->console($update);
            try {
                /*$this->set($update);*/
                $data = new stdClass();
                $data->estatus = true;
                $data->id = $this->set($update);


                #if (is_bool($inputs)):
                if ($return === true):
                    $sql = "SELECT * FROM $this->table WHERE $this->primaryKey = $data->id";
                    $data = $this->select($sql);
                    $data->estatus = true;
                endif;
                #endif;
                $this->reset();
                if (isset($_POST['testing']))
                    unset($_POST['testing']);
                return $data;
            } catch
            (Exception $e) {
                #console($e->getMessage());
                #die();
                $this->reset();
                $data = new stdClass();
                $data->mensaje = $e->getMessage();
                $mensahe = str_replace(': ', ':', $data->mensaje);
                $mensahe = stringToArray($mensahe, ':');
                $data->codigoSql = $mensahe[0] ?? '';
                $data->tipo = $mensahe[1] ?? '';
                $data->datosOriginal = $mensahe[2] ?? '';
                $data->sql = $update ?? '';
                #$data->datos = ltrim(substr($data->datos, 4));
                $data->estatus = false;
                if (isset($_POST['testing']))
                    unset($_POST['testing']);
                return $data;
            }
        }

        function delete($wherecampos = []) {
            $this->checkTable();
            if ($this->where == ''):
                $this->setWhere($wherecampos);
            endif;
            $update = "DELETE FROM $this->table $this->where";
            #console($update);
            #die();
            #console($update);
            try {
                $this->set($update);
                $data = new stdClass();
                $data->estatus = true;
                if (isset($_POST['testing']))
                    unset($_POST['testing']);
                return $data;
            } catch (Exception $e) {
                $data = new stdClass();
                $data->mensaje = $e->getMessage();
                $mensahe = str_replace(': ', ':', $data->mensaje);
                $mensahe = stringToArray($mensahe, ':');
                $data->codigoSql = $mensahe[0] ?? '';
                $data->tipo = $mensahe[1] ?? '';
                $data->datosOriginal = $mensahe[2] ?? '';
                #$data->datos = ltrim(substr($data->datos, 4));
                $data->estatus = false;
                if (isset($_POST['testing']))
                    unset($_POST['testing']);
                return $data;
                /*return false;*/
            }
        }

        function set($sql) {
            $this->checkTable();
            $sql = stringOnlyReduceToOneSpace($sql);
            $db = $this->db->connect();
            $db->query($sql);
            return $db->lastInsertId();
        }


        function setInputs($isPOST = true) {


            $this->inputs = $this->getInputs($isPOST);
            return $this;
        }

        function getInputs($isPOST = true) {

            $colums = $this->columns();
            //console($colums);
            if (is_object($isPOST))
                $vars = (array)$isPOST;
            elseif (is_array($isPOST))
                $vars = $isPOST;
            else
                $vars = $isPOST ? $_POST : $_GET;

            $this->primaryKey = array_find_obj($this->datos_tabla, 'Key', 'PRI');
            $this->primaryKey = $this->primaryKey[0]->Field ?? '';

            $inputs = [];
            foreach ($vars as $key => $m):


                if (is_array($m))
                    continue;


                if (array_search($key, $colums) === false):
                    #console($key);
                    #console($m);
                    #console('--------------------------------------------');
                    unset($vars[$key]);
                    continue;
                endif;
                $position = buscarPositionInArray($key, $colums);
                $type = $this->typescolum[$position] ?? false;
                $dependencia = $this->dependencia[$position] ?? false;

                #
                #
                if (encrypt(decrypt($m), true) == str_replace('=', '', $m))
                    $m = decrypt($m);
                if (encrypt(encrypt(decrypt(decrypt($m))), true) == str_replace('=', '', $m))
                    $m = decrypt(decrypt($m));
                #
                #


                if (strtolower(stringOnlyTrim($m)) == 'now()' or hasText(onlyTrim($m), 'nullif(', true)):
                    $inputs[] = "$key = $m";
                    continue;
                endif;

                if ($dependencia == true):
                    $m = $m == 0 ? '' : $m;
                    $inputs[] = "$key = NULLIF('$m', '')";
                    continue;
                endif;

                $m = addslashes($m);


                if ($type != false):
                    if (hasText('float', $type) or hasText('int', $type) or hasText('decimal', $type) or hasText('tintint', $type)):
                        $m = floatval($m);
                        $inputs[] = "$key = $m";
                    else:
                        $inputs[] = "$key = '$m'";
                    endif;
                    continue;
                endif;
                /*console("$key = $m");
                if (strtolower(stringOnlyTrim($m)) == 'now()'):
                    console("$key = $m");
                    $inputs[] = "$key = $m";
                else:*/
                $inputs[] = "$key = '$m'";
                /*endif;*/

            endforeach;

            $this->inputs = convertArrayToString($inputs, ',');
            return $this->inputs;
        }

        function columns() {

            $this->checkTable();

            $sql = "SHOW COLUMNS FROM $this->table;";
            $sql = "SELECT
                        COLUMNS . TABLE_NAME,
                        COLUMNS . COLUMN_NAME as 'Field',
                        COLUMNS . DATA_TYPE  as 'Type',
                        COLUMNS . COLUMN_KEY  as 'Key',
                        if (KEY_COLUMN_USAGE . REFERENCED_COLUMN_NAME IS NULL,0,1) as tiene_dependencia
                    FROM
                        INFORMATION_SCHEMA . COLUMNS
                    LEFT JOIN
                        INFORMATION_SCHEMA . KEY_COLUMN_USAGE
                    ON
                        COLUMNS . TABLE_NAME = KEY_COLUMN_USAGE . TABLE_NAME
                        and COLUMNS . COLUMN_NAME = KEY_COLUMN_USAGE . COLUMN_NAME
                        and COLUMNS . TABLE_SCHEMA = KEY_COLUMN_USAGE . TABLE_SCHEMA
                    WHERE
                        COLUMNS . TABLE_NAME = '$this->table'
                        and COLUMNS . TABLE_SCHEMA = '$this->dataBase';";

            $colum = $this->selectAll($sql);


            $this->datos_tabla = $colum;
            $this->colums = array_column($colum, 'Field');
            $this->typescolum = array_column($colum, 'Type');
            $this->dependencia = array_column($colum, 'tiene_dependencia');
            return $this->colums;
        }


        function where($campos) {
            if (!is_array($campos))
                die('Ingrese Array de campos');

            $sql = "SHOW COLUMNS FROM $this->table";
            $sql = preg_replace('/FROM\s+\w+\s+\K(\w+)/', '', $sql);

            #console3($sql);
            $result = $this->db->connect()->query($sql);
            $columnas_tabla = $result->fetchAll(PDO::FETCH_OBJ);

            $iswhere = [];
            foreach ($campos as $key => $m):
                if (is_numeric($key) and !is_array($m))
                    die('Ingrese Clave Valor para WHERE');
                if (is_array($m)):
                    $ors = [];
                    foreach ($m as $m2 => $mi):
                        $mi = addslashes($mi);
                        if (is_like($mi))
                            $ors[] = "LOWER(REPLACE(REPLACE($m2, \"'\",''),' ','')) LIKE \"$mi\"";
                        else
                            $ors[] = "$m2='$mi'";

                    endforeach;
                    $iswhere[] = "(" . convertArrayToString($ors, ' OR ') . ")";
                else:
                    $tipo = array_find_obj($columnas_tabla, 'Field', $key);
                    $tipo = $tipo[0] ?? [];

                    if (is_like($m)):
                        $iswhere[] = "LOWER(REPLACE(REPLACE($key,\"'\",''),' ','')) LIKE \"$m\"";
                    else:
                        if ($tipo and $tipo->Type == 'date' and onlyTrim($m) == ''):
                            $iswhere[] = "IFNULL($key,'')=''";
                        else:
                            $iswhere[] = "$key='$m'";
                        endif;
                    endif;
                endif;
            endforeach;


            $iswhere = convertArrayToString($iswhere, ' AND ');

            if (stringOnlyTrim($iswhere) == '')
                die('Ingrese Campos Para WHERE');
            return $iswhere;
        }

        function console($array) {
            if (is_numeric($this->test)):
                if ($this->test == 1):
                    echo '<pre>';
                    var_dump($array);
                    echo '</pre>';
                endif;
            endif;
        }
    }