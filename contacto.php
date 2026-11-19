<?php

/*CONEXIÓN A LA BASE DE DATOS */

include("administrador/config/bd.php");


/*GUARDAR MENSAJE*/

$mensajeExito = "";
$mensajeError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["correo"] ?? "");
    $mensaje = trim($_POST["mensaje"] ?? "");


    /*VALIDAR CAMPOS*/

    if ($nombre != "" && $email != "" && $mensaje != "") {


        /*VALIDAR EMAIL*/

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $mensajeError =
                "Ingresá un correo electrónico válido.";

        } else {


            /* INSERTAR MENSAJE
               USANDO PREPARE*/

            $sql = $conexion->prepare(
                "INSERT INTO contactos
                (nombre, email, mensaje)
                VALUES (?, ?, ?)"
            );


            if ($sql) {

                $sql->bind_param(
                    "sss",
                    $nombre,
                    $email,
                    $mensaje
                );


                if ($sql->execute()) {

                    $mensajeExito =
                        "¡Mensaje enviado correctamente! Nos pondremos en contacto con vos.";

                } else {

                    $mensajeError =
                        "No se pudo enviar el mensaje. Intentá nuevamente.";
                }


                $sql->close();

            } else {

                $mensajeError =
                    "No se pudo preparar el mensaje.";
            }
        }

    } else {

        $mensajeError =
            "Completá todos los campos.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Contacto - Librería El Estudiante
    </title>

    <link
        rel="stylesheet"
        href="css/style.css?v=24"
    >

</head>


<body>


<!--ENCABEZADO-->

<header>

    <div class="logo">
        📚 Librería El Estudiante
    </div>


    <nav>

        <a href="index.php">
            Inicio
        </a>

        <a href="productos.php">
            Productos
        </a>

        <a href="carrito.php">
            Carrito
        </a>

        <a href="contacto.php">
            Contacto
        </a>

        <a href="nosotros.php">
            Nosotros
        </a>

        <a href="servicios.php">
            Servicios
        </a>

        <a href="admin.php">
            Administración
        </a>

    </nav>

</header>


<!--CONTACTO -->

<main>

    <div class="contenedor-contacto">


        <!-- FORMULARIO-->

        <section class="form-box">

            <h1>
                📩 Contactanos
            </h1>


            <p class="texto-contacto">

                ¿Tenés alguna consulta? Escribinos y te responderemos
                lo antes posible.

            </p>


            <!-- MENSAJE DE ÉXITO -->

            <?php if ($mensajeExito != "") { ?>

                <div class="mensaje-exito">

                    <?php
                    echo htmlspecialchars($mensajeExito);
                    ?>

                </div>

            <?php } ?>


            <!-- MENSAJE DE ERROR -->

            <?php if ($mensajeError != "") { ?>

                <div class="mensaje-error">

                    <?php
                    echo htmlspecialchars($mensajeError);
                    ?>

                </div>

            <?php } ?>


            <form
                action="contacto.php"
                method="post"
            >


                <label for="nombre">
                    Nombre:
                </label>


                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Tu nombre"
                    required
                >


                <label for="correo">
                    Correo electrónico:
                </label>


                <input
                    type="email"
                    id="correo"
                    name="correo"
                    placeholder="tu@mail.com"
                    required
                >


                <label for="mensaje">
                    Mensaje:
                </label>


                <textarea
                    id="mensaje"
                    name="mensaje"
                    placeholder="Escribí tu consulta..."
                    required
                ></textarea>


                <button
                    type="submit"
                    class="btn-contacto"
                >
                    Enviar mensaje
                </button>


            </form>

        </section>


        <!--INFORMACIÓN -->

        <section class="info-box">


            <h2>
                📍 Información
            </h2>


            <div class="dato-contacto">

                <span>
                    📍
                </span>

                <p>

                    <strong>
                        Dirección
                    </strong>

                    <br>

                    Monte Caseros, Corrientes, Argentina

                </p>

            </div>


            <div class="dato-contacto">

                <span>
                    📧
                </span>

                <p>

                    <strong>
                        Correo electrónico
                    </strong>

                    <br>

                    libreriaelestudiante@gmail.com

                </p>

            </div>


            <div class="dato-contacto">

                <span>
                    📞
                </span>

                <p>

                    <strong>
                        Teléfono
                    </strong>

                    <br>

                    3775-444-5555

                </p>

            </div>


            <hr>


            
<div class="horario">

    <h3>
        🕘 Horarios
    </h3>

    <p>
        Lunes a Viernes
    </p>

    <p>
        8:00 a 20:00 hs
    </p>

    <p>
        Sábados
    </p>

    <p>
        9:00 a 13:00 hs
    </p>

</div>


            <div class="servicios-contacto">

                <h3>
                    📚 Nuestros servicios
                </h3>

                <p>
                    Anillados y fotocopiado en blanco y negro o color.
                </p>

            </div>


        </section>


    </div>

</main>


<?php include("footer.php"); ?>


</body>

</html>