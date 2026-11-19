<?php

include("administrador/config/bd.php");

session_start();


/* =========================================
   VERIFICAR MÉTODO DE PAGO
========================================= */

if (!isset($_POST["metodo_pago"])) {

    header("Location: metodo_pago.php");
    exit;

}

$metodo_pago = $_POST["metodo_pago"];


/* =========================================
   NOMBRE DEL MÉTODO
========================================= */

$nombre_metodo = "";

switch ($metodo_pago) {

    case "efectivo":

        $nombre_metodo = "Efectivo";

        break;


    case "tarjeta":

        $nombre_metodo = "Tarjeta de crédito o débito";

        break;


    case "mercado_pago":

        $nombre_metodo = "Mercado Pago";

        break;


    case "transferencia":

        $nombre_metodo = "Transferencia bancaria";

        break;


    default:

        header("Location: metodo_pago.php");
        exit;

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
        Datos del cliente - Librería El Estudiante
    </title>

    <link
    rel="stylesheet"
    href="css/style.css?v=99"
>

</head>


<body>


<header>

    <div class="logo">
        📚 El Estudiante
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


<main class="pagina-pago">


    <div class="pago-contenedor">


        <h1>
            Datos del cliente
        </h1>


        <p class="pago-descripcion">

            Completá tus datos para poder finalizar la compra.

        </p>


        <div class="detalle-compra">

            <p>

                <strong>
                    Método de pago seleccionado:
                </strong>

            </p>


            <p class="metodo-seleccionado">

                <?php

                echo htmlspecialchars($nombre_metodo);

                ?>

            </p>

        </div>


        <form
    action="confirmar_compra.php"
    method="POST"
    class="formulario-cliente"
>


            <!-- =========================================
                 GUARDAR MÉTODO DE PAGO
            ========================================= -->

            <input
                type="hidden"
                name="metodo_pago"
                value="<?php echo htmlspecialchars($metodo_pago); ?>"
            >


            <!-- =========================================
                 NOMBRE Y APELLIDO
            ========================================= -->

            <label for="nombre">
                Nombre y apellido
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                required
                maxlength="100"
                placeholder="Ingresá tu nombre y apellido"
            >


            <!-- =========================================
                 EMAIL
            ========================================= -->

            <label for="email">
                Correo electrónico
            </label>

            <input
                type="email"
                id="email"
                name="email"
                required
                maxlength="150"
                placeholder="Ingresá tu correo electrónico"
            >


            <!-- =========================================
                 TELÉFONO
            ========================================= -->

            <label for="telefono">
                Teléfono
            </label>

            <input
                type="tel"
                id="telefono"
                name="telefono"
                required
                maxlength="30"
                placeholder="Ingresá tu número de teléfono"
            >


            <!-- =========================================
                 DIRECCIÓN
            ========================================= -->

            <label for="direccion">
                Dirección
            </label>

            <input
                type="text"
                id="direccion"
                name="direccion"
                required
                maxlength="200"
                placeholder="Ingresá tu dirección"
            >


            <!-- =========================================
                 CIUDAD
            ========================================= -->

            <label for="ciudad">
                Ciudad
            </label>

            <input
                type="text"
                id="ciudad"
                name="ciudad"
                required
                maxlength="100"
                placeholder="Ingresá tu ciudad"
            >


            <!-- =========================================
                 CÓDIGO POSTAL
            ========================================= -->

            <label for="codigo_postal">
                Código postal
            </label>

            <input
                type="text"
                id="codigo_postal"
                name="codigo_postal"
                required
                maxlength="20"
                placeholder="Ingresá tu código postal"
            >


            <!-- =========================================
                 BOTÓN
            ========================================= -->

            <button
                type="submit"
                class="btn-confirmar"
            >

                Finalizar compra

            </button>


        </form>


        <a
            href="metodo_pago.php"
            class="volver-carrito"
        >

            ← Volver al método de pago

        </a>


    </div>


</main>


</body>

</html>