<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceso de venta</title>
    <link rel="stylesheet" href="assets/styles/gen_style.css">
    <link rel="stylesheet" href="assets/styles/add_style.css">
</head>

<body>
    <?php
    include "../../models/header.php"
    ?>
    <div class="titulo">
    </div>
    <section>
        <form action="controllers/crear_producto.php" method="post" autocomplete="off">
            <table>
                <tr>
                    <th colspan="2">
                        <h1>Agregar productos</h1>
                    </th>
                </tr>
                <tr>
                    <th>
                        <label for="codigo">Código</label>
                    </th>
                    <td><input type="text" name="codigo" id="codigo" required></td>
                </tr>
                <tr>
                    <th>
                        <label for="nombre">Nombre del producto</label>
                    </th>
                    <td><input type="text" name="nombre" id="nombre" required></td>
                </tr>
                <tr>
                    <th>
                        <label for="categoria">Categoría</label>
                    </th>
                    <td>
                        <select name="categoria" id="categoria" required>
                            <option value="">Seleccionar</option>
                            <option value="importada">Importada</option>
                            <option value="nacional">Nacional</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="tipo_producto" >Tipo de producto</label>
                    </th>
                    <td>
                        <select name="tipo_producto" id="tipo_producto" required>
                            <option value="">Seleccionar</option>
                            <option value="jean">Jean</option>
                            <option value="camisa">Camisa</option>
                            <option value="blusa">Blusa</option>
                            <option value="medias">Medias</option>
                            <option value="tenis">Tenis</option>
                            <option value="maquillaje">Maquillaje</option>
                            <option value="ropa interior">Ropa interior</option>
                            <option value="accesorio">Accesorio</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="costo">Costo del producto</label>
                    </th>
                    <td><input type="number" id="costo" name="costo" required></td>
                </tr>
                <tr>
                    <th>
                        <label for="venta">Precio de venta</label>
                    </th>
                    <td><input type="number" id="venta" name="venta" required></td>
                </tr>
                <tr>
                    <th>
                        <label for="cantidad">Cantidad</label>
                    </th>
                    <td><input type="number" name="cantidad" id="cantidad" required min="1"></td>
                </tr>
                <tr>
                    <th>
                        <label for="min_cant">Cantidad mínima</label>
                    </th>
                    <td><input type="number" name="min_cant" id="min_cant" required min="1"></td>
                </tr>
            </table>
            <input type="submit" value="Crear producto" name="agregar">
        </form>
    </section>
</body>

</html>