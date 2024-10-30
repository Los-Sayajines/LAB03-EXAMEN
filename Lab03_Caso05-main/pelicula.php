<?php
include('codigos/conexion2.inc');

// ID de la película cambiar segun la base de datos de sakila 
$film_id = 1; 

$sql = "SELECT
            f.film_id AS codigo,
            f.title AS titulo,
            f.description,
            f.release_year,
            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categorias,
            CONCAT(a.first_name, ' ', a.last_name) AS actor_nombre
        FROM
            film f
        INNER JOIN
            film_actor fa ON f.film_id = fa.film_id
        INNER JOIN
            actor a ON fa.actor_id = a.actor_id
        INNER JOIN
            film_category fc ON f.film_id = fc.film_id
        INNER JOIN
            category c ON fc.category_id = c.category_id
        WHERE
            f.film_id = $film_id
        GROUP BY
            a.actor_id
        ORDER BY
            actor_nombre";

$result = mysqli_query($conex, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "No se encontraron datos para la película con ID $film_id.";
    exit;
}

$xml = new SimpleXMLElement('<pelicula></pelicula>');

$row = mysqli_fetch_assoc($result);

$xml->addChild('codigo', $row['codigo']);
$xml->addChild('titulo', $row['titulo']);
$xml->addChild('descripcion', $row['description']);
$xml->addChild('release_year', $row['release_year']);

$categorias = explode(', ', $row['categorias']);
$categoriasElement = $xml->addChild('categorias');
foreach ($categorias as $categoria) {
    $categoriasElement->addChild('categoria', trim($categoria));
}

mysqli_data_seek($result, 0);

$actoresElement = $xml->addChild('actores');
while ($row = mysqli_fetch_assoc($result)) {
    $actoresElement->addChild('actor', $row['actor_nombre']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detalle de la Película</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .pelicula {
            margin: 20px;
        }
        .pelicula h1 {
            font-size: 24px;
            color: #333;
        }
        .detalle {
            margin-bottom: 20px;
        }
        .detalle p {
            margin: 5px 0;
        }
        .categorias, .actores {
            margin-bottom: 20px;
        }
        .categorias ul, .actores ul {
            list-style-type: none;
            padding: 0;
        }
        .categorias li, .actores li {
            background-color: #f0f0f0;
            margin: 2px 0;
            padding: 5px;
        }
    </style>
</head>
<body>

<div class="pelicula">
    <h1><?php echo $xml->titulo; ?> (<?php echo $xml->release_year; ?>)</h1>
    <div class="detalle">
        <p><strong>Código:</strong> <?php echo $xml->codigo; ?></p>
        <p><strong>Descripción:</strong> <?php echo $xml->descripcion; ?></p>
    </div>
    <div class="categorias">
        <h2>Categorías</h2>
        <ul>
            <?php foreach ($xml->categorias->categoria as $categoria): ?>
                <li><?php echo $categoria; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="actores">
        <h2>Actores</h2>
        <ul>
            <?php foreach ($xml->actores->actor as $actor): ?>
                <li><?php echo $actor; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

</body>
</html>
