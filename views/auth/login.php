<?php
include "../../controllers/errores.php";
include "../../controllers/session.php";

if (!isset($_SESSION["cargo"])) {
?>

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Log In</title>
        <link rel="stylesheet" href="assets/styles/style.css">
        <link rel="icon" href="assets/img/width_800.ico">
    </head>

    <body>
        <div class="sect1"></div>
        <div class="sect2">
            <?php if (isset($_GET["e"])) { ?>
                <script>
                    document.addEventListener("DOMContentLoaded", async () => {
                        await modalAlert("<?php echo addslashes(error($_GET["e"])); ?>");
                    });
                </script>
            <?php } ?>

            <div class="form">
                <img src="assets/img/width_800.ico" alt="" class="icon">
                <div class="form_flip">
                    <div class="form_front">
                        <h2 class="subtitle_log">Iniciar Sesión</h2>
                        <form action="controllers/auth.php" method="post" class="form_rec">
                            <div class="input_field">
                                <input type="text" name="email" id="email" required>
                                <label for="email">
                                    E-mail
                                </label>
                            </div>
                            <div class="input_field">
                                <input type="password" name="password" id="password" required>
                                <label for="password">
                                    Contraseña
                                </label>
                            </div>
                            <input type="submit" value="Ingresar" class="btn_00">
                            <p class="change_opt">¿No tienes una cuenta? <a class="flip_btn">Registrate</a></p>
                        </form>
                    </div>
                    <div class="form_back">
                        <h2 class="subtitle_log">Registrarse</h2>
                        <form action="controllers/singup.php" method="post" class="form_rec">
                            <div class="input_field">
                                <input type="text" name="nombre" id="nombre" required>
                                <label for="nombre">
                                    Nombre
                                </label>
                            </div>
                            <div class="input_field">
                                <input type="text" name="email" id="emai" required>
                                <label for="email">
                                    E-mail
                                </label>
                            </div>
                            <div class="input_field">
                                <input type="password" name="password" id="password" required>
                                <label for="password">
                                    Contraseña
                                </label>
                            </div>
                            <input type="submit" value="Registrarse" class="btn_00">
                            <p class="change_opt">¿Ya tienes una cuenta? <a class="flip_btn">Ingresa</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="sect3"></div>
        <div id="modal_alert" class="modal_cliente">
            <div class="modal_contenido">
                <p id="modal_alert_text"></p>

                <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <button type="button" id="alert_aceptar">Aceptar</button>
                </div>
            </div>
        </div>

        <script>
            document.querySelectorAll('.flip_btn').forEach(button => {
                button.addEventListener('click', () => {
                    document.querySelector('.form_flip').classList.toggle('flipped');
                    document.querySelector('.form').classList.toggle('res');
                });
            });
        </script>
        <script>
            function modalAlert(mensaje) {
                return new Promise(resolve => {
                    const modal = document.getElementById("modal_alert");
                    const texto = document.getElementById("modal_alert_text");
                    const btn = document.getElementById("alert_aceptar");

                    texto.textContent = mensaje;
                    modal.style.display = "flex";

                    btn.onclick = () => {
                        modal.style.display = "none";
                        btn.onclick = null;
                        resolve();
                    };
                });
            }
        </script>

    </body>

    </html>
<?php
} else {
    header("Location: venta");
}
?>