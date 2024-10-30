<?php

require('codigos/conexion2.inc');
require('codigos/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha_inicial'], $_POST['fecha_final'], $_POST['almacen_id'])) {

    $fecha_inicial = $_POST['fecha_inicial'];
    $fecha_final = $_POST['fecha_final'];
    $almacen_id = intval($_POST['almacen_id']);

    if (!strtotime($fecha_inicial) || !strtotime($fecha_final)) {
        die("Fechas inválidas.");
    }

    $sql = "
    SELECT
        f.film_id AS ID,
        f.title AS Nombre,
        c.name AS Genero,
        SUM(p.amount) AS Monto
    FROM
        payment p
    INNER JOIN rental r ON p.rental_id = r.rental_id
    INNER JOIN inventory i ON r.inventory_id = i.inventory_id
    INNER JOIN film f ON i.film_id = f.film_id
    INNER JOIN film_category fc ON f.film_id = fc.film_id
    INNER JOIN category c ON fc.category_id = c.category_id
    INNER JOIN store s ON i.store_id = s.store_id
    WHERE
        p.payment_date BETWEEN ? AND ?
        AND s.store_id = ?
    GROUP BY
        f.film_id, f.title, c.name
    ORDER BY
        Monto DESC
    ";

    $stmt = $conex->prepare($sql);
    $stmt->bind_param('ssi', $fecha_inicial, $fecha_final, $almacen_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Sakila Entretenimientos', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Reporte de ingresos ' . $fecha_inicial . ' a ' . $fecha_final, 0, 1, 'C');
    $pdf->Cell(0, 10, 'Almacen: ' . $almacen_id, 0, 1, 'C');

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(80, 10, 'Nombre', 1);
    $pdf->Cell(40, 10, 'Genero', 1);
    $pdf->Cell(40, 10, 'Monto', 1, 1, 'R');

    $pdf->SetFont('Arial', '', 12);
    $total_monto = 0;

    while ($fila = $resultado->fetch_assoc()) {
        $pdf->Cell(20, 10, $fila['ID'], 1);
        $pdf->Cell(80, 10, $fila['Nombre'], 1);
        $pdf->Cell(40, 10, $fila['Genero'], 1);
        $pdf->Cell(40, 10, number_format($fila['Monto'], 2), 1, 1, 'R');
        $total_monto += $fila['Monto'];
    }

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 10, 'Total Alquiler Acumulado por Almacen:', 0);
    $pdf->Cell(40, 10, number_format($total_monto, 2), 0, 0, 'R');

    $pdf->Output();

} else {

    // Mostrar el formulario
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Generar Reporte de Ingresos</title>
    </head>
    <body>
        <h1>Generar Reporte de Ingresos por Almacén</h1>
        <form action="index.php" method="post">
            <label for="fecha_inicial">Fecha Inicial:</label>
            <input type="date" name="fecha_inicial" id="fecha_inicial" required><br><br>

            <label for="fecha_final">Fecha Final:</label>
            <input type="date" name="fecha_final" id="fecha_final" required><br><br>

            <label for="almacen_id">Almacén ID:</label>
            <input type="number" name="almacen_id" id="almacen_id" required><br><br>

            <input type="submit" value="Generar Reporte">
        </form>
    </body>
    </html>
    <?php
}
?>
