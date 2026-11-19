<?php

session_start();

include("administrador/config/bd.php");


/*VERIFICAR MÉTODO DE PAGO*/

if (!isset($_POST["metodo_pago"])) {

    header("Location: metodo_pago.php");
    exit;

}

$metodo_pago = $_POST["metodo_pago"];

/* =========================================
   DATOS DEL CLIENTE
========================================= */

$nombre = trim($_POST["nombre"] ?? "");
$email = trim($_POST["email"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$direccion = trim($_POST["direccion"] ?? "");
$ciudad = trim($_POST["ciudad"] ?? "");
$codigo_postal = trim($_POST["codigo_postal"] ?? "");


/* =========================================
   VERIFICAR DATOS DEL CLIENTE
========================================= */

if (
    $nombre === "" ||
    $email === "" ||
    $telefono === "" ||
    $direccion === "" ||
    $ciudad === "" ||
    $codigo_postal === ""
) {

    header("Location: datos_cliente.php");
    exit;

}


/* =========================================
   VALIDAR MÉTODO
========================================= */

$metodos_validos = [
    "efectivo",
    "tarjeta",
    "mercado_pago",
    "transferencia"
];

if (!in_array($metodo_pago, $metodos_validos)) {

    header("Location: metodo_pago.php");
    exit;

}


/* =========================================
   VERIFICAR CARRITO
========================================= */

if (
    !isset($_SESSION["carrito"]) ||
    empty($_SESSION["carrito"])
) {

    header("Location: carrito.php");
    exit;

}


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
}


/* =========================================
   INICIAR TRANSACCIÓN
========================================= */

try {

    $conexion->begin_transaction();


    /* =========================================
       CALCULAR TOTAL Y VERIFICAR STOCK
    ========================================= */

    $total = 0;

    $productos_compra = [];


    foreach ($_SESSION["carrito"] as $id => $cantidad) {

        $id = intval($id);
        $cantidad = intval($cantidad);


        if ($cantidad <= 0) {
            continue;
        }


        $consulta = $conexion->prepare(
            "SELECT id, nombre, precio, descuento, stock
             FROM productos
             WHERE id = ?"
        );

        $consulta->bind_param("i", $id);

        $consulta->execute();

        $resultado = $consulta->get_result();

        $producto = $resultado->fetch_assoc();

        $consulta->close();


        /* =========================================
           PRODUCTO NO EXISTE
        ========================================= */

        if (!$producto) {

            throw new Exception(
                "Uno de los productos ya no está disponible."
            );

        }


        /* =========================================
           VERIFICAR STOCK
        ========================================= */

        if ($producto["stock"] < $cantidad) {

            throw new Exception(
                "No hay suficiente stock de: " .
                $producto["nombre"]
            );

        }


        /* =========================================
           CALCULAR PRECIO FINAL
        ========================================= */

        $precio = floatval($producto["precio"]);

        $descuento = floatval($producto["descuento"]);

        $precio_final = $precio;

        if ($descuento > 0) {

            $precio_final =
                $precio - ($precio * $descuento / 100);

        }


        /* =========================================
           CALCULAR SUBTOTAL
        ========================================= */

        $subtotal = $precio_final * $cantidad;

        $total += $subtotal;


        /* Guardar producto para insertar detalle */

        $productos_compra[] = [

            "id" => $id,

            "cantidad" => $cantidad,

            "precio" => $precio_final,

            "subtotal" => $subtotal

        ];

    }


    /* =========================================
       VERIFICAR TOTAL
    ========================================= */

    if ($total <= 0) {

        throw new Exception(
            "No se pudo calcular el total de la compra."
        );

    }


    /* =========================================
       REGISTRAR COMPRA
    ========================================= */

    $cliente = $nombre;

    $fecha = date("Y-m-d H:i:s");


    $consulta_carrito = $conexion->prepare(
    "INSERT INTO carrito
    (cliente, fecha, total, email, telefono, direccion, ciudad, codigo_postal)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);


$consulta_carrito->bind_param(
    "ssdsssss",
    $cliente,
    $fecha,
    $total,
    $email,
    $telefono,
    $direccion,
    $ciudad,
    $codigo_postal
);


    if (!$consulta_carrito->execute()) {

        throw new Exception(
            "No se pudo registrar la compra."
        );

    }


    /* =========================================
       OBTENER ID DE LA COMPRA
    ========================================= */

    $carrito_id = $conexion->insert_id;


    $consulta_carrito->close();


    /* =========================================
       PREPARAR DETALLE
    ========================================= */

    $consulta_detalle = $conexion->prepare(
        "INSERT INTO detalle_carrito
        (carrito_id, producto_id, cantidad, precio, subtotal)
        VALUES (?, ?, ?, ?, ?)"
    );


    /* =========================================
       PREPARAR ACTUALIZACIÓN DE STOCK
    ========================================= */

    $consulta_stock = $conexion->prepare(
        "UPDATE productos
         SET stock = stock - ?
         WHERE id = ?
         AND stock >= ?"
    );


    /* =========================================
       GUARDAR PRODUCTOS Y DESCONTAR STOCK
    ========================================= */

    foreach ($productos_compra as $producto) {

        $producto_id = $producto["id"];

        $cantidad = $producto["cantidad"];

        $precio = $producto["precio"];

        $subtotal = $producto["subtotal"];


        /* =========================================
           INSERTAR DETALLE
        ========================================= */

        $consulta_detalle->bind_param(
            "iiidd",
            $carrito_id,
            $producto_id,
            $cantidad,
            $precio,
            $subtotal
        );


        if (!$consulta_detalle->execute()) {

            throw new Exception(
                "No se pudo registrar el detalle de la compra."
            );

        }


        /* =========================================
           DESCONTAR STOCK
        ========================================= */

        $consulta_stock->bind_param(
            "iii",
            $cantidad,
            $producto_id,
            $cantidad
        );


        if (!$consulta_stock->execute()) {

            throw new Exception(
                "No se pudo actualizar el stock."
            );

        }


        /* Verificar que realmente se descontó */

        if ($consulta_stock->affected_rows == 0) {

            throw new Exception(
                "El stock cambió mientras se procesaba la compra."
            );

        }

    }


    $consulta_detalle->close();

    $consulta_stock->close();


    /* =========================================
       CONFIRMAR TRANSACCIÓN
    ========================================= */

    $conexion->commit();


    /* =========================================
       VACIAR CARRITO
    ========================================= */

    $_SESSION["carrito"] = [];


    /* =========================================
       GUARDAR DATOS DE CONFIRMACIÓN
    ========================================= */

    $_SESSION["compra_realizada"] = true;

    $_SESSION["metodo_pago"] = $metodo_pago;

    $_SESSION["nombre_metodo"] = $nombre_metodo;

    $_SESSION["total_compra"] = $total;

    $_SESSION["carrito_id"] = $carrito_id;


} catch (Exception $e) {


    /* =========================================
       CANCELAR TRANSACCIÓN
    ========================================= */

    $conexion->rollback();


    $_SESSION["error_compra"] =
        $e->getMessage();


    header("Location: carrito.php");

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
        Compra confirmada - Librería El Estudiante
    </title>


    <link
        rel="stylesheet"
        href="css/style.css?v=24"
    >

    <link
        rel="stylesheet"
        href="css/metodo_pago.css?v=1"
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


    <div class="pago-contenedor confirmacion-compra">


        <div class="icono-confirmacion">
            ✅
        </div>


        <h1>
            ¡Compra confirmada!
        </h1>


        <p class="pago-descripcion">

            Tu pedido fue registrado correctamente.

        </p>


        <div class="detalle-compra">


            <p>

                <strong>
                    Número de compra:
                </strong>

                <?php echo $carrito_id; ?>

            </p>


            <p>

                <strong>
                    Total:
                </strong>

                $<?php echo number_format($total, 2, ",", "."); ?>

            </p>


            <p>

                <strong>
                    Método de pago:
                </strong>

            </p>


            <p class="metodo-seleccionado">

                <?php
                echo htmlspecialchars($nombre_metodo);
                ?>

            </p>


        </div>


        <?php if ($metodo_pago == "efectivo") { ?>

            <p class="mensaje-pago">

                Podrás realizar el pago en efectivo
                al momento de retirar tu pedido.

            </p>

        <?php } ?>


        <?php if ($metodo_pago == "tarjeta") { ?>

            <p class="mensaje-pago">

                El pago con tarjeta será procesado
                al momento de confirmar el pedido.

            </p>

        <?php } ?>


        <?php if ($metodo_pago == "mercado_pago") { ?>

            <p class="mensaje-pago">

                El pago mediante Mercado Pago
                queda registrado como método seleccionado.

            </p>

        <?php } ?>


        <?php if ($metodo_pago == "transferencia") { ?>

            <p class="mensaje-pago">

                Recibirás los datos necesarios
                para realizar la transferencia.

            </p>

        <?php } ?>


        <a
            href="index.php"
            class="btn-confirmar"
        >
            Volver al inicio
        </a>


        <a
            href="productos.php"
            class="volver-carrito"
        >
            Seguir comprando
        </a>


    </div>


</main>


</body>

</html>