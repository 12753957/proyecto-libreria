<?php

include("../config/bd.php");

/*VERIFICAR ID*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: productos.php");
    exit;
}

$id = intval($_GET["id"]);


/*BUSCAR PRODUCTO */

$stmt = $conexion->prepare(
    "SELECT * FROM productos WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: productos.php");
    exit;
}

$producto = $resultado->fetch_assoc();

$stmt->close();


/*ACTUALIZAR PRODUCTO*/

if (isset($_POST["actualizar"])) {

    $nombre = trim($_POST["nombre"] ?? "");
    $autor = trim($_POST["autor"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $precio = floatval($_POST["precio"] ?? 0);
    $descuento = floatval($_POST["descuento"] ?? 0);
    $stock = intval($_POST["stock"] ?? 0);


    /* DESCUENTO*/

    if ($descuento < 0) {
        $descuento = 0;
    }

    if ($descuento > 100) {
        $descuento = 100;
    }


    /* CONSERVAR IMAGEN ACTUAL */

    $imagen = trim($producto["imagen"] ?? "");

    $imagen = str_replace("\\", "/", $imagen);

    $imagen = preg_replace(
        '#^(\./)?img/#i',
        "",
        $imagen
    );

    $imagen = ltrim($imagen, "/");


    /*IMÁGENES AUTOMÁTICAS*/

    $imagenesAutomaticas = [

        "El Principito" =>
            "el_principito.webp",

        "Don Quijote" =>
            "don_quijote.jpg",

        "Cuentos Cortos" =>
            "cuentoscortos.jpg",

        "Cuaderno universitario" =>
            "cuadernos.webp",

        "Mochila escolar" =>
            "mochila.webp",

        "Cartuchera escolar" =>
            "cartucheras.jpg"

    ];


    /* SI NO HAY IMAGEN
       BUSCAR AUTOMÁTICAMENTE */

    if ($imagen == "" && isset($imagenesAutomaticas[$nombre])) {

        $imagenAutomatica =
            $imagenesAutomaticas[$nombre];

        $rutaAutomatica =
            __DIR__ . "/../../img/" . $imagenAutomatica;

        if (file_exists($rutaAutomatica)) {

            $imagen =
                $imagenAutomatica;
        }
    }


    /*SUBIR NUEVA IMAGEN*/

    if (
        isset($_FILES["imagen"]) &&
        $_FILES["imagen"]["error"] === UPLOAD_ERR_OK
    ) {

        $nombreImagen =
            basename($_FILES["imagen"]["name"]);


        $nombreImagen =
            preg_replace(
                "/[^A-Za-z0-9._-]/",
                "_",
                $nombreImagen
            );


        $rutaDestino =
            __DIR__ . "/../../img/" . $nombreImagen;


        if (
            move_uploaded_file(
                $_FILES["imagen"]["tmp_name"],
                $rutaDestino
            )
        ) {

            /*
             * Guardamos solamente
             * el nombre del archivo.
             */

            $imagen =
                $nombreImagen;
        }
    }


    /* =========================
       ACTUALIZAR BASE DE DATOS
    ========================= */

    $stmt = $conexion->prepare("
        UPDATE productos
        SET nombre = ?,
            autor = ?,
            categoria = ?,
            precio = ?,
            descuento = ?,
            stock = ?,
            imagen = ?
        WHERE id = ?
    ");


    $stmt->bind_param(
        "sssddisi",
        $nombre,
        $autor,
        $categoria,
        $precio,
        $descuento,
        $stock,
        $imagen,
        $id
    );


    if ($stmt->execute()) {

        $stmt->close();

        header(
            "Location: productos.php?mensaje=editado"
        );

        exit;

    } else {

        $error =
            "Error al actualizar: " .
            $conexion->error;

        $stmt->close();
    }
}


include("../template/cabecera.php");

?>


<div class="container-fluid">


    <!-- =========================
         ENCABEZADO
    ========================= -->

    <div class="admin-header mb-4">

        <div>

            <h1>
                ✏️ Editar producto
            </h1>

            <p>
                Modificá la información y la promoción del producto
            </p>

        </div>

    </div>


    <!-- =========================
         ERROR
    ========================= -->

    <?php if (isset($error)): ?>

        <div class="alert alert-danger">

            ❌
            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         FORMULARIO
    ========================= -->

    <div class="producto-form-card">

        <h3>
            📦 Datos del producto
        </h3>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="row g-3">


                <!-- =========================
                     CATEGORÍA
                ========================= -->

                <div class="col-md-6">

                    <label class="form-label">
                        Categoría
                    </label>

                    <select
                        name="categoria"
                        id="categoria"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Seleccionar categoría
                        </option>


                        <option
                            value="Libros"
                            <?php
                            if ($producto["categoria"] == "Libros") {
                                echo "selected";
                            }
                            ?>
                        >
                            📚 Libros
                        </option>


                        <option
                            value="Cuadernos"
                            <?php
                            if ($producto["categoria"] == "Cuadernos") {
                                echo "selected";
                            }
                            ?>
                        >
                            📓 Cuadernos
                        </option>


                        <option
                            value="Mochilas"
                            <?php
                            if ($producto["categoria"] == "Mochilas") {
                                echo "selected";
                            }
                            ?>
                        >
                            🎒 Mochilas
                        </option>


                        <option
                            value="Cartucheras"
                            <?php
                            if ($producto["categoria"] == "Cartucheras") {
                                echo "selected";
                            }
                            ?>
                        >
                            ✏️ Cartucheras
                        </option>


                        <option
                            value="Útiles Escolares"
                            <?php
                            if ($producto["categoria"] == "Útiles Escolares") {
                                echo "selected";
                            }
                            ?>
                        >
                            🖍️ Útiles Escolares
                        </option>

                    </select>

                </div>


                <!-- =========================
                     PRODUCTO
                ========================= -->

                <div class="col-md-6">

                    <label class="form-label">
                        Producto
                    </label>

                    <select
                        name="nombre"
                        id="nombre"
                        class="form-select"
                        required
                    >

                        <option
                            value="<?php
                            echo htmlspecialchars(
                                $producto["nombre"]
                            );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $producto["nombre"]
                            );
                            ?>

                        </option>

                    </select>

                </div>


                <!-- =========================
                     AUTOR
                ========================= -->

                <div class="col-md-4">

                    <label class="form-label">
                        Autor
                    </label>

                    <input
                        type="text"
                        name="autor"
                        id="autor"
                        class="form-control"
                        value="<?php
                        echo htmlspecialchars(
                            $producto["autor"]
                        );
                        ?>"
                        readonly
                    >

                </div>


                <!-- =========================
                     PRECIO
                ========================= -->

                <div class="col-md-4">

                    <label class="form-label">
                        Precio
                    </label>

                    <input
                        type="number"
                        name="precio"
                        id="precio"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars(
                            $producto["precio"]
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- =========================
                     DESCUENTO
                ========================= -->

                <div class="col-md-4">

                    <label class="form-label">
                        🔥 Descuento (%)
                    </label>

                    <input
                        type="number"
                        name="descuento"
                        id="descuento"
                        class="form-control"
                        min="0"
                        max="100"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars(
                            $producto["descuento"] ?? 0
                        );
                        ?>"
                        required
                    >

                    <small class="text-muted">
                        Colocá 0 si no tiene promoción.
                    </small>

                </div>


                <!-- =========================
                     PRECIO FINAL
                ========================= -->

                <div class="col-md-12">

                    <div
                        id="precioPromocional"
                        class="precio-promocional-admin"
                    >

                        <?php

                        $precioOriginal =
                            floatval(
                                $producto["precio"]
                            );

                        $descuentoActual =
                            floatval(
                                $producto["descuento"] ?? 0
                            );

                        $precioFinal =
                            $precioOriginal -
                            (
                                $precioOriginal *
                                $descuentoActual /
                                100
                            );

                        ?>

                        Precio final:

                        <strong>

                            $
                            <?php
                            echo number_format(
                                $precioFinal,
                                2,
                                ",",
                                "."
                            );
                            ?>

                        </strong>

                    </div>

                </div>


                <!-- =========================
                     STOCK
                ========================= -->

                <div class="col-md-4">

                    <label class="form-label">
                        Stock
                    </label>

                    <input
                        type="number"
                        name="stock"
                        class="form-control"
                        min="0"
                        value="<?php
                        echo htmlspecialchars(
                            $producto["stock"]
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- =========================
                     IMAGEN ACTUAL
                ========================= -->

                <div class="col-md-6">

                    <label class="form-label">
                        Imagen actual
                    </label>

                    <div class="imagen-editar">

                        <?php

                        $imagenActual =
                            trim(
                                $producto["imagen"] ?? ""
                            );

                        $imagenActual =
                            str_replace(
                                "\\",
                                "/",
                                $imagenActual
                            );

                        $imagenActual =
                            preg_replace(
                                '#^(\./)?img/#i',
                                "",
                                $imagenActual
                            );

                        $imagenActual =
                            ltrim(
                                $imagenActual,
                                "/"
                            );

                        ?>

                        <?php if ($imagenActual != ""): ?>

                            <img
                                id="vistaImagen"
                                src="../../img/<?php
                                echo htmlspecialchars(
                                    $imagenActual
                                );
                                ?>"
                                class="imagen-editar-preview"
                                alt="Imagen del producto"
                            >

                        <?php else: ?>

                            <div
                                id="sinImagen"
                                class="text-muted"
                            >

                                📷 Sin imagen

                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- =========================
                     CAMBIAR IMAGEN
                ========================= -->

                <div class="col-md-6">

                    <label class="form-label">
                        Cambiar imagen
                    </label>

                    <input
                        type="file"
                        name="imagen"
                        id="imagen"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="text-muted">

                        Si no seleccionás una imagen,
                        se mantiene la actual.

                    </small>

                </div>


                <!-- =========================
                     BOTONES
                ========================= -->

                <div class="col-md-12">

                    <div class="botones-editar">

                        <button
                            type="submit"
                            name="actualizar"
                            class="btn btn-agregar-producto"
                        >

                            💾 Guardar cambios

                        </button>


                        <a
                            href="productos.php"
                            class="btn btn-cancelar-editar"
                        >

                            ❌ Cancelar

                        </a>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>


<script>

/* =========================================
   ELEMENTOS
========================================= */

const inputPrecio =
    document.getElementById("precio");

const inputDescuento =
    document.getElementById("descuento");

const precioPromocional =
    document.getElementById(
        "precioPromocional"
    );


/* =========================================
   CALCULAR PRECIO FINAL
========================================= */

function calcularPrecioFinal() {

    const precio =
        parseFloat(
            inputPrecio.value
        ) || 0;

    let descuento =
        parseFloat(
            inputDescuento.value
        ) || 0;


    if (descuento < 0) {
        descuento = 0;
    }

    if (descuento > 100) {
        descuento = 100;
    }


    const precioFinal =
        precio -
        (
            precio *
            descuento /
            100
        );


    precioPromocional.innerHTML =
        "Precio final: <strong>$" +
        precioFinal.toLocaleString(
            "es-AR",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        ) +
        "</strong>";
}


/* =========================================
   CAMBIAR PRECIO
========================================= */

inputPrecio.addEventListener(
    "input",
    calcularPrecioFinal
);


/* =========================================
   CAMBIAR DESCUENTO
========================================= */

inputDescuento.addEventListener(
    "input",
    calcularPrecioFinal
);


/* =========================================
   PREVISUALIZAR NUEVA IMAGEN
========================================= */

const inputImagen =
    document.getElementById("imagen");

const vistaImagen =
    document.getElementById("vistaImagen");


if (inputImagen) {

    inputImagen.addEventListener(
        "change",
        function () {

            const archivo =
                this.files[0];

            if (!archivo) {
                return;
            }


            const lector =
                new FileReader();


            lector.onload =
                function (e) {

                    if (vistaImagen) {

                        vistaImagen.src =
                            e.target.result;

                    } else {

                        const contenedor =
                            document.querySelector(
                                ".imagen-editar"
                            );

                        const imagen =
                            document.createElement(
                                "img"
                            );

                        imagen.id =
                            "vistaImagen";

                        imagen.className =
                            "imagen-editar-preview";

                        imagen.src =
                            e.target.result;

                        imagen.alt =
                            "Vista previa";

                        contenedor.innerHTML =
                            "";

                        contenedor.appendChild(
                            imagen
                        );
                    }

                };


            lector.readAsDataURL(
                archivo
            );

        }
    );

}


/* =========================================
   CALCULAR AL CARGAR
========================================= */

calcularPrecioFinal();

</script>


<?php include("../template/pie.php"); ?>