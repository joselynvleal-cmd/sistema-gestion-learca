<?php
session_start();
require_once 'conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Verificar si el correo existe en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Generar código de 6 dígitos y expiración en 15 minutos
        $codigo = rand(100000, 999999);
        $expiracion = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        // Guardar en la base de datos
        $update = $pdo->prepare("UPDATE usuarios SET codigo_recuperacion = ?, token_expiracion = ? WHERE email = ?");
        $update->execute([$codigo, $expiracion, $email]);

        $_SESSION['email_recuperacion'] = $email;

        // Configurar PHPMailer para enviar a Gmail
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'joselynvleal@gmail.com'; 
            $mail->Password   = 'iwsyrljbfbbvyhow'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('joselynvleal@gmail.com', 'Multiservicios Learca, C.A.');
            $mail->addAddress($email, $usuario['username']);

            $mail->isHTML(true);
            $mail->Subject = 'Codigo de Verificacion - Multiservicios Learca, C.A.';
            $mail->Body    = '<div style="font-family: Arial, sans-serif; padding: 20px; color: #1D2D44; background: #F8F8F6; border-radius: 8px;">' .
                             '<h2 style="color: #0E5AAC;">Multiservicios Learca, C.A.</h2>' .
                             '<p>Hola, <strong>' . htmlspecialchars($usuario['username']) . '</strong>.</p>' .
                             '<p>Has solicitado restablecer tu contraseña. Tu código de verificación es:</p>' .
                             '<div style="background: #0A3D78; color: #FFFFFF; font-size: 26px; font-weight: bold; padding: 12px 20px; width: fit-content; letter-spacing: 6px; border-radius: 6px; margin: 15px 0;">' . $codigo . '</div>' .
                             '<p style="color: #718096; font-size: 12px;">Este código expira en 15 minutos.</p>' .
                             '</div>';
            $mail->CharSet = 'UTF-8';

            $mail->send();
            
            // Redirigir a la pantalla para ingresar el código
            header("Location: verificar_codigo.php");
            exit();

        } catch (Exception $e) {
            $error = "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        $error = "El correo electrónico no se encuentra registrado en el sistema.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - Learca</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-split-container" style="justify-content: center; width: 100vw;">
        <div class="login-card-side" style="width: 100%;">
            <div class="login-form-box">
                <div class="login-header">
                    <h2>Recuperar Contraseña</h2>
                    <p>Ingresa tu correo registrado para recibir el código</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div style="background: #FEE2E2; color: #991B1B; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="recuperar.php" method="POST">
                    <div class="input-group">
                        <div class="input-with-icon">
                            <i class="fa-solid fa-envelope input-icon-left"></i>
                            <input type="email" name="email" placeholder="correo@dominio.com" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login-blue">
                        Enviar Código <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="index.php" style="color: var(--primary-blue); font-size: 0.85rem; text-decoration: none;">
                        <i class="fa-solid fa-arrow-left"></i> Volver al Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>