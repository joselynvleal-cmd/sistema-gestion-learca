<?php
session_start();
require_once 'conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y limpiar los datos del formulario 
    $identificador = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($identificador) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    try {
        // Consulta uniendo la tabla usuarios y roles permitiendo buscar por username O email
        $sql = "SELECT u.id_usuario, u.username, u.email, u.password, r.nombre_rol 
                FROM usuarios u 
                LEFT JOIN roles r ON u.id_rol = r.id_rol 
                WHERE u.username = :identificador OR u.email = :identificador 
                LIMIT 1";
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validar si el usuario existe y la contraseña coincide
        if ($usuario && password_verify($password, $usuario['password'])) {
            
            // Regenerar ID de sesión por seguridad 
            session_regenerate_id(true);

            // Registrar las variables de sesión corporativas necesarias
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['nombre_rol'] = $usuario['nombre_rol'] ?? 'Operativo';

            // Redirigir al dashboard corporativo del sistema de gestión
            header("Location: dashboard.php");
            exit();
            
        } else {
            // Credenciales incorrectas
            header("Location: index.php?error=invalid");
            exit();
        }

    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        error_log("Error en login: " . $e->getMessage());
        header("Location: index.php?error=db");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>