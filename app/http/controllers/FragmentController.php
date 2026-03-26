<?php
require_once 'utils/lib/mpdf/vendor/autoload.php';
require_once 'utils/lib/vendor/autoload.php';
class FragmentController extends Controller
{
    public function home()
    {
        return $this->view("fragment-views/cliente/home");
    }
    public function cotizacionesEdt($coti)
    {
        return $this->view("fragment-views/cliente/cotizaciones-edt", ["coti" => $coti]);
    }
    public function cotizacionesCuotas($coti)
    {
        try {
            $conectar = (new Conexion())->getConexion();
            $idCoti = intval($coti);
            $sql_cotizacion = "
                SELECT 
                    c.cotizacion_id, 
                    c.numero, 
                    c.fecha, 
                    c.total, 
                    c.estado
                FROM cotizaciones c
                WHERE c.cotizacion_id = ?
            ";

            $stmt_cotizacion = $conectar->prepare($sql_cotizacion);
            $stmt_cotizacion->bind_param('i', $idCoti);
            $stmt_cotizacion->execute();
            $result_cotizacion = $stmt_cotizacion->get_result();

            if ($result_cotizacion->num_rows > 0) {
                $cotizacion = $result_cotizacion->fetch_assoc();

                // Obtener el vendedor de la cotización
                $sql_vendedor = "SELECT u.usuario AS vendedor FROM cotizaciones c 
                                 LEFT JOIN usuarios u ON u.usuario_id = c.id_usuario 
                                 WHERE c.cotizacion_id = ?";
                $stmt_vendedor = $conectar->prepare($sql_vendedor);
                $stmt_vendedor->bind_param('i', $idCoti);
                $stmt_vendedor->execute();
                $result_vendedor = $stmt_vendedor->get_result();
                $vendedor_data = $result_vendedor->fetch_assoc();
                $vendedor = !empty($vendedor_data['vendedor']) ? $vendedor_data['vendedor'] : 'Sin vendedor';

                $sql_cuotas = "
            SELECT 
                cc.monto, 
                cc.estado AS estado_cuota,
                cc.fecha,
                cc.tipo_pago,
                cc.id_usuario,
                u.usuario AS usuario_cobro
            FROM cuotas_cotizacion cc
            LEFT JOIN usuarios u ON u.usuario_id = cc.id_usuario
            WHERE cc.id_coti = ?
        ";

                $stmt_cuotas = $conectar->prepare($sql_cuotas);
                $stmt_cuotas->bind_param('i', $idCoti);
                $stmt_cuotas->execute();
                $result_cuotas = $stmt_cuotas->get_result();
                $estado = ($cotizacion['estado'] == 1) ? 'Pagado' : 'Sin pagar';

                $html = "
                <html lang='es'>
                <head>
                    <meta charset='UTF-8'>
                    <title>Reporte de Pagos</title>
                    <style>
                            // body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 0; }
                            h1 { color: #333; text-align: center; margin-top: 20px; }
                            ul { list-style: none; padding: 0; margin: 0; }
                            li { margin-bottom: 10px; font-size: 14px; }
                            strong { color: #555; font-weight: bold; }
                            .content { margin: 20px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                        </style>
                </head>
                <body>
                    <h1>Reporte de Pagos</h1>
                    <div class='content'>
                        <ul>
                            <li><strong>ID:</strong> {$cotizacion['cotizacion_id']}</li>
                            <li><strong>Número:</strong> {$cotizacion['numero']}</li>
                            <li><strong>Fecha:</strong> {$cotizacion['fecha']}</li>
                            <li><strong>Total:</strong> S/ {$cotizacion['total']}</li>
                            <li><strong>Vendedor:</strong> {$vendedor}</li>
                        </ul>
                        
                        <h2>Cuotas Asociadas</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Monto</th>
                                    <th>Fecha Pago</th>
                                    <th>Tipo pago</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>";
                $contador = 1;
                $totalpagado = 0;
                while ($cuota = $result_cuotas->fetch_assoc()) {
                    // Mostrar el usuario que cobró, o "-" si no hay usuario
                    $usuario_mostrar = !empty($cuota['usuario_cobro']) ? $cuota['usuario_cobro'] : '-';
                    $fechaDate = ($cuota['fecha'] == '0000-00-00') ? '-----' : $cuota['fecha'];
                    // Solo sumar al total las cuotas que realmente están pagadas
                    if ($cuota['estado_cuota'] == 1) {
                        $totalpagado = $cuota['monto'] + $totalpagado;
                    }
                    $tipoPago = ($cuota['tipo_pago'] == NULL) ? 'Pendiente' : $cuota['tipo_pago'];
                    $html .= "
                    <tr>
                        <td>{$contador}</td>
                        <td>S/ " . number_format($cuota['monto'], 2) . "</td>
                        <td>{$fechaDate}</td>
                        <td>{$tipoPago}</td>
                        <td>{$usuario_mostrar}</td>
                    </tr>";
                    $contador++;
                }

                $html .= "
                            </tbody>
                        </table>
                    </div>
                    <div class='footer'>
                        <p style='text-align: right; font-size: 16px;'> <strong>TOTAL PAGADO : S/ " . number_format($totalpagado, 2) . "</strong></p>
                        <p style='text-align: right; font-size: 16px; color: red;'> <strong>SALDO PENDIENTE : S/ " . number_format($totalpagado > 0 ? ($cotizacion['total'] - $totalpagado) : 0, 2) . "</strong></p>
                    </div>
                </body>
                </html>";
                $mpdf = new \Mpdf\Mpdf();
                $mpdf->WriteHTML($html);
                $mpdf->Output(
                    'reporte_cotizacion.pdf',
                    'I'
                );
            } else {
                echo "<p>No se encontró la cotización con el ID: $idCoti</p>";
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
    public function adminEmpresasVentas($empresa)
    {
        return $this->view("fragment-views/cliente/admin-empresas-ventas", ["emprCod" => $empresa]);
    }
    public function adminEmpresas()
    {
        return $this->view("fragment-views/cliente/admin-empresas");
    }
    public function pagos()
    {
        return $this->view("fragment-views/cliente/pagos");
    }
    public function comprasAdd()
    {
        return $this->view("fragment-views/cliente/compra-add");
    }
    public function compras()
    {
        return $this->view("fragment-views/cliente/compras");
    }
    public function cajaFlujo()
    {
        return $this->view("fragment-views/cliente/flujo-caja");
    }
    public function cajaRegistros()
    {
        return $this->view("fragment-views/cliente/caja-registros");
    }
    public function miCaja()
    {
        return $this->view("fragment-views/cliente/mi-caja");
    }
    public function misCobros()
    {
        return $this->view("fragment-views/cliente/mis-cobros");
    }
    public function arqueoDiario()
    {
        return $this->view("fragment-views/cliente/arqueo-diario");
    }
    public function cobranzas()
    {
        return $this->view("fragment-views/cliente/cobranzas");
    }
    public function cotizacionesAdd()
    {
        return $this->view("fragment-views/cliente/cotizaciones-add");
    }
    public function cotizaciones()
    {
        return $this->view("fragment-views/cliente/cotizaciones");
    }
    public function ventas()
    {
        return $this->view("fragment-views/cliente/ventas");
    }
    public function notaElectronicaLista()
    {
        return $this->view("fragment-views/cliente/nota-electronica-lista");
    }
    public function deudas()
    {
        return $this->view("fragment-views/cliente/deudas");
    }
    public function notaElectronica()
    {
        return $this->view("fragment-views/cliente/nota-electronica");
    }
    public function ventasProductos()
    {
        return $this->view("fragment-views/cliente/ventas-productos");
    }
    public function ventasServicios()
    {
        return $this->view("fragment-views/cliente/ventas-servicios");
    }
    public function test()
    {
        return $this->view("fragment-views/cliente/test");
    }
    public function calendarioCliente()
    {
        return $this->view("fragment-views/cliente/calendario");
    }
    public function guiaRemision()
    {
        return $this->view("fragment-views/cliente/guia-remision");
    }
    public function guiaRemisionAdd()
    {
        return $this->view("fragment-views/cliente/guia-remision-add");
    }
    public function almacenProductos()
    {
        return $this->view("fragment-views/cliente/almacen-productos");
    }
    public function almacenIntercambioProductos()
    {
        return $this->view("fragment-views/cliente/intercambio-productos");
    }
    public function clientesLista()
    {
        return $this->view("fragment-views/cliente/clientes");
    }
    public function productoAdd()
    {
        return $this->view("fragment-views/cliente/add-producto");
    }
    public function cuentasPorCobrar()
    {
        return $this->view("fragment-views/cuentascobrar");
    }
    public function reporteExcel()
    {
        return $this->view("fragment-views/cliente/reporte-excel");
    }
    public function tes()
    {
        return "hola";
    }
    public function editarVentaServicio($idVenta)
    {
        return $this->view("fragment-views/cliente/editar-venta-servicio", ["idVenta" => $idVenta]);
    }
    public function editarVentaProducto($idVenta)
    {
        return $this->view("fragment-views/cliente/editar-venta-producto", ["idVenta" => $idVenta]);
    }

    public function usuariosLista()
    {
        return $this->view("fragment-views/cliente/usuarios");
    }
    public function proveedoresLista()
    {
        return $this->view("fragment-views/cliente/proveedores");
    }
    #
    public function devoluciones()
    {
        return $this->view("fragment-views/cliente/devoluciones");
    }
}
