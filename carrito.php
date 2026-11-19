<?php

session_start();

include("administrador/config/bd.php");


/* CREAR CARRITO*/

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}


/* AGREGAR PRODUCTO */

if (isset($_POST["agregar"])) {

    // Al agregar un producto, todavía NO se realizó la compra
    unset($_SESSION["compra_realizada"]);

    $id = intval($_POST["agregar"]);

    $consulta = $conexion->prepare(
        "SELECT id, nombre, precio, descuento, stock, imagen
         FROM productos
         WHERE id = ?"
    );

    $consulta->bind_param("i", $id);
    $consulta->execute();

    $resultado = $consulta->get_result();
    $producto = $resultado->fetch_assoc();

    $consulta->close();

    if ($producto) {

        $cantidadActual = $_SESSION["carrito"][$id] ?? 0;

        if ($cantidadActual < $producto["stock"]) {

            $_SESSION["carrito"][$id] =
                $cantidadActual + 1;
        }
    }

    header("Location: carrito.php");
    exit;
}


/* AUMENTAR CANTIDAD */

if (isset($_POST["aumentar"])) {

    $id = intval($_POST["aumentar"]);

    if (isset($_SESSION["carrito"][$id])) {

        $consulta = $conexion->prepare(
            "SELECT stock
             FROM productos
             WHERE id = ?"
        );

        $consulta->bind_param("i", $id);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $producto = $resultado->fetch_assoc();

        $consulta->close();

        if ($producto) {

            $cantidadActual =
                $_SESSION["carrito"][$id];

            if ($cantidadActual < $producto["stock"]) {

                $_SESSION["carrito"][$id] =
                    $cantidadActual + 1;
            }
        }
    }

    header("Location: carrito.php");
    exit;
}


/* DISMINUIR CANTIDAD*/

if (isset($_POST["disminuir"])) {

    $id = intval($_POST["disminuir"]);

    if (isset($_SESSION["carrito"][$id])) {

        $_SESSION["carrito"][$id]--;

        if ($_SESSION["carrito"][$id] <= 0) {

            unset($_SESSION["carrito"][$id]);
        }
    }

    header("Location: carrito.php");
    exit;
}


/* ELIMINAR PRODUCTO*/

if (isset($_POST["eliminar"])) {

    $id = intval($_POST["eliminar"]);

    if (isset($_SESSION["carrito"][$id])) {

        unset($_SESSION["carrito"][$id]);
    }

    header("Location: carrito.php");
    exit;
}


/* VACIAR CARRITO*/

if (isset($_POST["vaciar"])) {

    $_SESSION["carrito"] = [];

    header("Location: carrito.php");
    exit;
}


/*FINALIZAR COMPRA*/

if (isset($_POST["finalizar"])) {

    if (!empty($_SESSION["carrito"])) {

        $compraValida = true;


        /*
        PRIMERO VERIFICAMOS EL STOCK
        */

        foreach ($_SESSION["carrito"] as $id => $cantidad) {

            $id = intval($id);
            $cantidad = intval($cantidad);

            $consulta = $conexion->prepare(
                "SELECT id, precio, descuento, stock
                 FROM productos
                 WHERE id = ?"
            );

            $consulta->bind_param("i", $id);
            $consulta->execute();

            $resultado = $consulta->get_result();
            $producto = $resultado->fetch_assoc();

            $consulta->close();

            if (
                !$producto ||
                $cantidad <= 0 ||
                $cantidad > $producto["stock"]
            ) {

                $compraValida = false;
                break;
            }
        }


        /*
         SI EL STOCK ES CORRECTO
        GUARDAMOS TODA LA COMPRA
        */

        if ($compraValida) {

            /*INICIAR TRANSACCIÓN*/

            $conexion->begin_transaction();


            try {

                /*CALCULAR TOTAL REAL DESDE LA BASE DE DATOS*/

                $totalCompra = 0;


                foreach ($_SESSION["carrito"] as $id => $cantidad) {

                    $id = intval($id);
                    $cantidad = intval($cantidad);

                    $consulta = $conexion->prepare(
                        "SELECT precio, descuento, stock
                         FROM productos
                         WHERE id = ?"
                    );

                    $consulta->bind_param("i", $id);
                    $consulta->execute();

                    $resultado = $consulta->get_result();
                    $producto = $resultado->fetch_assoc();

                    $consulta->close();


                    if (!$producto) {

                        throw new Exception(
                            "No se encontró uno de los productos."
                        );
                    }


                    /*
                    VALIDAR STOCK NUEVAMENTE*/

                    if (
                        $cantidad <= 0 ||
                        $cantidad > $producto["stock"]
                    ) {

                        throw new Exception(
                            "No hay stock suficiente."
                        );
                    }


                    /* 
                    CALCULAR PRECIO FINAL*/

                    $precioOriginal =
                        (float)$producto["precio"];

                    $descuento =
                        (float)($producto["descuento"] ?? 0);


                    if ($descuento < 0) {
                        $descuento = 0;
                    }


                    if ($descuento > 100) {
                        $descuento = 100;
                    }


                    $precioFinal =
                        $precioOriginal -
                        (
                            $precioOriginal *
                            $descuento /
                            100
                        );


                    $subtotal =
                        $precioFinal *
                        $cantidad;


                    $totalCompra +=
                        $subtotal;
                }


                /*INSERTAR CABECERA DE LA COMPRA*/

                $cliente = "Cliente web";


                $insertarCarrito = $conexion->prepare(
                    "INSERT INTO carrito
                    (cliente, fecha, total)
                    VALUES (?, NOW(), ?)"
                );


                $insertarCarrito->bind_param(
                    "sd",
                    $cliente,
                    $totalCompra
                );


                if (!$insertarCarrito->execute()) {

                    throw new Exception(
                        "No se pudo guardar la compra."
                    );
                }


                /* OBTENER ID DEL CARRITO CREADO*/

                $carritoId =
                    $conexion->insert_id;


                $insertarCarrito->close();


                /*OBTENER DETALLE CARRITO*/

                $insertarDetalle = $conexion->prepare(
                    "INSERT INTO detalle_carrito
                    (carrito_id, producto_id, cantidad, precio, subtotal)
                    VALUES (?, ?, ?, ?, ?)"
                );


                /*PREPARAR ACTUALIZACIÓN DEL STOCK */

                $actualizarStock = $conexion->prepare(
                    "UPDATE productos
                     SET stock = stock - ?
                     WHERE id = ?
                     AND stock >= ?"
                );


                /* GUARDAR CADA PRODUCTO DE LA COMPRA */

                foreach ($_SESSION["carrito"] as $id => $cantidad) {

                    $id = intval($id);
                    $cantidad = intval($cantidad);


                    /*OBTENER PRODUCTO */

                    $consulta = $conexion->prepare(
                        "SELECT precio, descuento, stock
                         FROM productos
                         WHERE id = ?"
                    );


                    $consulta->bind_param(
                        "i",
                        $id
                    );


                    $consulta->execute();


                    $resultado =
                        $consulta->get_result();


                    $producto =
                        $resultado->fetch_assoc();


                    $consulta->close();


                    if (!$producto) {

                        throw new Exception(
                            "No se encontró el producto."
                        );
                    }


                    /* PRECIO*/

                    $precioOriginal =
                        (float)$producto["precio"];


                    $descuento =
                        (float)($producto["descuento"] ?? 0);


                    if ($descuento < 0) {
                        $descuento = 0;
                    }


                    if ($descuento > 100) {
                        $descuento = 100;
                    }


                    $precioFinal =
                        $precioOriginal -
                        (
                            $precioOriginal *
                            $descuento /
                            100
                        );


                    /* SUBTOTAL */

                    $subtotal =
                        $precioFinal *
                        $cantidad;


                    /*
                    ---------------------------------------------
                    GUARDAR DETALLE
                    ---------------------------------------------
                    */

                    $insertarDetalle->bind_param(
                        "iiidd",
                        $carritoId,
                        $id,
                        $cantidad,
                        $precioFinal,
                        $subtotal
                    );


                    if (!$insertarDetalle->execute()) {

                        throw new Exception(
                            "No se pudo guardar el detalle de la compra."
                        );
                    }


                    /*
                    ---------------------------------------------
                    DESCONTAR STOCK
                    ---------------------------------------------
                    */

                    $actualizarStock->bind_param(
                        "iii",
                        $cantidad,
                        $id,
                        $cantidad
                    );


                    if (!$actualizarStock->execute()) {

                        throw new Exception(
                            "No se pudo actualizar el stock."
                        );
                    }


                    /*
                    Verificamos que realmente haya
                    actualizado una fila.
                    */

                    if ($actualizarStock->affected_rows <= 0) {

                        throw new Exception(
                            "El stock cambió mientras se procesaba la compra."
                        );
                    }
                }


                /*
                =================================================
                CERRAR CONSULTAS
                =================================================
                */

                $insertarDetalle->close();
                $actualizarStock->close();


                /*
                =================================================
                CONFIRMAR TRANSACCIÓN
                =================================================
                */

                $conexion->commit();


                /*
                =================================================
                VACIAR CARRITO
                =================================================
                */

                $_SESSION["carrito"] = [];


                /* MENSAJE*/

                $_SESSION["compra_realizada"] = true;


                header("Location: carrito.php");
                exit;


            } catch (Exception $e) {

                /* ALGO FALLA, DESHACER TODO*/

                $conexion->rollback();


                $_SESSION["error_compra"] =
                    "No se pudo completar la compra. Intentá nuevamente.";


                header("Location: carrito.php");
                exit;
            }
        }
    }
}


/*MENSAJE DE COMPRA REALIZADA*/

$compraRealizada = false;

if (isset($_SESSION["compra_realizada"])) {

    $compraRealizada = true;

    unset($_SESSION["compra_realizada"]);
}


/* DE ERROR*/

$errorCompra = "";

if (isset($_SESSION["error_compra"])) {

    $errorCompra =
        $_SESSION["error_compra"];

    unset($_SESSION["error_compra"]);
}


/* PRODUCTOS*/

$productosCarrito = [];

$total = 0;

$cantidadTotalProductos = 0;


if (!empty($_SESSION["carrito"])) {

    foreach ($_SESSION["carrito"] as $id => $cantidad) {

        $id = intval($id);

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


        if ($producto) {


            /* =================================================
               CONTROLAR STOCK
            ================================================== */

            if ($cantidad > $producto["stock"]) {

                $cantidad =
                    $producto["stock"];

                $_SESSION["carrito"][$id] =
                    $cantidad;
            }


            if (
                $cantidad <= 0 ||
                $producto["stock"] <= 0
            ) {

                unset(
                    $_SESSION["carrito"][$id]
                );

                continue;
            }


            /* =================================================
               DESCUENTO
            ================================================== */

            $precioOriginal =
                (float)$producto["precio"];

            $descuento =
                (float)($producto["descuento"] ?? 0);


            if ($descuento < 0) {
                $descuento = 0;
            }


            if ($descuento > 100) {
                $descuento = 100;
            }


            /* =================================================
               PRECIO FINAL
            ================================================== */

            $precioFinal =
                $precioOriginal -
                (
                    $precioOriginal *
                    $descuento /
                    100
                );


            /* =================================================
               SUBTOTAL
            ================================================== */

            $subtotal =
                $precioFinal *
                $cantidad;


            $producto["cantidad"] =
                $cantidad;

            $producto["precio_original"] =
                $precioOriginal;

            $producto["descuento"] =
                $descuento;

            $producto["precio_final"] =
                $precioFinal;

            $producto["subtotal"] =
                $subtotal;


            $total +=
                $subtotal;


            $cantidadTotalProductos +=
                $cantidad;


            $productosCarrito[] =
                $producto;
        }
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
        Carrito - Librería El Estudiante
    </title>


    <link
        rel="stylesheet"
        href="css/style.css?v=24"
    >

</head>


<body>


<!-- =====================================================
     ENCABEZADO
===================================================== -->

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


<!-- =====================================================
     CARRITO
===================================================== -->

<main class="carrito-contenedor">

    <div class="carrito-panel">


        <?php if ($compraRealizada): ?>


            <div class="compra-realizada">

                <div class="icono-compra">
                    ✅
                </div>

                <h1>
                    ¡Compra realizada correctamente!
                </h1>

                <p>
                    Gracias por elegir Librería El Estudiante.
                </p>

                <p>
                    Tu pedido fue procesado correctamente.
                </p>

                <a
                    href="productos.php"
                    class="btn-carrito"
                >
                    📚 Seguir comprando
                </a>

            </div>


        <?php else: ?>


            <h1>
                🛒 Carrito de Compras
            </h1>


            <?php if ($errorCompra != ""): ?>

                <div class="alert alert-danger">

                    <?php
                    echo htmlspecialchars($errorCompra);
                    ?>

                </div>

            <?php endif; ?>


            <?php if (empty($productosCarrito)): ?>


                <div class="carrito-vacio">

                    <div class="icono-carrito">
                        🛒
                    </div>

                    <h2>
                        El carrito está vacío
                    </h2>

                    <p>
                        Todavía no agregaste ningún producto.
                    </p>

                    <a
                        href="productos.php"
                        class="btn-carrito"
                    >
                        Ver productos
                    </a>

                </div>


            <?php else: ?>


                <div class="cantidad-total-carrito">

                    🛍️

                    <?php echo $cantidadTotalProductos; ?>

                    producto<?php echo $cantidadTotalProductos != 1 ? "s" : ""; ?>

                    en el carrito

                </div>


                <div class="tabla-carrito">

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Imagen
                                </th>

                                <th>
                                    Producto
                                </th>

                                <th>
                                    Cantidad
                                </th>

                                <th>
                                    Precio
                                </th>

                                <th>
                                    Subtotal
                                </th>

                                <th>
                                    Acción
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($productosCarrito as $producto): ?>


                            <?php

                            $imagen =
                                trim(
                                    $producto["imagen"]
                                );

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

                            ?>


                            <tr>


                                <td>

                                    <?php if (
                                        !empty($imagen) &&
                                        file_exists($rutaImagen)
                                    ): ?>

                                        <img
                                            src="<?php echo htmlspecialchars($rutaWeb); ?>"
                                            alt="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                                            class="imagen-carrito"
                                        >

                                    <?php else: ?>

                                        <div class="sin-imagen-carrito">
                                            📷
                                        </div>

                                    <?php endif; ?>

                                </td>


                                <td class="nombre-producto">

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $producto["nombre"]
                                        );
                                        ?>

                                    </strong>


                                    <?php if (
                                        !empty($producto["autor"])
                                    ): ?>

                                        <small>

                                            <?php
                                            echo htmlspecialchars(
                                                $producto["autor"]
                                            );
                                            ?>

                                        </small>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <div class="control-cantidad">


                                        <form method="POST" style="display:inline;">

                                            <input
                                                type="hidden"
                                                name="disminuir"
                                                value="<?php echo (int)$producto["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-cantidad"
                                                title="Disminuir cantidad"
                                            >
                                                −
                                            </button>

                                        </form>


                                        <span class="cantidad">

                                            <?php
                                            echo $producto["cantidad"];
                                            ?>

                                        </span>


                                        <?php if (
                                            $producto["cantidad"] <
                                            $producto["stock"]
                                        ): ?>

                                            <form method="POST" style="display:inline;">

                                                <input
                                                    type="hidden"
                                                    name="aumentar"
                                                    value="<?php echo (int)$producto["id"]; ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-cantidad"
                                                    title="Aumentar cantidad"
                                                >
                                                    +
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <span
                                                class="btn-cantidad btn-cantidad-deshabilitado"
                                                title="Stock máximo alcanzado"
                                            >
                                                +
                                            </span>

                                        <?php endif; ?>


                                    </div>


                                    <small class="stock-disponible">

                                        Stock disponible:
                                        <?php echo (int)$producto["stock"]; ?>

                                    </small>

                                </td>


                                <td>

                                    <?php if (
                                        $producto["descuento"] > 0
                                    ): ?>

                                        <div class="precio-carrito-promocion">

                                            <span class="precio-carrito-original">

                                                $

                                                <?php
                                                echo number_format(
                                                    $producto["precio_original"],
                                                    0,
                                                    ",",
                                                    "."
                                                );
                                                ?>

                                            </span>


                                            <span class="badge-carrito-promocion">

                                                🔥

                                                <?php
                                                echo number_format(
                                                    $producto["descuento"],
                                                    0
                                                );
                                                ?>% OFF

                                            </span>


                                            <strong class="precio-carrito-final">

                                                $

                                                <?php
                                                echo number_format(
                                                    $producto["precio_final"],
                                                    0,
                                                    ",",
                                                    "."
                                                );
                                                ?>

                                            </strong>

                                        </div>


                                    <?php else: ?>


                                        <strong class="precio-carrito-normal">

                                            $

                                            <?php
                                            echo number_format(
                                                $producto["precio_final"],
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                        </strong>

                                    <?php endif; ?>

                                </td>


                                <td class="subtotal">

                                    $

                                    <?php
                                    echo number_format(
                                        $producto["subtotal"],
                                        0,
                                        ",",
                                        "."
                                    );
                                    ?>

                                </td>


                                <td>

                                    <form method="POST" style="display:inline;">

                                        <input
                                            type="hidden"
                                            name="eliminar"
                                            value="<?php echo (int)$producto["id"]; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-eliminar"
                                            title="Eliminar producto"
                                        >
                                            🗑️
                                        </button>

                                    </form>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


                <div class="carrito-resumen">


                    <div class="total-carrito">

                        <span>
                            Total:
                        </span>

                        <strong>

                            $

                            <?php
                            echo number_format(
                                $total,
                                0,
                                ",",
                                "."
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="acciones-carrito">


                        <a
                            href="productos.php"
                            class="btn-secundario"
                        >
                            ← Seguir comprando
                        </a>


                        <form method="POST" style="display:inline;">

                            <button
                                type="submit"
                                name="vaciar"
                                value="1"
                                class="btn-vaciar"
                                onclick="return confirm('¿Seguro que querés vaciar el carrito?');"
                            >
                                🗑️ Vaciar carrito
                            </button>


                           <a
                             href="metodo_pago.php"
                            class="btn-finalizar">

                           💳 Elegir método de pago
                                       </a>


                    </div>

                </div>


            <?php endif; ?>


        <?php endif; ?>


    </div>

</main>


<?php include("footer.php"); ?>


</body>

</html>