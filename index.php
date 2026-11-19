<?php

include("administrador/config/bd.php");

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
        Librería El Estudiante
    </title>

<link rel="stylesheet" href="css/style.css?v=25">

</head>


<body>


<!-- ENCABEZADO -->

<header>

    <div class="logo">
        📚 Libreria El Estudiante
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


<!--  BIENVENIDA -->

<section class="hero">

    <div class="hero-contenido">

        <div class="hero-icono">📚</div>

        <h1>Librería El Estudiante</h1>

        <p>Todo para tu estudio, en un solo lugar</p>

        <a href="productos.php" class="btn-ver-productos">
            Ver productos
        </a>

    </div>

</section>

<!-- PRODUCTOS-->

<main id="productos">

    <h2>
        Nuestros Productos
    </h2>


    <div class="productos-grid">


        <?php

        $res = $conexion->query(
            "SELECT
                id,
                nombre,
                autor,
                categoria,
                precio,
                descuento,
                stock,
                imagen
             FROM productos
             ORDER BY id ASC"
        );

if (!$res) {

    echo '<p>No se pudieron cargar los productos.</p>';

} elseif ($res->num_rows == 0) {

?>

    <div class="sin-productos">

        <h3>
            No hay productos disponibles
        </h3>

        <p>
            Próximamente agregaremos nuevos productos.
        </p>

    </div>

<?php

} else {

    while ($p = $res->fetch_assoc()) {

        /* DATOS */

        $id =
            (int)$p["id"];

        $nombre =
            htmlspecialchars(
                $p["nombre"]
            );

        $autor =
            htmlspecialchars(
                $p["autor"] ?? ""
            );

        $precioOriginal =
            (float)$p["precio"];

        $descuento =
            (float)($p["descuento"] ?? 0);


        if ($descuento < 0) {
            $descuento = 0;
        }


        if ($descuento > 100) {
            $descuento = 100;
        }


        /* PRECIO FINAL */

        $precioFinal =
            $precioOriginal -
            (
                $precioOriginal *
                $descuento /
                100
            );


        /* IMAGEN */

        $img =
            trim(
                $p["imagen"] ?? ""
            );


        $img =
            str_replace(
                "\\",
                "/",
                $img
            );


        $img =
            preg_replace(
                '#^(\./)?img/#i',
                "",
                $img
            );


        $img =
            ltrim(
                $img,
                "/"
            );


        $rutaImagen =
            __DIR__ .
            "/img/" .
            $img;


        if (
            empty($img) ||
            !file_exists($rutaImagen)
        ) {

            $img =
                "sin-imagen.png";
        }


        $rutaWeb =
            "img/" .
            $img;

        ?>


        <!-- PRODUCTO -->

        <div class="producto">


            <img
                src="<?php echo htmlspecialchars($rutaWeb); ?>"
                alt="<?php echo $nombre; ?>"
                class="card-img-top"
            >


            <h3>
                <?php echo $nombre; ?>
            </h3>


            <?php if (!empty($autor)): ?>

                <p class="autor-producto">

                    <?php echo $autor; ?>

                </p>

            <?php endif; ?>


            <!-- PRECIO -->

            <?php if ($descuento > 0): ?>


                <div class="precio-promocion">


                    <span class="precio-original">

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


                    <span class="badge-promocion">

                        🔥

                        <?php

                        echo number_format(
                            $descuento,
                            0
                        );

                        ?>% OFF

                    </span>


                    <strong class="precio-final">

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


                <div class="precio">

                    $

                    <?php

                    echo number_format(
                        $precioOriginal,
                        0,
                        ",",
                        "."
                    );

                    ?>

                </div>


            <?php endif; ?>


            <!-- STOCK -->

            <?php if ($p["stock"] > 0): ?>


                <p class="stock-producto">

                    Stock:
                    <?php echo (int)$p["stock"]; ?>

                </p>


                <!-- BOTONES -->

                <div class="acciones-producto">


                    <a
                        href="producto.php?id=<?php echo $id; ?>"
                        class="btn-ver-producto"
                    >
                        👁️ Ver producto
                    </a>


                    <form
                        method="POST"
                        action="carrito.php"
                        style="display:inline;"
                    >

                        <input
                            type="hidden"
                            name="agregar"
                            value="<?php echo $id; ?>"
                        >

                        <button
                            type="submit"
                            class="btn-comprar"
                        >
                            🛒 Comprar
                        </button>

                    </form>


                </div>


            <?php else: ?>


                <p class="producto-agotado">

                    Agotado

                </p>


                <div class="acciones-producto">


                    <a
                        href="producto.php?id=<?php echo $id; ?>"
                        class="btn-ver-producto"
                    >
                        👁️ Ver producto
                    </a>


                    <span class="btn-agotado">

                        Sin stock

                    </span>


                </div>


            <?php endif; ?>


        </div>


        <?php

    } 

} 
?>

    </div>

</main>


<!-- FOOTER-->

<?php include("footer.php"); ?>


</body>

</html>