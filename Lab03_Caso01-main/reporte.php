<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require('codigos/conexion2.inc');
    require('codigos/fpdf.php');

    
    $clienteId = $_POST['cliente'];
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];

    
    if (isset($clienteId, $fechaInicio, $fechaFin)) {
        $sqlCliente = "SELECT CompanyName, ContactName, City, Country FROM customers WHERE CustomerID = ?";
        $stmtCliente = $conex->prepare($sqlCliente);
        $stmtCliente->bind_param('s', $clienteId);
        $stmtCliente->execute();
        $resultCliente = $stmtCliente->get_result();
        $clienteData = $resultCliente->fetch_assoc();

        
        if (!$clienteData) {
            die('No se encontraron datos para el cliente seleccionado.');
        }

        
        $cliente = utf8_decode($clienteData['CompanyName']);
        $contacto = utf8_decode($clienteData['ContactName']);
        $ubicacion = utf8_decode($clienteData['City'] . ', ' . $clienteData['Country']);
    } else {
        die('No se recibieron correctamente los datos del formulario.');
    }

   
    class PDF extends FPDF {
        function Header() {
            global $cliente, $contacto, $ubicacion, $fechaInicio, $fechaFin;
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, utf8_decode('Cliente: ') . $cliente, 0, 1);
            $this->Cell(0, 10, utf8_decode('Contacto: ') . $contacto, 0, 1);
            $this->Cell(0, 10, utf8_decode('Ubicación: ') . $ubicacion, 0, 1);
            $this->Cell(0, 10, utf8_decode('Fecha de Consultas: ') . date('d/M/Y', strtotime($fechaInicio)) . ' - ' . date('d/M/Y', strtotime($fechaFin)), 0, 1);
            $this->Ln(10);
        }

        function Factura($factura) {
            $this->SetFont('Arial', '', 10);
            $this->SetFillColor(204, 255, 204); 
            $this->Cell(0, 10, utf8_decode('Factura #: ') . $factura['OrderID'], 0, 1, '', true);
            $this->Cell(0, 10, utf8_decode('Empleado: ') . utf8_decode($factura['EmployeeFirstName'] . ' ' . $factura['EmployeeLastName']), 0, 1, '', true);
            $this->Cell(0, 10, utf8_decode('Fecha Facturación: ') . date('d/M/Y', strtotime($factura['OrderDate'])), 0, 1, '', true);
            $this->Cell(0, 10, utf8_decode('Fecha Requerida: ') . date('d/M/Y', strtotime($factura['RequiredDate'])), 0, 1, '', true);
            $this->Cell(0, 10, utf8_decode('Fecha Despachada: ') . date('d/M/Y', strtotime($factura['ShippedDate'])), 0, 1, '', true);
            $this->Ln(10);

            $this->SetFont('Arial', 'B', 10);
            $this->Cell(20, 10, utf8_decode('Código'), 1);
            $this->Cell(60, 10, utf8_decode('Nombre'), 1);
            $this->Cell(20, 10, utf8_decode('Cantidad'), 1);
            $this->Cell(30, 10, utf8_decode('Precio Uni'), 1);
            $this->Cell(20, 10, utf8_decode('Descuento'), 1);
            $this->Cell(20, 10, utf8_decode('Total'), 1);
            $this->Ln();

            $this->SetFont('Arial', '', 10);
            $this->Cell(20, 10, utf8_decode($factura['ProductID']), 1);
            $this->Cell(60, 10, utf8_decode($factura['ProductName']), 1);
            $this->Cell(20, 10, $factura['Quantity'], 1);
            $this->Cell(30, 10, $factura['UnitPrice'], 1);
            $this->Cell(20, 10, $factura['Discount'], 1);
            $this->Cell(20, 10, $factura['Total'], 1);
            $this->Ln(10);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
        }
    }

    
    $pdf = new PDF();
    $pdf->AddPage();

    $sql = "
        SELECT o.OrderID, o.OrderDate, o.RequiredDate, o.ShippedDate, 
               e.FirstName AS EmployeeFirstName, e.LastName AS EmployeeLastName,
               od.ProductID, p.ProductName, od.Quantity, od.UnitPrice, od.Discount, 
               (od.Quantity * od.UnitPrice - od.Discount) AS Total
        FROM orders o
        JOIN order_details od ON o.OrderID = od.OrderID
        JOIN products p ON od.ProductID = p.ProductID
        JOIN employees e ON o.EmployeeID = e.EmployeeID
        WHERE o.CustomerID = ? 
        AND o.OrderDate BETWEEN ? AND ?";

    $stmt = $conex->prepare($sql);
    $stmt->bind_param('sss', $clienteId, $fechaInicio, $fechaFin);
    $stmt->execute();
    $result = $stmt->get_result();

    $facturas = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $facturas[] = $row;
        }

        
        foreach ($facturas as $factura) {
            $pdf->Factura($factura);
            $pdf->AddPage();
        }

        
        $totalCompras = array_sum(array_column($facturas, 'Total'));
        $pdf->Cell(0, 10, '=============================================', 0, 1, 'C');
        $pdf->Cell(0, 10, utf8_decode('Total de Compras Acumuladas: ') . number_format($totalCompras, 2), 0, 1, 'C');
    } else {
        echo "No se encontraron facturas para este cliente y rango de fechas.";
    }

    $pdf->Output();
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reporte PDF</title>
</head>
<body>
    <h1>Generar Reporte de Facturas</h1>
    <?php
    
    require('codigos/conexion.php');

   
    $sqlFechas = "SELECT MIN(OrderDate) AS minFecha, MAX(OrderDate) AS maxFecha FROM orders";
    $resultFechas = mysqli_query($conex, $sqlFechas);
    $fechas = mysqli_fetch_assoc($resultFechas);
    $minFecha = $fechas['minFecha'];
    $maxFecha = $fechas['maxFecha'];
    ?>
    <form action="reporte.php" method="POST">
        <label for="cliente">Cliente:</label>
        <select name="cliente" id="cliente">
            <?php
            $query = "SELECT CustomerID, CompanyName FROM customers";
            $result = mysqli_query($conex, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<option value="'.$row['CustomerID'].'">'.utf8_decode($row['CompanyName']).'</option>';
                }
            } else {
                echo '<option value="">No hay clientes disponibles</option>';
            }
            ?>
        </select>
        <br><br>
        <label for="fechaInicio">Fecha de inicio:</label>
        <input type="date" name="fechaInicio" min="<?= $minFecha ?>" max="<?= $maxFecha ?>" required>
        <br><br>
        <label for="fechaFin">Fecha final:</label>
        <input type="date" name="fechaFin" min="<?= $minFecha ?>" max="<?= $maxFecha ?>" required>
        <br><br>
        <input type="submit" value="Generar Reporte">
    </form>
</body>
</html>
<?php
}
?>
