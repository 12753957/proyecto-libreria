<?php

include("../config/bd.php");


/* ID*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: productos.php");
    exit;

}

$id = intval($_GET["id"]);


/* =====================================================
   BUSCAR PRODUCTO E IMAGEN
===================================================== */

$sql = "SELECT id, imagen FROM productos WHERE id = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    echo "Error al preparar la consulta.";
    exit;

}

$stmt->bind_param("i", $id);

$stmt->execute();

$resultado = $stmt->get_result();


/*VERIFICAR QUE EXISTA*/

if ($resultado->num_rows == 0) {

    $stmt->close();

    echo "
        <script>
            alert('El producto no existe.');
            window.location.href = 'productos.php';
        </script>
    ";

    exit;

}


$producto = $resultado->fetch_assoc();

$imagen = $producto["imagen"];

$stmt->close();


/*INICIAR TRANSACCIÓN */

$conexion->begin_transaction();


try {


    /*ELIMINAR REGISTROS DEL CARRITO*/

    $sql = "DELETE FROM detalle_carrito WHERE producto_id = ?";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("No se pudo preparar la eliminación del carrito.");
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception("No se pudieron eliminar los registros del carrito.");
    }

    $stmt->close();


    /* ELIMINAR PRODUCTO*/

    $sql = "DELETE FROM productos WHERE id = ?";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        throw new Exception("No se pudo preparar la eliminación del producto.");
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        throw new Exception("No se pudo eliminar el producto.");
    }

    $stmt->close();


    /* =================================================
       CONFIRMAR CAMBIOS
    ================================================= */

    $conexion->commit();


    /* =================================================
       ELIMINAR IMAGEN
    ================================================= */

    if (!empty($imagen)) {

        /*
         * basename evita que se intente borrar
         * archivos fuera de la carpeta img.
         */

        $nombreImagen = basename($imagen);

        $rutaImagen = __DIR__ . "/../../img/" . $nombreImagen;

        if (file_exists($rutaImagen)) {

            unlink($rutaImagen);

        }

    }


    /* =================================================
       MENSAJE DE ÉXITO
    ================================================= */

    echo "
        <script>

            alert('Producto eliminado correctamente');

            window.location.href = 'productos.php';

        </script>
    ";

    exit;


} catch (Exception $e) {


    /* =================================================
       DESHACER CAMBIOS SI OCURRE UN ERROR
    ================================================= */

    $conexion->rollback();


    echo "
        <script>

            alert('No se pudo eliminar el producto.');

            window.location.href = 'productos.php';

        </script>
    ";

    exit;

}

?>