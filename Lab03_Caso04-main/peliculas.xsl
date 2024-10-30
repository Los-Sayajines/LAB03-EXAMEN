<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" encoding="UTF-8" />

    <xsl:template match="/">
        <html>
            <head>
                <title>Películas más alquiladas</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 80%; margin: auto; border-collapse: collapse; }
                    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
                    th { background-color: #f2f2f2; }
                    h1 { text-align: center; }
                    .gran-total { font-weight: bold; }
                </style>
            </head>
            <body>
                <h1>Películas más alquiladas del <xsl:value-of select="/peliculas/pelicula[1]/datos_alquiler/veces_alquilada"/> al <xsl:value-of select="/peliculas/pelicula[1]/datos_alquiler/veces_alquilada"/></h1>
                <table>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Veces Alquilada</th>
                        <th>Total Generado ($)</th>
                    </tr>
                    <xsl:for-each select="/peliculas/pelicula">
                        <tr>
                            <td><xsl:value-of select="codigo"/></td>
                            <td><xsl:value-of select="nombre"/></td>
                            <td><xsl:value-of select="datos_alquiler/veces_alquilada"/></td>
                            <td><xsl:value-of select="datos_alquiler/total_generado"/></td>
                        </tr>
                    </xsl:for-each>
                    <tr>
                        <td colspan="3" class="gran-total">Gran Total</td>
                        <td class="gran-total"><xsl:value-of select="/peliculas/gran_total"/></td>
                    </tr>
                </table>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>
