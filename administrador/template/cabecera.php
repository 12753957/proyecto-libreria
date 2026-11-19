<?php
// INICIAR SESIÓN// 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CONTROL DE SESIÓN// 

if (!isset($_SESSION["usuario"])) {

    $enSeccion = strpos($_SERVER["PHP_SELF"], "/seccion/") !== false;

    if ($enSeccion) {
        header("Location: ../login.php");
    } else {
        header("Location: login.php");
    }

    exit;
}


// =========================
// DETECTAR UBICACIÓN
// =========================

$enSeccion = strpos($_SERVER["PHP_SELF"], "/seccion/") !== false;


// =========================
// DEFINIR RUTAS
// =========================

if ($enSeccion) {

    $rutaInicio = "../../index.php";
    $rutaPanel = "../index.php";
    $rutaProductos = "productos.php";
    $rutaContactos = "contactos.php";
    $rutaTienda = "../../index.php";
    $rutaCerrar = "../cerrar.php";
    $rutaCss = "../../css/style.css?v=25";

} else {

    $rutaInicio = "../index.php";
    $rutaPanel = "index.php";
    $rutaProductos = "seccion/productos.php";
    $rutaContactos = "seccion/contactos.php";
    $rutaTienda = "../index.php";
    $rutaCerrar = "cerrar.php";
    $rutaCss = "../css/style.css?v=25";
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

    <title>Panel - Librería El Estudiante</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"
    >


    <!-- CSS del proyecto -->

    <link
        rel="stylesheet"
        href="<?php echo $rutaCss; ?>"
    >

</head>


<body>


<div class="container-fluid">

    <div class="row">


        <!-- =========================
             MENÚ ADMINISTRADOR
        ========================= -->

        <aside class="col-md-2 sidebar-admin p-0">


            <!-- LOGO -->

            <div class="logo-admin">

                <i class="bi bi-book"></i>

                <span>
                    El Estudiante
                </span>

            </div>


            <!-- MENÚ -->

            <nav class="menu-admin">


                <!-- INICIO -->

                <a href="<?php echo $rutaInicio; ?>">

                    <i class="bi bi-house"></i>

                    <span>
                        Inicio
                    </span>

                </a>


                <!-- PANEL -->

                <a href="<?php echo $rutaPanel; ?>">

                    <i class="bi bi-speedometer2"></i>

                    <span>
                        Panel
                    </span>

                </a>


                <!-- PRODUCTOS -->

                <a href="<?php echo $rutaProductos; ?>">

                    <i class="bi bi-box-seam"></i>

                    <span>
                        Productos
                    </span>

                </a>


                <!-- CONTACTOS -->

                <a href="<?php echo $rutaContactos; ?>">

                    <i class="bi bi-envelope"></i>

                    <span>
                        Contactos
                    </span>

                </a>


                <!-- VER TIENDA -->

                <a
                    href="<?php echo $rutaTienda; ?>"
                    target="_blank"
                >

                    <i class="bi bi-shop"></i>

                    <span>
                        Ver tienda
                    </span>

                </a>


                <!-- CERRAR SESIÓN -->

                <a href="<?php echo $rutaCerrar; ?>">

                    <i class="bi bi-box-arrow-left"></i>

                    <span>
                        Cerrar sesión
                    </span>

                </a>


            </nav>

        </aside>


        <!--CONTENIDO PRINCIPAL-->

        <main class="col-md-10 contenido-admin p-4">