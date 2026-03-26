<?php

use Mpdf\Utils\Arrays;

require_once "app/models/Cliente.php";
require_once "utils/lib/exel/vendor/autoload.php";


class ClientesController extends Controller
{

    private $cliente;

    public function __construct()
    {
        $this->cliente = new Cliente();
    }
    public function insertar()
    {
        if (!empty($_POST)) {
            $doc = trim(filter_var($_POST['documentoAgregar'], FILTER_SANITIZE_NUMBER_INT));
            $datosAgregar = trim(filter_var($_POST['datosAgregar'], FILTER_SANITIZE_STRING));
            $direccionAgregar = trim(filter_var($_POST['direccionAgregar'], FILTER_SANITIZE_STRING));
            $direccionAgregar2 = trim(filter_var($_POST['direccionAgregar2'], FILTER_SANITIZE_STRING));
            $telefonoAgregar = trim(filter_var($_POST['telefonoAgregar'], FILTER_SANITIZE_NUMBER_INT));
            $telefonoAgregar2 = trim(filter_var($_POST['telefonoAgregar2'], FILTER_SANITIZE_NUMBER_INT));
            $direccion = trim(filter_var($_POST['direccion'], FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL));
            $telefonoIntVal = intval($telefonoAgregar);
            $docIntVal = intval($doc);
            if ($doc !== "" && $datosAgregar !== "") {
                $telefonoTrueInt = filter_var($telefonoIntVal, FILTER_VALIDATE_INT);
                $doctTrueInt = filter_var($docIntVal, FILTER_VALIDATE_INT);
                if ($doctTrueInt == true) {
                    $this->cliente->setDocumento($doc);
                    $this->cliente->setDatos($datosAgregar);
                    $this->cliente->setDireccion($direccionAgregar);
                    $this->cliente->setDireccion2($direccionAgregar2);
                    $this->cliente->setTelefono($telefonoAgregar);
                    $this->cliente->setTelefono2($telefonoAgregar2);
                    $this->cliente->setEmail($direccion);
                    $save = $this->cliente->insertar();
                    if ($save == true) {
                        echo json_encode($this->cliente->idLast());
                    } else {
                        echo json_encode("Ocurrio un Error");
                    }
                } else {
                    echo json_encode('Llene el formulario correctamente 39');
                }
            } else {
                echo json_encode('Llene el formulario correctamente 42');
            }
        } else {
            echo json_encode('Error');
        }
    }
    public function render()
    {
        $getAll = $this->cliente->getAllData();
        echo json_encode($getAll);
    }
    public function getOne()
    {
        /* $presupuesto = new PresupuestosModel(); */
        $data = $_POST;
        $id = $data['id'];
        $getOne = $this->cliente->getOne($id);
        echo json_encode($getOne);
    }
    public function cuentasCobrar()
    {
        /* $presupuesto = new PresupuestosModel(); */

        $getAll = $this->cliente->cuentasCobrar();
        echo json_encode($getAll);
    }
    public function cuentasCobrarEstado()
    {
        $getAll = $this->cliente->cuentasCobrarEstado($_POST['id']);
        echo json_encode($getAll);
    }
    public function editar()
    {
        if (!empty($_POST)) {
            $doc = trim(filter_var($_POST['documentoEditar'], FILTER_SANITIZE_STRING));
            $datosEditar = trim(filter_var($_POST['datosEditar'], FILTER_SANITIZE_STRING));
            $direccionEditar = trim(filter_var($_POST['direccionEditar'], FILTER_SANITIZE_STRING));
            $direccionEditar2 = trim(filter_var($_POST['direccionEditar2'], FILTER_SANITIZE_STRING));
            $telefonoEditar = trim(filter_var($_POST['telefonoEditar'], FILTER_SANITIZE_STRING));
            $telefonoEditar2 = trim(filter_var($_POST['telefonoEditar2'], FILTER_SANITIZE_STRING));
            $emailEditar = trim(filter_var($_POST['emailEditar'], FILTER_SANITIZE_EMAIL));
            $emailValidate = filter_var($emailEditar, FILTER_VALIDATE_EMAIL);
            $telefonoIntVal = intval($telefonoEditar);
            $docIntVal = intval($doc);
            $id = $_POST['idCliente'];
            if ($doc !== "" && $datosEditar !== "") {
                $telefonoTrueInt = filter_var($telefonoIntVal, FILTER_VALIDATE_INT);
                $doctTrueInt = filter_var($docIntVal, FILTER_VALIDATE_INT);

                if ($doctTrueInt == true && strlen($docIntVal) == 8 || strlen($docIntVal) == 11) {
                    $this->cliente->setDocumento($doc);
                    $this->cliente->setDatos($datosEditar);
                    $this->cliente->setDireccion($direccionEditar);
                    $this->cliente->setDireccion2($direccionEditar2);
                    $this->cliente->setTelefono($telefonoEditar);
                    $this->cliente->setTelefono2($telefonoEditar2);
                    $this->cliente->setEmail($emailEditar);
                    $save = $this->cliente->editar($_POST['idCliente']);
                    if ($save == true) {
                        echo json_encode($this->cliente->getOne($id));
                    } else {
                        echo json_encode("Ocurrio un Error");
                    }
                } else {
                    echo json_encode('Llene el formulario correctamente');
                }
            } else {
                echo json_encode('Llene el formulario correctamente');
            }
        } else {
            echo json_encode('Error');
        }
    }
    public function borrar()
    {
        $dataId = $_POST["value"];
        $save = $this->cliente->delete($dataId);
        if ($save) {
            echo json_encode("nice");
        } else {
            echo json_encode("error");
        }
    }

    public function importarExcel()
    {
        $respuesta = ["res" => false];
        $filename = $_FILES['file']['name'];

        $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
        $newName = Tools::getToken(80);
        /* Location */
        $loc_ruta = "files/temp";
        if (!file_exists($loc_ruta)) {
            mkdir($loc_ruta, 0777, true);
        }
        $location = $loc_ruta . "/" . $newName . '.' . $path_parts;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
            $nombre_logo = $newName . "." . $path_parts;

            $respuesta["res"] = true;
            $type = $path_parts;

            if ($type == "xlsx") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } elseif ($type == "xls") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } elseif ($type == "csv") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            }

            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load("files/temp/" . $nombre_logo);

            $schdeules = $spreadsheet->getActiveSheet()->toArray();
            // array_shift($schdeules);
            $respuesta["data"] = $schdeules;

            unlink($location);
            //return $schdeules;
            /*   $last = $this->cliente->idLast();
            $arr = array($respuesta, $last); */
        }

        return json_encode($respuesta);
    }
    /*   public function importAdd(){
        echo json_encode($_POST);
    } */
}
