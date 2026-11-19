<?php

include("administrador/config/bd.php");


/*CATEGORÍA SELECCIONADA */

$categoria = isset($_GET["categoria"])
    ? trim($_GET["categoria"])
    : "todos";


/*CONSULTAR PRODUCTOS*/

$productos = [];

$sql = "SELECT id, nombre, autor, categoria, precio, descuento, stock, imagen
        FROM productos
        ORDER BY id ASC";

$resultado = $conexion->query($sql);


if ($resultado) {

    while ($fila = $resultado->fetch_assoc()) {

        $productos[] = $fila;

    }

}


/*FUNCIÓN PARA NORMALIZAR CATEGORÍAS*/

function normalizarCategoria($categoria)
{
    $categoria = strtolower(trim($categoria));

    $categoria = str_replace(
        ["á", "é", "í", "ó", "ú", "ñ"],
        ["a", "e", "i", "o", "u", "n"],
        $categoria
    );

    return $categoria;
}


/*TÍTULO DE CATEGORÍA*/

$tituloCategoria = "Nuestros Productos";


if ($categoria == "libros") {

    $tituloCategoria = "📚 Libros";

} elseif ($categoria == "cuadernos") {

    $tituloCategoria = "📓 Cuadernos";

} elseif ($categoria == "mochilas") {

    $tituloCategoria = "🎒 Mochilas";

} elseif ($categoria == "cartucheras") {

    $tituloCategoria = "✏️ Cartucheras";

} elseif ($categoria == "utiles") {

    $tituloCategoria = "🖍️ Útiles Escolares";

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
        Productos - Librería El Estudiante
    </title>


    <link
        rel="stylesheet"
        href="css/bootstrap.min.css"
    >

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


<!--CONTENID-->

<main>

    <div class="contenedor-tienda">


        <!--CATEGORÍAS-->

        <aside class="categorias">

            <h3>
                Categorías
            </h3>


            <ul>

                <li>
                    <a href="productos.php?categoria=libros">
                        📚 Libros
                    </a>
                </li>


                <li>
                    <a href="productos.php?categoria=cuadernos">
                        📓 Cuadernos
                    </a>
                </li>


                <li>
                    <a href="productos.php?categoria=mochilas">
                        🎒 Mochilas
                    </a>
                </li>


                <li>
                    <a href="productos.php?categoria=cartucheras">
                        ✏️ Cartucheras
                    </a>
                </li>


                <li>
                    <a href="productos.php">
                        🔄 Ver todos
                    </a>
                </li>

            </ul>

        </aside>


        <!--PRODUCTOS-->

        <section class="lista-productos">


            <h1>
                <?php echo $tituloCategoria; ?>
            </h1>


            <!--BUSCADOR-->

            <div class="buscador">

                <input
                    type="text"
                    placeholder="Buscar productos"
                    id="buscarProducto"
                >

            </div>


            <!--GRILLA-->

            <section
                class="productos-grid"
                id="listaProductos"
            >


                <?php

                $cantidadMostrada = 0;


                foreach ($productos as $producto):


                    $categoriaProducto =
                        normalizarCategoria(
                            $producto["categoria"]
                        );


                    /*
                       Filtrar por categoría
                    */

                    if (
                        $categoria != "todos" &&
                        $categoriaProducto != normalizarCategoria($categoria)
                    ) {

                        continue;
                    }


                    $cantidadMostrada++;


                    /*
                       Imagen
                    */

                    $imagen =
                        trim($producto["imagen"]);

                    $imagen =
                        str_replace(
                            "\\",
                            "/",
                            $imagen
                        );

                    $imagen =
                        preg_replace(
                            '#^(\./)?img/#i',
                            "",
                            $imagen
                        );

                    $imagen =
                        ltrim(
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


                    /*CALCULAR PRECIO PROMOCIONAL*/

                    $precioOriginal =
                        (float)$producto["precio"];


                    $descuento =
                        (float)($producto["descuento"] ?? 0);


                    /*
                       Evitamos valores incorrectos
                    */

                    if ($descuento < 0) {
                        $descuento = 0;
                    }

                    if ($descuento > 100) {
                        $descuento = 100;
                    }


                    /*
                       Precio final
                    */

                    $precioFinal =
                        $precioOriginal -
                        (
                            $precioOriginal *
                            $descuento /
                            100
                        );

                ?>


                    <!--PRODUCTO-->

                    <div class="producto">


                        <!-- IMAGEN -->

                        <?php if (
                            !empty($imagen) &&
                            file_exists($rutaImagen)
                        ): ?>

                            <img
                                src="<?php echo htmlspecialchars($rutaWeb); ?>"
                                alt="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                            >

                        <?php else: ?>

                            <div class="sin-imagen-producto">

                                📷

                                <span>
                                    Sin imagen
                                </span>

                            </div>

                        <?php endif; ?>


                        <!-- NOMBRE -->

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $producto["nombre"]
                            );

                            ?>

                        </h3>


                        <!-- AUTOR -->

                        <?php if (
                            !empty($producto["autor"])
                        ): ?>

                            <p class="autor-producto">

                                <?php

                                echo htmlspecialchars(
                                    $producto["autor"]
                                );

                                ?>

                            </p>

                        <?php endif; ?>


                        <!--PRECIO-->

                        <?php if ($descuento > 0): ?>

                            <div class="precio-producto-promocion">


                                <span class="precio-original-tachado">

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


                                <span class="badge-promocion-publica">

                                    🔥
                                    <?php

                                    echo number_format(
                                        $descuento,
                                        0
                                    );

                                    ?>% OFF

                                </span>


                                <span class="precio-final-publico">

                                    $
                                    <?php

                                    echo number_format(
                                        $precioFinal,
                                        0,
                                        ",",
                                        "."
                                    );

                                    ?>

                                </span>


                            </div>

                        <?php else: ?>

                            <p class="precio">

                                $

                                <?php

                                echo number_format(
                                    $precioOriginal,
                                    0,
                                    ",",
                                    "."
                                );

                                ?>

                            </p>

                        <?php endif; ?>


                        <!--BOTONES-->

                        <div class="botones-producto">


                            <a
                                href="producto.php?id=<?php echo (int)$producto["id"]; ?>"
                                class="btn-ver-producto"
                            >
                                👁️ Ver producto
                            </a>


                            <?php if ($producto["stock"] > 0): ?>

                                <!--
                                Agregar al carrito mediante POST
                                -->

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
                                        class="btn-comprar"
                                    >
                                        🛒 Agregar al carrito
                                    </button>

                                </form>

                            <?php else: ?>

                                <span class="sin-stock">
                                    Sin stock
                                </span>

                            <?php endif; ?>


                        </div>


                    </div>


                <?php endforeach; ?>


                <!--NO HAY PRODUCTOS-->

                <?php if ($cantidadMostrada == 0): ?>


                    <div class="mensaje-productos">

                        <h3>
                            No se encontraron productos.
                        </h3>


                        <a href="productos.php">
                            Ver todos los productos
                        </a>

                    </div>


                <?php endif; ?>


            </section>


        </section>

    </div>

</main>


<?php include("footer.php"); ?>


<!--BUSCADOR-->

<script>

const buscador = document.getElementById("buscarProducto");
const productos = document.querySelectorAll(".producto");


if (buscador) {

    buscador.addEventListener("input", function () {

        const texto = this.value
            .toLowerCase()
            .trim();

        productos.forEach(function (producto) {

            const nombre = producto.querySelector("h3")
                ? producto.querySelector("h3").textContent.toLowerCase()
                : "";

            const autor = producto.querySelector(".autor-producto")
                ? producto.querySelector(".autor-producto").textContent.toLowerCase()
                : "";

            const categoria = producto.textContent.toLowerCase();

            const coincide =
                nombre.includes(texto) ||
                autor.includes(texto) ||
                categoria.includes(texto);

            if (coincide) {

                producto.style.display = "";

            } else {

                producto.style.display = "none";

            }

        });

    });

}

</script>


</body>

</html>