<?php

require_once 'app/models/Cobranza.php';

class CobranzaController extends Controller
{
    private $cobranza;

    public function __construct()
    {
        $this->cobranza = new Cobranza();
    }
    public function render()
    {
        $getAll = $this->cobranza->getAllCobranzas();
        echo json_encode($getAll);
    }
    public function getAllByIdVenta()
    {
        $getAll = $this->cobranza->getAllByIdVenta($_POST['id']);
        echo json_encode($getAll);
    }
    public function validarLista()
    {
        $listaPagos = json_decode($_POST['dias_lista'], true);
        echo json_encode($listaPagos);
    }
    public function pagarCuota()
    {
        $pagar = $this->cobranza->pagarCuota($_POST['id']);
        echo json_encode($pagar);
    }
}
