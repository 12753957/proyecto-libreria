<?php

session_start();

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

    <title>Método de pago - Librería El Estudiante</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

    <header>

        <div class="logo">
            📚 El Estudiante
        </div>

        <nav>

            <a href="index.php">Inicio</a>

            <a href="productos.php">Productos</a>

            <a href="carrito.php">Carrito</a>

            <a href="contacto.php">Contacto</a>

            <a href="nosotros.php">Nosotros</a>

            <a href="servicios.php">Servicios</a>

            <a href="admin.php">Administración</a>

        </nav>

    </header>


    <main class="pagina-pago">

        <div class="pago-contenedor">

            <h1>Método de pago</h1>

            <p>
                Seleccioná cómo querés realizar el pago de tu compra.
            </p>


           <form action="datos_clientes.php" method="POST">


                <label class="metodo">

                    <input
                        type="radio"
                        name="metodo_pago"
                        value="efectivo"
                        required
                    >

                    <span class="icono-pago">💵</span>

                    <span>
                        <strong>Efectivo</strong>

                        <small>
                            Pago en efectivo al retirar el pedido.
                        </small>
                    </span>

                </label>


                <label class="metodo">

                    <input
                        type="radio"
                        name="metodo_pago"
                        value="tarjeta"
                    >

                    <span class="icono-pago">💳</span>

                    <span>
                        <strong>Tarjeta de crédito o débito</strong>

                        <small>
                            Simulación de pago con tarjeta.
                        </small>
                    </span>

                </label>


                <label class="metodo">

                    <input
                        type="radio"
                        name="metodo_pago"
                        value="mercado_pago"
                    >

                    <span class="icono-pago">📱</span>

                    <span>
                        <strong>Mercado Pago</strong>

                        <small>
                            Simulación de pago mediante Mercado Pago.
                        </small>
                    </span>

                </label>


                <label class="metodo">

                    <input
                        type="radio"
                        name="metodo_pago"
                        value="transferencia"
                    >

                    <span class="icono-pago">🏦</span>

                    <span>
                        <strong>Transferencia bancaria</strong>

                        <small>
                            Se muestran los datos para realizar la transferencia.
                        </small>
                    </span>

                </label>


                <button
                    type="submit"
                    class="btn-confirmar"
                >

                    Confirmar método de pago

                </button>


            </form>


            <a
                href="carrito.php"
                class="volver-carrito"
            >

                ← Volver al carrito

            </a>

        </div>

    </main>

</body>

</html>