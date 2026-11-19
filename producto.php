<?php

include("administrador/config/bd.php");


/*VERIFICAR ID DEL PRODUCTO*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: productos.php");
    exit;

}

$id = intval($_GET["id"]);


/*BUSCAR PRODUCTO*/

$consulta = $conexion->prepare(
    "SELECT id, nombre, autor, categoria, precio, descuento, stock, imagen
     FROM productos
     WHERE id = ?"
);

$consulta->bind_param("i", $id);

$consulta->execute();

$resultado = $consulta->get_result();

$producto = $resultado->fetch_assoc();

$consulta->close();


/*SI NO EXISTE*/

if (!$producto) {

    header("Location: productos.php");
    exit;

}


/*CALCULAR PROMOCIÓN*/

$precioOriginal = (float)$producto["precio"];

$descuento = isset($producto["descuento"])
    ? (float)$producto["descuento"]
    : 0;


/* Evitar valores incorrectos */

if ($descuento < 0) {
    $descuento = 0;
}

if ($descuento > 100) {
    $descuento = 100;
}


/* Precio final */

$precioFinal = $precioOriginal -
    (
        $precioOriginal *
        $descuento /
        100
    );


/*PREPARAR IMAGEN*/

$imagen = trim($producto["imagen"]);

$imagen = str_replace(
    "\\",
    "/",
    $imagen
);

$imagen = preg_replace(
    '#^(\./)?img/#i',
    "",
    $imagen
);

$imagen = ltrim(
    $imagen,
    "/"
);


$rutaImagen =
    __DIR__ .
    "/img/" .
    $imagen;

$rutaWeb =
    "img/" .
    $imagen;

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

        <?php echo htmlspecialchars($producto["nombre"]); ?>

        - Librería El Estudiante

    </title>


    <!-- CSS PRINCIPAL -->

    <link
        rel="stylesheet"
        href="css/style.css?v=24"
    >

</head>


<body>


<!-- ENCABEZADO-->

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


<!--DETALLE DEL PRODUCTO-->

<main class="detalle-producto">


    <a
        href="productos.php"
        class="volver-productos"
    >
        ← Volver a productos
    </a>


    <div class="detalle-contenido">


        <!--  IMAGEN-->

        <div class="detalle-imagen">

            <?php if (
                !empty($imagen) &&
                file_exists($rutaImagen)
            ): ?>

                <img
                    src="<?php echo htmlspecialchars($rutaWeb); ?>"
                    alt="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                >

            <?php else: ?>

                <div class="detalle-sin-imagen">

                    📷

                    <span>
                        Sin imagen
                    </span>

                </div>

            <?php endif; ?>

        </div>


        <!-- INFORMACIÓN -->

        <div class="detalle-informacion">


            <!-- CATEGORÍA -->

            <p class="detalle-categoria">

                <?php

                echo htmlspecialchars(
                    $producto["categoria"]
                );

                ?>

            </p>


            <!-- NOMBRE -->

            <h1>

                <?php

                echo htmlspecialchars(
                    $producto["nombre"]
                );

                ?>

            </h1>


            <!-- AUTOR -->

            <?php if (!empty($producto["autor"])): ?>

                <p class="detalle-autor">

                    <strong>
                        Autor:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $producto["autor"]
                    );

                    ?>

                </p>

            <?php endif; ?>


            <!-- PRECIO -->

            <div class="detalle-precio">

                <?php if ($descuento > 0): ?>

                    <div class="detalle-precio-promocion">

                        <span class="detalle-precio-original">

                            $

                            <?php

                            echo number_format(
                                $precioOriginal,
                                0,
                                ",",
                                "."
                            );

                            ?>

                        </span>


                        <span class="detalle-badge-promocion">

                            🔥

                            <?php

                            echo number_format(
                                $descuento,
                                0
                            );

                            ?>% OFF

                        </span>


                        <strong class="detalle-precio-final">

                            $

                            <?php

                            echo number_format(
                                $precioFinal,
                                0,
                                ",",
                                "."
                            );

                            ?>

                        </strong>

                    </div>

                <?php else: ?>

                    <strong class="detalle-precio-normal">

                        $

                        <?php

                        echo number_format(
                            $precioOriginal,
                            0,
                            ",",
                            "."
                        );

                        ?>

                    </strong>

                <?php endif; ?>

            </div>


            <!-- STOCK -->

            <div class="detalle-stock">

                <?php if ($producto["stock"] > 0): ?>

                    <span class="stock-disponible">

                        ✓ Stock disponible:

                        <?php

                        echo (int)$producto["stock"];

                        ?>

                    </span>

                <?php else: ?>

                    <span class="stock-agotado">

                        ✕ Producto sin stock

                    </span>

                <?php endif; ?>

            </div>


            <!-- AGREGAR AL CARRITO -->

            <?php if ($producto["stock"] > 0): ?>

                <form
                    method="POST"
                    action="carrito.php"
                    style="display:inline;"
                >

                    <input
                        type="hidden"
                        name="agregar"
                        value="<?php echo (int)$producto["id"]; ?>"
                    >

                    <button
                        type="submit"
                        class="btn-detalle-carrito"
                    >
                        🛒 Agregar al carrito
                    </button>

                </form>

            <?php else: ?>

                <button
                    class="btn-detalle-agotado"
                    disabled
                >
                    Sin stock
                </button>

            <?php endif; ?>


        </div>

    </div>

</main>


<?php include("footer.php"); ?>


</body>

</html>