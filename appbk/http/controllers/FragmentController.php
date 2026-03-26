<?php

class FragmentController extends Controller
{
    public function home(){
        return $this->view("fragment-views/cliente/home");
    }
    public function cotizacionesEdt($coti){
        return $this->view("fragment-views/cliente/cotizaciones-edt",["coti"=>$coti]);
    }
    public function adminEmpresasVentas($empresa){
        return $this->view("fragment-views/cliente/admin-empresas-ventas",["emprCod"=>$empresa]);
    }
    public function adminEmpresas(){
        return $this->view("fragment-views/cliente/admin-empresas");
    }
    public function pagos(){
        return $this->view("fragment-views/cliente/pagos");
    }
    public function comprasAdd(){
        return $this->view("fragment-views/cliente/compra-add");
    }
    public function compras(){
        return $this->view("fragment-views/cliente/compras");
    }
    public function cajaFlujo(){
        return $this->view("fragment-views/cliente/flujo-caja");
    }
    public function cajaRegistros(){
        return $this->view("fragment-views/cliente/caja-registros");
    }
    public function cobranzas(){
        return $this->view("fragment-views/cliente/cobranzas");
    }
    public function cotizacionesAdd(){
        return $this->view("fragment-views/cliente/cotizaciones-add");
    }
    public function cotizaciones(){
        return $this->view("fragment-views/cliente/cotizaciones");
    }
    public function ventas(){
        return $this->view("fragment-views/cliente/ventas");
    }
    public function notaElectronicaLista(){
        return $this->view("fragment-views/cliente/nota-electronica-lista");
    }
    public function notaElectronica(){
        return $this->view("fragment-views/cliente/nota-electronica");
    }
    public function ventasProductos(){
        return $this->view("fragment-views/cliente/ventas-productos");
    }
    public function ventasServicios(){
        return $this->view("fragment-views/cliente/ventas-servicios");
    }
    public function calendarioCliente(){
        return $this->view("fragment-views/cliente/calendario");
    }
    public function guiaRemision(){
        return $this->view("fragment-views/cliente/guia-remision");
    }
    public function guiaRemisionAdd(){
        return $this->view("fragment-views/cliente/guia-remision-add");
    }
    public function almacenProductos(){
        return $this->view("fragment-views/cliente/almacen-productos");
    }
    public function clientesLista(){
        return $this->view("fragment-views/cliente/clientes");
    }
    public function productoAdd(){
        return $this->view("fragment-views/cliente/add-producto");
    }
    public function cuentasPorCobrar(){
        return $this->view("fragment-views/cuentascobrar");
    }
    public function tes(){
        return "hola";
    }

}