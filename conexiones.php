<?php
/////////////////////////////////LIBRERIAS EN LOCAL /////////////////////////
function conectarse_escuela() {
    $user = "root";
    $pass = "";
    $server = "localhost";
    $db = "escuela";

    $coneccion = new mysqli($server, $user, $pass, $db);

    if ($coneccion->connect_error) {
        die("Fallo la conexión con el servidor: " . $coneccion->connect_error);
    }

    // Establecer el charset si lo necesitas
    $coneccion->set_charset("utf8");

    return $coneccion;
}  //******************************************

?>

