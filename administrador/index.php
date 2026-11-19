<?php

include("config/bd.php");
include("template/cabecera.php");


// =========================
// TOTAL DE PRODUCTOS
// =========================

$totalProductos = 0;

$resultadoProductos = $conexion->query(
    "SELECT COUNT(*) AS total FROM productos"
);

if ($resultadoProductos) {
    $fila = $resultadoProductos->fetch_assoc();
    $totalProductos = $fila["total"];
}


// =========================
// TOTAL DE CONTACTOS
// =========================

$totalContactos = 0;

$resultadoContactos = $conexion->query(
    "SELECT COUNT(*) AS total FROM contactos"
);

if ($resultadoContactos) {
    $fila = $resultadoContactos->fetch_assoc();
    $totalContactos = $fila["total"];
}

?>

<!-- ENCABEZADO -->

<div class="admin-header">

    <h2>Panel de Administrador</h2>

    <p>
        Bienvenido al panel de administración de
        <strong>Librería El Estudiante</strong>.
    </p>

</div>


<!-- ESTADÍSTICAS -->

<div class="row mt-4">

    <!-- PRODUCTOS -->

    <div class="col-md-4 mb-4">

        <div class="card-stat productos-card">

            <div class="card-stat-icon">
                <i class="bi bi-box-seam"></i>
            </div>

            <div class="card-stat-info">

                <h5>Productos</h5>

                <h2>
                    <?php echo $totalProductos; ?>
                </h2>

                <p>
                    Total de productos cargados
                </p>

            </div>

        </div>

    </div>


    <!-- CONTACTOS -->

    <div class="col-md-4 mb-4">

        <div class="card-stat contactos-card">

            <div class="card-stat-icon">
                <i class="bi bi-envelope"></i>
            </div>

            <div class="card-stat-info">

                <h5>Contactos</h5>

                <h2>
                    <?php echo $totalContactos; ?>
                </h2>

                <p>
                    Mensajes recibidos
                </p>

            </div>

        </div>

    </div>

</div>


<!-- ADMINISTRACIÓN -->

<div class="card admin-menu mt-3">

    <div class="card-body">

        <h5>
            <i class="bi bi-gear"></i>
            Administración
        </h5>

        <p class="text-muted">
            Seleccioná una opción para administrar la tienda.
        </p>


        <div class="admin-buttons">

            <!-- PRODUCTOS -->

            <a
                href="seccion/productos.php"
                class="admin-button"
            >

                <i class="bi bi-box-seam"></i>

                <div>

                    <strong>
                        Administrar productos
                    </strong>

                    <small>
                        Agregar, modificar o eliminar productos.
                    </small>

                </div>

            </a>


            <!-- CONTACTOS -->

            <a
                href="seccion/contactos.php"
                class="admin-button"
            >

                <i class="bi bi-envelope"></i>

                <div>

                    <strong>
                        Ver contactos
                    </strong>

                    <small>
                        Consultar los mensajes recibidos.
                    </small>

                </div>

            </a>


            <!-- CERRAR SESIÓN -->

            <a
                href="cerrar.php"
                class="admin-button"
            >

                <i class="bi bi-box-arrow-right"></i>

                <div>

                    <strong>
                        Cerrar sesión
                    </strong>

                    <small>
                        Salir del panel de administración.
                    </small>

                </div>

            </a>

        </div>

    </div>

</div>


<?php

include("template/pie.php");

?>