<?php
include "../../controllers/session.php";
include "../../controllers/getProducts.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link rel="stylesheet" href="assets/styles/gen_style.css">
    <link rel="stylesheet" href="assets/styles/header_style.css">
    <style>
        button {
            width: 20px;
            height: 20px;
        }
        button img {
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include "../../models/header.php" ?>
    <section>
        <div class="titulo">
            <h1>Productos</h1>
        </div>
        <div class="tab_productos">
            <input type="text" name="filtro" id="filtro">
            <table id="tabla">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Catergoría</th>
                        <th>Tipo de producto</th>
                        <th>Costo del producto</th>
                        <th>Precio de venta</th>
                        <th>Cantidad</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($productos as $producto) {
                    ?>
                        <tr>
                            <td><?php echo $producto["codigo"]; ?></td>
                            <td><?php echo $producto["nombre"]; ?></td>
                            <td><?php echo $producto["categoria"]; ?></td>
                            <td><?php echo $producto["tipo_de_producto"]; ?></td>
                            <td><?php echo $producto["costo"]; ?></td>
                            <td><?php echo $producto["venta"]; ?></td>
                            <td><?php echo $producto["cantidad"]; ?></td>
                            <td>
                                <button class="btn-editar" data-id="<?php echo $producto['id_producto'] ?>">
                                    <img src="assets/img/editar.ico" alt="">
                                </button>
                                <button>
                                    <img src="assets/img/add.png" alt="">
                                </button>
                                <button>
                                    <img src="assets/img/delete.png" alt="">
                                </button>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
    <script src="assets/js/menu.js"></script>
    <script>
        const filtro = document.getElementById("filtro");
        const tabla = document.getElementById("tabla").querySelector("tbody");

        filtro.addEventListener("keyup", function() {
            const texto = this.value.toLowerCase();

            for (let fila of tabla.rows) {
                const contenido = fila.innerText.toLowerCase();
                fila.style.display = contenido.includes(texto) ? "" : "none";
            }
        })

        document.querySelectorAll(".btn-editar").forEach(btn => {
            btn.addEventListener("click", () => {
                alert(btn.dataset.id)
            });
        });
    </script>
</body>

</html>