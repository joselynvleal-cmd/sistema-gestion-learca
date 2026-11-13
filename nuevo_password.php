<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['email_recuperacion']) || !isset($_SESSION['codigo_verificado'])) {
    header("Location: recuperar.php");
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Encriptar la clave con password_hash 
    $nueva_password = password_hash($_POST['nueva_password'], PASSWORD_DEFAULT);
    $email = $_SESSION['email_recuperacion'];

    // Actualizar clave y limpiar los códigos temporales
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, codigo_recuperacion = NULL, token_expiracion = NULL WHERE email = ?");
    $stmt->execute([$nueva_password, $email]);

    // Limpiar sesiones
    unset($_SESSION['email_recuperacion']);
    unset($_SESSION['codigo_verificado']);

    $mensaje = "¡Contraseña actualizada con éxito!";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - Learca</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-split-container" style="justify-content: center; width: 100vw;">
        <div class="login-card-side" style="width: 100%;">
            <div class="login-form-box">
                <div class="login-header">
                    <h2>Nueva Contraseña</h2>
                    <p>Establece tu nueva clave de acceso al sistema</p>
                </div>

                <?php if (!empty($mensaje)): ?>
                    <div style="background: #D1FAE5; color: #065F46; padding: 0.9rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $mensaje; ?>
                        <br><br><a href="index.php" style="background: #0A3D78; color: #FFFFFF; padding: 0.5rem 1rem; border-radius: 6px; font-weight: bold; text-decoration: none; display: inline-block;">Ir a Iniciar Sesión</a>
                    </div>
                <?php else: ?>
                    <form action="nuevo_password.php" method="POST">
                        <div class="input-group">
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock input-icon-left"></i>
                                <input type="password" name="nueva_password" placeholder="Nueva contraseña" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-login-blue">Guardar Cambios</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>