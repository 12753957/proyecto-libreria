<?php

session_start();

include("config/bd.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtener datos del formulario
    $email = trim($_POST["email"] ?? "");
    $clave = $_POST["clave"] ?? "";

    // Verificar que no estén vacíos
    if ($email != "" && $clave != "") {

        /* BUSCAR USUARIO POR EMAIL*/

        $sql = $conexion->prepare(
            "SELECT id_usuario, nombre, apellido, email, password
             FROM usuario
             WHERE email = ?"
        );

        $sql->bind_param("s", $email);

        $sql->execute();

        $resultado = $sql->get_result();

        $fila = $resultado->fetch_assoc();


        /*VERIFICAR CONTRASEÑA */

        if ($fila && password_verify($clave, $fila["password"])) {

            // Crear sesión
            $_SESSION["usuario_id"] = $fila["id_usuario"];
            $_SESSION["usuario"] = $fila["email"];
            $_SESSION["nombre"] = $fila["nombre"];

            // Ir al panel administrador
            header("Location: index.php");
            exit();

        } else {

            $mensaje = "Email o contraseña incorrectos.";

        }

        $sql->close();

    } else {

        $mensaje = "Completá el email y la contraseña.";

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
        Login Administrador - Librería El Estudiante
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

<link rel="stylesheet" href="../css/style.css?v=25">

</head>


<body>


<div class="login-contenedor">


    <div class="login-box">


        <div class="login-logo">
            📚
        </div>


        <h2>
            Panel de Administración
        </h2>


        <p class="login-subtitulo">
            Librería El Estudiante
        </p>


        <?php if ($mensaje != "") { ?>

            <div class="alert alert-danger">

                <?php echo htmlspecialchars($mensaje); ?>

            </div>

        <?php } ?>


        <form method="POST">


            <div class="mb-3">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="Ingrese su email"
                    required
                >

            </div>


            <div class="mb-3">

                <label for="clave">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="clave"
                    name="clave"
                    class="form-control"
                    placeholder="Ingrese su contraseña"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >
                Ingresar
            </button>


        </form>


        <a
            href="../index.php"
            class="volver-tienda"
        >
            ← Volver a la tienda
        </a>


    </div>


</div>


</body>

</html>