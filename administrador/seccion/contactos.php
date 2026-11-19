<?php

include("../config/bd.php");
include("../template/cabecera.php");


/* =========================
   BORRAR CONTACTO
========================= */

if (isset($_GET['borrar'])) {

    $id = intval($_GET['borrar']);

    if ($id > 0) {

        $conexion->query(
            "DELETE FROM contactos WHERE id = $id"
        );
    }

    header("Location: contactos.php");
    exit;
}

?>


<!-- =========================
     ENCABEZADO
========================= -->

<div class="admin-header">

    <h2>
        <i class="bi bi-envelope"></i>
        Mensajes de Contacto
    </h2>

    <p>
        Desde aquí podés consultar, responder y eliminar los mensajes
        enviados desde la tienda.
    </p>

</div>


<!-- =========================
     TABLA
========================= -->

<div class="card admin-menu mt-4">

    <div class="card-body">

        <h5>
            <i class="bi bi-chat-left-text"></i>
            Mensajes recibidos
        </h5>


        <div class="table-responsive mt-3">

            <table class="table table-hover align-middle">

                <thead class="tabla-admin">

                    <tr>

                        <th>ID</th>

                        <th>Nombre</th>

                        <th>Email</th>

                        <th>Mensaje</th>

                        <th class="text-center">
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                /* =========================
                   COMPROBAR TABLA
                ========================= */

                $check = $conexion->query(
                    "SHOW TABLES LIKE 'contactos'"
                );


                if ($check && $check->num_rows > 0) {

                    $res = $conexion->query(
                        "SELECT * FROM contactos ORDER BY id DESC"
                    );


                    if ($res && $res->num_rows > 0) {

                        while ($f = $res->fetch_assoc()) {

                            $id = htmlspecialchars($f['id']);

                            $nombre = htmlspecialchars(
                                $f['nombre']
                            );

                            $email = htmlspecialchars(
                                $f['email']
                            );

                            $mensaje = htmlspecialchars(
                                $f['mensaje']
                            );

                ?>

                            <tr>

                                <td>
                                    <?php echo $id; ?>
                                </td>


                                <td>
                                    <strong>
                                        <?php echo $nombre; ?>
                                    </strong>
                                </td>


                                <td>
                                    <?php echo $email; ?>
                                </td>


                                <td class="mensaje-contacto">
                                    <?php echo $mensaje; ?>
                                </td>


                                <td class="text-center">

                                    <!-- BOTÓN RESPONDER-->

                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        onclick="responderMensaje(
                                            '<?php echo htmlspecialchars($email, ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>'
                                        )"
                                    >

                                        <i class="bi bi-reply"></i>

                                        Responder

                                    </button>


                                    <!--BOTÓN BORRAR-->

                                    <a
                                        href="?borrar=<?php echo $id; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Seguro que querés eliminar este mensaje?');"
                                    >

                                        <i class="bi bi-trash"></i>

                                        Borrar

                                    </a>

                                </td>

                            </tr>

                <?php

                        }

                    } else {

                ?>

                        <tr>

                            <td
                                colspan="5"
                                class="text-center py-4"
                            >

                                <i class="bi bi-inbox fs-2"></i>

                                <br>

                                No hay mensajes de contacto.

                            </td>

                        </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="5"
                            class="text-center py-4"
                        >

                            <i class="bi bi-exclamation-circle"></i>

                            No existe la tabla
                            <strong>contactos</strong>.

                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!--SCRIPT PARA RESPONDER -->

<script>

function responderMensaje(email, nombre) {

    let respuesta = prompt(
        "Escribí la respuesta para " + nombre + ":"
    );

    if (respuesta === null) {
        return;
    }

    respuesta = respuesta.trim();

    if (respuesta === "") {

        alert("Por favor, escribí una respuesta.");

        return;
    }


    let asunto = encodeURIComponent(
        "Respuesta - Librería El Estudiante"
    );


    let cuerpo = encodeURIComponent(
        "Hola " + nombre + ",\n\n" +
        respuesta +
        "\n\nSaludos,\n" +
        "Librería El Estudiante"
    );


    window.location.href =
        "mailto:" +
        email +
        "?subject=" +
        asunto +
        "&body=" +
        cuerpo;

}

</script>


<?php

include("../template/pie.php");

?>