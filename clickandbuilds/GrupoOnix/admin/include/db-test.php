<?php
//Esto solo es una prueba de conexion al servidor de XAMPP
try {
    $conn = new PDO("mysql:host=localhost;dbname=onix;charset=utf8", "root", "");
    echo "✅ Connection successful!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
