<?php
$host = 'db-service';  // Nombre del servicio de la base de datos en Kubernetes
$dbname = 'tareas';    // Nombre de la base de datos que has configurado en MariaDB
$username = 'root';    // Usuario de la base de datos
$password = 'tareas';  // Contraseña de la base de datos

// Crear la conexión
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
