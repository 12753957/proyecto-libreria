<?php

include("../config/bd.php");
include("../template/cabecera.php");


/* =====================================================
   IMÁGENES AUTOMÁTICAS PARA PRODUCTOS CONOCIDOS
===================================================== */

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


/* =====================================================
   DATOS AUTOMÁTICOS DE PRODUCTOS CONOCIDOS
===================================================== */

$datosAutomaticos = [

    "El Principito" => [
        "autor" => "Antoine de Saint-Exupéry",
        "precio" => 2500
    ],

    "Don Quijote" => [
        "autor" => "Miguel de Cervantes",
        "precio" => 3200
    ],

    "Cuentos Cortos" => [
        "autor" => "El Estudiante",
        "precio" => 1800
    ],

    "Cuaderno universitario" => [
        "autor" => "El Estudiante",
        "precio" => 2500
    ],

    "Mochila escolar" => [
        "autor" => "El Estudiante",
        "precio" => 15000
    ],

    "Cartuchera escolar" => [
        "autor" => "El Estudiante",
        "precio" => 5000
    ]

];


/* =====================================================
   AGREGAR PRODUCTO
===================================================== */

if (isset($_POST["agregar"])) {

    $nombre =
        trim($_POST["nombre"] ?? "");

    $autor =
        trim($_POST["autor"] ?? "");

    $categoria =
        trim($_POST["categoria"] ?? "");

    $precio =
        floatval($_POST["precio"] ?? 0);

    $descuento =
        floatval($_POST["descuento"] ?? 0);

    $stock =
        intval($_POST["stock"] ?? 0);


    /* =================================================
       VALIDACIONES
    ================================================= */

    if ($nombre === "") {

        echo "
            <script>
                alert('Debés ingresar el nombre del producto.');
                window.history.back();
            </script>
        ";

        exit;
    }


    if ($categoria === "") {

        echo "
            <script>
                alert('Debés seleccionar una categoría.');
                window.history.back();
            </script>
        ";

        exit;
    }


    if ($precio < 0) {

        $precio = 0;
    }


    if ($descuento < 0) {

        $descuento = 0;
    }


    if ($descuento > 100) {

        $descuento = 100;
    }


    if ($stock < 0) {

        $stock = 0;
    }


    /* =================================================
       AUTOR AUTOMÁTICO
       SOLO PARA PRODUCTOS CONOCIDOS
    ================================================= */

    if (
        $autor === "" &&
        isset($datosAutomaticos[$nombre])
    ) {

        $autor =
            $datosAutomaticos[$nombre]["autor"];
    }


    /*
       Si no se coloca autor en un producto nuevo,
       se utiliza "El Estudiante".
    */

    if ($autor === "") {

        $autor = "El Estudiante";
    }


    /* =================================================
       PRECIO AUTOMÁTICO
       SOLO SI NO SE INGRESÓ PRECIO
    ================================================= */

    if (
        $precio <= 0 &&
        isset($datosAutomaticos[$nombre])
    ) {

        $precio =
            $datosAutomaticos[$nombre]["precio"];
    }


    /* =================================================
       IMAGEN
    ================================================= */

    $imagen = "";


    /* =================================================
       IMAGEN SUBIDA MANUALMENTE
    ================================================= */

    if (
        isset($_FILES["imagen"]) &&
        $_FILES["imagen"]["error"] === UPLOAD_ERR_OK
    ) {

        $nombreImagen =
            basename(
                $_FILES["imagen"]["name"]
            );


        $nombreImagen =
            preg_replace(
                "/[^A-Za-z0-9._-]/",
                "_",
                $nombreImagen
            );


        $rutaDestino =
            __DIR__ .
            "/../../img/" .
            $nombreImagen;


        if (
            move_uploaded_file(
                $_FILES["imagen"]["tmp_name"],
                $rutaDestino
            )
        ) {

            $imagen =
                $nombreImagen;
        }
    }


    /* =================================================
       IMAGEN AUTOMÁTICA
    ================================================= */

    if (
        $imagen === "" &&
        isset($imagenesAutomaticas[$nombre])
    ) {

        $imagen =
            $imagenesAutomaticas[$nombre];
    }


    /* =================================================
       GUARDAR PRODUCTO
    ================================================= */

    $sql = "
        INSERT INTO productos
        (
            nombre,
            autor,
            categoria,
            precio,
            descuento,
            stock,
            imagen
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";


    $stmt =
        $conexion->prepare($sql);


    if ($stmt) {

        $stmt->bind_param(
            "sssddis",
            $nombre,
            $autor,
            $categoria,
            $precio,
            $descuento,
            $stock,
            $imagen
        );


        if ($stmt->execute()) {

            $stmt->close();

            echo "
                <script>
                    alert('Producto agregado correctamente');
                    window.location.href='productos.php';
                </script>
            ";

            exit;

        } else {

            echo "
                <script>
                    alert('Error al guardar el producto: " .
                    htmlspecialchars(
                        $stmt->error,
                        ENT_QUOTES,
                        "UTF-8"
                    ) .
                    "');
                    window.history.back();
                </script>
            ";

            $stmt->close();

            exit;
        }

    } else {

        echo "
            <script>
                alert('Error en la consulta.');
                window.history.back();
            </script>
        ";

        exit;
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
        Productos - Administración
    </title>


    <link rel="stylesheet" href="../../css/style.css?v=25">

</head>


<body>


<div class="contenedor-admin">


    <h1>
        📚 Administración de Productos
    </h1>


    <!--AGREGAR PRODUCTO-->

    <section class="formulario-producto">


        <h2>
            ➕ Agregar producto
        </h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- CATEGORÍA -->

            <div class="campo">

                <label for="categoria">
                    Categoría
                </label>


                <select
                    name="categoria"
                    id="categoria"
                    required
                >

                    <option value="">
                        Seleccionar categoría
                    </option>


                    <option value="Libros">
                        📚 Libros
                    </option>


                    <option value="Cuadernos">
                        📓 Cuadernos
                    </option>


                    <option value="Mochilas">
                        🎒 Mochilas
                    </option>


                    <option value="Cartucheras">
                        ✏️ Cartucheras
                    </option>

                </select>

            </div>


            <!-- PRODUCTO -->

            <div class="campo">

                <label for="nombre">
                    Producto
                </label>


                <input
                    type="text"
                    name="nombre"
                    id="nombre"
                    placeholder="Ej: Agenda escolar 2027"
                    required
                >


                <small>
                    Podés escribir cualquier producto nuevo.
                </small>

            </div>


            <!-- AUTOR -->

            <div class="campo">

                <label for="autor">
                    Autor
                </label>


                <input
                    type="text"
                    name="autor"
                    id="autor"
                    placeholder="Ej: El Estudiante"
                >


                <small>
                    Para libros podés colocar el autor.
                    Para útiles podés usar "El Estudiante".
                </small>

            </div>


            <!-- PRECIO -->

            <div class="campo">

                <label for="precio">
                    Precio
                </label>


                <input
                    type="number"
                    name="precio"
                    id="precio"
                    min="0"
                    step="0.01"
                    placeholder="Ej: 3500"
                    required
                >

            </div>


            <!-- DESCUENTO -->

            <div class="campo">

                <label for="descuento">
                    🔥 Descuento (%)
                </label>


                <input
                    type="number"
                    name="descuento"
                    id="descuento"
                    min="0"
                    max="100"
                    step="0.01"
                    value="0"
                    required
                >


                <small>
                    Colocá 0 si el producto no tiene promoción.
                </small>

            </div>


            <!-- PRECIO PROMOCIONAL -->

            <div
                id="precioPromocional"
                class="precio-promocional-admin"
            >

                Precio final: $0,00

            </div>


            <!-- STOCK -->

            <div class="campo">

                <label for="stock">
                    Stock
                </label>


                <input
                    type="number"
                    name="stock"
                    id="stock"
                    min="0"
                    value="10"
                    required
                >

            </div>


            <!-- IMAGEN -->

            <div class="campo">

                <label for="imagen">
                    Imagen
                </label>


                <input
                    type="file"
                    name="imagen"
                    id="imagen"
                    accept=".jpg,.jpeg,.png,.webp"
                >


                <small>
                    Si el producto no tiene una imagen automática,
                    podés subirla desde tu computadora.
                </small>

            </div>


            <!-- VISTA PREVIA -->

            <div class="vista-previa">

                <p>
                    Vista previa:
                </p>


                <img
                    id="vistaImagen"
                    src=""
                    alt="Vista previa"
                    class="imagen-preview"
                    style="display:none;"
                >


                <div
                    id="sinImagen"
                    class="sin-imagen"
                >

                    📷 Seleccioná una imagen

                </div>

            </div>


            <!-- BOTÓN -->

            <button
                type="submit"
                name="agregar"
                class="btn-agregar"
            >

                ➕ Agregar producto

            </button>


        </form>

    </section>


    <!-- =====================================================
         PRODUCTOS REGISTRADOS
    ===================================================== -->

    <section class="productos-registrados">


        <h2>
            📦 Productos registrados
        </h2>


        <div class="tabla-responsive">


            <table>


                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Imagen</th>

                        <th>Producto</th>

                        <th>Autor</th>

                        <th>Categoría</th>

                        <th>Precio</th>

                        <th>Promoción</th>

                        <th>Precio final</th>

                        <th>Stock</th>

                        <th>Acciones</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                $consulta = "
                    SELECT *
                    FROM productos
                    ORDER BY id DESC
                ";


                $resultado =
                    $conexion->query(
                        $consulta
                    );


                if (
                    $resultado &&
                    $resultado->num_rows > 0
                ) {


                    while (
                        $producto =
                        $resultado->fetch_assoc()
                    ) {


                        /* =====================================
                           IMAGEN GUARDADA EN BASE DE DATOS
                        ===================================== */

                        $imagenProducto =
                            trim(
                                $producto["imagen"] ?? ""
                            );


                        $imagenProducto =
                            str_replace(
                                "\\",
                                "/",
                                $imagenProducto
                            );


                        $imagenProducto =
                            preg_replace(
                                '#^(\./)?img/#i',
                                "",
                                $imagenProducto
                            );


                        $imagenProducto =
                            ltrim(
                                $imagenProducto,
                                "/"
                            );


                        /* =====================================
                           RUTA FÍSICA
                        ===================================== */

                        $rutaImagen =
                            __DIR__ .
                            "/../../img/" .
                            $imagenProducto;


                        /* =====================================
                           SI NO HAY IMAGEN O NO EXISTE,
                           BUSCAR AUTOMÁTICAMENTE
                        ===================================== */

                        if (
                            empty($imagenProducto) ||
                            !file_exists($rutaImagen)
                        ) {


                            $nombreProducto =
                                trim(
                                    $producto["nombre"] ?? ""
                                );


                            if (
                                isset(
                                    $imagenesAutomaticas[
                                        $nombreProducto
                                    ]
                                )
                            ) {


                                $imagenAutomatica =
                                    $imagenesAutomaticas[
                                        $nombreProducto
                                    ];


                                $rutaAutomatica =
                                    __DIR__ .
                                    "/../../img/" .
                                    $imagenAutomatica;


                                if (
                                    file_exists(
                                        $rutaAutomatica
                                    )
                                ) {

                                    $imagenProducto =
                                        $imagenAutomatica;


                                    $rutaImagen =
                                        $rutaAutomatica;
                                }
                            }
                        }


                        /* =====================================
                           RUTA WEB
                        ===================================== */

                        $rutaWeb =
                            "../../img/" .
                            $imagenProducto;


                        /* =====================================
                           DESCUENTO
                        ===================================== */

                        $descuentoProducto =
                            floatval(
                                $producto["descuento"] ?? 0
                            );


                        if (
                            $descuentoProducto < 0
                        ) {

                            $descuentoProducto = 0;
                        }


                        if (
                            $descuentoProducto > 100
                        ) {

                            $descuentoProducto = 100;
                        }


                        /* =====================================
                           PRECIO ORIGINAL
                        ===================================== */

                        $precioOriginal =
                            floatval(
                                $producto["precio"]
                            );


                        /* =====================================
                           PRECIO FINAL
                        ===================================== */

                        $precioFinal =
                            $precioOriginal -
                            (
                                $precioOriginal *
                                $descuentoProducto /
                                100
                            );

                ?>


                    <tr>


                        <td>

                            <?php
                            echo (int)
                                $producto["id"];
                            ?>

                        </td>


                        <td>

                            <?php

                            if (
                                !empty(
                                    $imagenProducto
                                ) &&
                                file_exists(
                                    $rutaImagen
                                )
                            ) {

                            ?>

                                <img
                                    src="<?php
                                    echo htmlspecialchars(
                                        $rutaWeb
                                    );
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars(
                                        $producto["nombre"]
                                    );
                                    ?>"
                                    class="imagen-producto"
                                >

                            <?php

                            } else {

                            ?>

                                <div
                                    class="sin-imagen-tabla"
                                >

                                    📷 Sin imagen

                                </div>

                            <?php

                            }

                            ?>

                        </td>


                        <td>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $producto["nombre"]
                                );

                                ?>

                            </strong>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $producto["autor"]
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $producto["categoria"]
                            );

                            ?>

                        </td>


                        <td>

                            $

                            <?php

                            echo number_format(
                                $precioOriginal,
                                2,
                                ",",
                                "."
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            if (
                                $descuentoProducto > 0
                            ) {

                            ?>

                                <span
                                    class="badge-promocion"
                                >

                                    🔥

                                    <?php

                                    echo rtrim(
                                        rtrim(
                                            number_format(
                                                $descuentoProducto,
                                                2,
                                                ",",
                                                "."
                                            ),
                                            "0"
                                        ),
                                        ","
                                    );

                                    ?>%

                                </span>

                            <?php

                            } else {

                            ?>

                                <span
                                    class="sin-promocion"
                                >

                                    Sin promoción

                                </span>

                            <?php

                            }

                            ?>

                        </td>


                        <td>

                            <strong
                                class="precio-final-admin"
                            >

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

                        </td>


                        <td>

                            <?php

                            echo (int)
                                $producto["stock"];

                            ?>

                        </td>


                        <td>

                            <a
                                href="editar.php?id=<?php
                                echo (int)
                                    $producto["id"];
                                ?>"
                                class="btn-editar"
                            >

                                ✏️ Editar

                            </a>


                            <a
                                href="eliminar.php?id=<?php
                                echo (int)
                                    $producto["id"];
                                ?>"
                                class="btn-eliminar"
                                onclick="return confirm('¿Seguro que querés eliminar este producto?');"
                            >

                                🗑️ Eliminar

                            </a>

                        </td>


                    </tr>


                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="10">

                            No hay productos registrados.

                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </section>


</div>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>


/* =====================================================
   DATOS AUTOMÁTICOS
   SOLO COMPLETAN PRODUCTOS CONOCIDOS
===================================================== */

const productosAutomaticos = {

    "El Principito": {
        autor: "Antoine de Saint-Exupéry",
        precio: 2500,
        imagen: "el_principito.webp"
    },

    "Don Quijote": {
        autor: "Miguel de Cervantes",
        precio: 3200,
        imagen: "don_quijote.jpg"
    },

    "Cuentos Cortos": {
        autor: "El Estudiante",
        precio: 1800,
        imagen: "cuentoscortos.jpg"
    },

    "Cuaderno universitario": {
        autor: "El Estudiante",
        precio: 2500,
        imagen: "cuadernos.webp"
    },

    "Mochila escolar": {
        autor: "El Estudiante",
        precio: 15000,
        imagen: "mochila.webp"
    },

    "Cartuchera escolar": {
        autor: "El Estudiante",
        precio: 5000,
        imagen: "cartucheras.jpg"
    }

};


/* =====================================================
   ELEMENTOS
===================================================== */

const nombre =
    document.getElementById("nombre");

const autor =
    document.getElementById("autor");

const precio =
    document.getElementById("precio");

const descuento =
    document.getElementById("descuento");

const imagen =
    document.getElementById("imagen");

const vistaImagen =
    document.getElementById("vistaImagen");

const sinImagen =
    document.getElementById("sinImagen");

const precioPromocional =
    document.getElementById("precioPromocional");


/* =====================================================
   CALCULAR PRECIO PROMOCIONAL
===================================================== */

function calcularPrecioPromocional() {

    const precioOriginal =
        parseFloat(precio.value) || 0;

    let porcentaje =
        parseFloat(descuento.value) || 0;


    if (porcentaje < 0) {
        porcentaje = 0;
    }


    if (porcentaje > 100) {
        porcentaje = 100;
    }


    const precioFinal =
        precioOriginal -
        (
            precioOriginal *
            porcentaje /
            100
        );


    precioPromocional.textContent =
        "Precio final: $" +
        precioFinal.toLocaleString(
            "es-AR",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
}


/* =====================================================
   PRECIO
===================================================== */

precio.addEventListener(
    "input",
    calcularPrecioPromocional
);


/* =====================================================
   DESCUENTO
===================================================== */

descuento.addEventListener(
    "input",
    calcularPrecioPromocional
);


/* =====================================================
   PRODUCTO
===================================================== */

nombre.addEventListener(
    "input",
    function () {

        const nombreProducto =
            this.value.trim();


        const producto =
            productosAutomaticos[
                nombreProducto
            ];


        /*
         * Si es uno de los productos conocidos,
         * completamos los datos automáticamente.
         */

        if (producto) {

            autor.value =
                producto.autor;

            precio.value =
                producto.precio;

            calcularPrecioPromocional();


            /*
             * Solo mostramos la imagen automática
             * si el usuario todavía no seleccionó
             * una imagen manual.
             */

            if (!imagen.files.length) {

                const ruta =
                    "../../img/" +
                    producto.imagen;

                vistaImagen.src =
                    ruta;

                vistaImagen.style.display =
                    "block";

                sinImagen.style.display =
                    "none";


                vistaImagen.onerror =
                    function () {

                        vistaImagen.style.display =
                            "none";

                        sinImagen.style.display =
                            "flex";

                        sinImagen.textContent =
                            "📷 Imagen no encontrada";
                    };
            }

        } else {

            /*
             * Producto nuevo:
             * el usuario puede completar todo manualmente.
             */

            autor.readOnly = false;

        }

    }
);


/* =====================================================
   IMAGEN SUBIDA MANUALMENTE
===================================================== */

imagen.addEventListener(
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

                vistaImagen.src =
                    e.target.result;

                vistaImagen.style.display =
                    "block";

                sinImagen.style.display =
                    "none";

            };


        lector.readAsDataURL(
            archivo
        );

    }
);


/* =====================================================
   INICIO
===================================================== */

calcularPrecioPromocional();

</script>


</body>

</html>