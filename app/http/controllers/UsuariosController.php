<?php
require_once 'utils/lib/mpdf/vendor/autoload.php';
require_once 'utils/lib/vendor/autoload.php';

class UsuariosController extends Controller
{

    private $cliente;

    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }
 public function verificarEstadoSesion()
    {
        
        if (isset($_SESSION['usuario_fac'])) {

            $usuario_id = $_SESSION['usuario_fac'];

            $sql = "SELECT available_status FROM usuarios WHERE usuario_id = '$usuario_id'";
            $result = $this->conectar->query($sql);
            $row = $result->fetch_assoc();
            
            $value = $row['available_status'];
            
            echo json_encode([
                'success' => !($value == 0),
                'message' => $value == 0 ? 'Tu cuenta ha sido desactivada, por favor inicia sesion de nuevo' : 'Usuario activo'
            ]);
            exit();

            if ($value == 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tu cuenta ha sido desactivada, por favor inicia sesi®Æn de nuevo.'
                ]);
                exit();
                // session_unset();
                // session_destroy();

                echo json_encode([
                    'success' => false,
                    'message' => 'Tu cuenta ha sido desactivada, por favor inicia sesi®Æn de nuevo.'
                ]);
                //exit();
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario activo'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No est®¢s logueado'
            ]);
        }
    }
public function toggleDisponibilidad()
    {
        // Verificar si hay usuarios con available_status = 1 y cuyo id_rol no sea 1
        $sqlCheck = "SELECT COUNT(*) as total FROM usuarios WHERE available_status = 1 AND id_rol != 1";
        $resultCheck = mysqli_query($this->conectar, $sqlCheck);
        $row = mysqli_fetch_assoc($resultCheck);

        // Determinar el nuevo estado: Si hay al menos un usuario activo (1), los ponemos en 0, sino, los ponemos en 1
        $nuevoEstado = ($row['total'] > 0) ? 0 : 1;

        // Actualizar todos los usuarios con el nuevo estado, excluyendo a los de id_rol = 1
        $sqlUpdate = "UPDATE usuarios SET available_status = $nuevoEstado WHERE id_rol != 1";
        $resultUpdate = mysqli_query($this->conectar, $sqlUpdate);

        if ($resultUpdate) {
            echo json_encode(["success" => true, "new_status" => $nuevoEstado, "message" => "Actualizado correctamente el estado de los usuarios"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar usuarios"]);
        }
    }

    public function render()
    {
        $sql = "SELECT
                    usuario_id,
                    r.nombre,
                    usuario,
                    email,
                    nombres,
                      available_status,
                    id_rol,
                    CASE 
                        WHEN sucursal = 1 THEN 'Tienda 435'
                        ELSE 'Tienda 426'
                    END AS tienda,
                    CASE 
                        WHEN rotativo = 0 THEN 'No'
                        ELSE 'Si'
                    END AS rotativo,
                    fecha_inicio,
                    fecha_salida,
                    funciones
                FROM
                    usuarios u
                INNER JOIN roles r ON r.rol_id = u.id_rol";
        $fila = mysqli_query($this->conectar, $sql);
        $respuesta = mysqli_fetch_all($fila, MYSQLI_ASSOC);
        return json_encode($respuesta);
    }

    public function getOne()
    {
        $sql = "SELECT
                    *
                FROM
                    usuarios u
                where u.usuario_id = {$_POST["id"]}";
        $fila = mysqli_query($this->conectar, $sql);
        $respuesta = mysqli_fetch_all($fila, MYSQLI_ASSOC);
        return json_encode($respuesta);
    }

    public function editar()
    {
        $udp = "";
        if (isset($_POST["clave"])) {
            $clave = sha1($_POST["clave"]);
            $udp = "clave='$clave',";
        }
        $sql = "update usuarios set 
                    id_rol='{$_POST["rol"]}',
                    nombres='{$_POST["datosEditar"]}',
                    num_doc='{$_POST["doc"]}',
                    usuario='{$_POST["usuariou"]}',
                    $udp
                    email='{$_POST["emailEditar"]}',
                    sucursal={$_POST["tiendau"]},
                    rotativo={$_POST["rotativou"]},
                    id_ruta='{$_POST["rutasu"]}',
                   fecha_inicio='{$_POST["fechaInicio"]}',
                fecha_salida='{$_POST["fechaFin"]}',
                funciones='{$_POST["funciones"]}'
                    where usuario_id = {$_POST["idCliente"]}";
        mysqli_query($this->conectar, $sql);
        return true;
    }

    public function borrar()
    {
        $sql = "DELETE FROM usuarios WHERE usuario_id = {$_POST["value"]}";
        mysqli_query($this->conectar, $sql);
        return true;
    }
    
    public function obtenerVendedores()
    {
        $sql = "SELECT
            u.usuario_id,
            r.nombre,
            u.usuario,
            u.email,
            u.nombres
            FROM
            usuarios u
            INNER JOIN roles r ON r.rol_id = u.id_rol
            WHERE r.rol_id=3";
        $fila = mysqli_query($this->conectar, $sql);
        $respuesta = mysqli_fetch_all($fila, MYSQLI_ASSOC);
        return json_encode($respuesta);
    }
    
    public function exportarClientesPdf()
    {
        $id_usuario = $_GET['id_usuario'] ?? "";
        if($id_usuario==""){
            die("No se ingresaron todos los campos requiridos");
        }

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $mpdf = new \Mpdf\Mpdf([
            'memory_limit' => '512M',
            'format' => 'Letter', 
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $sql = "SELECT c.* 
            FROM clientes c
            inner join rutas_vendedor rv on rv.id_ruta = c.id_ruta
            inner join usuarios u on u.usuario_id=rv.id_usuario        
            WHERE c.id_empresa='{$_SESSION['id_empresa']}' AND u.usuario_id='$id_usuario'
            ORDER BY rv.id_ruta ASC,c.datos ASC ";
        
        $vendedor = $this->conectar->query("SELECT usuario_id,usuario,nombres FROM usuarios WHERE usuario_id='$id_usuario' ")->fetch_assoc();
        
        
        $resultado = $this->conectar->query($sql);
        // Manejo de errores
        if (!$resultado) {
            die("Error en la consulta: " . $this->conectar->error);
        }
        
        // $cotizacion = $resultado->fetch_assoc();
        $clientes = array();
        while($row = $resultado->fetch_assoc()){
            $clientes[] = $row;
        }
        
        // Verificar si se encontr√≥ una cotizaci√≥n
        if (sizeof($clientes) <= 0) {
            die("No se encontraron cotizaciones para Vendedor: " . $id_usuario);
        }
        
        $contador = 1;
        $total_consolidado = 0;

        $rowHTML = '';

        foreach ($clientes as $cliente) {
            // $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);
            

            $rowHTML .= "
                <tr>
                    <td class='' style='font-weight: bold;font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 0;white-space: nowrap;'>$contador</td>
                    <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0;'>{$cliente['documento']}</td>                    
                    <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:0;white-space: nowrap; '>{$cliente['datos']}</td>                    
                    <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:0;white-space: nowrap;'>{$cliente['direccion']}  </td>                    
                    <td class='' style='text-align:left;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;white-space: nowrap;'>{$cliente['distrito']} </td>
                    <td class='' style='text-align:center;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;'>{$cliente['telefono']} </td>
                    <td class='' style='text-align:center;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;white-space: nowrap;'>{$cliente['dias_visitas']} </td>
                    <td class='' style='text-align:center;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;white-space: nowrap;'>{$cliente['mercado']} </td>
                    <td class='' style='text-align:center;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;'>{$cliente['id_ruta']} </td>
                </tr>
            ";
            $contador++;
        }

        $html = "
        <div style='width: 100%; padding-top: 0px; overflow: hidden;clear: both;'>
            <h1 style='text-align:center;'>Clientes para vendedor {$vendedor['nombres']}</h1>
        </div>
        <div style='width: 100%; padding-top: 20px; margin-left: 20px'>
            <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;'>
                <tr style='border-bottom: 1px solid #fff;border-collapse: collapse;'>
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>#</strong></td>
                    <td style=' font-size: 16px; color: #000;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Documento</strong></td>
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Nombre</strong></td>
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Direccion</strong></td> 
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Distrito</strong></td> 
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Telefono</strong></td> 
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>D.Visita</strong></td> 
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Mercado</strong></td> 
                    <td style=' font-size: 16px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Ruta</strong></td> 
                </tr>
                $rowHTML
                <tr>
                    <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:0;'>.</td>
                    <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:0;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:0;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                </tr>
            </table>
        </div>
        <div>
        </div>";
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        


        

        


        $mpdf->Output("Consolidado_camion_{$camion}.pdf", 'I');
    
    }
    
}
