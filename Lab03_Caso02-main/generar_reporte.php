<?php
include('codigos/conexion2.inc');

require('codigos/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Sakila Entretenimientos', 0, 1, 'C');
$pdf->Cell(0, 10, 'Listado de Peliculas', 0, 1, 'C');
$pdf->Ln(5);

$sql = "SELECT
            s.store_id AS almacen_id,
            CONCAT('Almacen ', s.store_id) AS almacen_nombre,
            c.name AS categoria_nombre,
            f.film_id AS pelicula_id,
            f.title AS pelicula_nombre,
            COUNT(i.inventory_id) AS existencias,
            f.release_year AS anio
        FROM
            store s
        INNER JOIN
            inventory i ON s.store_id = i.store_id
        INNER JOIN
            film f ON i.film_id = f.film_id
        INNER JOIN
            film_category fc ON f.film_id = fc.film_id
        INNER JOIN
            category c ON fc.category_id = c.category_id
        GROUP BY
            s.store_id, c.category_id, f.film_id
        ORDER BY
            s.store_id, c.name, f.title";

$result = mysqli_query($conex, $sql);

if (!$result) {
    die('Error en la consulta: ' . mysqli_error($conex));
}

$current_store = '';
$current_category = '';

while ($row = mysqli_fetch_assoc($result)) {
    if ($current_store != $row['almacen_nombre']) {
        $current_store = $row['almacen_nombre'];
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, $current_store, 0, 1, 'L');
        $pdf->Cell(0, 0, '', 1, 1); 
        $pdf->Ln(2);
    }

    if ($current_category != $row['categoria_nombre']) {
        $current_category = $row['categoria_nombre'];
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Categoria: ' . $current_category, 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 7, 'ID', 1);
        $pdf->Cell(80, 7, 'Nombre', 1);
        $pdf->Cell(30, 7, 'Existencias', 1);
        $pdf->Cell(20, 7, 'Anio', 1);
        $pdf->Ln();
    }

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 6, $row['pelicula_id'], 1);
    $pdf->Cell(80, 6, $row['pelicula_nombre'], 1);
    $pdf->Cell(30, 6, $row['existencias'], 1, 0, 'C');
    $pdf->Cell(20, 6, $row['anio'], 1, 0, 'C');
    $pdf->Ln();
}

$pdf->Output('I', 'reporte_peliculas.pdf');
?>
