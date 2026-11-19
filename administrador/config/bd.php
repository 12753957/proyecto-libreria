<?php
$host = "localhost";
$usuario = "root";
$contraseña = "";
$bd = "libreria";

$conexion = new mysqli($host, $usuario, $contraseña, $bd);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>