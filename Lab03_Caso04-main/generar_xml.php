<?php
// Incluir el archivo de conexión
include('codigos/conexion2.inc');

// Definir las fechas de inicio y fin (puedes modificarlas o obtenerlas dinámicamente)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '2005-05-24';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '2005-05-30';

// Consulta SQL
$sql = "SELECT
            f.film_id AS codigo,
            f.title AS nombre,
            COUNT(*) AS veces_alquilada,
            SUM(p.amount) AS total_generado
        FROM
            rental r
        INNER JOIN
            inventory i ON r.inventory_id = i.inventory_id
        INNER JOIN
            film f ON i.film_id = f.film_id
        INNER JOIN
            payment p ON r.rental_id = p.rental_id
        WHERE
            r.rental_date BETWEEN '$fecha_inicio' AND '$fecha_fin'
        GROUP BY
            f.film_id
        ORDER BY
            veces_alquilada DESC
        LIMIT 10";

$result = mysqli_query($conex, $sql);

if (!$result) {
    echo "Error en la consulta: " . mysqli_error($conex);
    exit;
}

// Crear el elemento raíz del XML
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><peliculas></peliculas>');

$gran_total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $pelicula = $xml->addChild('pelicula');
    $pelicula->addChild('codigo', $row['codigo']);
    $pelicula->addChild('nombre', htmlspecialchars($row['nombre'], ENT_XML1, 'UTF-8'));

    $datos_alquiler = $pelicula->addChild('datos_alquiler');
    $datos_alquiler->addChild('veces_alquilada', $row['veces_alquilada']);
    $datos_alquiler->addChild('total_generado', number_format($row['total_generado'], 2));

    $gran_total += $row['total_generado'];
}

// Agregar el gran total al XML
$xml->addChild('gran_total', number_format($gran_total, 2));

// Agregar referencia al archivo XSL
$dom = dom_import_simplexml($xml)->ownerDocument;
$pi = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="peliculas.xsl"');
$dom->insertBefore($pi, $dom->firstChild);

// Guardar el XML en un archivo
$dom->save('peliculas.xml');

echo "Documento XML generado exitosamente. <a href='peliculas.xml'>Ver resultado</a>";
?>
