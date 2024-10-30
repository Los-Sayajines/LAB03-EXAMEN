<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reporte PDF</title>
</head>
<body>
    <h1>Generar Reporte de Facturas</h1>
    <form action="reporte.php" method="POST">
        <label for="cliente">Cliente:</label>
        <select name="cliente" id="cliente">
            <?php
            
            require('codigos/conexion2.inc');
            $query = "SELECT CustomerID, CompanyName FROM customers";
            $result = mysqli_query($conex, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="'.$row['CustomerID'].'">'.$row['CompanyName'].'</option>';
            }
            ?>
        </select>
        <br><br>
        <label for="fechaInicio">Fecha de inicio:</label>
        <input type="date" name="fechaInicio" required>
        <br><br>
        <label for="fechaFin">Fecha final:</label>
        <input type="date" name="fechaFin" required>
        <br><br>
        <input type="submit" value="Generar Reporte">
    </form>
</body>
</html>
